<?php
use LLTool\Middleware\AuthMiddleware;

$user = AuthMiddleware::user();
$title = 'Nieuw Cohort';
ob_start();
?>

<div class="px-4 py-6 sm:px-0">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Nieuw Cohort</h1>

    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="/cohorts/create">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Cohort Naam
                </label>
                <input type="text" id="name" name="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Aanmaken
                </button>
                <a href="/cohorts" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                    Annuleren
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

