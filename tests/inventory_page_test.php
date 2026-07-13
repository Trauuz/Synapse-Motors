<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/inventory.php';
$page = file_get_contents($pagePath);

if ($page === false) {
    fwrite(STDERR, "Unable to read public/inventory.php\n");
    exit(1);
}

$expectations = [
    'inventory page landmark' => '<main id="main-content" class="inventory-page">',
    'inventory page heading' => 'Browse the full road-ready collection.',
    'inventory page cta' => 'Start browsing',
    'inventory page filter' => 'data-filter="collector"',
    'inventory page cards' => 'inventory-record',
    'vehicle helper include' => "require_once dirname(__DIR__) . '/app/helpers/vehicles.php';",
    'navbar include' => "require __DIR__ . '/includes/navbar.php';",
    'footer include' => "require __DIR__ . '/includes/footer.php';",
    'shared interaction script' => 'assets/js/landing.js',
];

$failures = [];

foreach ($expectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing inventory-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Inventory page contract passed.\n";
