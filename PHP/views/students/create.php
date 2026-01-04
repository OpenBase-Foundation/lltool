<?php
use LLTool\Middleware\AuthMiddleware;

$user = AuthMiddleware::user();
$title = 'Nieuwe Student';
ob_start();
?>

<div class="px-4 py-6 sm:px-0">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Nieuwe Student</h1>

    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/create" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Naam
                </label>
                <input type="text" id="name" name="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="leergroep" class="block text-sm font-medium text-gray-700 mb-2">
                    Leergroep
                </label>
                <select id="leergroep" name="leergroep" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecteer leergroep</option>
                    <option value="1">Leergroep 1</option>
                    <option value="2">Leergroep 2</option>
                    <option value="3">Leergroep 3</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                    Foto (optioneel)
                </label>
                <input type="file" id="photo" name="photo" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Aanmaken
                </button>
                <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
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

