<?php
$pageTitle = "{$course->code} — Course Grade Sheet";
$department = $course->getDepartment();
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <a href="/courses/<?= $course->id ?>/students" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
            &larr; Back to Course Students
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">📊 <?= htmlspecialchars($course->code) ?> Grade Sheet</h1>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($course->name) ?> &bull; Department: <?= $department ? htmlspecialchars($department->name) : 'General' ?> &bull; <?= $course->credits ?> Credit(s)</p>
    </div>
    <div class="flex gap-3">
        <a href="/results/record?course_id=<?= $course->id ?>"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Record Mark
        </a>
    </div>
</div>

<!-- Course Grade Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Class Average</div>
        <div class="text-3xl font-bold <?= $courseAvg >= 50 ? 'text-brand' : 'text-red-600' ?> mt-2">
            <?= number_format($courseAvg, 2) ?>
        </div>
        <div class="text-xs text-gray-400 mt-1">Mean class performance</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pass Rate</div>
        <div class="text-3xl font-bold <?= $passRate >= 50 ? 'text-emerald-600' : 'text-red-600' ?> mt-2">
            <?= $passRate ?>%
        </div>
        <div class="text-xs text-gray-400 mt-1"><?= $passCount ?> passed out of <?= count($grades) ?> assessed</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Assessed Students</div>
        <div class="text-3xl font-bold text-gray-900 mt-2">
            <?= count($grades) ?> <span class="text-sm font-normal text-gray-400">/ <?= count($enrollments) ?> enrolled</span>
        </div>
        <div class="text-xs text-gray-400 mt-1"><?= count($enrollments) - count($grades) ?> awaiting marks</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Course Lecturers</div>
        <div class="mt-2 text-sm font-medium text-gray-800">
            <?php if (empty($lecturers)): ?>
                <span class="text-gray-400 italic">None assigned</span>
            <?php else: ?>
                <?= implode(', ', array_map(fn($l) => htmlspecialchars($l->getFullName()), $lecturers)) ?>
            <?php endif; ?>
        </div>
        <div class="text-xs text-gray-400 mt-1">Authorized to record/update</div>
    </div>
</div>

<!-- Course Grade Sheet Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h2 class="text-base font-bold text-gray-900">Student Assessment Roster</h2>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
            <?= count($grades) ?> Recorded Grades
        </span>
    </div>

    <?php if (empty($grades)): ?>
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-lg font-medium text-gray-600">No marks recorded for this course yet.</p>
            <p class="text-sm text-gray-400 mt-1">Select students from the course roster to record assessment marks.</p>
            <a href="/results/record?course_id=<?= $course->id ?>" class="mt-4 inline-block text-brand font-medium hover:underline">
                Record marks now →
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Student ID</th>
                        <th class="px-6 py-3">Student Name</th>
                        <th class="px-6 py-3 text-center">Mark (%)</th>
                        <th class="px-6 py-3 text-center">Grade</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3">Remarks</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($grades as $grade):
                        $student  = $grade->getStudent();
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-semibold text-brand">
                                <?= $student ? htmlspecialchars($student->studentId) : 'N/A' ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <?= $student ? htmlspecialchars($student->getFullName()) : 'Student #' . $grade->studentId ?>
                            </td>
                            <td class="px-6 py-4 text-center font-mono font-bold text-base <?= $grade->isPass() ? 'text-gray-900' : 'text-red-600' ?>">
                                <?= number_format($grade->mark, 2) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-md bg-gray-100 text-gray-800">
                                    <?= htmlspecialchars($grade->letterGrade) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($grade->isPass()): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        PASS
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        FAIL
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                <?= htmlspecialchars((string)($grade->remarks ?: '—')) ?>
                            </td>
                            <td class="px-6 py-4 text-right text-xs space-x-2">
                                <a href="/results/<?= $grade->id ?>/edit" class="text-brand hover:underline font-medium">
                                    Edit Mark
                                </a>
                                <?php if ($student): ?>
                                    <span class="text-gray-300">|</span>
                                    <a href="/students/<?= $student->id ?>/results" class="text-gray-600 hover:text-gray-900">
                                        Transcript
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
