#!/usr/bin/env python3
"""Isolated Cashfree checkout integration test: fake gateway + disposable MySQL data.
No Cashfree credentials, no live requests, no real charges.
"""
import http.cookiejar
import base64
import hashlib
import hmac
import json
import os
import pathlib
import re
import secrets
import shutil
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parents[1]
GATEWAY_ROUTER = r'''<?php
$dir = __DIR__ . '/records';
@mkdir($dir, 0700, true);
header('Content-Type: application/json');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
if ($path === '/pg/orders' && $method === 'POST') {
    if (file_exists(__DIR__ . '/fail-next')) {
        unlink(__DIR__ . '/fail-next'); http_response_code(503);
        echo json_encode(['code' => 'temporary_unavailable']); exit;
    }
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['order_id'] ?? '';
    if (!preg_match('/^HV-[a-f0-9]{24}$/D', $id)) { http_response_code(400); echo json_encode(['code'=>'invalid_order']); exit; }
    $file = $dir . '/' . $id . '.json';
    if (is_file($file)) { http_response_code(409); echo json_encode(['code'=>'duplicate_order']); exit; }
    $record = ['order_id'=>$id, 'order_amount'=>$input['order_amount'], 'order_currency'=>'INR',
        'order_status'=>'ACTIVE', 'payment_session_id'=>'session_test_' . $id,
        'return_url'=>$input['order_meta']['return_url'],
        'notify_url'=>$input['order_meta']['notify_url'] ?? null];
    file_put_contents($file, json_encode($record)); http_response_code(201);
    echo json_encode($record); exit;
}
if (preg_match('#^/pg/orders/(HV-[a-f0-9]{24})$#D', $path, $m) && $method === 'GET') {
    $file = $dir . '/' . $m[1] . '.json';
    if (!is_file($file)) { http_response_code(404); echo json_encode(['code'=>'order_not_found']); exit; }
    echo file_get_contents($file); exit;
}
http_response_code(404); echo json_encode(['code'=>'not_found']);
'''


def check(condition, message):
    if not condition:
        raise AssertionError(message)
    print('PASS:', message)


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def request(opener, url, form=None):
    payload = urllib.parse.urlencode(form).encode() if form is not None else None
    req = urllib.request.Request(url, data=payload, method='POST' if form is not None else 'GET')
    try:
        with opener.open(req, timeout=15) as response:
            return response.status, dict(response.headers), response.read().decode()
    except urllib.error.HTTPError as err:
        return err.code, dict(err.headers), err.read().decode()


def post_raw(opener, url, payload, headers):
    req = urllib.request.Request(url, data=payload, headers=headers, method='POST')
    try:
        with opener.open(req, timeout=15) as response:
            return response.status, response.read().decode()
    except urllib.error.HTTPError as err:
        return err.code, err.read().decode()


def wait_port(port):
    for _ in range(80):
        try:
            with socket.create_connection(('127.0.0.1', port), timeout=.3):
                return
        except OSError:
            time.sleep(.1)
    raise RuntimeError('Local test HTTP server not responding on port ' + str(port))


