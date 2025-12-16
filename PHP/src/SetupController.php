<?php

namespace App;

class SetupController {
    private $pdo;
    private $config;

    public function __construct(\PDO $pdo, Config $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function show() {
        $errors = [];
        include __DIR__ . '/../templates/setup.php';
    }

    public function process() {
        $errors = [];

        // Validate CSRF
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            $errors[] = 'Invalid CSRF token';
        }

        // Validate inputs
        $org_name = \App\sanitize_string($_POST['organization_name'] ?? '');
        $admin_email = \App\sanitize_email($_POST['admin_email'] ?? '');
        $admin_password = $_POST['admin_password'] ?? '';
        $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
        $allow_registration = isset($_POST['allow_user_registration']) ? 1 : 0;

        if (!$org_name || strlen($org_name) === 0) {
            $errors[] = 'Organization name is required';
        }

        if (!$admin_email) {
            $errors[] = 'Admin email is invalid';
        }

        if ($admin_password !== $admin_password_confirm) {
            $errors[] = 'Passwords do not match';
        }

        $pwError = \App\validate_password($admin_password);
        if ($pwError) {
            $errors[] = $pwError;
        }

        if (!empty($errors)) {
            return $this->show();
        }

        // Create admin user
        try {
            $passwordHash = Auth::hashPassword($admin_password);
            $stmt = $this->pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
            $stmt->execute([$admin_email, $passwordHash]);
            $userId = $this->pdo->lastInsertId();
            log_event('INFO', 'Admin user created during setup', ['user_id' => $userId, 'email' => $admin_email]);
        } catch (Exception $e) {
            $errors[] = 'Failed to create admin user: ' . $e->getMessage();
            log_security('Setup: admin creation failed', ['error' => $e->getMessage()]);
            return $this->show();
        }

        // Save config
        $this->config->set('organization_name', $org_name);
        $this->config->set('allow_user_registration', $allow_registration ? 'true' : 'false');
        $this->config->markInitialized();

        log_event('INFO', 'Application setup completed', [
            'organization' => $org_name,
            'allow_registration' => $allow_registration ? 'yes' : 'no'
        ]);

        // Redirect to login
        header('Location: /?page=login');
        exit;
    }
}
