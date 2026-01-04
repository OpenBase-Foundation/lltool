<?php
declare(strict_types=1);

namespace LLTool\Database;

use PDO;
use PDOException;
use LLTool\Support\Config;

final class Database
{
    private static ?PDO $connection = null;

    /**
     * Get database connection instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = self::loadDatabaseConfig();
        
        $dsn = match ($config['driver']) {
            'pgsql' => sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $config['host'],
                $config['port'],
                $config['database']
            ),
            'mysql' => sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['database']
            ),
            default => throw new \RuntimeException('Unsupported database driver: ' . $config['driver']),
        };

        try {
            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return self::$connection;
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Load database configuration from .env file.
     */
    private static function loadDatabaseConfig(): array
    {
        $driver = Config::get('DB_DRIVER', 'mysql');
        $host = Config::get('DB_HOST', 'localhost');
        $port = (int)Config::get('DB_PORT', '3306');
        $database = Config::get('DB_DATABASE');
        $username = Config::get('DB_USERNAME');
        $password = Config::get('DB_PASSWORD');
        
        if (empty($database) || empty($username)) {
            throw new \RuntimeException('Database configuration not found. Please run the installer.');
        }
        
        return [
            'driver' => $driver,
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];
    }
}