def main():
    with tempfile.TemporaryDirectory(prefix='hv-cashfree-smoke-') as td:
        root = pathlib.Path(td)
        shutil.copytree(ROOT / 'public', root / 'public')
        shutil.copytree(ROOT / 'private', root / 'private')
        (root / 'private' / 'data').mkdir(exist_ok=True)
        (root / 'private' / 'data' / 'cashfree.json').write_text(json.dumps({
            'environment': 'sandbox', 'app_id': 'TEST_APP_ID', 'secret_key': 'TEST_SECRET'}))
        (root / 'private' / 'database.json').write_text(json.dumps({
            'host': '127.0.0.1', 'database': 'homevexa_test', 'username': 'root', 'password': 'testpass'}))
        cashfree = root / 'private' / 'cashfree.php'
        src = cashfree.read_text()
        old = 'https://sandbox.cashfree.com/pg/'
        check(old in src, 'Production code has expected sandbox endpoint')
        cashfree.write_text(src.replace(old, 'http://127.0.0.1:18081/pg/'))  # temp copy only
        mock = root / 'mock'
        mock.mkdir()
        (mock / 'router.php').write_text(GATEWAY_ROUTER)
        env = dict(os.environ, PHP_CLI_SERVER_WORKERS='1')
        gateway = subprocess.Popen(['php', '-S', '127.0.0.1:18081', '-t', str(mock), str(mock / 'router.php')],
            stdout=subprocess.DEVNULL, stderr=subprocess.PIPE, env=env)
        app = subprocess.Popen(['php', '-S', '127.0.0.1:18080', '-t', str(root / 'public')],
            stdout=subprocess.DEVNULL, stderr=subprocess.PIPE, env=env)
        try:
            wait_port(18080)
            wait_port(18081)
            opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()), NoRedirect())
            base = 'http://127.0.0.1:18080/'
            status, _, html = request(opener, base)
            check(status == 200 and 'Cashfree TEST payment' in html and 'no real money' in html,
                'Sandbox mode clearly labeled on checkout')
            token = re.search(r'name="token" value="([0-9a-f]+)"', html).group(1)
            request_id = re.search(r'name="request_id" value="([0-9a-f]+)"', html).group(1)
            form = dict(token=token, request_id=request_id, name='Integration Test', phone='9876543210',
                address='Test Street', city='Noida', pincode='201301', payment='cashfree', email='')
            status, _, raw = request(opener, base + 'order.php', form)
            data = json.loads(raw)
            check(status == 200 and data['status'] == 'test_pending' and
                data['cashfree_mode'] == 'sandbox' and data['payment_session_id'].startswith('session_test_'),
                'Sandbox payment creation returns correct session and test status')
            order_id = data['order_id']
            record_path = mock / 'records' / (order_id + '.json')
            check(record_path.is_file(), 'Gateway received a create-order request')
            status, _, raw = request(opener, base + 'order.php', form)
            check(status == 200 and json.loads(raw)['order_id'] == order_id,
                'Identical checkout retry reuses existing order and gateway session')
            changed = dict(form, phone='9123456789')
            status, _, raw = request(opener, base + 'order.php', changed)
            check(status == 409 and json.loads(raw)['code'] == 'checkout_changed',
                'Changed checkout cannot reuse another order')
            bad = dict(form, token='0' * len(token))
            status, _, raw = request(opener, base + 'order.php', bad)
            check(status == 403, 'CSRF token tampering rejected')
            record = json.loads(record_path.read_text())
            return_url = record['return_url']
            status, _, page = request(opener, return_url.replace('https://homevexa.online/', base))
            check(status == 200 and 'not confirmed yet' in page,
                'Pending payment does not confirm order')
            record['order_status'] = 'PAID'
            record['order_amount'] = 1
            record_path.write_text(json.dumps(record))
            status, _, page = request(opener, return_url.replace('https://homevexa.online/', base))
            check(status == 200 and 'not confirmed yet' in page,
                'Amount mismatch cannot mark order paid')
            record['order_amount'] = 239
            record_path.write_text(json.dumps(record))
            status, headers, _ = request(opener, return_url.replace('https://homevexa.online/', base))
            check(status == 303 and 'thank-you.php' in headers.get('Location',''),
                'Matching server-verified paid status redirects to confirmation')
            status, _, html = request(opener, base + headers['Location'].replace('./', ''))
            check(status == 200 and 'Test payment completed' in html and
                'NOT a real order' in html and 'Estimated delivery' not in html,
                'Test confirmation never promises real shipment')
            status, _, raw = request(opener, base + 'order.php', form)
            check(status == 409 and json.loads(raw)['code'] == 'checkout_changed',
                'Paid test order cannot be paid again with old checkout ID')
            form2 = dict(form, request_id=secrets.token_hex(16))
            (mock / 'fail-next').write_text('1')
            status, _, raw = request(opener, base + 'order.php', form2)
            check(status == 503 and json.loads(raw)['diagnostic']['stage'] == 'cashfree_create',
                'Gateway outage reports safe checkout diagnostic')
            status, _, raw = request(opener, base + 'order.php', form2)
            check(status == 200 and json.loads(raw)['status'] == 'test_pending',
                'Retry recreates gateway order after prior creation failed')
            retry_id = json.loads(raw)['order_id']
            retry_record_path = mock / 'records' / (retry_id + '.json')
            retry_record = json.loads(retry_record_path.read_text())
            check(retry_record['notify_url'] == 'https://homevexa.online/cashfree-webhook.php',
                'New gateway orders include signed webhook callback URL')
            pending_url = base + 'thank-you.php?' + urllib.parse.urlsplit(retry_record['return_url']).query
            status, _, _ = request(opener, pending_url)
            check(status == 404, 'Pending order cannot access paid confirmation')
            retry_record['order_status'] = 'PAID'
            retry_record_path.write_text(json.dumps(retry_record))
            event = {'type': 'PAYMENT_SUCCESS_WEBHOOK',
                'data': {'order': {'order_id': retry_id}, 'payment': {'payment_status': 'SUCCESS'}}}
            event_bytes = json.dumps(event, separators=(',', ':')).encode()
            timestamp = str(int(time.time()))
            good_signature = base64.b64encode(hmac.new(
                b'TEST_SECRET', timestamp.encode() + event_bytes, hashlib.sha256).digest()).decode()
            url = base + 'cashfree-webhook.php'
            status, _ = post_raw(opener, url, event_bytes, {
                'Content-Type': 'application/json', 'x-webhook-timestamp': timestamp,
                'x-webhook-signature': 'invalid'})
            check(status == 401, 'Forged webhook signature rejected')
            status, _, _ = request(opener, pending_url)
            check(status == 404, 'Forged webhook cannot mark order paid')
            headers = {'Content-Type': 'application/json', 'x-webhook-timestamp': timestamp,
                'x-webhook-signature': good_signature}
            status, _ = post_raw(opener, url, event_bytes, headers)
            check(status == 200, 'Signed webhook verifies provider and updates order')
            status, _, html = request(opener, pending_url)
            check(status == 200 and 'Test payment completed' in html,
                'Webhook handles browser-not-returned payment')
            status, _ = post_raw(opener, url, event_bytes, headers)
            check(status == 200, 'Repeated signed webhook is idempotent')
            form3 = dict(form, request_id=secrets.token_hex(16), payment='cod')
            status, _, raw = request(opener, base + 'order.php', form3)
            check(status == 200 and json.loads(raw)['status'] == 'cod_confirmed',
                'Cash on delivery checkout still works')
            print('\nAll isolated mocked integration tests passed. REAL Cashfree sandbox not tested.')
        finally:
            for process in (app, gateway):
                process.terminate()
                try:
                    process.communicate(timeout=5)
                except subprocess.TimeoutExpired:
                    process.kill()
                    process.communicate()


if __name__ == '__main__':
    main()
