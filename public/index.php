<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
startCheckout();
header('Cache-Control: no-store');
$_SESSION['checkout_token'] ??= bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charSet="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="./assets/store.css?v=<?= filemtime(__DIR__ . '/assets/store.css') ?>" data-precedence="next"/>
<meta name="theme-color" content="#f7f5ef"/>
<meta name="color-scheme" content="light"/>
<title>Branded Silicone Scrubbers | Home Vexa</title>
<meta name="description" content="Home Vexa — Everyday Home Essentials. Five colorful reusable silicone dish scrubbers, ₹239 down from ₹999."/>
<meta name="generator" content="v0.app"/>
<link rel="icon" href="./assets/home-vexa-mark.svg" media="(prefers-color-scheme: light)"/>
<link rel="icon" href="./assets/home-vexa-mark.svg" media="(prefers-color-scheme: dark)"/>
<link rel="icon" href="./assets/home-vexa-mark.svg" type="image/svg+xml"/>
<link rel="apple-touch-icon" href="./assets/apple-icon.png"/>
<link rel="stylesheet" href="./assets/checkout.css?v=<?= filemtime(__DIR__ . '/assets/checkout.css') ?>">
<script src="./assets/store.js?v=<?= filemtime(__DIR__ . '/assets/store.js') ?>" defer>
</script>
</head>
<body class="antialiased">
<div hidden="">
</div>
<main class="he-page" id="home">
<div class="he-announcement">
<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles" aria-hidden="true">
<path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z">
</path>
<path d="M20 2v4">
</path>
<path d="M22 4h-4">
</path>
<circle cx="4" cy="20" r="2">
</circle>
</svg>
<span>LITTLE THINGS. LOVELY HOME.</span>
<span>·</span>
<span class="he-price-pair he-price-pair--micro" role="group" aria-label="MRP ₹999, price ₹239, 76% off.">
<del>₹999</del>
<strong>₹239</strong>
<span class="he-price-discount">76% OFF</span>
</span>
</div>
<header class="he-header">
<div class="he-brand-lockup">
<div class="he-brand-row">
<img alt="" loading="lazy" width="32" height="32" decoding="async" data-nimg="1" class="he-brand-symbol" style="color:transparent" src="./assets/home-vexa-mark.svg"/>
<a class="he-wordmark" href="#home" aria-label="Home Vexa home">Home Vexa</a>
</div>
<span class="he-brand-tagline">EVERYDAY HOME ESSENTIALS</span>
</div>
<nav class="he-nav" aria-label="Page navigation">
<a href="#product">The scrubber</a>
<a href="#story">Why you&#x27;ll love it</a>
<a href="#details">Details</a>
</nav>
<button class="he-header-buy" data-buy>Buy now <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true">
<path d="M7 7h10v10">
</path>
<path d="M7 17 17 7">
</path>
</svg>
</button>
</header>
<div class="he-breadcrumb">
<a href="#home">Home</a>
<span>/</span>
<span>Kitchen essentials</span>
<span>/</span>
<span>Silicone scrubber set</span>
</div>
<section class="he-product" id="product">
<div class="he-gallery" aria-label="Product gallery">
<div class="he-stage">
<div class="swiper he-gallery-swiper">
<div class="swiper-wrapper">
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Five-piece set">
<img alt="Five turquoise silicone scrubbers arranged on a cream stone plinth in a sunlit studio." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/01.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Bristle detail">
<img alt="Close-up of the soft, densely textured bristles on a turquoise silicone scrubber." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/02.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Gentle everyday clean">
<img alt="A hand using a turquoise silicone scrubber to wash a white plate at a bright kitchen sink." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/03.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Five in every set">
<img alt="Five matching turquoise scrubbers fanned across a warm ivory studio backdrop." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/04.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: How to Use">
<img alt="A turquoise scrubber washing a soapy plate beneath running water, with another scrubber hanging nearby to dry." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/05.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Sink-side storage">
<img alt="A turquoise scrubber hanging beside a soap dispenser at a warm cream kitchen sink." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/06.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: Flexible silicone">
<img alt="A turquoise scrubber shown at an angle to reveal its flexible silicone body and bristles." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/07.webp"/>
</button>
</div>
<div class="swiper-slide">
<button class="he-stage-image-button" type="button" aria-label="Enlarge product photo: A little color story">
<img alt="Five turquoise scrubbers arranged on ivory ceramic and sage stone display blocks." decoding="async" data-nimg="fill" class="he-main-image" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/08.webp"/>
</button>
</div>
</div>
</div>
<span class="he-image-count" aria-live="polite">01 <i>/</i> 08</span>
<button class="he-image-control he-previous" type="button" aria-label="Previous product photo">
<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left" aria-hidden="true">
<path d="m12 19-7-7 7-7">
</path>
<path d="M19 12H5">
</path>
</svg>
</button>
<button class="he-image-control he-next" type="button" aria-label="Next product photo">
<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
<span class="he-image-caption">Five little helpers, one happy sink.</span>
</div>
<div class="he-thumbnails" role="group" aria-label="Choose a product photo">
<button class="he-thumbnail is-active" aria-label="Show product photo 1: Five-piece set" aria-pressed="true">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/01.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 2: Bristle detail" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/02.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 3: Gentle everyday clean" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/03.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 4: Five in every set" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/04.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 5: How to Use" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/05.webp"/>
<span class="he-thumbnail-badge" aria-hidden="true">HOW TO</span>
</button>
<button class="he-thumbnail " aria-label="Show product photo 6: Sink-side storage" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/06.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 7: Flexible silicone" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/07.webp"/>
</button>
<button class="he-thumbnail " aria-label="Show product photo 8: A little color story" aria-pressed="false">
<img alt="" loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/08.webp"/>
</button>
<span class="he-gallery-note">MORE PHOTOS <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</span>
</div>
</div>
<div class="he-product-info">
<div class="he-product-topline">
<span>KITCHEN CARE <span class="he-dot">·</span> FIVE-PIECE SET</span>
<button class="he-save " aria-label="Save this product" aria-pressed="false">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart" aria-hidden="true">
<path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5">
</path>
</svg>
</button>
</div>
<p class="he-style-number">THE EVERYDAY CLEAN SET</p>
<h1>Branded Silicone <em>Scrubbers.</em>
</h1>
<p class="he-subtitle">by Home Vexa</p>
<div class="he-price-row">
<span class="he-price-pair he-price-pair--hero" role="group" aria-label="MRP ₹999, price ₹239, 76% off.">
<del>₹999</del>
<strong>₹239</strong>
<span class="he-price-discount">76% OFF</span>
</span>
<span class="he-price-note">Set of 5</span>
</div>
<p class="he-description">Meet your new sink-side favorites: five colorful silicone scrubbers with flexible bristles for your daily dishwashing ritual. Easy to rinse, ready to use again.</p>
<div class="he-highlights">
<div>
<span class="he-highlight-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles" aria-hidden="true">
<path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z">
</path>
<path d="M20 2v4">
</path>
<path d="M22 4h-4">
</path>
<circle cx="4" cy="20" r="2">
</circle>
</svg>
</span>
<span>Soft, flexible<br/>bristles</span>
</div>
<div>
<span class="he-highlight-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-leaf" aria-hidden="true">
<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z">
</path>
<path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12">
</path>
</svg>
</span>
<span>Rinse &amp;<br/>reuse</span>
</div>
<div>
<span class="he-highlight-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check" aria-hidden="true">
<path d="M20 6 9 17l-5-5">
</path>
</svg>
</span>
<span>5 pieces<br/>in every set</span>
</div>
</div>
<button class="he-buy-button" data-buy>Buy now <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
<ul class="he-trust-badges" aria-label="Shopping assurances">
<li class="he-trust-badge">
<span class="he-trust-badge-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rotate-ccw" aria-hidden="true">
<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8">
</path>
<path d="M3 3v5h5">
</path>
</svg>
</span>
<span class="he-trust-badge-copy">
<strong>7 Days</strong>
<span>Easy Return</span>
</span>
</li>
<li class="he-trust-badge">
<span class="he-trust-badge-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck" aria-hidden="true">
<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2">
</path>
<path d="M15 18H9">
</path>
<path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
</path>
<circle cx="17" cy="18" r="2">
</circle>
<circle cx="7" cy="18" r="2">
</circle>
</svg>
</span>
<span class="he-trust-badge-copy">
<strong>Cash on</strong>
<span>Delivery</span>
</span>
</li>
<li class="he-trust-badge">
<span class="he-trust-badge-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-check" aria-hidden="true">
<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z">
</path>
<path d="m9 12 2 2 4-4">
</path>
</svg>
</span>
<span class="he-trust-badge-copy">
<strong>5 Pieces</strong>
<span>In Each Set</span>
</span>
</li>
</ul>
<div class="he-promise">
<span>01 <i>—</i> SMALL JOYS, DAILY</span>
<span>02 <i>—</i> CASH ON DELIVERY</span>
</div>
</div>
</section>
<section class="he-story" id="story">
<div class="he-story-image he-story-image--benefits">
<img alt="Five turquoise silicone scrubbers with four key benefits: powerful cleaning, food-grade silicone, reusable durability, and quick-dry hanging storage." loading="lazy" decoding="async" data-nimg="fill" style="position:absolute;height:100%;width:100%;left:0;top:0;right:0;bottom:0;color:transparent" src="./assets/benefits.webp"/>
</div>
<div class="he-story-copy">
<p class="he-eyebrow">The little everyday upgrade</p>
<h2>Make the<br/>mundane a little<br/>
<em>more lovely.</em>
</h2>
<p>Good kitchen tools don&#x27;t need to be complicated. This cheerful five-piece set brings a pop of color and a soft, grippy texture to the sink—then rinses clean for next time.</p>
<button class="he-text-link" data-buy>Bring home the set <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right" aria-hidden="true">
<path d="M7 7h10v10">
</path>
<path d="M7 17 17 7">
</path>
</svg>
</button>
<span class="he-story-index">HOME ESSENTIAL <i>·</i> EVERYDAY, EASIER</span>
</div>
</section>
<section class="he-details" id="details">
<div class="he-details-heading">
<p class="he-eyebrow">Thoughtful little details</p>
<h2>Made for your<br/>
<em>daily rhythm.</em>
</h2>
<p>Simple, colorful, and easy to reach for when it&#x27;s time to tidy up.</p>
</div>
<div class="he-feature-list">
<article class="he-feature">
<span class="he-feature-number">01</span>
<span class="he-feature-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles" aria-hidden="true">
<path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z">
</path>
<path d="M20 2v4">
</path>
<path d="M22 4h-4">
</path>
<circle cx="4" cy="20" r="2">
</circle>
</svg>
</span>
<div>
<h3>Flexible silicone bristles</h3>
<p>A soft, textured surface for everyday dishwashing.</p>
</div>
</article>
<article class="he-feature">
<span class="he-feature-number">02</span>
<span class="he-feature-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-leaf" aria-hidden="true">
<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z">
</path>
<path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12">
</path>
</svg>
</span>
<div>
<h3>Reusable by design</h3>
<p>Easy to rinse clean and use again.</p>
</div>
</article>
<article class="he-feature">
<span class="he-feature-number">03</span>
<span class="he-feature-icon">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check" aria-hidden="true">
<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
</path>
<path d="m9 12 2 2 4-4">
</path>
</svg>
</span>
<div>
<h3>Five in the set</h3>
<p>A fresh color for every corner of the kitchen.</p>
</div>
</article>
</div>
</section>
<section class="he-reviews" id="reviews" aria-roledescription="carousel" aria-labelledby="he-reviews-title">
<div class="he-reviews-inner">
<header class="he-reviews-heading">
<div>
<p class="he-eyebrow">Customer reviews</p>
<h2 id="he-reviews-title" class="he-reviews-title">Good to have<br/>
<em>around the sink.</em>
</h2>
</div>
</header>
<div class="swiper he-reviews-swiper">
<div class="swiper-wrapper">
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Riya">
<article class="he-review-card">
<blockquote class="he-review-quote">“It&#x27;s practical and convenient for everyday use.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Ri</span>
<span class="he-review-author-name">Riya</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Sameer">
<article class="he-review-card">
<blockquote class="he-review-quote">“Simple design but works really well. Easy to hold and comfortable to use for daily cleaning.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Sa</span>
<span class="he-review-author-name">Sameer</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Neha">
<article class="he-review-card">
<blockquote class="he-review-quote">“Works well even on old, greasy pans. Removes dirt without requiring too much effort.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Ne</span>
<span class="he-review-author-name">Neha</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Karan">
<article class="he-review-card">
<blockquote class="he-review-quote">“The grip is really good, and it doesn&#x27;t slip while washing dishes. Great for everyday use.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Ka</span>
<span class="he-review-author-name">Karan</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Pooja">
<article class="he-review-card">
<blockquote class="he-review-quote">“Initially, it felt a little stiff, but after a few uses, it became comfortable.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Po</span>
<span class="he-review-author-name">Pooja</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Imran">
<article class="he-review-card">
<blockquote class="he-review-quote">“Easy to rinse after washing, doesn&#x27;t retain an unpleasant smell, and is very practical.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Im</span>
<span class="he-review-author-name">Imran</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Nisha">
<article class="he-review-card">
<blockquote class="he-review-quote">“Cleans small food particles effectively and doesn&#x27;t require too much pressure while scrubbing.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Ni</span>
<span class="he-review-author-name">Nisha</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Rohan">
<article class="he-review-card">
<blockquote class="he-review-quote">“Comfortable to hold, sturdy, and perfect for daily dishwashing. Good value for money.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Ro</span>
<span class="he-review-author-name">Rohan</span>
</div>
</article>
</div>
<div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="Review by Vivek">
<article class="he-review-card">
<blockquote class="he-review-quote">“Simple, durable, and gets the job done. A useful addition to the kitchen.”</blockquote>
<div class="he-review-author">
<span class="he-review-avatar he-review-avatar-initials" aria-hidden="true">Vi</span>
<span class="he-review-author-name">Vivek</span>
</div>
</article>
</div>
</div>
</div>
<div class="he-review-controls">
<span class="he-review-pair-status" aria-live="polite" aria-atomic="true">Review 1 of 9</span>
<div class="he-review-nav">
<button class="he-review-nav-button" type="button" aria-label="Show previous customer review">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left" aria-hidden="true">
<path d="m12 19-7-7 7-7">
</path>
<path d="M19 12H5">
</path>
</svg>
</button>
<button class="he-review-nav-button" type="button" aria-label="Show next customer review">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
</div>
</div>
</div>
</section>
<section class="he-closing">
<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkles" aria-hidden="true">
<path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z">
</path>
<path d="M20 2v4">
</path>
<path d="M22 4h-4">
</path>
<circle cx="4" cy="20" r="2">
</circle>
</svg>
<p>Five little helpers. One happy sink.</p>
<button data-buy>Buy the set <span class="he-price-pair he-price-pair--button" role="group" aria-label="MRP ₹999, price ₹239, 76% off.">
<del>₹999</del>
<strong>₹239</strong>
<span class="he-price-discount">76% OFF</span>
</span>
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
</section>
<footer class="he-footer">
<a class="he-wordmark he-footer-brand" href="/">
<img alt="" loading="lazy" width="28" height="28" decoding="async" data-nimg="1" class="he-brand-symbol" style="color:transparent" src="./assets/home-vexa-mark.svg"/>Home Vexa</a>
<p>EVERYDAY HOME ESSENTIALS</p>
<nav class="he-footer-links" aria-label="Store information">
<a href="./about-us/">About Us</a>
<a href="./contact-us/">Contact Us</a>
<a href="./privacy-policy/">Privacy Policy</a>
<a href="./terms-and-conditions/">Terms &amp; Conditions</a>
<a href="./refund-returns/">Refunds &amp; Returns</a>
</nav>
<a href="#product">Back to the scrubbers ↑</a>
<div class="he-footer-contact" style="width:100%;text-align:center;padding:16px 12px 0;line-height:1.8;font-size:14px;">
<strong>Contact Us</strong><br>
Email: <a href="mailto:hiritikrhaj367@gmail.com">hiritikrhaj367@gmail.com</a><br>
Phone: <a href="tel:+918789913840">+91 8789913840</a>
</div>
</footer>
<div class="he-mobile-buy">
<div>
<span>SET OF 5</span>
<span class="he-price-pair he-price-pair--compact" role="group" aria-label="MRP ₹999, price ₹239, 76% off.">
<del>₹999</del>
<strong>₹239</strong>
<span class="he-price-discount">76% OFF</span>
</span>
</div>
<button data-buy>Buy now <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
</div>
<dialog class="he-image-viewer" aria-label="Enlarged product photos">
<div class="he-viewer-content">
<div class="swiper he-viewer-swiper"><div class="swiper-wrapper"></div></div>
<span class="he-viewer-count" aria-live="polite">01 / 08</span>
<button class="he-viewer-step he-viewer-step--previous" type="button" aria-label="Previous enlarged product photo">
<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left" aria-hidden="true">
<path d="m12 19-7-7 7-7">
</path>
<path d="M19 12H5">
</path>
</svg>
</button>
<button class="he-viewer-step he-viewer-step--next" type="button" aria-label="Next enlarged product photo">
<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
<button class="he-viewer-close" type="button" aria-label="Close enlarged product photos">
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true">
<path d="M18 6 6 18">
</path>
<path d="m6 6 12 12">
</path>
</svg>
</button>
<p class="he-viewer-hint">Swipe or use the arrow keys to browse</p>
</div>
</dialog>
<dialog class="he-checkout" aria-labelledby="checkout-title" aria-describedby="checkout-description">
<div class="he-checkout-inner">
<button class="he-checkout-close" type="button" aria-label="Close checkout" autofocus>
<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x" aria-hidden="true">
<path d="M18 6 6 18">
</path>
<path d="m6 6 12 12">
</path>
</svg>
</button>
<div class="he-checkout-brand">
  <img src="./assets/home-vexa-mark.svg" width="26" height="26" alt="">
  <span>Home Vexa</span>
