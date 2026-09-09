<?php
$pageTitle = 'Lecturer Portal — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 uppercase tracking-wide">
            Lecturer Access Granted
        </span>
        <h1 class="text-2xl font-bold text-gray-900 mt-4">Lecturer Faculty Portal</h1>
        <p class="text-gray-600 mt-2">Welcome to your faculty dashboard, <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>.</p>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
