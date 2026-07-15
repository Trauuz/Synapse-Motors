<?php

declare(strict_types=1);

function app_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent()) {
        return;
    }

    $currentSavePath = (string) ini_get('session.save_path');

    if ($currentSavePath === '' || !is_dir($currentSavePath) || !is_writable($currentSavePath)) {
        $fallbackSavePath = dirname(__DIR__, 2) . '/tmp/sessions';

        if (!is_dir($fallbackSavePath)) {
            mkdir($fallbackSavePath, 0777, true);
        }

        if (is_dir($fallbackSavePath) && is_writable($fallbackSavePath)) {
            session_save_path($fallbackSavePath);
        }
    }

    session_start();
}

function flash_set(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_pull(string $key, mixed $default = null): mixed
{
    if (!isset($_SESSION['_flash']) || !array_key_exists($key, $_SESSION['_flash'])) {
        return $default;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);

    if ($_SESSION['_flash'] === []) {
        unset($_SESSION['_flash']);
    }

    return $value;
}

function csrf_token(): string
{
    if (isset($_SESSION['_csrf']) && is_string($_SESSION['_csrf'])) {
        return $_SESSION['_csrf'];
    }

    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

function verify_csrf_token(?string $token): bool
{
    if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        return false;
    }

    if ($token === null || $token === '') {
        return false;
    }

    return hash_equals($_SESSION['_csrf'], $token);
}
