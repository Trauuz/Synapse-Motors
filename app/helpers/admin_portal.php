<?php

declare(strict_types=1);

const ADMIN_DEFAULT_ACCESS_STATUS = 'active';
const ADMIN_SESSION_SYNC_INTERVAL_SECONDS = 300;

/**
 * @param array<string, mixed> $admin
 * @return array<string, mixed>
 */
function normalize_admin_record(array $admin): array
{
    $admin['email_verified'] = ($admin['email_verified_at'] ?? null) !== null;

    return $admin;
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
    $adminId = (string) ($admin['id'] ?? '');

    if ($adminId === '') {
        return normalize_admin_record(user_repository()->create($admin));
    }

    return normalize_admin_record(user_repository()->update($adminId, $admin));
}

/**
 * @return array<int, array<string, mixed>>
 */
function admin_user_accounts(): array
{
    return array_map('normalize_admin_record', user_repository()->allAdmins());
}

function admin_user_count(): int
{
    return user_repository()->countAdmins();
}

function active_admin_user_count(): int
{
    return user_repository()->countAdminsByAccessStatus(ADMIN_DEFAULT_ACCESS_STATUS);
}

function pending_admin_user_count(): int
{
    return user_repository()->countAdminsPendingVerification();
}

/**
 * @return array<string, mixed>|null
 */
function admin_user_by_id(string $adminId): ?array
{
    $admin = user_repository()->findById($adminId);

    if (!is_array($admin) || (string) ($admin['role'] ?? '') !== 'Admin') {
        return null;
    }

    return normalize_admin_record($admin);
}

/**
 * @return array<string, mixed>|null
 */
function admin_user_by_email(string $email): ?array
{
    $admin = user_repository()->findAdminByEmail($email);

    if (!is_array($admin)) {
        return null;
    }

    return normalize_admin_record($admin);
}

function admin_session_cache_key(): string
{
    return 'current_admin_account';
}

function admin_session_sync_cache_key(): string
{
    return 'current_admin_account_synced_at';
}

/**
 * @param array<string, mixed> $admin
 */
function cache_current_admin_account(array $admin): array
{
    $_SESSION[admin_session_cache_key()] = $admin;
    $_SESSION[admin_session_sync_cache_key()] = time();

    return $admin;
}

function current_admin_session_snapshot(): ?array
{
    $admin = $_SESSION[admin_session_cache_key()] ?? null;

    return is_array($admin) ? $admin : null;
}

function admin_session_snapshot_is_fresh(): bool
{
    $syncedAt = $_SESSION[admin_session_sync_cache_key()] ?? null;

    if (!is_int($syncedAt)) {
        return false;
    }

    return (time() - $syncedAt) < ADMIN_SESSION_SYNC_INTERVAL_SECONDS;
}

/**
 * @param array<string, mixed> $existingAdmin
 * @param array<string, mixed> $currentUser
 */
function admin_profile_needs_sync(array $existingAdmin, array $currentUser): bool
{
    $expectedName = (string) ($currentUser['name'] ?? $existingAdmin['name'] ?? 'Admin');
    $expectedEmail = normalize_admin_email((string) ($currentUser['email'] ?? ''));
    $currentName = (string) ($existingAdmin['name'] ?? '');
    $currentEmail = normalize_admin_email((string) ($existingAdmin['email'] ?? ''));

    if ($currentName !== $expectedName || $currentEmail !== $expectedEmail) {
        return true;
    }

    if ((string) ($existingAdmin['role'] ?? '') !== 'Admin') {
        return true;
    }

    $lastSeenAt = (string) ($existingAdmin['last_seen_at'] ?? '');

    if ($lastSeenAt === '') {
        return true;
    }

    $lastSeenTimestamp = strtotime($lastSeenAt . ' UTC');

    if ($lastSeenTimestamp === false) {
        return true;
    }

    return (time() - $lastSeenTimestamp) >= ADMIN_SESSION_SYNC_INTERVAL_SECONDS;
}

/**
 * @return array<string, mixed>|null
 */
