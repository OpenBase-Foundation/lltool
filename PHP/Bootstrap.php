<?php
declare(strict_types=1);

namespace LLTool;

use LLTool\Support\Config;
use LLTool\Http\Router;
use LLTool\Error\SentryHandler;

final class Bootstrap
{
    public static function run(): void
    {
        // Initialize error handling first
        self::initializeErrorHandling();

        Config::load();

        $router = new Router();
        $router->dispatch();
    }

    /**
     * Initialize error handling and Sentry.
     */
    private static function initializeErrorHandling(): void
    {
        // Initialize Sentry
        SentryHandler::init();

        // Set error handler
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $exception = new \ErrorException($message, 0, $severity, $file, $line);
            SentryHandler::captureException($exception);

            return false; // Let PHP handle it normally too
        });

        // Set exception handler
        set_exception_handler(function (\Throwable $exception): void {
            SentryHandler::captureException($exception);
            
            // Show error page in development
            if (Config::get('APP_DEBUG', false)) {
                throw $exception;
            }
            
            http_response_code(500);
            echo 'An error occurred. Please try again later.';
        });
    }

}
?>