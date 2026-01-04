<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Middleware\AuthMiddleware;
use LLTool\Services\CohortService;

final class CohortController
{
    private CohortService $cohortService;

    public function __construct()
    {
        $this->cohortService = new CohortService();
    }

    /**
     * List all cohorts.
     */
    public function index(): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $cohorts = $this->cohortService->getAccessibleCohorts($userId);
        
        require __DIR__ . '/../../views/cohorts/index.php';
    }

    /**
     * Show create form.
     */
    public function create(): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        require __DIR__ . '/../../views/cohorts/create.php';
    }

    /**
     * Store new cohort.
     */
    public function store(): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $name = $_POST['name'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = 'Cohort naam is verplicht';
            header('Location: /cohorts/create');
            exit;
        }

        $cohort = $this->cohortService->createCohort($name, $userId);
        
        header('Location: /cohorts');
        exit;
    }

    /**
     * Show edit form.
     */
    public function edit(string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $cohort = $this->cohortService->getCohort($id, $userId);

        if (!$cohort) {
            http_response_code(404);
            echo 'Cohort niet gevonden';
            exit;
        }

        require __DIR__ . '/../../views/cohorts/edit.php';
    }

    /**
     * Update cohort.
     */
    public function update(string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $name = $_POST['name'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = 'Cohort naam is verplicht';
            header("Location: /cohorts/{$id}/edit");
            exit;
        }

        $cohort = $this->cohortService->updateCohort($id, $name, $userId);

        if (!$cohort) {
            $_SESSION['error'] = 'Cohort niet gevonden of geen rechten';
            header('Location: /cohorts');
            exit;
        }

        header('Location: /cohorts');
        exit;
    }

    /**
     * Delete cohort.
     */
    public function delete(string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $success = $this->cohortService->deleteCohort($id, $userId);

        if (!$success) {
            $_SESSION['error'] = 'Cohort niet gevonden of geen rechten';
        }

        header('Location: /cohorts');
        exit;
    }

    /**
     * Share cohort with user.
     */
    public function share(string $id): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $shareUserId = $_POST['user_id'] ?? '';
        $permissions = $_POST['permissions'] ?? 'view';

        if (empty($shareUserId)) {
            $_SESSION['error'] = 'Gebruiker is verplicht';
            header("Location: /cohorts/{$id}");
            exit;
        }

        $success = $this->cohortService->shareCohort($id, $shareUserId, $permissions, $userId);

        if (!$success) {
            $_SESSION['error'] = 'Kan cohort niet delen';
        }

        header("Location: /cohorts/{$id}");
        exit;
    }
}

