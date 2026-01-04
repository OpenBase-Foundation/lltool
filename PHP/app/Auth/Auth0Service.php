<?php
declare(strict_types=1);

namespace LLTool\Auth;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use LLTool\Support\Config;

final class Auth0Service
{
    private ?Auth0 $auth0 = null;

    /**
     * Get Auth0 SDK instance.
     */
    public function getAuth0(): Auth0
    {
        if ($this->auth0 !== null) {
            return $this->auth0;
        }

        // Get from .env
        $domain = Config::get('AUTH0_DOMAIN', '');
        $clientId = Config::get('AUTH0_CLIENT_ID', '');
        $clientSecret = Config::get('AUTH0_CLIENT_SECRET', '');
        $callbackUrl = Config::get('AUTH0_CALLBACK_URL', $this->getDefaultCallbackUrl());
        $audience = Config::get('AUTH0_AUDIENCE', null);
        
        $audience = Config::get('AUTH0_AUDIENCE', null);
        
        // Treat empty string as null
        if ($audience === '') {
            $audience = null;
        }

        // Wrap audience in array if it's a string (and not null)
        if ($audience !== null && !is_array($audience)) {
            $audience = [$audience];
        }
        
        $cookieSecret = Config::get('AUTH0_COOKIE_SECRET', null);
        
        $config = new SdkConfiguration(
            domain: $domain,
            clientId: $clientId,
            clientSecret: $clientSecret,
            redirectUri: $callbackUrl,
            audience: $audience,
            cookieSecret: $cookieSecret,
        );

        $this->auth0 = new Auth0($config);
        return $this->auth0;
    }

    /**
     * Get login URL.
     */
    public function getLoginUrl(): string
    {
        return $this->getAuth0()->login();
    }

    /**
     * Handle callback and exchange code for tokens.
     */
    public function handleCallback(): array
    {
        $auth0 = $this->getAuth0();
        
        try {
            $auth0->exchange();
            $user = $auth0->getUser();
            
            if ($user === null) {
                throw new \RuntimeException('Failed to retrieve user information');
            }
            
            return [
                'success' => true,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current user.
     */
    public function getUser(): ?array
    {
        try {
            return $this->getAuth0()->getUser();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->getUser() !== null;
    }

    /**
     * Get logout URL.
     */
    public function getLogoutUrl(): string
    {
        return $this->getAuth0()->logout(
            returnTo: Config::get('APP_URL') . '/auth/logout'
        );
    }

    /**
     * Get default callback URL.
     */
    private function getDefaultCallbackUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}/auth/callback";
    }
}

