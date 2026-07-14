<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/checkout.php';
$page = file_get_contents($pagePath);

if ($page === false) {
    fwrite(STDERR, "Unable to read public/checkout.php\n");
    exit(1);
}

$expectations = [
    'checkout page landmark' => '<main id="main-content" class="checkout-page">',
    'checkout page form' => 'action="checkout/submit.php"',
    'checkout page payment link' => 'payment.php',
    'checkout page navbar include' => "require __DIR__ . '/includes/navbar.php';",
    'checkout page auth modal include' => "require __DIR__ . '/includes/auth_modal.php';",
    'checkout page headline' => 'Confirm the details and move to payment.',
];

$failures = [];

foreach ($expectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing checkout-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Checkout page contract passed.\n";
