<?php

declare(strict_types=1);

$indexPath = __DIR__ . '/../public/index.php';
$modalPath = __DIR__ . '/../public/includes/order_complete_modal.php';
$paymentSubmitPath = __DIR__ . '/../public/payment/submit.php';
$scriptPath = __DIR__ . '/../public/assets/js/landing.js';

$index = file_get_contents($indexPath);
$modal = file_get_contents($modalPath);
$paymentSubmit = file_get_contents($paymentSubmitPath);
$script = file_get_contents($scriptPath);

foreach ([
    'public/index.php' => $index,
    'public/includes/order_complete_modal.php' => $modal,
    'public/payment/submit.php' => $paymentSubmit,
    'public/assets/js/landing.js' => $script,
] as $label => $content) {
    if ($content !== false) {
        continue;
    }

    fwrite(STDERR, "Unable to read {$label}\n");
    exit(1);
}

$expectations = [
    'landing includes order complete modal' => [$index, "require __DIR__ . '/includes/order_complete_modal.php';"],
    'order complete shell hook' => [$modal, 'data-order-complete-modal'],
    'order complete title' => [$modal, "Order complete. We'll take it from here."],
    'order complete close hook' => [$modal, 'data-order-complete-close'],
    'payment submit redirects home' => [$paymentSubmit, "header('Location: ../index.php');"],
    'order complete controller hook' => [$script, "const orderCompleteModal = document.querySelector('[data-order-complete-modal]');"],
];

$failures = [];

foreach ($expectations as $label => [$content, $needle]) {
    if (str_contains($content, $needle)) {
        continue;
    }

    $failures[] = $label;
}

if ($failures !== []) {
    fwrite(STDERR, 'Missing order-complete contracts: ' . implode(', ', $failures) . "\n");
    exit(1);
}

echo "Order complete modal contract passed.\n";
