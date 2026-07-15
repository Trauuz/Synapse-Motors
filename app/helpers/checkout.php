<?php

declare(strict_types=1);

const CHECKOUT_DRAFT_SESSION_KEY = 'checkout_order_id';
const CHECKOUT_PAYMENT_RECEIPT_SESSION_KEY = 'checkout_payment_receipt';

/**
 * @param array<string, mixed> $draft
 */
function save_checkout_draft(array $draft): void
{
    $user = current_user();
    $userId = is_array($user) ? (string) ($user['id'] ?? $user['auth_user_id'] ?? '') : '';

    if ($userId === '') {
        return;
    }

    $existingDraft = checkout_draft();

    if (is_array($existingDraft) && isset($existingDraft['id'])) {
        order_repository()->deleteDraft((string) $existingDraft['id']);
    }

    $order = order_repository()->createDraft($userId, $draft);
    $_SESSION[CHECKOUT_DRAFT_SESSION_KEY] = (string) ($order['id'] ?? '');
}

/**
 * @return array<string, mixed>|null
 */
function checkout_draft(): ?array
{
    $user = current_user();
    $orderId = $_SESSION[CHECKOUT_DRAFT_SESSION_KEY] ?? null;

    if (!is_array($user) || !is_string($orderId) || $orderId === '') {
        return null;
    }

    $order = order_repository()->findDraftForUser((string) ($user['id'] ?? $user['auth_user_id'] ?? ''), $orderId);

    if (!is_array($order)) {
        return null;
    }

    return [
        'customer' => [
            'name' => (string) ($order['customer_name'] ?? ''),
            'email' => (string) ($order['customer_email'] ?? ''),
            'contact_number' => (string) ($order['contact_number'] ?? ''),
        ],
        'notes' => (string) ($order['notes'] ?? ''),
        'line_items' => is_array($order['line_items'] ?? null) ? $order['line_items'] : [],
        'total_php' => (int) ($order['total_php'] ?? 0),
    ];
}

function current_checkout_order_id(): ?string
{
    $orderId = $_SESSION[CHECKOUT_DRAFT_SESSION_KEY] ?? null;

    return is_string($orderId) && $orderId !== '' ? $orderId : null;
}

function clear_checkout_draft(): void
{
    $orderId = current_checkout_order_id();

    if ($orderId !== null) {
        order_repository()->deleteDraft($orderId);
    }

    unset($_SESSION[CHECKOUT_DRAFT_SESSION_KEY]);
}

/**
 * @param array<string, mixed> $receipt
 */
function save_latest_payment_receipt(array $receipt): void
{
    $_SESSION[CHECKOUT_PAYMENT_RECEIPT_SESSION_KEY] = $receipt;
}

/**
 * @return array<string, mixed>|null
 */
function latest_payment_receipt(): ?array
{
    $receipt = $_SESSION[CHECKOUT_PAYMENT_RECEIPT_SESSION_KEY] ?? null;

    return is_array($receipt) ? $receipt : null;
}

function clear_latest_payment_receipt(): void
{
    unset($_SESSION[CHECKOUT_PAYMENT_RECEIPT_SESSION_KEY]);
}
