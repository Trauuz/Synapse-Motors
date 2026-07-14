<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/about.php';
$navbarPath = __DIR__ . '/../public/includes/navbar.php';

$page = file_get_contents($pagePath);
$navbar = file_get_contents($navbarPath);

foreach ([
    'public/about.php' => $page,
    'public/includes/navbar.php' => $navbar,
] as $label => $content) {
    if ($content !== false) {
        continue;
    }

    fwrite(STDERR, "Unable to read {$label}\n");
    exit(1);
}

$pageExpectations = [
    'about page landmark' => '<main id="main-content" class="about-page">',
    'about page heading' => 'We source cars for people who still notice the long way home.',
    'about inventory action' => 'Browse the collection',
    'about visit action' => 'Plan a visit',
    'about storyline section' => 'From first sighting to final handover, we keep the process legible.',
    'navbar include' => "require __DIR__ . '/includes/navbar.php';",
    'footer include' => "require __DIR__ . '/includes/footer.php';",
    'auth modal include' => "require __DIR__ . '/includes/auth_modal.php';",
    'component stylesheet' => 'assets/css/components.css',
    'media stylesheet' => 'assets/css/media.css',
    'shared interaction script' => 'assets/js/landing.js',
];

$navbarExpectations = [
    'desktop about route' => '$aboutHref = \'about.php\';',
    'mobile about link label' => 'About us',
];

$failures = [];

foreach ($pageExpectations as $label => $needle) {
    if (str_contains($page, $needle)) {
        continue;
    }

    $failures[] = $label;
}

foreach ($navbarExpectations as $label => $needle) {
    if (str_contains($navbar, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing about-page contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "About page contract passed.\n";