</div>
<h2 id="checkout-title">Checkout</h2>
<p id="checkout-description" class="he-checkout-description">Enter your delivery details and pay when your order arrives.</p>
<section class="he-order-summary" aria-label="Order summary">
  <img class="he-checkout-photo" src="./assets/01.webp" width="60" height="60" alt="">
  <div>
    <h3>Silicone Scrubber Set</h3>
    <small>1 set · 5 reusable scrubbers</small>
  </div>
  <strong>₹<?= COD_PRICE ?></strong>
</section>
<form class="he-checkout-form" action="./order.php" method="post">
<input type="hidden" name="token" value="<?= escape($_SESSION['checkout_token']) ?>">
<input type="hidden" name="request_id" value="<?= escape(bin2hex(random_bytes(16))) ?>">
<h3 class="he-checkout-section-title">Delivery details</h3>
<div class="he-form-grid">
<label class="he-field">Full name<input autoComplete="name" placeholder="Your name" required="" name="name" maxlength="100"/>
</label>
<label class="he-field">Mobile number<input type="tel" inputMode="numeric" autoComplete="tel-national" placeholder="10-digit mobile number" pattern="[0-9]{10}" maxLength="10" required="" name="phone"/>
</label>
<label class="he-field he-field-wide">Email (optional)<input type="email" autocomplete="email" placeholder="For your order confirmation" name="email" maxlength="254"/>
</label>
<label class="he-field he-field-wide">Full delivery address<textarea maxlength="500" name="address" autoComplete="street-address" placeholder="House no., street, area" rows="2" required="">
</textarea>
</label>
<label class="he-field">City<input autoComplete="address-level2" placeholder="Your city" required="" name="city" maxlength="100"/>
</label>
<label class="he-field">PIN code<input inputMode="numeric" autoComplete="postal-code" placeholder="6-digit PIN" pattern="[0-9]{6}" maxLength="6" required="" name="pincode"/>
</label>
</div>
<div class="he-form-total">
  <div>
    <span>Total to pay</span>
    <small>Cash on delivery</small>
  </div>
  <strong>₹<?= COD_PRICE ?></strong>
</div>
<button class="he-buy-button he-submit-button" type="submit">Place COD order <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right" aria-hidden="true">
<path d="M5 12h14">
</path>
<path d="m12 5 7 7-7 7">
</path>
</svg>
</button>
<p id="checkout-status" role="status" hidden>
</p>
<p class="he-demo-disclaimer">Delivery details are used only to process this order.</p>
</form>
</div>
</dialog>
</main>
</body>
</html>
