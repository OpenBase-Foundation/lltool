<?php
declare(strict_types=1);

namespace LLTool\Migrations;

use LLTool\Database\Migration;
use PDO;

final class CreateCohortAccess extends Migration
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS cohort_access (
            id CHAR(36) PRIMARY KEY,
            cohort_id CHAR(36) NOT NULL,
            user_id VARCHAR(255) NOT NULL,
            permissions ENUM('view', 'edit') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
            UNIQUE KEY unique_access (cohort_id, user_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS cohort_access");
    }
}

