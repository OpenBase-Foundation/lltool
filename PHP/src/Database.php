<?php

namespace App;

class Database {
    private $pdo;

    public function __construct() {
        $this->loadDotEnv(__DIR__ . '/../.env');

        $dsn = getenv('DB_DSN') ?: null;
        $user = getenv('DB_USER') ?: null;
        $pass = getenv('DB_PASS') ?: null;

        // Support explicit MySQL env vars if DB_DSN isn't provided
        if (!$dsn) {
            $dbHost = getenv('DB_HOST') ?: null;
            $dbName = getenv('DB_NAME') ?: null;
            $dbPort = getenv('DB_PORT') ?: null;
            if ($dbHost && $dbName) {
                $portPart = $dbPort ? (";port={$dbPort}") : '';
                $dsn = "mysql:host={$dbHost};dbname={$dbName}{$portPart};charset=utf8mb4";
                $user = $user ?: getenv('DB_USER');
                $pass = $pass ?: getenv('DB_PASS');
            }
        }

        // Fallback to local sqlite file if no DSN determined
        if (!$dsn) {
            $dsn = 'sqlite:' . __DIR__ . '/../database/database.sqlite';
        }

        $this->pdo = new \PDO($dsn, $user, $pass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    private function loadDotEnv($path) {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (!strpos($line, '=')) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
        }
    }

    public function getPdo() {
        return $this->pdo;
    }
}
