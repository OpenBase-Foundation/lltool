<?php
// Simple migration runner — applies appropriate SQL file based on environment
require_once __DIR__ . '/../src/Database.php';

use App\Database;

// load .env via Database constructor
$db = new Database();
$pdo = $db->getPdo();

// Determine driver
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Detected PDO driver: $driver\n";

if ($driver === 'mysql') {
    $sql = file_get_contents(__DIR__ . '/../migrations/schema.mysql.sql');
} else {
    // fallback to sqlite-friendly schema
    $sql = file_get_contents(__DIR__ . '/../migrations/schema.sql');
}

if ($sql === false) {
    echo "Migration file not found.\n";
    exit(1);
}

// Split into statements by semicolon — naive but sufficient for simple SQL
$stmts = array_filter(array_map('trim', explode(';', $sql)));

foreach ($stmts as $stmt) {
    if ($stmt === '') continue;
    try {
        $pdo->exec($stmt);
    } catch (Exception $e) {
        echo "Failed to execute statement: " . $e->getMessage() . "\n";
        // continue running other statements
    }
}

echo "Migrations applied (best-effort).\n";
