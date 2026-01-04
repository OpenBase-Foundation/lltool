<?php
declare(strict_types=1);

namespace LLTool\Http;

use LLTool\Controllers\HomeController;
use LLTool\Controllers\AuthController;
use LLTool\Controllers\CohortController;
use LLTool\Controllers\StudentController;
use LLTool\Controllers\ExportController;
use LLTool\Controllers\ApiClientController;
use LLTool\Controllers\AdminClientController;
use LLTool\Middleware\AuthMiddleware;

/**
 * Simple router that dispatches requests to appropriate controllers.
 */
final class Router
{
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Auth routes (no auth required)
        if (str_starts_with($uri, '/auth')) {
            $this->handleAuthRoutes($uri);
            return;
        }

        // Client API routes (secured by API Key)
        if (str_starts_with($uri, '/api/client')) {
            $this->handleApiClientRoutes($uri);
            return;
        }

        // Apply AuthMiddleware to all other routes
        (new AuthMiddleware())->handle(function () use ($uri, $method) {
            // Home page
            if ($uri === '/' || $uri === '') {
                (new HomeController())->index();
                return;
            }

            // Cohort routes
            if (preg_match('#^/cohorts/([^/]+)/students/([^/]+)/edit$#', $uri, $matches) && $method === 'GET') {
                (new StudentController())->edit($matches[1], $matches[2]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/students/([^/]+)/edit$#', $uri, $matches) && $method === 'POST') {
                (new StudentController())->update($matches[1], $matches[2]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/students/([^/]+)/delete$#', $uri, $matches) && $method === 'POST') {
                (new StudentController())->delete($matches[1], $matches[2]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/students/create$#', $uri, $matches) && $method === 'GET') {
                (new StudentController())->create($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/students/create$#', $uri, $matches) && $method === 'POST') {
                (new StudentController())->store($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/students$#', $uri, $matches)) {
                (new StudentController())->index($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/export$#', $uri, $matches)) {
                (new ExportController())->export($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/edit$#', $uri, $matches) && $method === 'GET') {
                (new CohortController())->edit($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/edit$#', $uri, $matches) && $method === 'POST') {
                (new CohortController())->update($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/delete$#', $uri, $matches) && $method === 'POST') {
                (new CohortController())->delete($matches[1]);
                return;
            }
            if (preg_match('#^/cohorts/([^/]+)/share$#', $uri, $matches) && $method === 'POST') {
                (new CohortController())->share($matches[1]);
                return;
            }
            if ($uri === '/cohorts/create' && $method === 'GET') {
                (new CohortController())->create();
                return;
            }
            if ($uri === '/cohorts/create' && $method === 'POST') {
                (new CohortController())->store();
                return;
            }
            if ($uri === '/cohorts') {
                (new CohortController())->index();
                return;
            }

            // Admin Client Routes
            if ($uri === '/admin/client' && $method === 'GET') {
                (new AdminClientController())->index();
                return;
            }
            if ($uri === '/admin/client/students' && $method === 'POST') {
                (new AdminClientController())->storeStudent();
                return;
            }
            if ($uri === '/admin/client/students/delete' && $method === 'POST') {
                (new AdminClientController())->deleteStudent();
                return;
            }
            if ($uri === '/admin/client/teachers' && $method === 'POST') {
                (new AdminClientController())->storeTeacher();
                return;
            }
            if ($uri === '/admin/client/teachers/delete' && $method === 'POST') {
                (new AdminClientController())->deleteTeacher();
                return;
            }

            http_response_code(404);
            echo '404 Not Found';
        });
    }

    /**
     * Handle Client API routes.
     */
    private function handleApiClientRoutes(string $uri): void
    {
        $controller = new ApiClientController();
        
        if ($uri === '/api/client/student') {
            $controller->handleStudent();
            return;
        }
        if ($uri === '/api/client/teacher') {
            $controller->handleTeacher();
            return;
        }
        
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    /**
     * Handle authentication routes.
     */
    private function handleAuthRoutes(string $uri): void
    {
        $controller = new AuthController();
        
        if ($uri === '/auth/login') {
            $controller->login();
            return;
        }
        if ($uri === '/auth/callback') {
            $controller->callback();
            return;
        }
        if ($uri === '/auth/logout') {
            $controller->logout();
            return;
        }
    }
}
