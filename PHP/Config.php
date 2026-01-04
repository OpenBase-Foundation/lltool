<?php
declare(strict_types=1);

namespace LLTool\Support;

final class Config
{
    private static array $items = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Load .env file if it exists (try multiple locations)
        $envPaths = [
            __DIR__ . '/.env',
            dirname(__DIR__) . '/.env',
        ];
        foreach ($envPaths as $envPath) {
            if (file_exists($envPath)) {
                self::loadEnv($envPath);
                break;
            }
        }

        // Load config files
        $appConfigPath = self::configPath('app.php');
        if (file_exists($appConfigPath)) {
            self::$items['app'] = require $appConfigPath;
        } else {
            // Default app config
            self::$items['app'] = [
                'name' => 'LLTool',
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
            ];
        }
        
        self::$loaded = true;
    }

    /**
     * Load environment variables from .env file.
     */
    private static function loadEnv(string $path): void
    {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                // Set environment variable if not already set
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        // Check environment variables first
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        // Check config files
        if (strpos($key, '.') !== false) {
            [$file, $item] = explode('.', $key, 2);
            return self::$items[$file][$item] ?? $default;
        }

        return $default;
    }

    private static function configPath(string $file): string
    {
        // Try multiple possible locations
        $paths = [
            __DIR__ . '/../config/' . $file,
            dirname(__DIR__) . '/config/' . $file,
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Return default path even if doesn't exist (will be handled by caller)
        return __DIR__ . '/../config/' . $file;
    }
}
?>