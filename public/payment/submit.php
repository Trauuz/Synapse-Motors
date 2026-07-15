<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ../payment.php');
    exit;
}

if (!is_logged_in() || !verify_csrf_token($_POST['_csrf'] ?? null)) {
    header('Location: ../payment.php');
    exit;
}

$draft = checkout_draft();

if ($draft === null) {
    header('Location: ../checkout.php');
    exit;
}

$old = [
    'payment_method' => trim((string) ($_POST['payment_method'] ?? '')),
];

$errors = [];
$paymentMethodLabels = [
    'reservation' => 'Reservation deposit',
    'bank-transfer' => 'Bank transfer confirmation',
    'advisor-follow-up' => 'Advisor follow-up',
];

if (!array_key_exists($old['payment_method'], $paymentMethodLabels)) {
    $errors['payment_method'] = 'Choose a simulated payment route.';
}

if ($errors !== []) {
    flash_set('payment_errors', $errors);
    flash_set('payment_old', $old);
    header('Location: ../payment.php');
    exit;
}

$customer = is_array($draft['customer'] ?? null) ? $draft['customer'] : [];
$amountPhp = (int) ($draft['total_php'] ?? 0);
$reference = 'SM-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
$orderId = current_checkout_order_id();

if (!is_string($orderId) || $orderId === '') {
    header('Location: ../checkout.php');
    exit;
}

$submittedOrder = order_repository()->submitDraft(
    $orderId,
    $old['payment_method'],
    $paymentMethodLabels[$old['payment_method']],
    $reference
);

save_latest_payment_receipt([
    'reference' => $reference,
    'payment_method' => $old['payment_method'],
    'payment_method_label' => $paymentMethodLabels[$old['payment_method']],
    'amount_php' => (int) ($submittedOrder['total_php'] ?? $amountPhp),
    'customer_name' => (string) ($customer['name'] ?? 'Synapse Member'),
]);

unset($_SESSION[CHECKOUT_DRAFT_SESSION_KEY]);
clear_current_cart();

header('Location: ../index.php');
exit;
