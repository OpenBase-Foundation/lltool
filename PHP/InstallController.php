<?php
declare(strict_types=1);

namespace LLTool\Install;

use LLTool\Install\DatabaseConnectionTester;
use LLTool\Install\EnvConfigurator;
use LLTool\Install\InstallerState;
use LLTool\Install\SystemCheck;
use LLTool\Database\Database;
use LLTool\Database\MigrationRunner;
use LLTool\Services\SettingsService;

/**
 * Handles installation wizard controller logic.
 * Routes step 1 (system check) and step 2 (database configuration).
 */
final class InstallController
{
    /**
     * Step 1: System checks.
     */
    public function step1(): void
    {
        if (InstallerState::isInstalled()) {
            http_response_code(403);
            echo 'LLTool is already installed.';
            return;
        }

        // Try to fix permissions automatically before checking
        self::ensureDirectoriesExist();

        $checks = SystemCheck::run();
        
        // If system checks are all OK, mark step 1 as completed so next steps unlock
        // GD is sufficient, imagick is optional
        $requiredExtensions = ['pdo', 'curl', 'openssl', 'json', 'gd'];
        $extensionsOk = true;
        foreach ($requiredExtensions as $ext) {
            if (!($checks['extensions'][$ext] ?? false)) {
                $extensionsOk = false;
                break;
            }
        }
        
        $systemOk = (
            ($checks['php_version']['ok'] ?? false)
            && $extensionsOk
            && !in_array(false, $checks['permissions'] ?? [], true)
        );

        if ($systemOk) {
            InstallerState::markStepCompleted('system');
        }

        require $this->view('step1.php');
    }

