<?php
use LLTool\Middleware\AuthMiddleware;
use LLTool\Models\Cohort;
use LLTool\Services\CohortService;

$user = AuthMiddleware::user();
$cohortService = new CohortService();
$cohort = Cohort::find($cohortId);
$title = $cohort ? htmlspecialchars($cohort->name) . ' - Studenten' : 'Studenten';
ob_start();
?>

<div class="px-4 py-6 sm:px-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $cohort ? htmlspecialchars($cohort->name) : 'Studenten' ?>
            </h1>
            <?php if ($cohort): ?>
                <p class="text-gray-500 mt-1">Cohort beheer</p>
            <?php endif; ?>
        </div>
        <div class="flex space-x-3">
            <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Nieuwe Student
            </a>
            <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/export" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Exporteren
            </a>
            <a href="/cohorts" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                Terug
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="mb-4 flex space-x-2">
        <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students" 
           class="px-3 py-1 rounded <?= !isset($_GET['leergroep']) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
            Alle
        </a>
        <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students?leergroep=1" 
           class="px-3 py-1 rounded <?= isset($_GET['leergroep']) && $_GET['leergroep'] == 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
            Leergroep 1
        </a>
        <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students?leergroep=2" 
           class="px-3 py-1 rounded <?= isset($_GET['leergroep']) && $_GET['leergroep'] == 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
            Leergroep 2
        </a>
        <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students?leergroep=3" 
           class="px-3 py-1 rounded <?= isset($_GET['leergroep']) && $_GET['leergroep'] == 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
            Leergroep 3
        </a>
    </div>

    <?php if (empty($students)): ?>
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500 mb-4">Nog geen studenten toegevoegd.</p>
            <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/create" class="text-blue-600 hover:text-blue-800">Voeg je eerste student toe</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($students as $student): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <?php if ($student->photo_url): ?>
                            <img src="<?= htmlspecialchars($student->photo_url) ?>" 
                                 alt="<?= htmlspecialchars($student->name) ?>"
                                 class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                        <?php else: ?>
                            <div class="w-24 h-24 rounded-full mx-auto mb-4 bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400 text-2xl"><?= strtoupper(substr($student->name, 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-lg font-medium text-gray-900 text-center mb-1">
                            <?= htmlspecialchars($student->name) ?>
                        </h3>
                        <p class="text-sm text-gray-500 text-center mb-4">
                            Leergroep <?= $student->leergroep ?>
                        </p>
                        <div class="flex justify-center space-x-3">
                            <a href="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/<?= htmlspecialchars($student->id) ?>/edit" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Bewerken
                            </a>
                            <form method="POST" 
                                  action="/cohorts/<?= htmlspecialchars($cohortId) ?>/students/<?= htmlspecialchars($student->id) ?>/delete" 
                                  class="inline" 
                                  onsubmit="return confirm('Weet je zeker dat je deze student wilt verwijderen?');">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    Verwijderen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

