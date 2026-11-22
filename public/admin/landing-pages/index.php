<?php
declare(strict_types=1);
require dirname(__DIR__, 2) . '/bootstrap.php';
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
session_name('homevexa_admin');
startCheckout();
$hash = adminPasswordHash();
$valid = password_get_info($hash)['algo'] !== null && hash_equals(hash('sha256',$hash), $_SESSION['auth_marker'] ?? '') && ($_SESSION['last_seen'] ?? 0) > time()-1800;
if (!$valid) { http_response_code(403); exit('Please sign in to /admin/ first, then open Landing Pages.'); }
$_SESSION['last_seen'] = time();
$_SESSION['landing_csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['landing_csrf'];
$db = orderDatabase();
$db->exec("CREATE TABLE IF NOT EXISTS landing_pages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(200) NOT NULL, status ENUM('draft','published') NOT NULL DEFAULT 'draft', data_json LONGTEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$error='';
$fields=['headline','subtitle','description','price','mrp','gallery','benefits','features','faqs','reviews','delivery','seo_title','seo_description'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!hash_equals($csrf,(string)($_POST['csrf']??''))) {http_response_code(403);exit('Invalid CSRF token');}
 $slug = strtolower(trim((string)($_POST['slug']??'')));
 $title = trim((string)($_POST['title']??''));
 $status = ($_POST['status']??'draft')==='published'?'published':'draft';
 if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D',$slug) || strlen($slug)>120 || $title==='' || strlen($title)>200) $error='Enter a title and a valid unique slug (lowercase letters, numbers and hyphens).';
 else {
  $data=[];
  foreach($fields as $field) $data[$field]=substr(trim((string)($_POST[$field]??'')),0,12000);
  if (!is_numeric($data['price']) || (float)$data['price']<=0 || !is_numeric($data['mrp'])) $error='Enter valid prices.';
  else {
   try {
    $stmt=$db->prepare('INSERT INTO landing_pages (slug,title,status,data_json) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),status=VALUES(status),data_json=VALUES(data_json)');
    $stmt->execute([$slug,$title,$status,json_encode($data,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)]);
    header('Location: ./?edit='.rawurlencode($slug).'&saved=1',true,303);exit;
   } catch(Throwable $e) {error_log('Landing save failed: '.get_class($e));$error='Could not save landing page.';}
  }
 }
}
$rows=$db->query('SELECT slug,title,status,updated_at FROM landing_pages ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$edit=trim((string)($_GET['edit']??''));
$current=null;
if($edit!==''){$stmt=$db->prepare('SELECT * FROM landing_pages WHERE slug=?');$stmt->execute([$edit]);$current=$stmt->fetch(PDO::FETCH_ASSOC)?:null;}
$data=$current?json_decode($current['data_json'],true):[];
function field(string $key,array $data):string{return escape((string)($data[$key]??''));}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Landing Pages | Home Vexa Admin</title><style>
body{font:16px system-ui;background:#f7f5ef;color:#24342e;margin:0;padding:24px}main{max-width:1050px;margin:auto}a{color:#285d48}header{display:flex;justify-content:space-between;align-items:center;gap:12px}section{background:white;border:1px solid #ddd;border-radius:14px;padding:22px;margin:18px 0}label{display:block;font-weight:600;margin:14px 0 5px}input,textarea,select{box-sizing:border-box;width:100%;padding:12px;border:1px solid #aaa;border-radius:7px;font:inherit}textarea{min-height:85px}button,.button{background:#285d48;color:white;border:0;border-radius:7px;padding:12px 18px;cursor:pointer;text-decoration:none;display:inline-block}table{width:100%;border-collapse:collapse}td,th{padding:10px;border-bottom:1px solid #ddd;text-align:left}small{color:#667}.error{color:#a00} .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px}
</style></head><body><main><header><div><a href="../">← Orders</a><h1>Landing Pages</h1></div><a class="button" href="./">+ Create Landing Page</a></header>
<?php if($error):?><p class="error"><?=escape($error)?></p><?php endif ?>
<?php if(isset($_GET['saved'])):?><p>Saved successfully.</p><?php endif ?>
<section><h2>Existing pages</h2><table><thead><tr><th>Name</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $row):?><tr><td><?=escape($row['title'])?><br><small>/landing-page/<?=escape($row['slug'])?></small></td><td><?=escape($row['status'])?></td><td><a href="?edit=<?=rawurlencode($row['slug'])?>">Edit</a> · <a target="_blank" rel="noopener" href="/landing-page/<?=rawurlencode($row['slug'])?>?preview=1">Preview</a></td></tr><?php endforeach ?>
</tbody></table></section>
<section><h2><?= $current?'Edit landing page':'Create landing page' ?></h2><p>Independent from the homepage. One unique slug per landing page.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=escape($csrf)?>">
<div class="grid"><div><label>Page title</label><input name="title" required maxlength="200" value="<?=escape((string)($current['title']??''))?>"></div><div><label>URL slug</label><input name="slug" required pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="120" value="<?=escape((string)($current['slug']??''))?>"></div></div>
<?php foreach($fields as $field):?><label><?=escape(ucwords(str_replace('_',' ',$field)))?></label>
<?php if(in_array($field,['price','mrp','headline','subtitle','seo_title'],true)):?><input name="<?=escape($field)?>" value="<?=field($field,$data)?>" <?=in_array($field,['price','mrp'],true)?'required':''?>>
<?php else:?><textarea name="<?=escape($field)?>"><?=field($field,$data)?></textarea><?php endif ?>
<?php endforeach ?>
<label>Visibility</label><select name="status"><option value="draft" <?=($current['status']??'')==='draft'?'selected':''?>>Draft</option><option value="published" <?=($current['status']??'')==='published'?'selected':''?>>Published</option></select>
<p><button type="submit">Save landing page</button></p></form></section></main></body></html>