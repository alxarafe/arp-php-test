<?php

declare(strict_types=1);

namespace ArpPhpTest\Infrastructure;

use PDO;

final class Database
{
    public static function connect(?string $databaseOverride = null): PDO
    {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '3306';
        $database = $databaseOverride ?? (getenv('DB_NAME') ?: 'arp_php_test');
        $user = getenv('DB_USER') ?: 'arp';
        $password = getenv('DB_PASSWORD') ?: '';

        return new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
}
