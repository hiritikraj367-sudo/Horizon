'use strict';

const checkout = document.querySelector('.he-checkout');
const form = checkout.querySelector('form');
const feedback = document.querySelector('#checkout-status');
const submit = form.querySelector('button[type="submit"]');
const viewer = document.querySelector('.he-image-viewer');
let completed = false;

document.querySelectorAll('button[data-buy]').forEach(button => {
  button.addEventListener('click', () => checkout.showModal());
});

for (const [dialog, closeSelector] of [[checkout, '.he-checkout-close'], [viewer, '.he-viewer-close']]) {
  dialog.querySelector(closeSelector).addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', event => {
    if (event.target === dialog) dialog.close();
  });
}

const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');

function connectCarousel(container, previous, next, visibleCount, onChange) {
  const slides = [...container.querySelectorAll('.swiper-slide')];
  const lastIndex = slides.length - visibleCount;
  let index = 0;

  function goTo(target, smooth = true) {
    const nextIndex = (target + lastIndex + 1) % (lastIndex + 1);
    const wraps = target !== nextIndex;
    const animate = smooth && !wraps && !reducedMotion.matches;
    container.scrollTo({ left: slides[nextIndex].offsetLeft, behavior: animate ? 'smooth' : 'instant' });
    if (!animate) updateIndex();
  }

  function updateIndex() {
    const stride = slides[1].offsetLeft - slides[0].offsetLeft;
    if (!stride) return;
    const nextIndex = Math.max(0, Math.min(lastIndex, Math.round(container.scrollLeft / stride)));
    if (index === nextIndex) return;
    index = nextIndex;
    onChange(index);
  }

  container.addEventListener('scroll', updateIndex, { passive: true });
  previous.addEventListener('click', () => goTo(index - 1));
  next.addEventListener('click', () => goTo(index + 1));
  return goTo;
}

const galleryTrack = document.querySelector('.he-gallery-swiper');
const gallerySlides = [...galleryTrack.querySelectorAll('.swiper-slide')];
const thumbnails = [...document.querySelectorAll('.he-thumbnail')];
const galleryCount = document.querySelector('.he-image-count');
const viewerCount = viewer.querySelector('.he-viewer-count');
const viewerTrack = viewer.querySelector('.he-viewer-swiper');
const viewerSlides = viewerTrack.querySelector('.swiper-wrapper');
const [previousReview, nextReview] = document.querySelectorAll('.he-review-nav-button');
const reviewTrack = document.querySelector('.he-reviews-swiper');
const reviewStatus = document.querySelector('.he-review-pair-status');
const reviewCount = reviewTrack.querySelectorAll('.swiper-slide').length;
let viewerIndex = 0;

for (const slide of gallerySlides) {
  const viewerSlide = document.createElement('div');
  viewerSlide.className = 'swiper-slide';
  const image = slide.querySelector('img').cloneNode();
  image.className = 'enlarged-photo';
  image.removeAttribute('style');
  viewerSlide.append(image);
  viewerSlides.append(viewerSlide);
}

function photoCount(index) {
  return String(index + 1).padStart(2, '0') + ' / ' + String(gallerySlides.length).padStart(2, '0');
}

const goToPhoto = connectCarousel(galleryTrack, document.querySelector('.he-previous'), document.querySelector('.he-next'), 1, index => {
  thumbnails.forEach((button, number) => {
    const active = number === index;
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', String(active));
  });
  galleryCount.textContent = photoCount(index);
});

const goToViewerPhoto = connectCarousel(viewerTrack, viewer.querySelector('.he-viewer-step--previous'), viewer.querySelector('.he-viewer-step--next'), 1, index => {
  viewerIndex = index;
  viewerCount.textContent = photoCount(index);
});

gallerySlides.forEach((slide, index) => {
  thumbnails[index].addEventListener('click', () => goToPhoto(index));
  slide.querySelector('button').addEventListener('click', () => {
    viewer.showModal();
    viewerIndex = index;
    viewerCount.textContent = photoCount(index);
    goToViewerPhoto(index, false);
  });
});

viewer.addEventListener('close', () => goToPhoto(viewerIndex, false));
viewer.addEventListener('keydown', event => {
  if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
    event.preventDefault();
    goToViewerPhoto(viewerIndex + (event.key === 'ArrowLeft' ? -1 : 1));
  }
});

connectCarousel(reviewTrack, previousReview, nextReview, 2, index => {
  reviewStatus.textContent = 'Review ' + (index + 1) + ' of ' + reviewCount;
});

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

const paymentInputs = [...form.querySelectorAll('input[name="payment"]')];
const paymentCaption = document.querySelector('#he-payment-caption');
function updatePaymentChoice() {
  const online = form.querySelector('input[name="payment"]:checked')?.value === 'cashfree';
  submit.firstChild.textContent = online ? 'Pay online ' : 'Place COD order ';
  const mode = form.querySelector('input[name="payment"][value="cashfree"]')?.dataset.cashfreeMode;
  paymentCaption.textContent = online ? (mode === 'sandbox' ? 'TEST PAYMENT — no real money' : 'Secure online payment') : 'Cash on delivery';
}
paymentInputs.forEach(input => input.addEventListener('change', updatePaymentChoice));

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
    if (!response.ok) {
      if (response.status === 409 && result.code === 'checkout_changed') {
        // A previously saved order is bound to the old request ID. Do not
        // silently create another order; require an explicit second click.
        const requestField = form.querySelector('input[name="request_id"]');
        const bytes = new Uint8Array(16);
        crypto.getRandomValues(bytes);
        requestField.value = [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
        throw new Error('Your checkout has been reset for a new attempt. Review your details and click the payment button again.');
      }
      const reason = result.diagnostic?.reason;
      throw new Error((result.error || 'Order could not be saved. Try again.') +
        (typeof reason === 'string' ? ' (' + reason + ')' : ''));
    }
    if (['payment_pending', 'test_pending'].includes(result.status) && result.payment_session_id) {
      if (typeof Cashfree !== 'function') throw new Error('Payment checkout could not load. Please try again.');
      if (!['sandbox', 'production'].includes(result.cashfree_mode)) throw new Error('Payment mode unavailable. Please retry.');
      if (result.cashfree_mode === 'sandbox') {
        feedback.textContent = 'Cashfree TEST MODE: no real money will be charged and no order will be shipped.';
        feedback.hidden = false;
      }
      const gateway = Cashfree({ mode: result.cashfree_mode });
      await gateway.checkout({ paymentSessionId: result.payment_session_id, redirectTarget: '_self' });
      return;
    }
    if (result.status !== 'cod_confirmed' || !result.order_id) {
      throw new Error('Order confirmation could not be retrieved. Please try again.');
    }
    completed = true;
    [...form.querySelectorAll('input, textarea, button')].forEach(element => { element.disabled = true; });
    const confirmation = new URLSearchParams({ order_id: result.order_id });
    if (result.confirmation_token) confirmation.set('access', result.confirmation_token);
    window.location.assign('./thank-you.php?' + confirmation);
  } catch (error) {
    feedback.textContent = error.message || 'Checkout could not complete. Try again.';
  } finally {
    submit.disabled = completed;
    feedback.hidden = feedback.textContent.trim() === '';
    if (!feedback.hidden) feedback.scrollIntoView({ block: 'nearest' });
  }
});

