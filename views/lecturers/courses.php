<?php
/** @var \App\Models\Lecturer $lecturer */
/** @var \App\Models\Course[] $courses */
$pageTitle = "Assigned Courses — {$lecturer->getFullName()}";
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6">
    <a href="/lecturers" class="text-sm text-gray-400 hover:text-gray-600">← Lecturers</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">
        Courses assigned to <span class="text-brand"><?= htmlspecialchars($lecturer->getFullName()) ?></span>
    </h1>
    <p class="text-sm text-gray-400 mt-0.5"><?= htmlspecialchars($lecturer->email) ?></p>
</div>

<?php if (empty($courses)): ?>
    <div class="text-center py-12 text-gray-400">
        <p>No courses assigned to this lecturer yet.</p>
        <a href="/courses" class="mt-2 inline-block text-brand hover:underline">Browse courses →</a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($courses as $course): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand"><?= htmlspecialchars($course->code) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($course->name) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $course->credits ?></td>
                    <td class="px-6 py-4 text-right">
                        <a href="/courses/<?= $course->id ?>/edit" class="text-brand hover:underline text-sm">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
