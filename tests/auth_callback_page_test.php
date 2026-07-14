<?php

declare(strict_types=1);

$pagePath = __DIR__ . '/../public/auth-callback.php';
$scriptPath = __DIR__ . '/../public/assets/js/auth-callback.js';
$servicePath = __DIR__ . '/../app/services/AuthService.php';

$page = file_get_contents($pagePath);
$script = file_get_contents($scriptPath);
$service = file_get_contents($servicePath);

foreach ([
    'public/auth-callback.php' => $page,
    'public/assets/js/auth-callback.js' => $script,
    'app/services/AuthService.php' => $service,
] as $label => $content) {
    if ($content !== false) {
        continue;
    }

    fwrite(STDERR, "Unable to read {$label}\n");
    exit(1);
}

$expectations = [
    'callback page heading' => [$page, 'Confirming your Synapse Motors email.'],
    'callback page status hook' => [$page, 'data-auth-callback-status="pending"'],
    'callback page script include' => [$page, 'assets/js/auth-callback.js'],
    'callback verifier endpoint' => [$script, '/auth/v1/verify'],
    'callback token support' => [$script, 'token_hash'],
    'signup redirect target' => [$service, "app_public_url('auth-callback.php')"],
];

$failures = [];

foreach ($expectations as $label => [$content, $needle]) {
    if (str_contains($content, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing auth-callback contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Auth callback contract passed.\n";
