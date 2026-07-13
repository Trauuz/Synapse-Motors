<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Public values that are safe to expose to frontend JavaScript.
 *
 * @return array{url: string, anonKey: string}
 */
function supabase_public_config(): array
{
    return [
        'url' => env('SUPABASE_URL', ''),
        'anonKey' => env('SUPABASE_ANON_KEY', ''),
    ];
}

/**
 * Service role auth headers for secure server-to-server requests.
 *
 * @return array<string, string>
 */
function supabase_service_headers(): array
{
    $serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY', '');

    return [
        'apikey' => $serviceRoleKey,
        'Authorization' => 'Bearer ' . $serviceRoleKey,
        'Content-Type' => 'application/json',
    ];
}

/**
 * Sends a simple authenticated request to Supabase REST to verify connectivity.
 *
 * @return array{ok: bool, status: int, body: string, error: string|null}
 */
function supabase_health_check(): array
{
    $url = rtrim(env('SUPABASE_URL', ''), '/');
    $anonKey = env('SUPABASE_ANON_KEY', '');
    $serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY', '');

    if ($url === '' || $anonKey === '' || $serviceRoleKey === '') {
        return [
            'ok' => false,
            'status' => 0,
            'body' => '',
            'error' => 'Missing one or more required Supabase environment variables.',
        ];
    }

    $headers = [
        'apikey: ' . $serviceRoleKey,
        'Authorization: Bearer ' . $serviceRoleKey,
        'Accept: application/openapi+json',
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
            'timeout' => 15,
        ],
    ]);

    $responseBody = @file_get_contents($url . '/rest/v1/', false, $context);
    $responseHeaders = $http_response_header ?? [];
    $statusLine = $responseHeaders[0] ?? '';
    $status = 0;

    if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
        $status = (int) $matches[1];
    }

    if ($responseBody === false) {
        $lastError = error_get_last();

        return [
            'ok' => false,
            'status' => $status,
            'body' => '',
            'error' => $lastError['message'] ?? 'Unknown network error.',
        ];
    }

    return [
        'ok' => $status >= 200 && $status < 300,
        'status' => $status,
        'body' => $responseBody,
        'error' => null,
    ];
}
