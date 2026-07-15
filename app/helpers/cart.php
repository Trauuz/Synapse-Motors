<?php

declare(strict_types=1);

function current_cart_owner_key(): ?string
{
    $user = current_user();

    if (!is_array($user)) {
        return null;
    }

    $userId = $user['id'] ?? $user['auth_user_id'] ?? null;

    if (is_string($userId) && $userId !== '') {
        return $userId;
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

    return cart_repository()->quantitiesForUser($ownerKey);
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

    $currentQuantity = (int) (current_cart_quantities()[$vehicleId] ?? 0);
    cart_repository()->setQuantity($ownerKey, $vehicleId, $currentQuantity + $quantity);

    return true;
}

function remove_vehicle_from_cart(string $vehicleId): void
{
    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return;
    }

    cart_repository()->remove($ownerKey, $vehicleId);
}

function clear_current_cart(): void
{
    $ownerKey = current_cart_owner_key();

    if ($ownerKey === null) {
        return;
    }

    cart_repository()->clearForUser($ownerKey);
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
        $total += (int) ($lineItem['line_total_php'] ?? 0);
    }

    return $total;
}
