<?php

namespace App;

// ===== CSRF Protection =====

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $t = csrf_token();
    return "<input type=\"hidden\" name=\"_csrf\" value=\"" . htmlspecialchars($t) . "\">";
}

function verify_csrf($token) {
    return !empty($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ===== Input Validation & Sanitization =====

function sanitize_string($str) {
    return htmlspecialchars(trim((string)$str), ENT_QUOTES, 'UTF-8');
}

function sanitize_email($email) {
    $email = trim((string)$email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }
    return strtolower($email);
}

function validate_password($password) {
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one digit.';
    }
    return null; // valid
}

function safe_json_encode($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

// ===== Logging =====

function log_event($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logEntry = "[$timestamp] [$level] $message $contextStr\n";
    
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/app.log';
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function log_security($message, $context = []) {
    log_event('SECURITY', $message, $context);
}

// ===== Rate Limiting =====

function check_rate_limit($key, $limit = 5, $window = 300) {
    $cacheFile = sys_get_temp_dir() . '/ratelimit_' . md5($key) . '.txt';
    $attempts = [];
    
    if (file_exists($cacheFile)) {
        $data = @unserialize(file_get_contents($cacheFile));
        $attempts = $data['attempts'] ?? [];
    }
    
    $now = time();
    $attempts = array_filter($attempts, fn($t) => $now - $t < $window);
    
    if (count($attempts) >= $limit) {
        return false;
    }
    
    $attempts[] = $now;
    file_put_contents($cacheFile, serialize(['attempts' => $attempts]), LOCK_EX);
    return true;
}

