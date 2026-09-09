<?php
/** @var \App\Models\Lecturer $lecturer */
/** @var \App\Models\Course[] $courses */
$isLecturer = \App\Auth\Auth::isLecturer();
$isAdmin    = \App\Auth\Auth::isAdmin();

$pageTitle = $isLecturer ? 'My Teaching Assignments' : "Assigned Courses — {$lecturer->getFullName()}";
require __DIR__ . '/../layout/header.php';

$totalStudentsCount = 0;
foreach ($courses as $c) {
    $totalStudentsCount += count($c->getEnrolledStudents());
}
?>

<div class="mb-6">
    <?php if ($isLecturer): ?>
        <a href="/dashboard" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1">
            &larr; Back to Dashboard
        </a>
    <?php else: ?>
        <a href="/lecturers" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center gap-1">
            &larr; Back to Lecturers
        </a>
    <?php endif; ?>
</div>

<!-- Faculty Profile Banner -->
<div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-slate-900 rounded-2xl p-6 mb-8 text-white shadow-md">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-white text-2xl font-bold font-mono shadow-inner">
                <?= strtoupper(substr($lecturer->firstName, 0, 1) . substr($lecturer->lastName, 0, 1)) ?>
            </div>
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/15 text-indigo-200 text-xs font-semibold mb-1">
                    <span>Faculty Member</span>
                    <span>&bull;</span>
                    <span class="font-mono"><?= htmlspecialchars($lecturer->staffNumber ?? 'FACULTY') ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
                    <?= htmlspecialchars($lecturer->getFullName()) ?>
                </h1>
                <p class="text-indigo-200 text-sm mt-0.5"><?= htmlspecialchars($lecturer->email) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-4 border-t sm:border-t-0 sm:border-l border-white/10 pt-4 sm:pt-0 sm:pl-6">
            <div class="text-center px-3">
                <div class="text-2xl font-extrabold"><?= count($courses) ?></div>
                <div class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Assigned Courses</div>
            </div>
            <div class="w-px h-8 bg-white/20"></div>
            <div class="text-center px-3">
                <div class="text-2xl font-extrabold"><?= $totalStudentsCount ?></div>
                <div class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Active Students</div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-bold text-gray-900">Assigned Teaching Modules</h2>
    <div class="flex items-center gap-2">
        <a href="/results" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
            📊 View Grade Book
        </a>
        <a href="/results/record" class="text-xs font-semibold text-white bg-brand hover:bg-brand-dark px-3 py-1.5 rounded-lg transition-colors shadow-sm">
            📝 Record Marks
        </a>
    </div>
</div>

<?php if (empty($courses)): ?>
    <div class="text-center py-16 bg-white rounded-xl border border-gray-200 text-gray-400">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-base font-medium text-gray-600">No courses assigned yet.</p>
        <p class="text-sm text-gray-400 mt-1">You are currently not assigned as an instructor to any active course module.</p>
        <?php if ($isAdmin): ?>
            <a href="/courses" class="mt-3 inline-block text-brand hover:underline font-medium text-sm">Assign courses in Curriculum →</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Roster</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Teaching Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($courses as $course):
                    $enrolledCount = count($course->getEnrolledStudents());
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand"><?= htmlspecialchars($course->code) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($course->name) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $course->credits ?> credits</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $enrolledCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' ?>">
                            <?= $enrolledCount ?> student<?= $enrolledCount === 1 ? '' : 's' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="/courses/<?= $course->id ?>/students"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                            Class Roster
                        </a>
                        <a href="/results/record?course_id=<?= $course->id ?>"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-xs font-semibold text-white transition-colors shadow-sm">
                            Grade Sheet
                        </a>
                        <?php if ($isAdmin): ?>
                            <a href="/courses/<?= $course->id ?>/edit" class="text-xs text-gray-500 hover:underline">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
