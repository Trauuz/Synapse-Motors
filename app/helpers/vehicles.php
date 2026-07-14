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

/**
 * @param array<int, array<string, mixed>> $inventory
 */
function save_inventory_catalog(array $inventory): void
{
    $filePath = inventory_catalog_data_path();
    $directory = dirname($filePath);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    file_put_contents($filePath, json_encode(array_values($inventory), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

/**
 * @return array<int, array<string, mixed>>|null
 */
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
    foreach (synapse_vehicle_inventory() as $vehicle) {
        if (($vehicle['id'] ?? null) !== $vehicleId) {
            continue;
        }

        return $vehicle;
    }

    return null;
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
            'image' => 'hero-coast.png',
            'width' => 1962,
            'height' => 802,
            'alt' => 'Graphite performance coupe on a coastal overlook',
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
            'image' => 'tunnel-gt.png',
            'width' => 1796,
            'height' => 876,
            'alt' => 'Oxblood grand touring coupe driving through a concrete tunnel',
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
            'image' => 'alpine-suv.png',
            'width' => 1536,
            'height' => 1024,
            'alt' => 'Silver electric SUV parked beside a lakeside cabin',
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
            'image' => 'alpine-suv.png',
            'width' => 1536,
            'height' => 1024,
            'alt' => 'Silver electric SUV parked beside a lakeside cabin',
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
            'image' => 'tunnel-gt.png',
            'width' => 1796,
            'height' => 876,
            'alt' => 'Oxblood grand touring coupe driving through a concrete tunnel',
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
            'image' => 'hero-coast.png',
            'width' => 1962,
            'height' => 802,
            'alt' => 'Graphite performance coupe on a coastal overlook',
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
    $storedInventory = load_inventory_catalog();

    if (is_array($storedInventory)) {
        return $storedInventory;
    }

    return default_synapse_vehicle_inventory();
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

    $inventory[] = $vehicle;
    save_inventory_catalog($inventory);

    return $vehicle;
}

/**
 * @param array<string, mixed> $changes
 * @return array<string, mixed>|null
 */
function update_inventory_vehicle(string $vehicleId, array $changes): ?array
{
    $inventory = synapse_vehicle_inventory();

    foreach ($inventory as $index => $vehicle) {
        if (($vehicle['id'] ?? null) !== $vehicleId) {
            continue;
        }

        $nextName = trim((string) ($changes['name'] ?? $vehicle['name'] ?? ''));
        $nextDetail = trim((string) ($changes['detail'] ?? $vehicle['detail'] ?? ''));
        $nextCollection = trim((string) ($changes['collection'] ?? $vehicle['collection'] ?? ''));
        $nextPrice = max(0, (int) ($changes['price'] ?? $vehicle['price'] ?? 0));
        $nextStockQuantity = max(0, (int) ($changes['stock_quantity'] ?? $vehicle['stock_quantity'] ?? 0));
        $nextAvailability = trim((string) ($changes['availability'] ?? $vehicle['availability'] ?? ''));
        $specs = inventory_specs_from_detail($nextDetail);

        if ($nextName === '' || $nextDetail === '') {
            return null;
        }

        $inventory[$index]['name'] = $nextName;
        $inventory[$index]['detail'] = $nextDetail;
        $inventory[$index]['collection'] = $nextCollection === '' ? (string) ($vehicle['collection'] ?? 'New arrivals') : $nextCollection;
        $inventory[$index]['price'] = $nextPrice;
        $inventory[$index]['stock_quantity'] = $nextStockQuantity;
        $inventory[$index]['availability'] = $nextAvailability === '' ? (string) ($vehicle['availability'] ?? 'Available now') : $nextAvailability;
        $inventory[$index]['body'] = $specs['body'];
        $inventory[$index]['powertrain'] = $specs['powertrain'];
        $inventory[$index]['category'] = inventory_category_from_vehicle_data(
            $nextName,
            $nextDetail,
            (string) $inventory[$index]['collection'],
            $specs['body'],
            $specs['powertrain']
        );
        $inventory[$index]['alt'] = $nextName . ' vehicle listing image';

        save_inventory_catalog($inventory);

        return $inventory[$index];
    }

    return null;
}

/**
 * @return array<string, mixed>|null
 */
function delete_inventory_vehicle(string $vehicleId): ?array
{
    $inventory = synapse_vehicle_inventory();

    foreach ($inventory as $index => $vehicle) {
        if (($vehicle['id'] ?? null) !== $vehicleId) {
            continue;
        }

        $deletedVehicle = $vehicle;
        unset($inventory[$index]);
        save_inventory_catalog(array_values($inventory));

        return $deletedVehicle;
    }

    return null;
}
