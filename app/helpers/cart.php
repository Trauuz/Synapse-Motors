<?php

declare(strict_types=1);

const CART_SESSION_KEY = 'cart_items';

/**
 * @return array<string, array<string, int>>
 */
function cart_store(): array
{
    $store = $_SESSION[CART_SESSION_KEY] ?? [];

    return is_array($store) ? $store : [];
}

/**
 * @param array<string, array<string, int>> $store
 */
function save_cart_store(array $store): void
{
    $_SESSION[CART_SESSION_KEY] = $store;
}

function current_cart_owner_key(): ?string
{
    $user = current_user();

    if (!is_array($user)) {
        return null;
    }

    $authUserId = $user['auth_user_id'] ?? null;

    if (is_string($authUserId) && $authUserId !== '') {
        return $authUserId;
    }

    $email = $user['email'] ?? null;

    if (is_string($email) && $email !== '') {
        return strtolower($email);
    }

    return null;
}

/**
 * @return array<string, int>
 */
function current_cart_quantities(): array
{
    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return [];
    }

    $store = cart_store();
    $quantities = $store[$ownerKey] ?? [];

    return is_array($quantities) ? $quantities : [];
}

function cart_item_count(): int
{
    return array_sum(current_cart_quantities());
}

function cart_contains_vehicle(string $vehicleId): bool
{
    return array_key_exists($vehicleId, current_cart_quantities());
}

function add_vehicle_to_cart(string $vehicleId, int $quantity = 1): bool
{
    if ($quantity < 1 || find_vehicle_by_id($vehicleId) === null) {
        return false;
    }

    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return false;
    }

    $store = cart_store();
    $ownerCart = $store[$ownerKey] ?? [];

    if (!is_array($ownerCart)) {
        $ownerCart = [];
    }

    $ownerCart[$vehicleId] = max(0, (int) ($ownerCart[$vehicleId] ?? 0)) + $quantity;
    $store[$ownerKey] = $ownerCart;
    save_cart_store($store);

    return true;
}

function remove_vehicle_from_cart(string $vehicleId): void
{
    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return;
    }

    $store = cart_store();
    $ownerCart = $store[$ownerKey] ?? [];

    if (!is_array($ownerCart) || !array_key_exists($vehicleId, $ownerCart)) {
        return;
    }

    unset($ownerCart[$vehicleId]);

    if ($ownerCart === []) {
        unset($store[$ownerKey]);
    } else {
        $store[$ownerKey] = $ownerCart;
    }

    save_cart_store($store);
}

function clear_current_cart(): void
{
    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return;
    }

    $store = cart_store();

    if (!array_key_exists($ownerKey, $store)) {
        return;
    }

    unset($store[$ownerKey]);
    save_cart_store($store);
}

/**
 * @return array<int, array{vehicle: array<string, mixed>, quantity: int, line_total_php: int}>
 */
function cart_line_items(): array
{
    $lineItems = [];

    foreach (current_cart_quantities() as $vehicleId => $quantity) {
        $vehicle = find_vehicle_by_id((string) $vehicleId);

        if ($vehicle === null || $quantity < 1) {
            continue;
        }

        $unitPricePhp = vehicle_price_in_php((int) ($vehicle['price'] ?? 0));
        $lineItems[] = [
            'vehicle' => $vehicle,
            'quantity' => $quantity,
            'line_total_php' => $unitPricePhp * $quantity,
        ];
    }

    return $lineItems;
}

function cart_total_php(): int
{
    $total = 0;

    foreach (cart_line_items() as $lineItem) {
        $total += (int) $lineItem['line_total_php'];
    }

    return $total;
}
