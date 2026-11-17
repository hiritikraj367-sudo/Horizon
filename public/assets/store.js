'use strict';

const checkout = document.querySelector('.he-checkout');
const form = checkout.querySelector('form');
const feedback = document.querySelector('#checkout-status');
const submit = form.querySelector('button[type="submit"]');
const viewer = document.querySelector('.he-image-viewer');
const slides = [...document.querySelectorAll('.he-gallery-swiper .swiper-slide')];
const thumbnails = [...document.querySelectorAll('.he-thumbnail')];
const reviews = [...document.querySelectorAll('.he-reviews-swiper .swiper-slide')];
const showTwoReviews = matchMedia('(min-width: 720px)');
const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
let photoIndex = 0;
let reviewIndex = 0;
let completed = false;
let checkoutSdk;

document.querySelectorAll('button[data-buy]').forEach(button => {
  button.addEventListener('click', () => checkout.showModal());
});

for (const [dialog, closeSelector] of [[checkout, '.he-checkout-close'], [viewer, '.he-viewer-close']]) {
  dialog.querySelector(closeSelector).addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });
}

function renderPhoto(index) {
  photoIndex = (index + slides.length) % slides.length;
  slides.forEach((slide, number) => { slide.hidden = number !== photoIndex; });
  thumbnails.forEach((button, number) => {
    const active = number === photoIndex;
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', String(active));
  });
  const count = String(photoIndex + 1).padStart(2, '0') + ' / ' + String(slides.length).padStart(2, '0');
  document.querySelector('.he-image-count').textContent = count;
  viewer.querySelector('.he-viewer-count').textContent = count;
  const photo = slides[photoIndex].querySelector('img');
  const enlarged = viewer.querySelector('.enlarged-photo');
  enlarged.src = photo.src;
  enlarged.alt = photo.alt;
}

thumbnails.forEach((button, index) => button.addEventListener('click', () => renderPhoto(index)));
slides.forEach(slide => slide.querySelector('button').addEventListener('click', () => viewer.showModal()));
for (const [selector, direction] of [
  ['.he-previous', -1], ['.he-next', 1],
  ['.he-viewer-step--previous', -1], ['.he-viewer-step--next', 1],
]) {
  document.querySelector(selector).addEventListener('click', () => renderPhoto(photoIndex + direction));
}
viewer.addEventListener('keydown', event => {
  if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
    event.preventDefault();
    renderPhoto(photoIndex + (event.key === 'ArrowLeft' ? -1 : 1));
  }
});
for (const surface of [viewer.querySelector('.enlarged-photo'), document.querySelector('.he-gallery-swiper')]) {
  let startX;
  surface.addEventListener('touchstart', event => { startX = event.changedTouches[0].clientX; }, { passive: true });
  surface.addEventListener('touchend', event => {
    const distance = event.changedTouches[0].clientX - startX;
    if (Math.abs(distance) > 50) renderPhoto(photoIndex + (distance < 0 ? 1 : -1));
  }, { passive: true });
}
renderPhoto(0);

function renderReviews(index) {
  reviewIndex = (index + reviews.length) % reviews.length;
  reviews.forEach((review, number) => {
    const next = (reviewIndex + 1) % reviews.length;
    review.hidden = number !== reviewIndex && !(showTwoReviews.matches && number === next);
    review.style.order = number === reviewIndex ? 0 : 1;
  });
  document.querySelector('.he-review-pair-status').textContent = 'Review ' + (reviewIndex + 1) + ' of ' + reviews.length;
}
const [previousReview, nextReview] = document.querySelectorAll('.he-review-nav-button');
previousReview.addEventListener('click', () => renderReviews(reviewIndex - 1));
nextReview.addEventListener('click', () => renderReviews(reviewIndex + 1));
const reviewCarousel = document.querySelector('.he-reviews-swiper');
let reviewTouchStart;

reviewCarousel.addEventListener('touchstart', event => {
  reviewTouchStart = event.touches.length === 1 ? event.touches[0] : null;
}, { passive: true });

