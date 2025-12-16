<?php

namespace App;

class Auth {
    private $pdo;
    private const LOGIN_RATE_LIMIT = 5;
    private const LOGIN_RATE_WINDOW = 300;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function currentUser() {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare('SELECT id, email FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        }
        return null;
    }

    public function signIn($email, $password) {
        $email = sanitize_email($email);
        if (!$email) {
            log_security('Invalid email format attempted', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            return false;
        }

        $rateKey = 'login_' . md5($_SERVER['REMOTE_ADDR'] . $email);
        if (!check_rate_limit($rateKey, self::LOGIN_RATE_LIMIT, self::LOGIN_RATE_WINDOW)) {
            log_security('Login rate limit exceeded', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['last_activity'] = time();
            log_event('INFO', 'User logged in', ['user_id' => $user['id'], 'email' => $email]);
            return true;
        }

        log_security('Failed login attempt', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
        return false;
    }

    public function signOut() {
        if (isset($_SESSION['user_id'])) {
            log_event('INFO', 'User logged out', ['user_id' => $_SESSION['user_id']]);
        }
        session_destroy();
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
    }
}
