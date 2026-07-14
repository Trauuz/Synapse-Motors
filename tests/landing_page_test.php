<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/index.php';
$page = file_get_contents($pagePath);
$footerPath = __DIR__ . '/../public/includes/footer.php';
$footer = file_get_contents($footerPath);

if ($page === false) {
    fwrite(STDERR, "Unable to read public/index.php\n");
    exit(1);
}

if ($footer === false) {
    fwrite(STDERR, "Unable to read public/includes/footer.php\n");
    exit(1);
}

$expectations = [
    'guest page landmark' => '<main id="main-content">',
    'inventory heading' => 'Find your next drive.',
    'primary action' => 'Explore inventory',
    'inventory route' => 'inventory.php',
    'test drive action' => 'Book a test drive',
    'navbar include' => "require __DIR__ . '/includes/navbar.php';",
    'footer include' => "require __DIR__ . '/includes/footer.php';",
    'order complete modal include' => "require __DIR__ . '/includes/order_complete_modal.php';",
    'responsive image sizing' => 'width="1962" height="802"',
    'global stylesheet' => 'assets/css/global.css',
    'component stylesheet' => 'assets/css/components.css',
    'media stylesheet' => 'assets/css/media.css',
    'page interaction script' => 'assets/js/landing.js',
];

$footerExpectations = [
    'footer contact section' => 'Contact us',
    'footer help section' => 'Help',
    'footer about section' => 'About us',
    'footer whatsapp contact' => 'WhatsApp',
    'footer returns link' => 'Returns &amp; Refunds',
];

$failures = [];

foreach ($expectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

foreach ($footerExpectations as $label => $needle) {
    if (str_contains($footer, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing landing-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Landing page contract passed.\n";
