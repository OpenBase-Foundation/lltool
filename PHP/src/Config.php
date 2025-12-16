<?php

namespace App;

class Config {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get config value
     */
    public function get($key, $default = null) {
        try {
            $stmt = $this->pdo->prepare('SELECT value FROM app_config WHERE key = ?');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            if ($row) {
                $value = $row['value'];
                // Try to decode JSON for complex values
                if (in_array($value, ['true', 'false'])) {
                    return $value === 'true';
                }
                return $value;
            }
        } catch (Exception $e) {
            log_security('Config get failed', ['key' => $key, 'error' => $e->getMessage()]);
        }
        return $default;
    }

    /**
     * Set config value
     */
    public function set($key, $value) {
        try {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $stmt = $this->pdo->prepare('
                INSERT INTO app_config (key, value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = CURRENT_TIMESTAMP
            ');
            $stmt->execute([$key, $value]);
            return true;
        } catch (Exception $e) {
            log_security('Config set failed', ['key' => $key, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if app is initialized
     */
    public function isInitialized() {
        return $this->get('app_initialized') === true || $this->get('app_initialized') === 'true';
    }

    /**
     * Mark app as initialized
     */
    public function markInitialized() {
        return $this->set('app_initialized', true);
    }

    /**
     * Check if user registration is allowed
     */
    public function allowUserRegistration() {
        return $this->get('allow_user_registration', false) === true || $this->get('allow_user_registration') === 'true';
    }

    /**
     * Get organization name
     */
    public function getOrganizationName() {
        return $this->get('organization_name', 'LLTool');
    }
}
