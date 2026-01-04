<?php
use LLTool\Middleware\AuthMiddleware;

$user = AuthMiddleware::user();
$title = 'Student Bewerken';
ob_start();
?>

<div class="px-4 py-6 sm:px-0">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Student Bewerken</h1>

    <div class="bg-white shadow rounded-lg p-6 max-w-md">
        <form method="POST" action="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/<?= htmlspecialchars($student->id) ?>/edit" enctype="multipart/form-data">
            <div class="mb-4">
                <?php if ($student->photo_url): ?>
                    <img src="<?= htmlspecialchars($student->photo_url) ?>" 
                         alt="<?= htmlspecialchars($student->name) ?>"
                         class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                <?php endif; ?>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                    Foto (optioneel)
                </label>
                <input type="file" id="photo" name="photo" accept="image/*"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Naam
                </label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($student->name) ?>" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="leergroep" class="block text-sm font-medium text-gray-700 mb-2">
                    Leergroep
                </label>
                <select id="leergroep" name="leergroep" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="1" <?= $student->leergroep == 1 ? 'selected' : '' ?>>Leergroep 1</option>
                    <option value="2" <?= $student->leergroep == 2 ? 'selected' : '' ?>>Leergroep 2</option>
                    <option value="3" <?= $student->leergroep == 3 ? 'selected' : '' ?>>Leergroep 3</option>
                </select>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Opslaan
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

