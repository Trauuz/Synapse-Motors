<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

function smtp_host(): string
{
    return trim((string) env('SMTP_HOST', ''));
}

function smtp_port(): int
{
    $port = trim((string) env('SMTP_PORT', '587'));

    if ($port === '' || !ctype_digit($port)) {
        return 587;
    }

    return (int) $port;
}

function smtp_username(): string
{
    return trim((string) env('SMTP_USERNAME', ''));
}

function smtp_password(): string
{
    return (string) env('SMTP_PASSWORD', '');
}

function smtp_encryption(): string
{
    $encryption = strtolower(trim((string) env('SMTP_ENCRYPTION', 'tls')));

    if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
        return 'tls';
    }

    return $encryption;
}

function mail_from_address(): string
{
    return trim((string) env('MAIL_FROM_ADDRESS', ''));
}

function mail_from_name(): string
{
    return trim((string) env('MAIL_FROM_NAME', 'Synapse Motors'));
}
