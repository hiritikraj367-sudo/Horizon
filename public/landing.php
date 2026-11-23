<?php
declare(strict_types=1);
/**
 * Independent landing page. Renders the existing homepage markup into a buffer
 * and substitutes editable product content without modifying public/index.php.
 * Checkout remains the original flow; product-specific checkout wiring is a
 * separate integration step before selling other products.
 */
$product = require __DIR__ . '/landing-product.php';
$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$price = (int) $product['price'];
$mrp = (int) $product['mrp'];
$discount = $mrp > 0 ? (int) round(100 * ($mrp - $price) / $mrp) : 0;
ob_start();
require __DIR__ . '/index.php';
$html = (string) ob_get_clean();
$replace = [
    '<title>Branded Silicone Scrubbers | Home Vexa</title>' => '<title>' . $escape($product['name']) . ' | Home Vexa</title>',
    '<h1>Branded Silicone <em>Scrubbers.</em>' => '<h1>' . $product['headline_html'],
    '<p class="he-subtitle">by Home Vexa</p>' => '<p class="he-subtitle">' . $escape($product['subtitle']) . '</p>',
    '<p class="he-style-number">THE EVERYDAY CLEAN SET</p>' => '<p class="he-style-number">' . $escape($product['collection']) . '</p>',
    '<span>Kitchen essentials</span>' => '<span>' . $escape($product['category']) . '</span>',
    '<span>Silicone scrubber set</span>' => '<span>' . $escape($product['short_name']) . '</span>',
    '<span class="he-price-note">Set of 5</span>' => '<span class="he-price-note">' . $escape($product['quantity_label']) . '</span>',
    'Meet your new sink-side favorites: five colorful silicone scrubbers with flexible bristles for your daily dishwashing ritual. Easy to rinse, ready to use again.' => $escape($product['description']),
    '₹999' => '₹' . number_format($mrp),
    '₹239' => '₹' . number_format($price),
    '76% OFF' => $discount . '% OFF',
];
$html = strtr($html, $replace);
// Update both main gallery and thumbnails, preserving all existing CSS/JS.
foreach ($product['gallery'] as $index => $image) {
    $old = sprintf('./assets/%02d.webp', $index + 1);
    $new = $escape((string) $image['src']);
    $html = str_replace('src="' . $old . '"', 'src="' . $new . '"', $html);
}
echo $html;