    /**
     * Step 2: Database configuration.
     */
    public function database(): void
    {
        if (InstallerState::isInstalled()) {
            header('Location: /');
            exit;
        }

        // Only allow access to database step after system checks passed
        if (!InstallerState::isStepCompleted('system')) {
            header('Location: /install');
            exit;
        }

        $errors = [];
        $success = false;
        $formData = [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => '',
            'username' => '',
            'password' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'driver' => $_POST['driver'] ?? 'mysql',
                'host' => trim($_POST['host'] ?? ''),
                'port' => trim($_POST['port'] ?? ''),
                'database' => trim($_POST['database'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
            ];

            // Set sensible default ports if none provided
            if (empty($formData['port'])) {
                $formData['port'] = $formData['driver'] === 'pgsql' ? '5432' : '3306';
            }

            // Validate input
            $errors = self::validateInput($formData);

            if (empty($errors)) {
                // Test connection
                $testResult = DatabaseConnectionTester::test($formData);

                if ($testResult['ok']) {
                    // Write to .env only
                    try {
                        $envData = [
                            'DB_DRIVER' => $formData['driver'],
                            'DB_HOST' => $formData['host'],
                            'DB_PORT' => $formData['port'],
                            'DB_DATABASE' => $formData['database'],
                            'DB_USERNAME' => $formData['username'],
                            'DB_PASSWORD' => $formData['password'],
                        ];
                        EnvConfigurator::write($envData, true);
                        
                        InstallerState::markStepCompleted('database');
                        $success = true;

                        // Redirect to Auth0 step
                        header('Refresh: 2; URL=/install/auth0');
                    } catch (\RuntimeException $e) {
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors[] = 'Database connection failed: ' . $testResult['error'];
                }
            }
        }

        require $this->view('step2_database.php');
    }

    /**
     * Step 3: Auth0 configuration.
     */
    public function auth0(): void
    {
        if (InstallerState::isInstalled()) {
            header('Location: /');
            exit;
        }

        if (!InstallerState::isStepCompleted('database')) {
            header('Location: /install/database');
            exit;
        }

        $errors = [];
        $success = false;
        
        // Try to read from database first, then fallback to .env
        $formData = [
            'domain' => SettingsService::get('AUTH0_DOMAIN') ?? '',
            'client_id' => SettingsService::get('AUTH0_CLIENT_ID') ?? '',
            'client_secret' => SettingsService::get('AUTH0_CLIENT_SECRET') ?? '',
            'audience' => SettingsService::get('AUTH0_AUDIENCE') ?? '',
        ];
        
        // Fallback to .env if database is empty
        if (empty($formData['domain'])) {
            $existing = EnvConfigurator::read();
            $formData = [
                'domain' => $existing['AUTH0_DOMAIN'] ?? '',
                'client_id' => $existing['AUTH0_CLIENT_ID'] ?? '',
                'client_secret' => $existing['AUTH0_CLIENT_SECRET'] ?? '',
                'audience' => $existing['AUTH0_AUDIENCE'] ?? '',
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'domain' => trim($_POST['domain'] ?? ''),
                'client_id' => trim($_POST['client_id'] ?? ''),
                'client_secret' => trim($_POST['client_secret'] ?? ''),
                'audience' => trim($_POST['audience'] ?? ''),
            ];

            // Validate
            if (empty($formData['domain'])) {
                $errors['domain'] = 'Auth0 Domain is required';
            }
            if (empty($formData['client_id'])) {
                $errors['client_id'] = 'Client ID is required';
            }
            if (empty($formData['client_secret'])) {
                $errors['client_secret'] = 'Client Secret is required';
            }

            if (empty($errors)) {
                // Test Auth0 connection (basic validation)
                if (!str_contains($formData['domain'], '.auth0.com') && !str_contains($formData['domain'], '.auth0.dev')) {
                    $errors['domain'] = 'Invalid Auth0 domain format';
                } else {
                    // Save to database
                    try {
                        SettingsService::setMultiple([
                            'AUTH0_DOMAIN' => $formData['domain'],
                            'AUTH0_CLIENT_ID' => $formData['client_id'],
                            'AUTH0_CLIENT_SECRET' => $formData['client_secret'],
                            'AUTH0_AUDIENCE' => $formData['audience'],
                            'AUTH0_CALLBACK_URL' => self::detectAppUrl() . '/auth/callback',
                        ]);
                        InstallerState::markStepCompleted('auth0');
                        $success = true;
                        header('Refresh: 2; URL=/install/sentry');
                    } catch (\Exception $e) {
                        $errors[] = 'Failed to save Auth0 configuration: ' . $e->getMessage();
                    }
                }
            }
        }

        require $this->view('step3_auth0.php');
    }

    /**
     * Step 4: Sentry configuration.
     */
    public function sentry(): void
    {
        if (InstallerState::isInstalled()) {
            header('Location: /');
            exit;
        }

        if (!InstallerState::isStepCompleted('auth0')) {
            header('Location: /install/auth0');
            exit;
        }

        $errors = [];
        $success = false;
        
        // Try to read from database first, then fallback to .env
        $formData = [
            'dsn' => SettingsService::get('SENTRY_DSN') ?? '',
            'environment' => SettingsService::get('SENTRY_ENVIRONMENT') ?? 'production',
        ];
        
        // Fallback to .env if database is empty
        if (empty($formData['dsn']) && $formData['environment'] === 'production') {
            $existing = EnvConfigurator::read();
            $formData = [
                'dsn' => $existing['SENTRY_DSN'] ?? '',
                'environment' => $existing['SENTRY_ENVIRONMENT'] ?? 'production',
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'dsn' => trim($_POST['dsn'] ?? ''),
                'environment' => trim($_POST['environment'] ?? 'production'),
            ];

            // DSN is optional, but validate format if provided
            if (!empty($formData['dsn'])) {
                if (!str_starts_with($formData['dsn'], 'https://') || !str_contains($formData['dsn'], '@sentry.io')) {
                    $errors['dsn'] = 'Invalid Sentry DSN format';
                }
            }

            if (empty($errors)) {
                // Save to database
                try {
                    SettingsService::setMultiple([
                        'SENTRY_DSN' => $formData['dsn'],
                        'SENTRY_ENVIRONMENT' => $formData['environment'],
                    ]);
                    InstallerState::markStepCompleted('sentry');
                    $success = true;
                    header('Refresh: 2; URL=/install/migrations');
                } catch (\Exception $e) {
                    $errors[] = 'Failed to save Sentry configuration: ' . $e->getMessage();
                }
            }
        }

        require $this->view('step4_sentry.php');
    }

    /**
     * Step 5: Database migrations.
     */
    public function migrations(): void
    {
        if (InstallerState::isInstalled()) {
            header('Location: /');
            exit;
        }

        if (!InstallerState::isStepCompleted('sentry')) {
            header('Location: /install/sentry');
            exit;
        }

        $migrationsRun = false;
        $migrationResults = [];
        $error = null;

        // Run migrations if POST request or if not already done
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || !InstallerState::isStepCompleted('migrations')) {
            if (!InstallerState::isStepCompleted('migrations')) {
                try {
                    require_once __DIR__ . '/../app/Database/Database.php';
                    require_once __DIR__ . '/../app/Database/Migration.php';
                    require_once __DIR__ . '/../app/Database/MigrationRunner.php';
                    
                    $pdo = Database::getConnection();
                    $runner = new MigrationRunner($pdo);
                    $migrationResults = $runner->run();
                    $migrationsRun = true;
                    
                    // Check if all migrations succeeded
                    $allSuccess = true;
                    foreach ($migrationResults as $result) {
                        if ($result['status'] !== 'success') {
                            $allSuccess = false;
                            break;
                        }
                    }
                    
                    if ($allSuccess && !empty($migrationResults)) {
                        InstallerState::markStepCompleted('migrations');
                        header('Refresh: 2; URL=/install/complete');
                    } elseif (empty($migrationResults)) {
                        // No migrations to run
                        InstallerState::markStepCompleted('migrations');
                        header('Refresh: 2; URL=/install/complete');
                    } else {
                        $error = 'Some migrations failed. Please check the errors below.';
                    }
                } catch (\Exception $e) {
                    $error = 'Migration error: ' . $e->getMessage();
                }
            } else {
                header('Location: /install/complete');
                exit;
            }
        }

        require $this->view('step5_migrations.php');
    }

    /**
     * Installation complete - finalize.
     */
    public function complete(): void
    {
        if (!InstallerState::isStepCompleted('migrations')) {
            header('Location: /install/migrations');
            exit;
        }

        $migrationsRun = false;
        $migrationResults = [];
        $error = null;

        // Run migrations if not already done
        if (!InstallerState::isStepCompleted('migrations')) {
            try {
                require_once __DIR__ . '/../app/Database/Database.php';
                require_once __DIR__ . '/../app/Database/Migration.php';
                require_once __DIR__ . '/../app/Database/MigrationRunner.php';
                
                $pdo = Database::getConnection();
                $runner = new MigrationRunner($pdo);
                $migrationResults = $runner->run();
                $migrationsRun = true;
                
                // Check if all migrations succeeded
                $allSuccess = true;
                foreach ($migrationResults as $result) {
                    if ($result['status'] !== 'success') {
                        $allSuccess = false;
                        break;
                    }
                }
                
                if ($allSuccess) {
                    InstallerState::markStepCompleted('migrations');
                } else {
                    $error = 'Some migrations failed. Please check the errors below.';
                }
            } catch (\Exception $e) {
                $error = 'Migration error: ' . $e->getMessage();
            }
        }

        // Mark installation as complete
        if (!InstallerState::isInstalled()) {
            InstallerState::markInstalled();
        }

        require $this->view('complete.php');
    }

    /**
     * Ensure required directories exist and have correct permissions.
     */
    private static function ensureDirectoriesExist(): void
    {
        $baseDir = dirname(__DIR__);
        $directories = [
            'storage',
            'storage/photos',
            'config',
        ];
        
        foreach ($directories as $dir) {
            $path = $baseDir . '/' . $dir;
            
            // Create directory if it doesn't exist
            if (!is_dir($path)) {
                @mkdir($path, 0777, true);
            }
            
            // Try to set permissions (may fail on Windows/Docker volume mounts)
            @chmod($path, 0777);
            
            // Try to set ownership if possible
            if (function_exists('chown')) {
                $webUser = 'www-data';
                if (function_exists('posix_getpwuid')) {
                    $processUser = @posix_getpwuid(@posix_geteuid());
                    if ($processUser) {
                        $webUser = $processUser['name'] ?? 'www-data';
                    }
                }
                @chown($path, $webUser);
                @chgrp($path, $webUser);
            }
        }
    }

    /**
     * Detect application URL.
     */
    private static function detectAppUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}";
    }

    /**
     * Validate form input.
     * Returns array of error messages (empty if valid).
     */
    private static function validateInput(array $data): array
    {
        $errors = [];

        if (empty($data['host'])) {
            $errors['host'] = 'Host is required';
        }

        if (empty($data['port']) || !is_numeric($data['port']) || (int)$data['port'] < 1 || (int)$data['port'] > 65535) {
            $errors['port'] = 'Port must be a number between 1 and 65535';
        }

        if (empty($data['database'])) {
            $errors['database'] = 'Database name is required';
        }

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }

        if (!in_array($data['driver'], ['mysql', 'pgsql'], strict: true)) {
            $errors['driver'] = 'Invalid database driver';
        }

        return $errors;
    }

    /**
     * Get view file path.
     */
    private function view(string $file): string
    {
        // Prefer views colocated in the PHP/ folder (flat layout), fall back to app/Views/install
        $local = __DIR__ . '/' . $file;
        if (file_exists($local)) {
            return $local;
        }

        return dirname(__DIR__) . '/app/Views/install/' . $file;
    }
}
?>