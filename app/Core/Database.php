<?php

declare(strict_types=1);

namespace PrimeSales\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = $GLOBALS['database_config'] ?? [];
        $host = trim((string) ($config['host'] ?? 'localhost'));
        $port = (int) ($config['port'] ?? 3306);
        $database = trim((string) ($config['database'] ?? ''));
        $charset = (string) ($config['charset'] ?? 'utf8mb4');
        if ($database === '') {
            throw new PDOException('Missing database name in app/Config/database.php.');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
        self::$connection = new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci",
        ]);

        return self::$connection;
    }
}