reviewCarousel.addEventListener('touchcancel', () => {
  reviewTouchStart = null;
}, { passive: true });

reviewCarousel.addEventListener('touchend', event => {
  const start = reviewTouchStart;
  reviewTouchStart = null;
  if (!start || event.touches.length > 0) return;

  const end = event.changedTouches[0];
  if (!end || end.identifier !== start.identifier) return;

  const horizontalDistance = end.clientX - start.clientX;
  const verticalDistance = end.clientY - start.clientY;
  if (Math.abs(horizontalDistance) >= 50 && Math.abs(horizontalDistance) > Math.abs(verticalDistance)) {
    renderReviews(reviewIndex + (horizontalDistance < 0 ? 1 : -1));
  }
}, { passive: true });

showTwoReviews.addEventListener('change', () => renderReviews(reviewIndex));
renderReviews(0);

const save = document.querySelector('.he-save');
function setSaved(saved) {
  save.classList.toggle('is-saved', saved);
  save.setAttribute('aria-pressed', String(saved));
}
try { setSaved(localStorage.getItem('home-vexa-saved') === '1'); } catch {}
save.addEventListener('click', () => {
  const saved = save.getAttribute('aria-pressed') !== 'true';
  setSaved(saved);
  try { localStorage.setItem('home-vexa-saved', saved ? '1' : '0'); } catch {}
});

function updatePayment() {
  const online = form.elements.payment.value === 'cashfree';
  const amount = online ? 200 : 239;
  const discount = Math.round((999 - amount) / 999 * 100) + '% OFF';
  const label = online ? 'online payment' : 'cash on delivery';
  const total = form.querySelector('.he-form-total');
  total.firstElementChild.textContent = 'Total · ' + label;
  total.querySelector('strong').textContent = money.format(amount);
  total.querySelector('.he-price-discount').textContent = discount;
  total.querySelector('[role="group"]').setAttribute('aria-label', 'MRP ₹999, ' + label + ' price ' + money.format(amount) + ', ' + discount);
  submit.firstChild.textContent = online ? ' Pay securely ' : ' Place COD order ';
}

function loadCashfree() {
  checkoutSdk ??= new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://sdk.cashfree.com/js/v3/cashfree.js';
    script.onload = () => window.Cashfree ? resolve(window.Cashfree) : reject(new Error('Payment page could not load. Try again.'));
    script.onerror = () => reject(new Error('Payment page could not load. Try again.'));
    document.head.append(script);
  }).catch(error => {
    checkoutSdk = undefined;
    throw error;
  });
  return checkoutSdk;
}

form.addEventListener('change', updatePayment);
form.addEventListener('submit', async event => {
  event.preventDefault();
  if (completed || submit.disabled) return;
  feedback.hidden = true;
  feedback.textContent = '';
  submit.disabled = true;
  try {
    const data = new FormData(form);
    const response = await fetch(form.action, { method: 'POST', body: data });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error || 'Order could not be saved. Try again.');
    if (result.session) {
      const Cashfree = await loadCashfree();
      const payment = await Cashfree({ mode: result.mode }).checkout({
        paymentSessionId: result.session,
        redirectTarget: '_self',
      });
      if (payment?.error) throw new Error('Payment could not start. Please try again.');
      return;
    }
    if (!['cod_confirmed', 'paid'].includes(result.status) || !result.order_id) {
      throw new Error('Order confirmation could not be retrieved. Please try again.');
    }
    completed = true;
    [...form.querySelectorAll('input, textarea, button')].forEach(element => { element.disabled = true; });
    window.location.assign('./thank-you.php?order_id=' + encodeURIComponent(result.order_id));
  } catch (error) {
    feedback.textContent = error.message || 'Checkout could not complete. Try again.';
  } finally {
    submit.disabled = completed;
    feedback.hidden = feedback.textContent.trim() === '';
    if (!feedback.hidden) feedback.scrollIntoView({ block: 'nearest' });
  }
});
