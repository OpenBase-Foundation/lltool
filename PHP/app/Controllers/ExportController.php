<?php
declare(strict_types=1);

namespace LLTool\Controllers;

use LLTool\Middleware\AuthMiddleware;
use LLTool\Services\ExportService;
use LLTool\Services\CohortService;

final class ExportController
{
    private ExportService $exportService;
    private CohortService $cohortService;

    public function __construct()
    {
        $this->exportService = new ExportService();
        $this->cohortService = new CohortService();
    }

    /**
     * Export cohort to Word document.
     */
    public function export(string $cohortId): void
    {
        AuthMiddleware::check() || (new AuthMiddleware())->handle();
        
        $userId = AuthMiddleware::userId();
        $cohort = $this->cohortService->getCohort($cohortId, $userId);

        if (!$cohort) {
            http_response_code(404);
            echo 'Cohort niet gevonden';
            exit;
        }

        // Generate temporary file
        $filename = 'cohort_' . $cohortId . '_' . date('Y-m-d') . '.docx';
        $tempPath = sys_get_temp_dir() . '/' . $filename;

        $success = $this->exportService->exportCohort($cohortId, $tempPath);

        if (!$success) {
            $_SESSION['error'] = 'Kan export niet genereren';
            header("Location: /cohorts/{$cohortId}/students");
            exit;
        }

        // Send file to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tempPath));
        
        readfile($tempPath);
        unlink($tempPath);
        exit;
    }
}

