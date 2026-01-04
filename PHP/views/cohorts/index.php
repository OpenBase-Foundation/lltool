<?php
use LLTool\Middleware\AuthMiddleware;

$user = AuthMiddleware::user();
$title = 'Cohorts';
ob_start();
?>

<div class="px-4 py-6 sm:px-0">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Cohorts</h1>
        <a href="/cohorts/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Nieuw Cohort
        </a>
    </div>

    <?php if (empty($cohorts)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">Nog geen cohorts aangemaakt.</p>
            <a href="/cohorts/create" class="text-blue-600 hover:text-blue-800">Maak je eerste cohort aan</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($cohorts as $cohort): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">
                                <a href="/cohorts/<?= htmlspecialchars($cohort->id) ?>/students" class="hover:text-blue-600">
                                    <?= htmlspecialchars($cohort->name) ?>
                                </a>
                            </h3>
                            <?php if ($cohort->isOwner($user['sub'])): ?>
                                <span class="text-xs text-gray-500">Eigenaar</span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 flex space-x-3">
                            <a href="/cohorts/<?= htmlspecialchars($cohort->id) ?>/students" class="text-blue-600 hover:text-blue-800 text-sm">
                                Bekijken
                            </a>
                            <?php if ($cohort->isOwner($user['sub'])): ?>
                                <a href="/cohorts/<?= htmlspecialchars($cohort->id) ?>/edit" class="text-gray-600 hover:text-gray-800 text-sm">
                                    Bewerken
                                </a>
                                <form method="POST" action="/cohorts/<?= htmlspecialchars($cohort->id) ?>/delete" class="inline" onsubmit="return confirm('Weet je zeker dat je dit cohort wilt verwijderen?');">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                        Verwijderen
                                    </button>
                                </form>
                            <?php endif; ?>
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

