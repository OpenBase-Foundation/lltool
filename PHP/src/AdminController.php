<?php

namespace App;

class AdminController {
    private $pdo;
    private $config;

    public function __construct(\PDO $pdo, Config $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function dashboard() {
        // Get statistics
        $userCount = $this->getUserCount();
        $cohortCount = $this->getCohortCount();
        $studentCount = $this->getStudentCount();
        
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/admin/dashboard.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function settings() {
        $errors = [];
        $org_name = $this->config->getOrganizationName();
        $allow_registration = $this->config->allowUserRegistration();
        
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/admin/settings.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function updateSettings() {
        $errors = [];
        
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            $errors[] = 'Invalid CSRF token';
        }

        $org_name = \App\sanitize_string($_POST['organization_name'] ?? '');
        $allow_registration = isset($_POST['allow_user_registration']) ? 1 : 0;

        if (!$org_name || strlen($org_name) === 0) {
            $errors[] = 'Organization name is required';
        }

        if (!empty($errors)) {
            $_POST['organization_name'] = $org_name;
            $_POST['allow_user_registration'] = $allow_registration;
            return $this->settings();
        }

        $this->config->set('organization_name', $org_name);
        $this->config->set('allow_user_registration', $allow_registration ? 'true' : 'false');

        log_event('INFO', 'Admin updated settings', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'organization' => $org_name,
            'allow_registration' => $allow_registration ? 'yes' : 'no'
        ]);

        header('Location: /?page=admin&action=settings');
        exit;
    }

    public function users() {
        $stmt = $this->pdo->query('SELECT id, email, created_at FROM users ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        
        include __DIR__ . '/../templates/header.php';
        include __DIR__ . '/../templates/admin/users.php';
        include __DIR__ . '/../templates/footer.php';
    }

    public function deleteUser($userId) {
        $csrf = $_POST['_csrf'] ?? '';
        if (!\App\verify_csrf($csrf)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        // Prevent deleting yourself
        if ($userId == $_SESSION['user_id']) {
            header('Location: /?page=admin&action=users');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([intval($userId)]);
            log_event('WARNING', 'Admin deleted user', ['deleted_user_id' => $userId, 'admin_id' => $_SESSION['user_id']]);
        } catch (Exception $e) {
            log_security('User deletion failed', ['error' => $e->getMessage()]);
        }

        header('Location: /?page=admin&action=users');
        exit;
    }

    private function getUserCount() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM users');
        return $stmt->fetch()['count'] ?? 0;
    }

    private function getCohortCount() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM cohorts');
        return $stmt->fetch()['count'] ?? 0;
    }

    private function getStudentCount() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM students');
        return $stmt->fetch()['count'] ?? 0;
    }
}
