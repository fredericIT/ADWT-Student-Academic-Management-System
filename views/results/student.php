<?php
$pageTitle = htmlspecialchars($student->getFullName()) . ' — Academic Record';
$department = $student->getDepartment();
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <a href="/students/<?= $student->id ?>" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
            &larr; Back to Student Profile
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">📜 Academic Record & Transcript</h1>
        <p class="text-sm text-gray-500 font-mono">Student: <span class="font-bold text-gray-900"><?= htmlspecialchars($student->getFullName()) ?></span> (<?= htmlspecialchars($student->studentId) ?>)</p>
    </div>
    <div class="flex gap-3">
        <a href="/results/record?student_id=<?= $student->id ?>"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            + Record New Mark
        </a>
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Transcript
        </button>
    </div>
</div>

<!-- Academic Summary Scorecard -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Arithmetic Average</div>
        <div class="text-3xl font-bold <?= $average >= 50 ? 'text-emerald-600' : 'text-red-600' ?> mt-2">
            <?= number_format($average, 2) ?>
        </div>
        <div class="text-xs text-gray-400 mt-1">Mean of all course marks</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Weighted Average</div>
        <div class="text-3xl font-bold text-brand mt-2">
            <?= number_format($weighted, 2) ?>
        </div>
        <div class="text-xs text-gray-400 mt-1">Weighted by credit hours</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Credits Earned</div>
        <div class="text-3xl font-bold text-gray-900 mt-2">
            <?= $record->getEarnedCredits() ?> <span class="text-sm font-normal text-gray-400">/ <?= $record->getTotalCredits() ?></span>
        </div>
        <div class="text-xs text-gray-400 mt-1"><?= $record->getPassedCoursesCount() ?> passed, <?= $record->getFailedCoursesCount() ?> failed</div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Academic Status</div>
        <div class="mt-2">
            <?php if (empty($grades)): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600">
                    PENDING
                </span>
            <?php elseif ($status === 'PASS'): ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                    ✓ PASS
                </span>
            <?php else: ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                    ✕ FAIL
                </span>
            <?php endif; ?>
        </div>
        <div class="text-xs text-gray-400 mt-1">Pass criteria: Average &ge; 50.00</div>
    </div>
</div>

<!-- Detailed Grades Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <div>
            <h2 class="text-base font-bold text-gray-900">Official Course Results</h2>
            <p class="text-xs text-gray-500">Department: <?= $department ? htmlspecialchars($department->name) : 'General' ?></p>
        </div>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
            <?= count($grades) ?> Courses Graded
        </span>
    </div>

    <?php if (empty($grades)): ?>
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-lg font-medium text-gray-600">No grades recorded yet.</p>
            <p class="text-sm text-gray-400 mt-1">No exam or assessment marks have been saved for this student.</p>
            <a href="/results/record?student_id=<?= $student->id ?>" class="mt-4 inline-block text-brand font-medium hover:underline">
                Record marks for this student →
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Course Code</th>
                        <th class="px-6 py-3">Course Name</th>
                        <th class="px-6 py-3 text-center">Credits</th>
                        <th class="px-6 py-3 text-center">Mark (%)</th>
                        <th class="px-6 py-3 text-center">Letter Grade</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3">Remarks</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($grades as $grade):
                        $course   = $grade->getCourse();
                        $lecturer = $grade->getLecturer();
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-gray-900">
                                <?= $course ? htmlspecialchars($course->code) : 'N/A' ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800">
                                <?= $course ? htmlspecialchars($course->name) : 'Course #' . $grade->courseId ?>
                            </td>
                            <td class="px-6 py-4 text-center text-gray-600 font-mono">
                                <?= $course ? $course->credits : 1 ?>
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
                            <td class="px-6 py-4 text-right text-xs">
                                <a href="/results/<?= $grade->id ?>/edit" class="text-brand hover:underline font-medium">
                                    Edit Mark
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
