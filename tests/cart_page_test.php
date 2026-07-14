<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/cart.php';
$page = file_get_contents($pagePath);

if ($page === false) {
    fwrite(STDERR, "Unable to read public/cart.php\n");
    exit(1);
}

$expectations = [
    'cart page landmark' => '<main id="main-content" class="cart-page">',
    'cart page heading' => 'Your garage, staged for checkout.',
    'cart page includes navbar' => "require __DIR__ . '/includes/navbar.php';",
    'cart page includes footer' => "require __DIR__ . '/includes/footer.php';",
    'cart page includes auth modal' => "require __DIR__ . '/includes/auth_modal.php';",
    'cart page total block' => 'cart-summary',
    'cart page inventory link' => 'inventory.php',
];

$failures = [];

foreach ($expectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing cart-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Cart page contract passed.\n";
