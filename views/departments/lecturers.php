<?php
/** @var \App\Models\Department $department */
/** @var \App\Models\Lecturer[] $lecturers */
$pageTitle = "Lecturers — {$department->name}";
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6">
    <a href="/departments" class="text-sm text-gray-400 hover:text-gray-600">← Departments</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">
        Lecturers in <span class="text-brand"><?= htmlspecialchars($department->name) ?></span>
    </h1>
</div>

<?php if (empty($lecturers)): ?>
    <div class="text-center py-12 text-gray-400">
        <p>No lecturers associated with this department yet.</p>
        <a href="/lecturers/create" class="mt-2 inline-block text-brand hover:underline">Add a lecturer →</a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($lecturers as $lec): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($lec->getFullName()) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($lec->email) ?></td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-3">
                            <a href="/lecturers/<?= $lec->id ?>/courses" class="text-gray-500 hover:text-brand text-sm">Courses</a>
                            <a href="/lecturers/<?= $lec->id ?>/edit"    class="text-brand hover:underline text-sm">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
