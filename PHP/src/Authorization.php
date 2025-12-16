<?php

namespace App;

class Authorization {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if current user is authenticated
     */
    public static function requireAuth() {
        if (empty($_SESSION['user_id'])) {
            header('Location: /?page=login');
            exit;
        }
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Check if cohort belongs to user (for future multi-tenant support)
     * Currently all users can see all cohorts
     */
    public function userCanAccessCohort($userId, $cohortId) {
        // In future: implement user_id FK in cohorts table for true per-user isolation
        return true;
    }

    /**
     * Check if student belongs to cohort that user can access
     */
    public function userCanAccessStudent($userId, $studentId) {
        // Verify student exists and belongs to accessible cohort
        $stmt = $this->pdo->prepare('SELECT id FROM students WHERE id = ?');
        $stmt->execute([$studentId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Log authorization failure
     */
    public static function deny($action = 'access') {
        $userId = self::getCurrentUserId();
        log_security("Authorization denied: $action", [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'path' => $_SERVER['REQUEST_URI'] ?? ''
        ]);
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
}
