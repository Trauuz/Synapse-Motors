<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/view.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/vehicles.php';
require_once __DIR__ . '/../app/helpers/cart.php';
require_once __DIR__ . '/../app/helpers/checkout.php';

app_start_session();

$_SESSION = [];

sign_in_user([
    'auth_user_id' => 'checkout-user-1',
    'email' => 'driver@example.com',
    'name' => 'Test Driver',
    'role' => 'Buyer',
]);

add_vehicle_to_cart('apex-gt');
add_vehicle_to_cart('vela-r');

$draft = [
    'customer' => [
        'name' => 'Test Driver',
        'email' => 'driver@example.com',
        'contact_number' => '+63 917 000 0000',
    ],
    'notes' => 'Please prepare the graphite coupe first.',
    'line_items' => cart_line_items(),
    'total_php' => cart_total_php(),
];

save_checkout_draft($draft);

if (checkout_draft() !== $draft) {
    fwrite(STDERR, "Expected checkout draft to round-trip through the session.\n");
    exit(1);
}

$receipt = [
    'reference' => 'SM-240714-TEST',
    'payment_method' => 'reservation',
    'amount_php' => $draft['total_php'],
];

save_latest_payment_receipt($receipt);

if (latest_payment_receipt() !== $receipt) {
    fwrite(STDERR, "Expected the simulated payment receipt to persist in the session.\n");
    exit(1);
}

clear_checkout_draft();

if (checkout_draft() !== null) {
    fwrite(STDERR, "Expected the checkout draft to be cleared.\n");
    exit(1);
}

clear_current_cart();

if (cart_item_count() !== 0) {
    fwrite(STDERR, "Expected the current cart to be empty after clearing it.\n");
    exit(1);
}

clear_latest_payment_receipt();

if (latest_payment_receipt() !== null) {
    fwrite(STDERR, "Expected the latest payment receipt to be cleared.\n");
    exit(1);
}

echo "Checkout helper contract passed.\n";
