<?php
/** @var \App\Models\Lecturer[] $lecturers */
$pageTitle = 'Lecturers';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Lecturers</h1>
    <a href="/lecturers/create"
       class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Register Lecturer
    </a>
</div>

<!-- Search Form -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
    <form method="GET" action="/lecturers" class="flex gap-3">
        <div class="relative flex-1">
            <input type="text"
                   name="query"
                   value="<?= htmlspecialchars($_GET['query'] ?? $_GET['search'] ?? '') ?>"
                   placeholder="Search lecturer by Name or Email..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Search
        </button>
        <?php if (!empty($_GET['query']) || !empty($_GET['search'])): ?>
            <a href="/lecturers"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($lecturers)): ?>
    <div class="text-center py-16 text-gray-400 bg-white rounded-xl border border-gray-200">
        <p class="text-lg">No lecturers found.</p>
        <a href="/lecturers/create" class="mt-3 inline-block text-brand hover:underline">Register a lecturer →</a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($lecturers as $lec): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        <?= htmlspecialchars($lec->getFullName()) ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= htmlspecialchars($lec->email) ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?php if ($lec->departmentId): ?>
                            <?php $dept = \App\Models\Department::findById($lec->departmentId); ?>
                            <?= $dept ? htmlspecialchars($dept->name) : '—' ?>
                        <?php else: ?>
                            <span class="text-gray-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center gap-3">
                            <a href="/lecturers/<?= $lec->id ?>/courses"
                               class="text-gray-500 hover:text-brand text-sm font-medium">Courses</a>
                            <a href="/lecturers/<?= $lec->id ?>/edit"
                               class="text-brand hover:underline text-sm font-medium">Edit</a>
                            <form method="POST" action="/lecturers/<?= $lec->id ?>/delete" onsubmit="return confirm('Are you sure you want to delete this lecturer?');" class="inline">
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
