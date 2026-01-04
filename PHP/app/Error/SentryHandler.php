<?php
declare(strict_types=1);

namespace LLTool\Error;

use Sentry\ClientBuilder;
use Sentry\State\Scope;
use LLTool\Support\Config;

final class SentryHandler
{
    private static bool $initialized = false;

    /**
     * Initialize Sentry.
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }

        // Get from .env
        $dsn = Config::get('SENTRY_DSN');
        $environment = Config::get('SENTRY_ENVIRONMENT', 'production');
        
        if (empty($dsn)) {
            return; // Sentry not configured
        }

        \Sentry\init([
            'dsn' => $dsn,
            'environment' => $environment,
            'traces_sample_rate' => 1.0,
            'error_types' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED,
        ]);

        self::$initialized = true;
    }

    /**
     * Capture exception.
     */
    public static function captureException(\Throwable $exception, array $context = []): void
    {
        if (!self::$initialized) {
            self::init();
        }

        \Sentry\configureScope(function (Scope $scope) use ($context): void {
            foreach ($context as $key => $value) {
                $scope->setContext($key, $value);
            }

            // Add user context if available
            if (isset($_SESSION['auth.user'])) {
                $scope->setUser([
                    'id' => $_SESSION['auth.user']['sub'] ?? null,
                    'email' => $_SESSION['auth.user']['email'] ?? null,
                ]);
            }

            // Add request context
            $scope->setContext('request', [
                'url' => ($_SERVER['REQUEST_URI'] ?? ''),
                'method' => ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
                'ip' => ($_SERVER['REMOTE_ADDR'] ?? ''),
            ]);
        });

        \Sentry\captureException($exception);
    }

    /**
     * Capture message.
     */
    public static function captureMessage(string $message, string $level = 'info'): void
    {
        if (!self::$initialized) {
            self::init();
        }

        \Sentry\captureMessage($message, \Sentry\Severity::fromString($level));
    }
}

