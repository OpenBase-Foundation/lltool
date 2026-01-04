<?php
declare(strict_types=1);

namespace LLTool\Session;

final class SessionManager
{
    private static bool $started = false;

    /**
     * Start session if not already started.
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        self::$started = true;
    }

    /**
     * Get session value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value.
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Remove session value.
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Check if session key exists.
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Clear all session data.
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Destroy session.
     */
    public static function destroy(): void
    {
        self::start();
        session_destroy();
        self::$started = false;
    }
}

