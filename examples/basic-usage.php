<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_shoppingcart PrestaShop module.
 *
 * ps_shoppingcart renders the cart summary widget in the storefront header,
 * displaying the number of items and total price. It supports AJAX updates
 * when products are added without a full page reload.
 *
 * This file documents common usage patterns.
 */

// --- Widget invocation in Smarty/Twig template ---
// {widget name="ps_shoppingcart" hook="displayNav1"}

// --- Accessing the current cart programmatically ---
// $context = Context::getContext();
// $cart    = $context->cart;
//
// $itemCount   = $cart->getNbProducts();
// $totalAmount = $cart->getOrderTotal(with_taxes: true);
// $currency    = new Currency($cart->id_currency);
//
// echo "Items: $itemCount\n";
// echo "Total: " . number_format($totalAmount, 2) . ' ' . $currency->symbol . "\n";

// --- Getting cart products ---
// $products = $cart->getProducts();
// foreach ($products as $product) {
//     echo $product['name'] . ' x' . $product['cart_quantity'];
//     echo ' = €' . $product['total_price'] . "\n";
// }

// --- Adding a product to cart ---
// $cart->updateQty(
//     quantity: 1,
//     id_product: 42,
//     id_product_attribute: 0,
// );

// --- Hook: displayShoppingCartFooter ---
// Renders content after the cart dropdown (e.g., promo code input).

// --- AJAX cart update ---
// The module registers a JavaScript handler for 'add to cart' buttons.
// When clicked, it calls:
//   POST /index.php?action=update&ajax=1&controller=cart
// And updates the cart widget without reloading the page.

// --- Template override ---
// themes/{theme}/modules/ps_shoppingcart/views/templates/hook/ps_shoppingcart.tpl
// themes/{theme}/modules/ps_shoppingcart/views/templates/hook/ps_shoppingcart-modal.tpl
