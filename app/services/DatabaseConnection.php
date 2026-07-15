<?php

declare(strict_types=1);

final class DatabaseConnection
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $dsn = database_dsn();

        if ($dsn === '') {
            throw new RuntimeException('Database environment variables are missing.');
        }

        $pdo = new PDO(
            $dsn,
            database_username(),
            database_password(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        self::$instance = $pdo;

        return $pdo;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
