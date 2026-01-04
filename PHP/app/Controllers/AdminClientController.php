<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Middleware\AuthMiddleware;
use LLTool\Support\Config;
use PDO;

final class AdminClientController
{
    private PDO $pdo;

    public function __construct()
    {
        // Require authentication for all admin actions
        AuthMiddleware::check() || (new AuthMiddleware())->handle();

        // Database connection
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

    public function index(): void
    {
        // Fetch students
        $stmt = $this->pdo->query("SELECT * FROM client_students ORDER BY created_at DESC");
        $students = $stmt->fetchAll();

        // Fetch teachers
        $stmt = $this->pdo->query("SELECT * FROM client_teachers ORDER BY created_at DESC");
        $teachers = $stmt->fetchAll();

        require __DIR__ . '/../../views/admin/client/index.php';
    }

    public function storeStudent(): void
    {
        $name = $_POST['name'] ?? '';
        
        if (!empty($name)) {
            $stmt = $this->pdo->prepare("INSERT INTO client_students (name) VALUES (?)");
            $stmt->execute([$name]);
        }
        
        header('Location: /admin/client');
        exit;
    }

    public function deleteStudent(): void
    {
        $id = $_POST['id'] ?? '';
        
        if (!empty($id)) {
            $stmt = $this->pdo->prepare("DELETE FROM client_students WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header('Location: /admin/client');
        exit;
    }

    public function storeTeacher(): void
    {
        $name = $_POST['name'] ?? '';
        
        if (!empty($name)) {
            $stmt = $this->pdo->prepare("INSERT INTO client_teachers (name) VALUES (?)");
            $stmt->execute([$name]);
        }
        
        header('Location: /admin/client');
        exit;
    }

    public function deleteTeacher(): void
    {
        $id = $_POST['id'] ?? '';
        
        if (!empty($id)) {
            $stmt = $this->pdo->prepare("DELETE FROM client_teachers WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header('Location: /admin/client');
        exit;
    }
}
