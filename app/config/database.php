<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

function database_dsn(): string
{
    $configuredDsn = trim((string) env('DB_DSN', ''));

    if ($configuredDsn !== '') {
        return $configuredDsn;
    }

    $host = trim((string) env('DB_HOST', ''));
    $databaseName = trim((string) env('DB_NAME', ''));

    if ($host === '' || $databaseName === '') {
        return '';
    }

    $port = trim((string) env('DB_PORT', '3306'));
    $charset = trim((string) env('DB_CHARSET', 'utf8mb4'));

    return sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $host,
        $port === '' ? '3306' : $port,
        $databaseName,
        $charset === '' ? 'utf8mb4' : $charset
    );
}

function database_username(): string
{
    return (string) env('DB_USER', '');
}

function database_password(): string
{
    return (string) env('DB_PASSWORD', '');
}

function database_is_configured(): bool
{
    return database_dsn() !== '';
}

function app_public_base_path(): string
{
    $scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    $basePath = preg_replace('#/(auth|admin|buyer|seller)$#', '', $scriptDirectory);

    if (!is_string($basePath) || $basePath === '.' || $basePath === '/') {
        return '';
    }

    return rtrim($basePath, '/');
}

function app_public_url(string $path = ''): string
{
    $configuredBaseUrl = trim((string) env('APP_URL', ''));

    if ($configuredBaseUrl !== '') {
        $baseUrl = rtrim($configuredBaseUrl, '/');
        return $path === '' ? $baseUrl : $baseUrl . '/' . ltrim($path, '/');
    }

    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));

    if ($host === '') {
        return '';
    }

    $https = $_SERVER['HTTPS'] ?? '';
    $scheme = ($https !== '' && $https !== 'off') ? 'https' : 'http';
    $baseUrl = $scheme . '://' . $host . app_public_base_path();

    return $path === '' ? $baseUrl : $baseUrl . '/' . ltrim($path, '/');
}
