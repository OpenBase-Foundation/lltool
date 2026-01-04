<?php
$title = 'Client Beheer';
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Students Column -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Client Studenten
            </h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <form action="/admin/client/students" method="POST" class="mb-6">
                <div class="flex rounded-md shadow-sm">
                    <input type="text" name="name" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 border" placeholder="Naam student" required>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Toevoegen
                    </button>
                </div>
            </form>

            <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                <?php if (empty($students)): ?>
                    <li class="px-4 py-4 sm:px-6 text-sm text-gray-500 text-center">Geen studenten gevonden.</li>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <li class="px-4 py-4 sm:px-6 flex justify-between items-center hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($student['name']) ?></span>
                            <form action="/admin/client/students/delete" method="POST" class="ml-4 flex-shrink-0" onsubmit="return confirm('Weet je zeker dat je deze student wilt verwijderen?');">
                                <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Verwijderen</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Teachers Column -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Client Docenten
            </h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <form action="/admin/client/teachers" method="POST" class="mb-6">
                <div class="flex rounded-md shadow-sm">
                    <input type="text" name="name" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md sm:text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 border" placeholder="Naam docent" required>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-r-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Toevoegen
                    </button>
                </div>
            </form>

            <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                <?php if (empty($teachers)): ?>
                    <li class="px-4 py-4 sm:px-6 text-sm text-gray-500 text-center">Geen docenten gevonden.</li>
                <?php else: ?>
                    <?php foreach ($teachers as $teacher): ?>
                        <li class="px-4 py-4 sm:px-6 flex justify-between items-center hover:bg-gray-50">
                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($teacher['name']) ?></span>
                            <form action="/admin/client/teachers/delete" method="POST" class="ml-4 flex-shrink-0" onsubmit="return confirm('Weet je zeker dat je deze docent wilt verwijderen?');">
                                <input type="hidden" name="id" value="<?= $teacher['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Verwijderen</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layout.php';
?>
