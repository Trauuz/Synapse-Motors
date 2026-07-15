<?php

declare(strict_types=1);

const SYNAPSE_USD_TO_PHP_RATE = 60.5;

function inventory_catalog_data_path(): string
{
    if (defined('SYNAPSE_INVENTORY_DATA_PATH')) {
        return (string) SYNAPSE_INVENTORY_DATA_PATH;
    }

    return dirname(__DIR__, 2) . '/tmp/inventory-catalog.json';
}

function load_inventory_catalog(): ?array
{
    $filePath = inventory_catalog_data_path();

    if (!is_file($filePath)) {
        return null;
    }

    $contents = file_get_contents($filePath);

    if (!is_string($contents) || trim($contents) === '') {
        return null;
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : null;
}

/**
 * @param array<int, array<string, mixed>> $inventory
 */
function sync_inventory_catalog(array $inventory): void
{
    vehicle_repository()->replaceCatalog($inventory);
}

function vehicle_price_in_php(int $priceInUsd): int
{
    return (int) round($priceInUsd * SYNAPSE_USD_TO_PHP_RATE);
}

function format_vehicle_price_php(int $priceInUsd): string
{
    $convertedPrice = vehicle_price_in_php($priceInUsd);

    return '₱' . number_format($convertedPrice);
}

function inventory_vehicle_slug(string $name): string
{
    $normalized = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $normalized);

    if (!is_string($slug)) {
        return 'vehicle';
    }

    $slug = trim($slug, '-');

    return $slug === '' ? 'vehicle' : $slug;
}

/**
 * @param array<int, array<string, mixed>> $inventory
 */