function current_admin_account(): ?array
{
    static $cachedAdmin = null;
    static $hasResolved = false;

    if ($hasResolved) {
        return is_array($cachedAdmin) ? $cachedAdmin : null;
    }

    if (!is_admin()) {
        $hasResolved = true;
        return null;
    }

    $user = current_user();

    if (!is_array($user)) {
        $hasResolved = true;
        return null;
    }

    $sessionAdmin = current_admin_session_snapshot();
    $sessionUserId = trim((string) ($sessionAdmin['id'] ?? ''));
    $authUserId = trim((string) ($user['id'] ?? $user['auth_user_id'] ?? ''));
    $authEmail = normalize_admin_email((string) ($user['email'] ?? ''));

    if (
        is_array($sessionAdmin)
        && admin_session_snapshot_is_fresh()
        && (
            ($sessionUserId !== '' && $sessionUserId === $authUserId)
            || normalize_admin_email((string) ($sessionAdmin['email'] ?? '')) === $authEmail
        )
    ) {
        $cachedAdmin = $sessionAdmin;
        $hasResolved = true;

        return $cachedAdmin;
    }

    $userId = $authUserId;
    $email = $authEmail;

    if ($userId !== '') {
        $existingAdmin = admin_user_by_id($userId);

        if (is_array($existingAdmin)) {
            if (admin_profile_needs_sync($existingAdmin, $user)) {
                $existingAdmin = persist_admin_record([
                    'id' => $userId,
                    'name' => (string) ($user['name'] ?? $existingAdmin['name'] ?? 'Admin'),
                    'email' => $email,
                    'role' => 'Admin',
                    'password_hash' => $existingAdmin['password_hash'] ?? null,
                    'address' => (string) ($existingAdmin['address'] ?? ''),
                    'contact_no' => (string) ($existingAdmin['contact_no'] ?? ''),
                    'email_verified_at' => $existingAdmin['email_verified_at'] ?? gmdate('Y-m-d H:i:s'),
                    'access_status' => (string) ($existingAdmin['access_status'] ?? ADMIN_DEFAULT_ACCESS_STATUS),
                    'invited_by_user_id' => $existingAdmin['invited_by_user_id'] ?? null,
                    'invited_at' => $existingAdmin['invited_at'] ?? null,
                    'last_seen_at' => gmdate('Y-m-d H:i:s'),
                ]);
            }

            $cachedAdmin = cache_current_admin_account($existingAdmin);
            $hasResolved = true;

            return $cachedAdmin;
        }
    }

    $adminByEmail = admin_user_by_email($email);

    if (is_array($adminByEmail)) {
        if (admin_profile_needs_sync($adminByEmail, $user)) {
            $adminByEmail = persist_admin_record([
                'id' => (string) ($adminByEmail['id'] ?? ''),
                'name' => (string) ($user['name'] ?? $adminByEmail['name'] ?? 'Admin'),
                'email' => $email,
                'role' => 'Admin',
                'password_hash' => $adminByEmail['password_hash'] ?? null,
                'address' => (string) ($adminByEmail['address'] ?? ''),
                'contact_no' => (string) ($adminByEmail['contact_no'] ?? ''),
                'email_verified_at' => $adminByEmail['email_verified_at'] ?? gmdate('Y-m-d H:i:s'),
                'access_status' => (string) ($adminByEmail['access_status'] ?? ADMIN_DEFAULT_ACCESS_STATUS),
                'invited_by_user_id' => $adminByEmail['invited_by_user_id'] ?? null,
                'invited_at' => $adminByEmail['invited_at'] ?? null,
                'last_seen_at' => gmdate('Y-m-d H:i:s'),
            ]);
        }

        $cachedAdmin = cache_current_admin_account($adminByEmail);
        $hasResolved = true;

        return $cachedAdmin;
    }

    $cachedAdmin = cache_current_admin_account(normalize_admin_record(user_repository()->create([
        'id' => $userId !== '' ? $userId : synapse_uuid(),
        'name' => (string) ($user['name'] ?? 'Admin'),
        'email' => $email,
        'password_hash' => null,
        'role' => 'Admin',
        'address' => '',
        'contact_no' => '',
        'email_verified_at' => gmdate('Y-m-d H:i:s'),
        'access_status' => ADMIN_DEFAULT_ACCESS_STATUS,
        'invited_by_user_id' => null,
        'invited_at' => null,
        'last_seen_at' => gmdate('Y-m-d H:i:s'),
    ])));
    $hasResolved = true;

    return $cachedAdmin;
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

    foreach (admin_user_accounts() as $existingAdmin) {
        if (normalize_admin_email((string) ($existingAdmin['email'] ?? '')) === $normalizedEmail) {
            return $existingAdmin;
        }
    }

    return normalize_admin_record(user_repository()->create([
        'name' => trim($name) === '' ? 'New admin' : trim($name),
        'email' => $normalizedEmail,
        'password_hash' => null,
        'role' => 'Admin',
        'address' => '',
        'contact_no' => '',
        'email_verified_at' => null,
        'access_status' => ADMIN_DEFAULT_ACCESS_STATUS,
        'invited_by_user_id' => is_array($currentAdmin) ? (string) ($currentAdmin['id'] ?? '') : null,
        'invited_at' => gmdate('Y-m-d H:i:s'),
        'last_seen_at' => null,
    ]));
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

    $nextStatus = (string) ($changes['access_status'] ?? $admin['access_status'] ?? ADMIN_DEFAULT_ACCESS_STATUS);

    if ($nextStatus !== 'active' && $nextStatus !== 'disabled') {
        $nextStatus = ADMIN_DEFAULT_ACCESS_STATUS;
    }

    $currentAdmin = current_admin_account();
    $currentAdminId = is_array($currentAdmin) ? (string) ($currentAdmin['id'] ?? '') : '';

    if ($currentAdminId !== '' && $currentAdminId === $adminId && $nextStatus === 'disabled') {
        $nextStatus = ADMIN_DEFAULT_ACCESS_STATUS;
    }

    return user_repository()->update($adminId, [
        'name' => trim((string) ($changes['name'] ?? $admin['name'] ?? '')) ?: 'Admin',
        'email' => (string) ($admin['email'] ?? ''),
        'password_hash' => $admin['password_hash'] ?? null,
        'role' => 'Admin',
        'address' => (string) ($admin['address'] ?? ''),
        'contact_no' => (string) ($admin['contact_no'] ?? ''),
        'email_verified_at' => $admin['email_verified_at'] ?? null,
        'access_status' => $nextStatus,
        'invited_by_user_id' => $admin['invited_by_user_id'] ?? null,
        'invited_at' => $admin['invited_at'] ?? null,
        'last_seen_at' => $admin['last_seen_at'] ?? null,
    ]);
}

