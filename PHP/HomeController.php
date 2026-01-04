<?php
declare(strict_types=1);

namespace LLTool\Controllers;

final class HomeController
{
    public function index(): void
    {
        // Redirect to cohorts page
        header('Location: /cohorts');
        exit;
    }
}
?>