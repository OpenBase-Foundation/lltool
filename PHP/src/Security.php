<?php

namespace App;

class Security {
    public static function initSession() {
        // Harden session configuration
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1); // HTTPS only in production
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.cache_limiter', 'nocache');
        ini_set('session.gc_maxlifetime', 1800); // 30 minutes
        
        session_start();
        
        // Regenerate session ID after login (done in controllers)
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
            session_destroy();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data:; font-src \'self\'; connect-src \'self\'');
    }

    public static function validateRequest() {
        // Check for common attack patterns
        foreach ($_REQUEST as $value) {
            if (is_string($value) && preg_match('/(<|>|script|iframe|onclick|onerror)/i', $value)) {
                log_security('Potential XSS attack detected', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
                http_response_code(400);
                exit('Invalid input');
            }
        }
    }
}
