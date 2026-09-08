<?php
/** @var \App\Models\Course $course */
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Enrollment[] $enrollments */
$pageTitle = "Students — {$course->code}";
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="/courses" class="text-sm text-gray-400 hover:text-gray-600">← Courses</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">
            Registered Students in <span class="font-mono text-brand"><?= htmlspecialchars($course->code) ?></span> — <?= htmlspecialchars($course->name) ?>
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Credits: <?= $course->credits ?> | Total active students: <?= count($students) ?>
        </p>
    </div>
    <div class="flex gap-3">
        <a href="/courses/<?= $course->id ?>/results"
           class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Grade Sheet
        </a>
        <a href="/enrollments/create?course_id=<?= $course->id ?>"
           class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Enroll Student
        </a>
    </div>
</div>

<?php if (empty($students)): ?>
    <div class="text-center py-16 bg-white rounded-xl border border-gray-200 text-gray-400">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-lg font-medium text-gray-600">No students registered yet.</p>
        <p class="text-sm text-gray-400 mt-1">There are currently no active students enrolled in this course.</p>
        <a href="/enrollments/create?course_id=<?= $course->id ?>" class="mt-4 inline-block text-brand font-medium hover:underline">
            Register a student for this course →
        </a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student ID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($students as $st):
                    $dept = $st->getDepartment();
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand"><?= htmlspecialchars($st->studentId) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?= htmlspecialchars($st->getFullName()) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($st->email) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= $dept ? htmlspecialchars($dept->name) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right text-sm space-x-3">
                        <a href="/results/record?course_id=<?= $course->id ?>&student_id=<?= $st->id ?>" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs">
                            Record Mark
                        </a>
                        <a href="/students/<?= $st->id ?>/results" class="text-brand hover:underline font-medium text-xs">
                            Results
                        </a>
                        <a href="/enrollments?student_id=<?= $st->id ?>" class="text-gray-500 hover:text-gray-700 font-medium text-xs">
                            Enrollments
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
