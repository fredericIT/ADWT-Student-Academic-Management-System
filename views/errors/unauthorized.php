<?php
$pageTitle = $pageTitle ?? '403 — Unauthorized';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-lg mx-auto my-16 bg-white p-8 rounded-xl shadow-lg border border-red-100 text-center">
    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-gray-900">403 — Access Denied</h1>
    <p class="text-sm text-gray-600 mt-3"><?= htmlspecialchars($errorMessage ?? 'You do not have permission to access this resource.') ?></p>

    <div class="mt-8 flex justify-center gap-4">
        <a href="/dashboard" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow transition-colors">
            Return to Dashboard
        </a>
        <a href="/logout" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-5 py-2.5 rounded-lg border border-gray-300 transition-colors">
            Sign In as Different User
        </a>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
