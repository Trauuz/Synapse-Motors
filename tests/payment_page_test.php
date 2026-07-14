<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/payment.php';
$page = file_get_contents($pagePath);

if ($page === false) {
    fwrite(STDERR, "Unable to read public/payment.php\n");
    exit(1);
}

$expectations = [
    'payment page landmark' => '<main id="main-content" class="payment-page">',
    'payment page form' => 'action="payment/submit.php"',
    'payment page checkout link' => 'checkout.php',
    'payment page navbar include' => "require __DIR__ . '/includes/navbar.php';",
    'payment page success heading' => 'Reserved. Routed. Ready for follow-up.',
    'payment page simulation copy' => 'This is a guided payment simulation for the Synapse Motors checkout journey.',
];

$failures = [];

foreach ($expectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing payment-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Payment page contract passed.\n";