/**
 * @return array<int, array<string, mixed>>
 */
function load_admin_audit_log(): array
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return [];
    }

    return admin_audit_log_repository()->forActor((string) ($currentAdmin['id'] ?? ''));
}

function current_admin_audit_log_count(): int
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return 0;
    }

    return admin_audit_log_repository()->countForActor((string) ($currentAdmin['id'] ?? ''));
}

/**
 * @return array<int, array<string, mixed>>
 */
function current_admin_recent_audit_log(int $limit = 5): array
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return [];
    }

    return admin_audit_log_repository()->forActorPage(
        (string) ($currentAdmin['id'] ?? ''),
        max(1, $limit),
        0
    );
}

function record_admin_activity(string $action, string $summary): void
{
    $currentAdmin = current_admin_account();

    if (!is_array($currentAdmin)) {
        return;
    }

    admin_audit_log_repository()->create([
        'actor_user_id' => (string) ($currentAdmin['id'] ?? ''),
        'actor_name' => (string) ($currentAdmin['name'] ?? 'Admin'),
        'actor_email' => (string) ($currentAdmin['email'] ?? ''),
        'action' => $action,
        'summary' => $summary,
        'occurred_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s.u'),
    ]);
}

/**
 * @return array<int, array<string, mixed>>
 */
function current_admin_audit_log(): array
{
    return load_admin_audit_log();
}

/**
 * @return array{
 *   entries: array<int, array<string, mixed>>,
 *   total_entries: int,
 *   total_pages: int,
 *   current_page: int,
 *   per_page: int,
 *   has_previous_page: bool,
 *   has_next_page: bool
 * }
 */
function current_admin_audit_log_page(int $page = 1, int $perPage = 10): array
{
    $normalizedPerPage = max(1, $perPage);
    $totalEntries = current_admin_audit_log_count();
    $totalPages = max(1, (int) ceil($totalEntries / $normalizedPerPage));
    $currentPage = min(max(1, $page), $totalPages);
    $offset = ($currentPage - 1) * $normalizedPerPage;
    $entries = current_admin_recent_audit_log($normalizedPerPage);

    if ($offset > 0) {
        $currentAdmin = current_admin_account();
        $entries = is_array($currentAdmin)
            ? admin_audit_log_repository()->forActorPage((string) ($currentAdmin['id'] ?? ''), $normalizedPerPage, $offset)
            : [];
    }

    return [
        'entries' => $entries,
        'total_entries' => $totalEntries,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'per_page' => $normalizedPerPage,
        'has_previous_page' => $currentPage > 1,
        'has_next_page' => $currentPage < $totalPages,
    ];
}
