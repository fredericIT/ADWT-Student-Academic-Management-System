<?php
/** @var \App\Models\Course[] $courses */
/** @var \App\Models\Department[] $departments */
/** @var \App\Models\Course|null $searchResult */
/** @var string $searchCode */
$pageTitle = 'Courses';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Courses</h1>
    <a href="/courses/create"
       class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Course
    </a>
</div>

<!-- Search by code -->
<form method="GET" action="/courses" class="mb-6 flex gap-2 max-w-md">
    <input type="text" name="code"
           value="<?= htmlspecialchars($searchCode) ?>"
           placeholder="Search by course code…"
           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
    <button type="submit"
            class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
        Search
    </button>
    <?php if ($searchCode): ?>
        <a href="/courses" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<!-- Search result -->
<?php if ($searchCode !== ''): ?>
    <?php if ($searchResult): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-blue-800 mb-1">Search result for "<?= htmlspecialchars($searchCode) ?>"</p>
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-mono font-bold text-brand"><?= htmlspecialchars($searchResult->code) ?></span>
                    <span class="text-gray-700 ml-2"><?= htmlspecialchars($searchResult->name) ?></span>
                    <span class="text-gray-400 text-xs ml-2"><?= $searchResult->credits ?> credit(s)</span>
                </div>
                <a href="/courses/<?= $searchResult->id ?>/edit" class="text-brand text-sm hover:underline">Edit</a>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">
            No course found with code "<?= htmlspecialchars($searchCode) ?>".
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Full course table -->
<?php if (empty($courses)): ?>
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg">No courses yet.</p>
        <a href="/courses/create" class="mt-3 inline-block text-brand hover:underline">Create the first one →</a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                // Index departments by id for quick lookup
                $deptMap = [];
                foreach ($departments as $d) { $deptMap[$d->id] = $d; }
                foreach ($courses as $course):
                    $dept = $course->departmentId ? ($deptMap[$course->departmentId] ?? null) : null;
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand"><?= htmlspecialchars($course->code) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($course->name) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $course->credits ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= $dept ? htmlspecialchars($dept->name) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="/courses/<?= $course->id ?>/edit" class="text-brand hover:underline text-sm font-medium">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
