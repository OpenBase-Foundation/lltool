<?php
declare(strict_types=1);

namespace LLTool\Services;

use LLTool\Database\Database;
use PDO;

final class SettingsService
{
    private static ?array $cache = null;

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        if (self::$cache === null) {
            self::loadCache();
        }

        return self::$cache[$key] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, ?string $value): void
    {
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO settings (`key`, `value`) 
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE `value` = :value, updated_at = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([
            'key' => $key,
            'value' => $value,
        ]);
        
        // Clear cache
        self::$cache = null;
    }

    /**
     * Set multiple settings at once.
     */
    public static function setMultiple(array $settings): void
    {
        $pdo = Database::getConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO settings (`key`, `value`) 
            VALUES (:key, :value)
            ON DUPLICATE KEY UPDATE `value` = :value, updated_at = CURRENT_TIMESTAMP
        ");
        
        foreach ($settings as $key => $value) {
            $stmt->execute([
                'key' => $key,
                'value' => $value,
            ]);
        }
        
        // Clear cache
        self::$cache = null;
    }

    /**
     * Load all settings into cache.
     */
    private static function loadCache(): void
    {
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            self::$cache = [];
            foreach ($results as $row) {
                self::$cache[$row['key']] = $row['value'];
            }
        } catch (\Exception $e) {
            // If table doesn't exist yet (during installation), return empty cache
            self::$cache = [];
        }
    }

    /**
     * Clear the cache (useful after updates).
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}

