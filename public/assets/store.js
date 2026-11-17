'use strict';

const checkout = document.querySelector('.he-checkout');
const form = checkout.querySelector('form');
const feedback = document.querySelector('#checkout-status');
const submit = form.querySelector('button[type="submit"]');
const viewer = document.querySelector('.he-image-viewer');
const money = new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 });
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
