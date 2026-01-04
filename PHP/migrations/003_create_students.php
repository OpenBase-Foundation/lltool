<?php
declare(strict_types=1);

namespace LLTool\Migrations;

use LLTool\Database\Migration;
use PDO;

final class CreateStudents extends Migration
{
    public function up(PDO $pdo): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS students (
            id CHAR(36) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            leergroep TINYINT NOT NULL CHECK (leergroep IN (1, 2, 3)),
            photo_url VARCHAR(500) NULL,
            cohort_id CHAR(36) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (cohort_id) REFERENCES cohorts(id) ON DELETE CASCADE,
            INDEX idx_cohort (cohort_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS students");
    }
}

