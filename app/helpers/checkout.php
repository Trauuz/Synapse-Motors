<?php

declare(strict_types=1);

const CHECKOUT_DRAFT_SESSION_KEY = 'checkout_draft';
const CHECKOUT_PAYMENT_RECEIPT_SESSION_KEY = 'checkout_payment_receipt';

/**
 * @param array<string, mixed> $draft
 */
function save_checkout_draft(array $draft): void
{
    $_SESSION[CHECKOUT_DRAFT_SESSION_KEY] = $draft;
}

/**
 * @return array<string, mixed>|null
 */
function checkout_draft(): ?array
{
    $draft = $_SESSION[CHECKOUT_DRAFT_SESSION_KEY] ?? null;

    return is_array($draft) ? $draft : null;
}

function clear_checkout_draft(): void
{
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
