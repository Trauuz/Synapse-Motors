<?php

declare(strict_types=1);

$testDataDir = __DIR__ . '/../tmp/admin-portal-tests';

if (!is_dir($testDataDir)) {
    mkdir($testDataDir, 0777, true);
}

define('SYNAPSE_INVENTORY_DATA_PATH', $testDataDir . '/inventory.json');
define('ADMIN_DIRECTORY_DATA_PATH', $testDataDir . '/admin-users.json');
define('ADMIN_AUDIT_LOG_DATA_PATH', $testDataDir . '/audit-log.json');

@unlink(SYNAPSE_INVENTORY_DATA_PATH);
@unlink(ADMIN_DIRECTORY_DATA_PATH);
@unlink(ADMIN_AUDIT_LOG_DATA_PATH);

require_once __DIR__ . '/../app/helpers/session.php';
require_once __DIR__ . '/../app/helpers/view.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/vehicles.php';
require_once __DIR__ . '/../app/helpers/admin_portal.php';

app_start_session();

$_SESSION = [];

sign_in_user([
    'auth_user_id' => 'admin-auth-1',
    'email' => 'lead@example.com',
    'name' => 'Lead Seller',
    'role' => 'Admin',
]);

$currentAdmin = current_admin_account();

if (!is_array($currentAdmin)) {
    fwrite(STDERR, "Expected the current admin account to be provisioned.\n");
    exit(1);
}

if (($currentAdmin['email_verified'] ?? null) !== true) {
    fwrite(STDERR, "Expected the signed-in admin to be marked as email verified.\n");
    exit(1);
}

if (($currentAdmin['access_status'] ?? null) !== 'active') {
    fwrite(STDERR, "Expected the signed-in admin to start as active.\n");
    exit(1);
}

$invitedAdmin = invite_admin_user('Second Admin', 'second@example.com');

if (($invitedAdmin['email_verified'] ?? null) !== false) {
    fwrite(STDERR, "Expected invited admins to start as unverified.\n");
    exit(1);
}

update_admin_user($invitedAdmin['id'], [
    'name' => 'Second Admin Updated',
    'access_status' => 'disabled',
]);

$updatedAdmin = admin_user_by_id($invitedAdmin['id']);

if (($updatedAdmin['name'] ?? null) !== 'Second Admin Updated') {
    fwrite(STDERR, "Expected invited admin names to be editable.\n");
    exit(1);
}

if (($updatedAdmin['access_status'] ?? null) !== 'disabled') {
    fwrite(STDERR, "Expected admins to be disable-able.\n");
    exit(1);
}

record_admin_activity('Viewed reports', 'Reviewed stock coverage and audit activity.');
record_admin_activity('Updated inventory', 'Adjusted Apex GT stock to 7 units.');

$auditEntries = current_admin_audit_log();

if (count($auditEntries) !== 2) {
    fwrite(STDERR, 'Expected 2 current-admin audit log entries, got ' . count($auditEntries) . "\n");
    exit(1);
}

if (($auditEntries[0]['action'] ?? null) !== 'Updated inventory') {
    fwrite(STDERR, "Expected the audit log to return newest entries first.\n");
    exit(1);
}

update_inventory_vehicle('apex-gt', [
    'price' => 130000,
    'stock_quantity' => 7,
    'availability' => 'Available now',
]);

$updatedVehicle = find_vehicle_by_id('apex-gt');

if (($updatedVehicle['price'] ?? null) !== 130000) {
    fwrite(STDERR, "Expected inventory price updates to persist.\n");
    exit(1);
}

if (($updatedVehicle['stock_quantity'] ?? null) !== 7) {
    fwrite(STDERR, "Expected inventory stock updates to persist.\n");
    exit(1);
}

$createdVehicle = create_inventory_vehicle([
    'name' => 'Summit LX',
    'collection' => 'Mountain reserve',
    'detail' => 'SUV - Electric',
    'price' => 118500,
    'stock_quantity' => 5,
    'availability' => 'Available now',
]);

if (!is_array($createdVehicle)) {
    fwrite(STDERR, "Expected new inventory vehicles to be creatable.\n");
    exit(1);
}

if (($createdVehicle['id'] ?? null) !== 'summit-lx') {
    fwrite(STDERR, "Expected created vehicles to receive a slug id.\n");
    exit(1);
}

if (($createdVehicle['body'] ?? null) !== 'SUV' || ($createdVehicle['powertrain'] ?? null) !== 'Electric') {
    fwrite(STDERR, "Expected created vehicles to infer body and powertrain from detail.\n");
    exit(1);
}

$editedVehicle = update_inventory_vehicle('summit-lx', [
    'name' => 'Summit LX Touring',
    'collection' => 'Updated reserve',
    'detail' => 'Sedan - Petrol',
    'price' => 121000,
    'stock_quantity' => 2,
    'availability' => 'Viewing this week',
]);

if (($editedVehicle['name'] ?? null) !== 'Summit LX Touring' || ($editedVehicle['body'] ?? null) !== 'Sedan') {
    fwrite(STDERR, "Expected inventory edits to update listing details.\n");
    exit(1);
}

$deletedVehicle = delete_inventory_vehicle('summit-lx');

if (($deletedVehicle['id'] ?? null) !== 'summit-lx' || find_vehicle_by_id('summit-lx') !== null) {
    fwrite(STDERR, "Expected inventory vehicles to be removable.\n");
    exit(1);
}

if (!admin_access_is_enabled()) {
    fwrite(STDERR, "Expected the current admin to retain access.\n");
    exit(1);
}

echo "Admin portal helper contract passed.\n";
