<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Auth\Auth0Service;
use LLTool\Session\SessionManager;
use LLTool\Middleware\AuthMiddleware;

final class AuthController
{
    private Auth0Service $auth0;

    public function __construct()
    {
        $this->auth0 = new Auth0Service();
    }

    /**
     * Redirect to Auth0 login.
     */
    public function login(): void
    {
        $loginUrl = $this->auth0->getLoginUrl();
        header("Location: {$loginUrl}");
        exit;
    }

    /**
     * Handle Auth0 callback.
     */
    public function callback(): void
    {
        $result = $this->auth0->handleCallback();

        if ($result['success']) {
            // Store user in session
            SessionManager::set('auth.user', $result['user']);

            // Redirect to intended URL or home
            $intended = SessionManager::get('auth.intended', '/');
            SessionManager::remove('auth.intended');
            
            header("Location: {$intended}");
            exit;
        } else {
            // Redirect to login with error
            $_SESSION['auth.error'] = $result['error'] ?? 'Authentication failed';
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * Logout user.
     */
    public function logout(): void
    {
        SessionManager::clear();
        
        $logoutUrl = $this->auth0->getLogoutUrl();
        header("Location: {$logoutUrl}");
        exit;
    }
}

