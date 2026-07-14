<?php

declare(strict_types=1);

const ADMIN_DEFAULT_ACCESS_STATUS = 'active';

function admin_directory_data_path(): string
{
    if (defined('ADMIN_DIRECTORY_DATA_PATH')) {
        return (string) ADMIN_DIRECTORY_DATA_PATH;
    }

    return dirname(__DIR__, 2) . '/tmp/admin-users.json';
}

function admin_audit_log_data_path(): string
{
    if (defined('ADMIN_AUDIT_LOG_DATA_PATH')) {
        return (string) ADMIN_AUDIT_LOG_DATA_PATH;
    }

    return dirname(__DIR__, 2) . '/tmp/admin-audit-log.json';
}

function admin_portal_storage_dir(string $filePath): void
{
    $directory = dirname($filePath);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

/**
 * @return array<int, array<string, mixed>>
 */
function load_admin_directory(): array
{
    $filePath = admin_directory_data_path();

    if (!is_file($filePath)) {
        return [];
    }

    $contents = file_get_contents($filePath);

    if (!is_string($contents) || trim($contents) === '') {
        return [];
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
}

/**
 * @param array<int, array<string, mixed>> $admins
 */
function save_admin_directory(array $admins): void
{
    $filePath = admin_directory_data_path();
    admin_portal_storage_dir($filePath);
    file_put_contents($filePath, json_encode(array_values($admins), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

function current_admin_lookup_key(): ?string
{
    $user = current_user();

    if (!is_array($user)) {
        return null;
    }

    $authUserId = $user['auth_user_id'] ?? null;

    if (is_string($authUserId) && $authUserId !== '') {
        return 'auth:' . $authUserId;
    }

    $email = $user['email'] ?? null;

    if (is_string($email) && $email !== '') {
        return 'email:' . strtolower($email);
    }

    return null;
}

function normalize_admin_email(string $email): string
{
    return strtolower(trim($email));
}

/**
 * @param array<string, mixed> $admin
 * @return array<string, mixed>
 */
function persist_admin_record(array $admin): array
{
    $admins = load_admin_directory();
    $updatedAdmins = [];
    $matched = false;

    foreach ($admins as $existingAdmin) {
        if (($existingAdmin['id'] ?? null) !== ($admin['id'] ?? null)) {
            $updatedAdmins[] = $existingAdmin;
            continue;
        }

        $updatedAdmins[] = $admin;
        $matched = true;
    }

    if (!$matched) {
        $updatedAdmins[] = $admin;
    }

    save_admin_directory($updatedAdmins);

    return $admin;
}

/**
 * @return array<int, array<string, mixed>>
 */
function admin_user_accounts(): array
{
    $admins = load_admin_directory();

    usort($admins, static function (array $left, array $right): int {
        return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
    });

    return $admins;
}

/**
 * @return array<string, mixed>|null
 */
function admin_user_by_id(string $adminId): ?array
{
    foreach (load_admin_directory() as $admin) {
        if (($admin['id'] ?? null) === $adminId) {
            return $admin;
        }
    }

    return null;
}

/**
 * @return array<string, mixed>|null
 */
function current_admin_account(): ?array
{
    if (!is_admin()) {
        return null;
    }

    $user = current_user();

    if (!is_array($user)) {
        return null;
    }

    $email = normalize_admin_email((string) ($user['email'] ?? ''));
    $authUserId = trim((string) ($user['auth_user_id'] ?? ''));

    if ($email === '') {
        return null;
    }

    foreach (load_admin_directory() as $admin) {
        $adminEmail = normalize_admin_email((string) ($admin['email'] ?? ''));
        $adminAuthUserId = trim((string) ($admin['auth_user_id'] ?? ''));

        if ($adminAuthUserId !== '' && $adminAuthUserId === $authUserId) {
            $admin['name'] = (string) ($user['name'] ?? $admin['name'] ?? 'Admin');
            $admin['email_verified'] = true;
            $admin['last_seen_at'] = gmdate('c');

            return persist_admin_record($admin);
        }

        if ($adminEmail !== '' && $adminEmail === $email) {
            $admin['auth_user_id'] = $authUserId;
            $admin['name'] = (string) ($user['name'] ?? $admin['name'] ?? 'Admin');
            $admin['email_verified'] = true;
            $admin['last_seen_at'] = gmdate('c');

            return persist_admin_record($admin);
        }
    }

    return persist_admin_record([
        'id' => 'admin-' . bin2hex(random_bytes(6)),
        'auth_user_id' => $authUserId,
        'name' => (string) ($user['name'] ?? 'Admin'),
        'email' => $email,
        'role' => 'Admin',
        'email_verified' => true,
        'access_status' => ADMIN_DEFAULT_ACCESS_STATUS,
        'invited_by' => null,
        'invited_at' => null,
        'created_at' => gmdate('c'),
        'last_seen_at' => gmdate('c'),
    ]);
}

function admin_access_is_enabled(): bool
{
    $admin = current_admin_account();

    if (!is_array($admin)) {
        return false;
    }

    return ($admin['access_status'] ?? '') === ADMIN_DEFAULT_ACCESS_STATUS;
}

/**
 * @return array<string, mixed>
 */
function invite_admin_user(string $name, string $email): array
{
    $normalizedEmail = normalize_admin_email($email);
    $currentAdmin = current_admin_account();

    foreach (load_admin_directory() as $existingAdmin) {
        if (normalize_admin_email((string) ($existingAdmin['email'] ?? '')) !== $normalizedEmail) {
            continue;
        }

        return $existingAdmin;
    }

    $record = [
        'id' => 'admin-' . bin2hex(random_bytes(6)),
        'auth_user_id' => '',
        'name' => trim($name) === '' ? 'New admin' : trim($name),
        'email' => $normalizedEmail,
        'role' => 'Admin',
        'email_verified' => false,
        'access_status' => ADMIN_DEFAULT_ACCESS_STATUS,
        'invited_by' => is_array($currentAdmin) ? (string) ($currentAdmin['email'] ?? '') : null,
        'invited_at' => gmdate('c'),
        'created_at' => gmdate('c'),
        'last_seen_at' => null,
    ];

    return persist_admin_record($record);
}

/**
 * @param array<string, mixed> $changes
 * @return array<string, mixed>
 */
function update_admin_user(string $adminId, array $changes): array
{
    $admin = admin_user_by_id($adminId);

    if (!is_array($admin)) {
        throw new RuntimeException('Admin account not found.');
    }

    $nextName = trim((string) ($changes['name'] ?? $admin['name'] ?? 'Admin'));
    $nextStatus = (string) ($changes['access_status'] ?? $admin['access_status'] ?? ADMIN_DEFAULT_ACCESS_STATUS);

    if ($nextStatus !== 'active' && $nextStatus !== 'disabled') {
        $nextStatus = ADMIN_DEFAULT_ACCESS_STATUS;
    }

    $currentAdmin = current_admin_account();
    $currentAdminId = is_array($currentAdmin) ? (string) ($currentAdmin['id'] ?? '') : '';

    if ($currentAdminId !== '' && $currentAdminId === $adminId && $nextStatus === 'disabled') {
        $nextStatus = ADMIN_DEFAULT_ACCESS_STATUS;
    }

    $admin['name'] = $nextName === '' ? 'Admin' : $nextName;
    $admin['access_status'] = $nextStatus;

    return persist_admin_record($admin);
}

/**
 * @return array<int, array<string, mixed>>
 */
function load_admin_audit_log(): array
{
    $filePath = admin_audit_log_data_path();

    if (!is_file($filePath)) {
        return [];
    }

    $contents = file_get_contents($filePath);

    if (!is_string($contents) || trim($contents) === '') {
        return [];
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
}

/**
 * @param array<int, array<string, mixed>> $entries
 */
function save_admin_audit_log(array $entries): void
{
    $filePath = admin_audit_log_data_path();
    admin_portal_storage_dir($filePath);
    file_put_contents($filePath, json_encode(array_values($entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
}

function record_admin_activity(string $action, string $summary): void
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return;
    }

    $entries = load_admin_audit_log();
    array_unshift($entries, [
        'id' => 'audit-' . bin2hex(random_bytes(6)),
        'actor_admin_id' => (string) ($currentAdmin['id'] ?? ''),
        'actor_name' => (string) ($currentAdmin['name'] ?? 'Admin'),
        'actor_email' => (string) ($currentAdmin['email'] ?? ''),
        'action' => $action,
        'summary' => $summary,
        'occurred_at' => gmdate('c'),
    ]);

    save_admin_audit_log(array_slice($entries, 0, 200));
}

/**
 * @return array<int, array<string, mixed>>
 */
function current_admin_audit_log(): array
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return [];
    }

    $currentAdminId = (string) ($currentAdmin['id'] ?? '');

    return array_values(array_filter(load_admin_audit_log(), static function (array $entry) use ($currentAdminId): bool {
        return (string) ($entry['actor_admin_id'] ?? '') === $currentAdminId;
    }));
}
