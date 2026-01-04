<?php
declare(strict_types=1);

namespace LLTool\Migrations;

use LLTool\Database\Migration;
use PDO;

final class CreateCohorts extends Migration
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS cohorts (
            id CHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            owner_id VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_owner (owner_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS cohorts");
    }
}

