<?php
/** @var \App\Models\Department[] $departments */
$pageTitle = 'Departments';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
    <a href="/departments/create"
       class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Department
    </a>
</div>

<?php if (empty($departments)): ?>
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg">No departments yet.</p>
        <a href="/departments/create" class="mt-3 inline-block text-brand hover:underline">Create the first one →</a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-2xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gradient-to-r from-brand/5 to-brand/3 border-b-2 border-brand">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Code</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Description</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($departments as $dept): ?>
                <tr class="hover:bg-brand/3 transition-colors duration-150">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand">
                        <?= htmlspecialchars($dept->code) ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($dept->name) ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= htmlspecialchars($dept->description ?? '—') ?>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <div class="flex justify-end gap-3 items-center">
                            <a href="/departments/<?= $dept->id ?>/courses"
                               class="text-gray-500 hover:text-brand font-medium">Courses</a>
                            <a href="/departments/<?= $dept->id ?>/lecturers"
                               class="text-gray-500 hover:text-brand font-medium">Lecturers</a>
                            <a href="/departments/<?= $dept->id ?>/edit"
                               class="text-brand hover:underline font-medium">Edit</a>
                            <?php if (\App\Auth\Auth::isAdmin()): ?>
                                <form method="POST" action="/departments/<?= $dept->id ?>/delete" class="inline" onsubmit="return confirm('Delete this department? This action cannot be undone.');">
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
