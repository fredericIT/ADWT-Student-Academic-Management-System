<?php
$pageTitle = 'Dashboard — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto space-y-8">
    <!-- Welcome Header -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?>!</h1>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 uppercase tracking-wide">
                    <?= htmlspecialchars($user['role'] ?? 'Guest') ?>
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Logged in as <span class="font-medium text-gray-700"><?= htmlspecialchars($user['email'] ?? '') ?></span> (User ID: #<?= htmlspecialchars((string) ($user['id'] ?? '')) ?>)</p>
        </div>

        <div>
            <a href="/logout" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm px-4 py-2 rounded-lg transition-colors border border-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign Out
            </a>
        </div>
    </div>

    <!-- Quick Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Student Portal</h3>
                <p class="text-sm text-gray-500 mt-2">View registered courses, grades, and academic profile.</p>
            </div>
            <div class="mt-6">
                <a href="/student-area" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Access Portal &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Lecturer Portal</h3>
                <p class="text-sm text-gray-500 mt-2">Manage assigned courses, record student marks, and schedules.</p>
            </div>
            <div class="mt-6">
                <a href="/lecturer-area" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Access Portal &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Admin Console</h3>
                <p class="text-sm text-gray-500 mt-2">Manage academic departments, course catalogs, and faculty users.</p>
            </div>
            <div class="mt-6">
                <a href="/admin-area" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Access Console &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
