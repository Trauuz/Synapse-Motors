<?php

declare(strict_types=1);

function sign_in_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['auth_user'] = $user;
}

function sign_out_user(): void
{
    unset($_SESSION['auth_user']);
    session_regenerate_id(true);
}

function current_user(): ?array
{
    $user = $_SESSION['auth_user'] ?? null;

    return is_array($user) ? $user : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    return strcasecmp((string) ($user['role'] ?? ''), 'Admin') === 0;
}

function require_admin(): void
{
    if (is_admin() && (!function_exists('admin_access_is_enabled') || admin_access_is_enabled())) {
        return;
    }

    header('Location: ../index.php');
    exit;
}

function auth_form_state(): array
{
    static $state = null;

    if ($state !== null) {
        return $state;
    }

    $state = flash_pull('auth_form', [
        'mode' => 'signin',
        'errors' => [],
        'old' => [],
        'message' => null,
        'open' => false,
    ]);

    return $state;
}

function has_auth_feedback(): bool
{
    $state = auth_form_state();

    if (($state['message'] ?? null) !== null) {
        return true;
    }

    return ($state['errors'] ?? []) !== [];
}

function current_request_path(): string
{
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/index.php';
    $path = parse_url($requestUri, PHP_URL_PATH);
    $query = parse_url($requestUri, PHP_URL_QUERY);
    $basename = basename((string) $path);

    if ($basename === '') {
        $basename = 'index.php';
    }

    if (!is_string($query) || $query === '') {
        return $basename;
    }

    return $basename . '?' . $query;
}
