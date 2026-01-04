<?php
declare(strict_types=1);

namespace LLTool\Database;

use PDO;
use PDOException;

final class MigrationRunner
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Run all pending migrations.
     */
    public function run(): array
    {
        $this->ensureMigrationsTable();
        
        $executed = $this->getExecutedMigrations();
        $available = $this->getAvailableMigrations();
        $pending = array_diff($available, $executed);
        
        $results = [];
        
        foreach ($pending as $migration) {
            try {
                $this->pdo->beginTransaction();
                
                $migrationClass = $this->loadMigration($migration);
                $migrationClass->up($this->pdo);
                
                $this->markAsExecuted($migration);
                
                $this->pdo->commit();
                
                $results[$migration] = ['status' => 'success'];
            } catch (\Exception $e) {
                $this->pdo->rollBack();
                $results[$migration] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Ensure migrations table exists.
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            migration VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    /**
     * Get list of executed migrations.
     */
    private function getExecutedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY migration");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get list of available migration files.
     */
    private function getAvailableMigrations(): array
    {
        $migrationsDir = dirname(__DIR__, 2) . '/migrations';
        
        if (!is_dir($migrationsDir)) {
            return [];
        }
        
        $files = glob($migrationsDir . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }
        
        sort($migrations);
        return $migrations;
    }

    /**
     * Load migration class.
     */
    private function loadMigration(string $migration): Migration
    {
        $file = dirname(__DIR__, 2) . '/migrations/' . $migration . '.php';
        
        if (!file_exists($file)) {
            throw new \RuntimeException("Migration file not found: {$migration}");
        }
        
        require_once $file;
        
        $className = str_replace('_', '', ucwords($migration, '_'));
        $fullClassName = "LLTool\\Migrations\\{$className}";
        
        if (!class_exists($fullClassName)) {
            throw new \RuntimeException("Migration class not found: {$fullClassName}");
        }
        
        return new $fullClassName();
    }

    /**
     * Mark migration as executed.
     */
    private function markAsExecuted(string $migration): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migration]);
    }
}

