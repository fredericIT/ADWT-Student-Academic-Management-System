<?php
$pageTitle = 'Student Directory — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Student Directory</h1>
        <p class="text-sm text-gray-500">Manage student records, addresses, profiles, and departmental assignments.</p>
    </div>
    <div>
        <a href="/students/create"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg font-medium shadow-sm transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Register Student
        </a>
    </div>
</div>

<!-- Search Form -->
<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6">
    <form method="GET" action="/students" class="flex gap-3">
        <div class="relative flex-1">
            <input type="text"
                   name="query"
                   value="<?= htmlspecialchars($searchQuery ?? '') ?>"
                   placeholder="Search student by Registration ID, Name, or Email..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit"
                class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Search
        </button>
        <?php if (!empty($searchQuery)): ?>
            <a href="/students"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Students List Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <?php if (empty($students)): ?>
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-800">No students found</h3>
            <p class="text-sm text-gray-500 mt-1">
                <?= !empty($searchQuery) ? 'No student matched your search query.' : 'Get started by registering a new student.' ?>
            </p>
            <?php if (empty($searchQuery)): ?>
                <a href="/students/create" class="inline-block mt-4 text-sm font-medium text-brand hover:underline">
                    + Register Student
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gradient-to-r from-brand/5 to-brand/3 border-b-2 border-brand">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Student ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Full Name</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Department</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Address</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($students as $s): ?>
                        <?php
                            $dept = $s->getDepartment();
                            $addr = $s->getAddress();
                        ?>
                        <tr class="hover:bg-brand/3 transition-colors duration-150">
                            <td class="px-6 py-4 font-mono font-semibold text-brand">
                                <?= htmlspecialchars($s->studentId) ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <?= htmlspecialchars($s->getFullName()) ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <?= htmlspecialchars($s->email) ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <?php if ($dept): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <?= htmlspecialchars($dept->name) ?> (<?= htmlspecialchars($dept->code) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs max-w-xs truncate">
                                <?= $addr ? htmlspecialchars($addr->getFullAddress()) : '<span class="text-gray-400 italic">No address</span>' ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3 items-center">
                                    <a href="/students/<?= $s->id ?>" class="font-medium text-brand hover:underline">View</a>
                                    <a href="/students/<?= $s->id ?>/edit" class="font-medium text-gray-600 hover:text-gray-900">Edit</a>
                                    <?php if (\App\Auth\Auth::isAdmin()): ?>
                                        <form method="POST" action="/students/<?= $s->id ?>/delete" class="inline" onsubmit="return confirm('Delete this student? This action cannot be undone.');">
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">Delete</button>
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
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
