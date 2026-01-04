<?php
declare(strict_types=1);

namespace LLTool\Install;

final class SystemCheck
{
    public static function run(): array
    {
        return [
            'php_version' => [
                'label' => 'PHP 8.2+',
                'ok' => PHP_VERSION_ID >= 80200,
                'current' => PHP_VERSION,
            ],

            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'curl' => extension_loaded('curl'),
                'openssl' => extension_loaded('openssl'),
                'json' => extension_loaded('json'),
                'gd' => extension_loaded('gd'),
                'imagick' => extension_loaded('imagick'), // Optional, GD is sufficient
            ],

            'permissions' => [
                'config' => is_writable(self::path('config')),
                'storage' => is_writable(self::path('storage')),
                'storage/photos' => is_writable(self::path('storage/photos')),
            ],
        ];
    }

    private static function path(string $dir): string
    {
        // Get base directory (where index.php is located)
        $baseDir = dirname(__DIR__);
        
        // Try relative to base directory
        $path = $baseDir . '/' . $dir;
        
        // If that doesn't exist, try current directory
        if (!file_exists($path) && !is_dir($path)) {
            $path = __DIR__ . '/../' . $dir;
        }
        
        // Ensure directory exists (create if needed)
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        
        // Try to set permissions (may fail on Windows/Docker volume mounts, that's OK)
        @chmod($path, 0777);
        
        // Also try to set ownership if possible (may fail if not root)
        if (function_exists('chown')) {
            $webUser = 'www-data';
            if (function_exists('posix_getpwuid')) {
                $processUser = @posix_getpwuid(@posix_geteuid());
                if ($processUser) {
                    $webUser = $processUser['name'] ?? 'www-data';
                }
            }
            @chown($path, $webUser);
            @chgrp($path, $webUser);
        }
        
        return $path;
    }
}
?>