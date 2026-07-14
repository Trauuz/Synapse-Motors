<?php

declare(strict_types=1);

$navbarPath = __DIR__ . '/../public/includes/navbar.php';
$landingPath = __DIR__ . '/../public/index.php';
$inventoryPath = __DIR__ . '/../public/inventory.php';
$scriptPath = __DIR__ . '/../public/assets/js/landing.js';
$modalPath = __DIR__ . '/../public/includes/auth_modal.php';

$navbar = file_get_contents($navbarPath);
$landing = file_get_contents($landingPath);
$inventory = file_get_contents($inventoryPath);
$script = file_get_contents($scriptPath);
$modal = file_get_contents($modalPath);

foreach ([
    'public/includes/navbar.php' => $navbar,
    'public/index.php' => $landing,
    'public/inventory.php' => $inventory,
    'public/assets/js/landing.js' => $script,
    'public/includes/auth_modal.php' => $modal,
] as $label => $content) {
    if ($content !== false) {
        continue;
    }

    fwrite(STDERR, "Unable to read {$label}\n");
    exit(1);
}

$expectations = [
    'shared auth modal include on landing' => [$landing, "require __DIR__ . '/includes/auth_modal.php';"],
    'shared auth modal include on inventory' => [$inventory, "require __DIR__ . '/includes/auth_modal.php';"],
    'signup auth trigger' => [$navbar, 'data-auth-trigger="signin"'],
    'cart auth trigger' => [$navbar, 'data-auth-trigger="cart"'],
    'save auth trigger on inventory' => [$inventory, 'data-auth-trigger="save"'],
    'signup mode switch link' => [$modal, 'data-auth-switch="signup"'],
    'signup contact field' => [$modal, 'data-auth-contact-field'],
    'signup address field' => [$modal, 'data-auth-address-field'],
    'auth modal controller hook' => [$script, '[data-auth-modal]'],
    'auth modal open helper' => [$script, 'const openAuthModal ='],
    'auth modal mode helper' => [$script, 'const setAuthModalMode ='],
];

$failures = [];

foreach ($expectations as $label => [$content, $needle]) {
    if (str_contains($content, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing auth-modal contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Auth modal contract passed.\n";
