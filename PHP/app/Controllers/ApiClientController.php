<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Support\Config;
use PDO;

final class ApiClientController
{
    private PDO $pdo;

    public function __construct()
    {
        // Simple database connection for now, reusing what seems to be available or creating new
        // Ideally we should use a Database class if available, but I'll check how other controllers do it.
        // StudentController uses StudentService which probably handles DB.
        // Let's look at how to get a DB connection. 
        // Use Config to get DB credentials.
        $driver = Config::get('DB_DRIVER', 'mysql');
        $host = Config::get('DB_HOST', 'localhost');
        $port = Config::get('DB_PORT', 3306);
        $database = Config::get('DB_DATABASE', 'lltool');
        $username = Config::get('DB_USERNAME', 'root');
        $password = Config::get('DB_PASSWORD', '');
        
        $dsn = "$driver:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function handleStudent(): void
    {
        $this->validateKey('CLIENT_API_STUDENT_KEY');
        
        $stmt = $this->pdo->query("SELECT id, name FROM client_students ORDER BY name ASC");
        $students = $stmt->fetchAll();
        
        $this->jsonResponse($students);
    }

    public function handleTeacher(): void
    {
        $this->validateKey('CLIENT_API_TEACHER_KEY');
        
        $stmt = $this->pdo->query("SELECT id, name FROM client_teachers ORDER BY name ASC");
        $teachers = $stmt->fetchAll();
        
        $this->jsonResponse($teachers);
    }

    private function validateKey(string $configKey): void
    {
        $headerKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        $validKey = Config::get($configKey);
        
        if (empty($validKey) || $headerKey !== $validKey) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
