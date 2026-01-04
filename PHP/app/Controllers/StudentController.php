<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Middleware\AuthMiddleware;
use LLTool\Services\StudentService;
use LLTool\Services\PhotoUploadService;

final class StudentController
{
    private StudentService $studentService;
    private PhotoUploadService $photoService;

    public function __construct()
    {
        $this->studentService = new StudentService();
        $this->photoService = new PhotoUploadService();
    }

    /**
     * List students in cohort.
     */
    public function index(string $cohortId): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $leergroep = isset($_GET['leergroep']) ? (int)$_GET['leergroep'] : null;
        
        $students = $this->studentService->getStudents($cohortId, $userId, $leergroep);
        
        require __DIR__ . '/../../views/students/index.php';
    }

    /**
     * Show create form.
     */
    public function create(string $cohortId): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        require __DIR__ . '/../../views/students/create.php';
    }

    /**
     * Store new student.
     */
    public function store(string $cohortId): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $name = $_POST['name'] ?? '';
        $leergroep = isset($_POST['leergroep']) ? (int)$_POST['leergroep'] : null;

        if (empty($name) || !in_array($leergroep, [1, 2, 3])) {
            $_SESSION['error'] = 'Naam en leergroep zijn verplicht';
            header("Location: /cohorts/{$cohortId}/students/create");
            exit;
        }

        $data = [
            'name' => $name,
            'leergroep' => $leergroep,
            'cohort_id' => $cohortId,
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoUrl = $this->photoService->upload($_FILES['photo'], 400, 400);
            if ($photoUrl) {
                $data['photo_url'] = $photoUrl;
            }
        }

        $student = $this->studentService->createStudent($data, $userId);

        if (!$student) {
            $_SESSION['error'] = 'Kan student niet aanmaken';
            header("Location: /cohorts/{$cohortId}/students/create");
            exit;
        }

        header("Location: /cohorts/{$cohortId}/students");
        exit;
    }

    /**
     * Show edit form.
     */
    public function edit(string $cohortId, string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $student = $this->studentService->getStudent($id, $userId);

        if (!$student) {
            http_response_code(404);
            echo 'Student niet gevonden';
            exit;
        }

        require __DIR__ . '/../../views/students/edit.php';
    }

    /**
     * Update student.
     */
    public function update(string $cohortId, string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $name = $_POST['name'] ?? '';
        $leergroep = isset($_POST['leergroep']) ? (int)$_POST['leergroep'] : null;

        if (empty($name) || !in_array($leergroep, [1, 2, 3])) {
            $_SESSION['error'] = 'Naam en leergroep zijn verplicht';
            header("Location: /cohorts/{$cohortId}/students/{$id}/edit");
            exit;
        }

        $data = [
            'name' => $name,
            'leergroep' => $leergroep,
        ];

        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $student = $this->studentService->getStudent($id, $userId);
            if ($student && $student->photo_url) {
                $this->photoService->delete($student->photo_url);
            }
            
            $photoUrl = $this->photoService->upload($_FILES['photo'], 400, 400);
            if ($photoUrl) {
                $data['photo_url'] = $photoUrl;
            }
        }

        $student = $this->studentService->updateStudent($id, $data, $userId);

        if (!$student) {
            $_SESSION['error'] = 'Kan student niet bijwerken';
            header("Location: /cohorts/{$cohortId}/students/{$id}/edit");
            exit;
        }

        header("Location: /cohorts/{$cohortId}/students");
        exit;
    }

    /**
     * Delete student.
     */
    public function delete(string $cohortId, string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $student = $this->studentService->getStudent($id, $userId);

        if ($student && $student->photo_url) {
            $this->photoService->delete($student->photo_url);
        }

        $success = $this->studentService->deleteStudent($id, $userId);

        if (!$success) {
            $_SESSION['error'] = 'Kan student niet verwijderen';
        }

        header("Location: /cohorts/{$cohortId}/students");
        exit;
    }
}

