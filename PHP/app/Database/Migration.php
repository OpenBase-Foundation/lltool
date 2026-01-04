<?php
declare(strict_types=1);

namespace LLTool\Database;

use PDO;

abstract class Migration
{
    /**
     * Run the migration.
     */
    abstract public function up(PDO $pdo): void;

    /**
     * Reverse the migration (optional).
     */
    public function down(PDO $pdo): void
    {
        // Override in subclasses if rollback is needed
    }
}

