<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../checkout.php');
    exit;
}

if (!is_logged_in() || !verify_csrf_token($_POST['_csrf'] ?? null)) {
    header('Location: ../checkout.php');
    exit;
}

if (cart_line_items() === []) {
    header('Location: ../cart.php');
    exit;
}

$old = [
    'customer_name' => trim((string) ($_POST['customer_name'] ?? '')),
    'customer_email' => trim((string) ($_POST['customer_email'] ?? '')),
    'contact_number' => trim((string) ($_POST['contact_number'] ?? '')),
    'notes' => trim((string) ($_POST['notes'] ?? '')),
];

$errors = [];

if ($old['customer_name'] === '') {
    $errors['customer_name'] = 'Please enter the primary contact name.';
}

if ($old['customer_email'] === '' || filter_var($old['customer_email'], FILTER_VALIDATE_EMAIL) === false) {
    $errors['customer_email'] = 'Please enter a valid email address.';
}

if ($old['contact_number'] === '') {
    $errors['contact_number'] = 'Please enter a contact number.';
}

if ($errors !== []) {
    flash_set('checkout_errors', $errors);
    flash_set('checkout_old', $old);
    header('Location: ../checkout.php');
    exit;
}

clear_latest_payment_receipt();

save_checkout_draft([
    'customer' => [
        'name' => $old['customer_name'],
        'email' => $old['customer_email'],
        'contact_number' => $old['contact_number'],
    ],
    'notes' => $old['notes'],
    'line_items' => cart_line_items(),
    'total_php' => cart_total_php(),
]);

header('Location: ../payment.php');
exit;
