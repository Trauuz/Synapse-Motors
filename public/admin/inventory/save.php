<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/bootstrap.php';

require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verify_csrf_token($_POST['_csrf'] ?? null)) {
    header('Location: ../inventory.php');
    exit;
}

$action = trim((string) ($_POST['inventory_action'] ?? 'update'));
$vehicleId = trim((string) ($_POST['vehicle_id'] ?? ''));

if ($action === 'create') {
    $createPayload = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'collection' => trim((string) ($_POST['collection'] ?? '')),
        'detail' => trim((string) ($_POST['detail'] ?? '')),
        'price' => (int) ($_POST['price'] ?? 0),
        'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
        'availability' => trim((string) ($_POST['availability'] ?? '')),
    ];

    $createdVehicle = create_inventory_vehicle($createPayload);

    if (!is_array($createdVehicle)) {
        flash_set('admin_feedback', ['tone' => 'error', 'message' => 'Add a vehicle name and detail before saving.']);
        flash_set('admin_inventory_create_form', $createPayload);
        header('Location: ../inventory.php?create=1');
        exit;
    }

    record_admin_activity('Added inventory', 'Added ' . (string) ($createdVehicle['name'] ?? 'vehicle') . ' to the inventory.');
    flash_set('admin_feedback', ['tone' => 'success', 'message' => 'Vehicle added successfully.']);

    header('Location: ../inventory.php');
    exit;
}

if ($action === 'delete') {
    if ($vehicleId === '') {
        flash_set('admin_feedback', ['tone' => 'error', 'message' => 'Choose a vehicle to remove.']);
        header('Location: ../inventory.php');
        exit;
    }

    $deletedVehicle = delete_inventory_vehicle($vehicleId);

    if (!is_array($deletedVehicle)) {
        flash_set('admin_feedback', ['tone' => 'error', 'message' => 'Vehicle not found.']);
        header('Location: ../inventory.php');
        exit;
    }

    record_admin_activity('Deleted inventory', 'Removed ' . (string) ($deletedVehicle['name'] ?? 'vehicle') . ' from the inventory.');
    flash_set('admin_feedback', ['tone' => 'success', 'message' => 'Vehicle removed successfully.']);

    header('Location: ../inventory.php');
    exit;
}

if ($vehicleId === '') {
    flash_set('admin_feedback', ['tone' => 'error', 'message' => 'Choose a vehicle to update.']);
    header('Location: ../inventory.php');
    exit;
}

$updatedVehicle = update_inventory_vehicle($vehicleId, [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'collection' => trim((string) ($_POST['collection'] ?? '')),
    'detail' => trim((string) ($_POST['detail'] ?? '')),
    'price' => (int) ($_POST['price'] ?? 0),
    'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
    'availability' => trim((string) ($_POST['availability'] ?? '')),
]);

if (!is_array($updatedVehicle)) {
    flash_set('admin_feedback', ['tone' => 'error', 'message' => 'Add a vehicle name and detail before saving.']);
    flash_set('admin_inventory_edit_form', [
        'vehicle_id' => $vehicleId,
        'name' => trim((string) ($_POST['name'] ?? '')),
        'collection' => trim((string) ($_POST['collection'] ?? '')),
        'detail' => trim((string) ($_POST['detail'] ?? '')),
        'price' => (int) ($_POST['price'] ?? 0),
        'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
        'availability' => trim((string) ($_POST['availability'] ?? '')),
    ]);
    header('Location: ../inventory.php?edit=' . urlencode($vehicleId));
    exit;
}

record_admin_activity('Updated inventory', 'Updated ' . (string) ($updatedVehicle['name'] ?? 'vehicle') . ' inventory details.');
flash_set('admin_feedback', ['tone' => 'success', 'message' => 'Inventory updated successfully.']);

header('Location: ../inventory.php');
exit;
