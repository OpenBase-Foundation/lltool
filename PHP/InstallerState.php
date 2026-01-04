<?php
declare(strict_types=1);

namespace LLTool\Install;

/**
 * Manages installation state persistence.
 * Tracks completed installation steps via storage files.
 */
final class InstallerState
{
    private const STATE_FILE = 'install.state';
    private const APP_KEY_FILE = 'app.key';

    /**
     * Check if installation is fully complete.
     */
    public static function isInstalled(): bool
    {
        return file_exists(self::storagePath(self::APP_KEY_FILE));
    }

    /**
     * Mark installation as complete (all steps done).
     */
    public static function markInstalled(): void
    {
        file_put_contents(self::storagePath(self::APP_KEY_FILE), bin2hex(random_bytes(32)));
    }

    /**
     * Check if a specific step is completed.
     */
    public static function isStepCompleted(string $step): bool
    {
        $state = self::getState();
        return isset($state[$step]) && $state[$step] === true;
    }

    /**
     * Mark a specific step as completed.
     */
    public static function markStepCompleted(string $step): void
    {
        $state = self::getState();
        $state[$step] = true;
        self::saveState($state);
    }

    /**
     * Get all step states.
     */
    private static function getState(): array
    {
        $path = self::storagePath(self::STATE_FILE);

        if (!file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);
        return json_decode($json, associative: true) ?? [];
    }

    /**
     * Save step states to file.
     */
    private static function saveState(array $state): void
    {
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(self::storagePath(self::STATE_FILE), $json);
    }

    /**
     * Get full storage path for a file.
     */
    private static function storagePath(string $file): string
    {
        return dirname(__DIR__) . '/storage/' . $file;
    }
}
?>