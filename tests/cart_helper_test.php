<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/view.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/vehicles.php';
require_once __DIR__ . '/../app/helpers/cart.php';

app_start_session();

$_SESSION = [];

sign_in_user([
    'auth_user_id' => 'cart-user-1',
    'email' => 'driver@example.com',
    'role' => 'Buyer',
]);

if (!add_vehicle_to_cart('apex-gt')) {
    fwrite(STDERR, "Expected Apex GT to be added to cart.\n");
    exit(1);
}

add_vehicle_to_cart('apex-gt');
add_vehicle_to_cart('vela-r');

$lineItems = cart_line_items();

if (count($lineItems) !== 2) {
    fwrite(STDERR, 'Expected 2 cart line items, got ' . count($lineItems) . "\n");
    exit(1);
}

if (cart_item_count() !== 3) {
    fwrite(STDERR, 'Expected cart item count of 3, got ' . cart_item_count() . "\n");
    exit(1);
}

if (!cart_contains_vehicle('apex-gt')) {
    fwrite(STDERR, "Expected cart to contain apex-gt.\n");
    exit(1);
}

$firstItem = $lineItems[0];

if (($firstItem['vehicle']['id'] ?? null) !== 'apex-gt') {
    fwrite(STDERR, "Expected first line item to be apex-gt.\n");
    exit(1);
}

if (($firstItem['quantity'] ?? null) !== 2) {
    fwrite(STDERR, 'Expected apex-gt quantity 2, got ' . ($firstItem['quantity'] ?? 'missing') . "\n");
    exit(1);
}

remove_vehicle_from_cart('apex-gt');

if (cart_contains_vehicle('apex-gt')) {
    fwrite(STDERR, "Expected apex-gt to be removed from cart.\n");
    exit(1);
}

if (cart_item_count() !== 1) {
    fwrite(STDERR, 'Expected cart item count of 1 after removal, got ' . cart_item_count() . "\n");
    exit(1);
}

echo "Cart helper contract passed.\n";

