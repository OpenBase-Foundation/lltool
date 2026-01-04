<?php
declare(strict_types=1);

namespace LLTool\Middleware;

use LLTool\Auth\Auth0Service;
use LLTool\Session\SessionManager;

final class AuthMiddleware
{
    private Auth0Service $auth0;

    public function __construct()
    {
        $this->auth0 = new Auth0Service();
    }

    /**
     * Handle authentication check.
     * Redirects to login if not authenticated.
     */
    public function handle(?callable $next = null): bool
    {
        SessionManager::start();

        // Check if user is authenticated via Auth0
        $user = $this->auth0->getUser();
        
        if ($user === null) {
            // Store intended URL for redirect after login
            $intended = $_SERVER['REQUEST_URI'] ?? '/';
            SessionManager::set('auth.intended', $intended);
            
            // Redirect to login
            header('Location: ' . $this->auth0->getLoginUrl());
            exit;
        }

        // Store user in session for easy access
        SessionManager::set('auth.user', $user);
        
        // Execute next middleware/controller
        if ($next) {
            $next();
        }

        return true;
    }

    /**
     * Get current authenticated user.
     */
    public static function user(): ?array
    {
        SessionManager::start();
        return SessionManager::get('auth.user');
    }

    /**
     * Get user ID.
     */
    public static function userId(): ?string
    {
        $user = self::user();
        return $user['sub'] ?? null;
    }

    /**
     * Check if user is authenticated.
     */
    public static function check(): bool
    {
        return self::user() !== null;
    }
}