function inventory_vehicle_id_exists(array $inventory, string $vehicleId): bool
{
    foreach ($inventory as $vehicle) {
        if (($vehicle['id'] ?? null) === $vehicleId) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<int, array<string, mixed>> $inventory
 */
function next_inventory_vehicle_id(array $inventory, string $name): string
{
    $baseId = inventory_vehicle_slug($name);

    if (!inventory_vehicle_id_exists($inventory, $baseId)) {
        return $baseId;
    }

    $suffix = 2;

    while (inventory_vehicle_id_exists($inventory, $baseId . '-' . $suffix)) {
        $suffix++;
    }

    return $baseId . '-' . $suffix;
}

function ensure_inventory_catalog_seeded(): void
{
    static $isSeeded = false;

    if ($isSeeded) {
        return;
    }

    if (vehicle_repository()->countAll() > 0) {
        $isSeeded = true;
        return;
    }

    $legacyInventory = load_inventory_catalog();

    if (is_array($legacyInventory) && $legacyInventory !== []) {
        sync_inventory_catalog($legacyInventory);
        $isSeeded = true;
        return;
    }

    sync_inventory_catalog(default_synapse_vehicle_inventory());
    $isSeeded = true;
}

/**
 * @return array{body: string, powertrain: string}
 */
function inventory_specs_from_detail(string $detail): array
{
    $parts = array_values(array_filter(array_map('trim', explode('-', $detail)), static fn(string $item): bool => $item !== ''));
    $body = $parts[0] ?? 'Coupe';
    $powertrain = $parts[1] ?? 'Petrol';

    return [
        'body' => $body,
        'powertrain' => $powertrain,
    ];
}

function inventory_category_from_vehicle_data(string $name, string $detail, string $collection, string $body, string $powertrain): string
{
    $rawCategory = strtolower(implode(' ', [$name, $detail, $collection, $body, $powertrain]));
    $normalized = preg_replace('/[^a-z0-9]+/', ' ', $rawCategory);

    if (!is_string($normalized)) {
        return 'touring';
    }

    $category = trim(preg_replace('/\s+/', ' ', $normalized) ?? 'touring');

    return $category === '' ? 'touring' : $category;
}

/**
 * @return array<string, mixed>|null
 */
function find_vehicle_by_id(string $vehicleId): ?array
{
    ensure_inventory_catalog_seeded();

    return vehicle_repository()->findById($vehicleId);
}

/**
 * @return array<int, array<string, mixed>>
 */
function default_synapse_vehicle_inventory(): array
{
    return [
        [
            'id' => 'apex-gt',
            'name' => 'Apex GT',
            'detail' => 'Performance coupe - Petrol',
            'price' => 128000,
            'category' => 'performance touring',
            'image' => 'inventory-apex-gt.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Graphite black performance coupe on a coastal overlook at dusk',
            'body' => 'Coupe',
            'powertrain' => 'Petrol',
            'drive' => 'Rear-wheel drive',
            'availability' => 'Available now',
            'collection' => 'Coast road edit',
            'stock_quantity' => 4,
        ],
        [
            'id' => 'vela-r',
            'name' => 'Vela R',
            'detail' => 'Grand touring coupe - Petrol',
            'price' => 142500,
            'category' => 'performance touring',
            'image' => 'inventory-vela-r.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Oxblood grand touring coupe on a coastal overlook at dusk',
            'body' => 'Grand touring',
            'powertrain' => 'Petrol',
            'drive' => 'All-wheel drive',
            'availability' => 'Available now',
            'collection' => 'Long-distance picks',
            'stock_quantity' => 3,
        ],
        [
            'id' => 'northline-e',
            'name' => 'Northline E',
            'detail' => 'SUV - Electric',
            'price' => 96500,
            'category' => 'electric suv',
            'image' => 'inventory-northline-e.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Pearl white electric SUV on a coastal overlook at dusk',
            'body' => 'SUV',
            'powertrain' => 'Electric',
            'drive' => 'Dual-motor AWD',
            'availability' => 'Available now',
            'collection' => 'Quiet weekender',
            'stock_quantity' => 6,
        ],
        [
            'id' => 'harbor-s',
            'name' => 'Harbor S',
            'detail' => 'City crossover - Electric',
            'price' => 68400,
            'category' => 'electric city suv',
            'image' => 'inventory-harbor-s.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Silver electric city crossover on a coastal overlook at dusk',
            'body' => 'Crossover',
            'powertrain' => 'Electric',
            'drive' => 'Rear-wheel drive',
            'availability' => 'Available to order',
            'collection' => 'Urban daily',
            'stock_quantity' => 8,
        ],
        [
            'id' => 'monarch-t',
            'name' => 'Monarch T',
            'detail' => 'Touring sedan - Petrol',
            'price' => 88400,
            'category' => 'touring city',
            'image' => 'inventory-monarch-t.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Deep navy touring sedan on a coastal overlook at dusk',
            'body' => 'Sedan',
            'powertrain' => 'Petrol',
            'drive' => 'Front-engine touring',
            'availability' => 'Viewing this week',
            'collection' => 'Open-road catalogue',
            'stock_quantity' => 2,
        ],
        [
            'id' => 'cinder-rs',
            'name' => 'Cinder RS',
            'detail' => 'Track coupe - Petrol',
            'price' => 171900,
            'category' => 'performance collector',
            'image' => 'inventory-cinder-rs.png',
            'width' => 1672,
            'height' => 941,
            'alt' => 'Charcoal track-focused coupe on a coastal overlook at dusk',
            'body' => 'Coupe',
            'powertrain' => 'Petrol',
            'drive' => 'Rear-wheel drive',
            'availability' => 'By appointment',
            'collection' => 'Collector shelf',
            'stock_quantity' => 1,
        ],
    ];
}

function synapse_vehicle_inventory(): array
{
    ensure_inventory_catalog_seeded();

    return vehicle_repository()->all();
}

/**
 * @param array<string, mixed> $vehicleData
 * @return array<string, mixed>|null
 */
function create_inventory_vehicle(array $vehicleData): ?array
{
    $name = trim((string) ($vehicleData['name'] ?? ''));
    $detail = trim((string) ($vehicleData['detail'] ?? ''));

    if ($name === '' || $detail === '') {
        return null;
    }

    $inventory = synapse_vehicle_inventory();
    $collection = trim((string) ($vehicleData['collection'] ?? ''));
    $availability = trim((string) ($vehicleData['availability'] ?? ''));
    $price = max(0, (int) ($vehicleData['price'] ?? 0));
    $stockQuantity = max(0, (int) ($vehicleData['stock_quantity'] ?? 0));
    $specs = inventory_specs_from_detail($detail);

    $vehicle = [
        'id' => next_inventory_vehicle_id($inventory, $name),
        'name' => $name,
        'detail' => $detail,
        'price' => $price,
        'category' => inventory_category_from_vehicle_data($name, $detail, $collection, $specs['body'], $specs['powertrain']),
        'image' => 'hero-coast.png',
        'width' => 1962,
        'height' => 802,
        'alt' => $name . ' vehicle listing image',
        'body' => $specs['body'],
        'powertrain' => $specs['powertrain'],
        'drive' => 'Seller to confirm',
        'availability' => $availability === '' ? 'Available now' : $availability,
        'collection' => $collection === '' ? 'New arrivals' : $collection,
        'stock_quantity' => $stockQuantity,
    ];

    return vehicle_repository()->create($vehicle);
}

/**
 * @param array<string, mixed> $changes
 * @return array<string, mixed>|null
 */
function update_inventory_vehicle(string $vehicleId, array $changes): ?array
{
    $vehicle = find_vehicle_by_id($vehicleId);

    if (!is_array($vehicle)) {
        return null;
    }

    $nextName = trim((string) ($changes['name'] ?? $vehicle['name'] ?? ''));
    $nextDetail = trim((string) ($changes['detail'] ?? $vehicle['detail'] ?? ''));
    $nextCollection = trim((string) ($changes['collection'] ?? $vehicle['collection'] ?? ''));
    $nextPrice = max(0, (int) ($changes['price'] ?? $vehicle['price'] ?? 0));
    $nextStockQuantity = max(0, (int) ($changes['stock_quantity'] ?? $vehicle['stock_quantity'] ?? 0));
    $nextAvailability = trim((string) ($changes['availability'] ?? $vehicle['availability'] ?? ''));
    $specs = inventory_specs_from_detail($nextDetail);
    $resolvedCollection = $nextCollection === '' ? (string) ($vehicle['collection'] ?? 'New arrivals') : $nextCollection;

    if ($nextName === '' || $nextDetail === '') {
        return null;
    }

    return vehicle_repository()->update($vehicleId, [
        'name' => $nextName,
        'detail' => $nextDetail,
        'collection' => $resolvedCollection,
        'price' => $nextPrice,
        'stock_quantity' => $nextStockQuantity,
        'availability' => $nextAvailability === '' ? (string) ($vehicle['availability'] ?? 'Available now') : $nextAvailability,
        'body' => $specs['body'],
        'powertrain' => $specs['powertrain'],
        'category' => inventory_category_from_vehicle_data(
            $nextName,
            $nextDetail,
            $resolvedCollection,
            $specs['body'],
            $specs['powertrain']
        ),
        'alt' => $nextName . ' vehicle listing image',
    ]);
}

/**
 * @return array<string, mixed>|null
 */
function delete_inventory_vehicle(string $vehicleId): ?array
{
    ensure_inventory_catalog_seeded();

    return vehicle_repository()->delete($vehicleId);
}
