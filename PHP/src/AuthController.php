<?php

namespace App;

class AuthController {
    private $pdo;
    private $config;

    public function __construct(\PDO $pdo, Config $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function register() {
        if (!$this->config->allowUserRegistration()) {
            http_response_code(403);
            echo 'User registration is disabled';
            exit;
        }

        $errors = [];
        include __DIR__ . '/../templates/register.php';
    }

    public function processRegister() {
        if (!$this->config->allowUserRegistration()) {
            http_response_code(403);
            echo 'User registration is disabled';
            exit;
        }

        $errors = [];

        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            $errors[] = 'Invalid CSRF token';
        }

        $email = \App\sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (!$email) {
            $errors[] = 'Invalid email address';
        }

        if ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match';
        }

        $pwError = \App\validate_password($password);
        if ($pwError) {
            $errors[] = $pwError;
        }

        if (!empty($errors)) {
            return $this->register();
        }

        // Check if email already exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered';
            return $this->register();
        }

        // Rate limit registration attempts
        $rateKey = 'register_' . md5($_SERVER['REMOTE_ADDR']);
        if (!\App\check_rate_limit($rateKey, 5, 3600)) {
            $errors[] = 'Too many registration attempts. Please try again later.';
            return $this->register();
        }

        // Create user
        try {
            $passwordHash = Auth::hashPassword($password);
            $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $stmt->execute([$email, $passwordHash]);
            $userId = $this->pdo->lastInsertId();
            log_event('INFO', 'User registered', ['user_id' => $userId, 'email' => $email]);

            // Auto-login after registration
            $_SESSION['user_id'] = $userId;
            $_SESSION['last_activity'] = time();

            header('Location: /?page=home');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Registration failed. Please try again.';
            log_security('Registration failed', ['error' => $e->getMessage()]);
            return $this->register();
        }
    }
}
