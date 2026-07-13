<?php

declare(strict_types=1);

/**
 * Loads simple KEY=value pairs from the project .env file once per request.
 */
function load_project_env(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $loaded = true;
    $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';

    if (!is_file($envPath) || !is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $separator = strpos($trimmed, '=');
        if ($separator === false) {
            continue;
        }

        $key = trim(substr($trimmed, 0, $separator));
        $value = trim(substr($trimmed, $separator + 1));

        if ($value !== '' && (
            ($value[0] === '"' && str_ends_with($value, '"')) ||
            ($value[0] === "'" && str_ends_with($value, "'"))
        )) {
            $value = substr($value, 1, -1);
        }

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

/**
 * Returns an environment value after loading the local .env file.
 */
function env(string $key, ?string $default = null): ?string
{
    load_project_env();

    $value = getenv($key);
    return $value === false ? $default : $value;
}
