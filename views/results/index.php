<?php
$pageTitle = 'Academic Results & Records';
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">📊 Academic Results & Records</h1>
        <p class="text-sm text-gray-500 mt-1">Manage grade recording, track student performance, and view transcripts.</p>
    </div>
    <div class="flex gap-3">
        <a href="/results/record"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Record Marks
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Grades</div>
        <div class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_grades'] ?></div>
        <div class="text-xs text-gray-400 mt-1">Recorded assessments</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Overall Pass Rate</div>
        <div class="text-3xl font-bold <?= $stats['pass_rate'] >= 50 ? 'text-emerald-600' : 'text-red-600' ?> mt-2">
            <?= $stats['pass_rate'] ?>%
        </div>
        <div class="text-xs text-gray-400 mt-1"><?= $stats['passed_count'] ?> passed / <?= $stats['failed_count'] ?> failed</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Mark</div>
        <div class="text-3xl font-bold text-brand mt-2"><?= number_format($stats['average_mark'], 2) ?></div>
        <div class="text-xs text-gray-400 mt-1">Out of 100.00 max</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pass Standard</div>
        <div class="text-3xl font-bold text-gray-800 mt-2">50.0</div>
        <div class="text-xs text-gray-400 mt-1">Passing threshold mark</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" action="/results" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filter by Student</label>
            <select name="student_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="">-- All Students --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s->id ?>" <?= $selectedStudentId === $s->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s->studentId) ?> — <?= htmlspecialchars($s->getFullName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filter by Course</label>
            <select name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="">-- All Courses --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c->id ?>" <?= $selectedCourseId === $c->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c->code) ?> — <?= htmlspecialchars($c->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Apply Filter
            </button>
            <?php if ($selectedStudentId !== null || $selectedCourseId !== null): ?>
                <a href="/results" class="text-sm text-gray-500 hover:text-gray-800 px-3 py-2">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h2 class="text-base font-bold text-gray-900">Recorded Academic Marks</h2>
        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
            <?= count($grades) ?> Records
        </span>
    </div>

    <?php if (empty($grades)): ?>
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-lg font-medium text-gray-600">No grades found.</p>
            <p class="text-sm text-gray-400 mt-1">No marks have been recorded matching your criteria.</p>
            <a href="/results/record" class="mt-4 inline-block text-brand font-medium hover:underline">
                Record a student mark →
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-3">Student</th>
                        <th class="px-6 py-3">Course</th>
                        <th class="px-6 py-3 text-center">Mark</th>
                        <th class="px-6 py-3 text-center">Grade</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3">Lecturer</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ($grades as $grade):
                        $student  = $grade->getStudent();
                        $course   = $grade->getCourse();
                        $lecturer = $grade->getLecturer();
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <?php if ($student): ?>
                                    <a href="/students/<?= $student->id ?>/results" class="font-medium text-gray-900 hover:text-brand">
                                        <?= htmlspecialchars($student->getFullName()) ?>
                                    </a>
                                    <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($student->studentId) ?></div>
                                <?php else: ?>
                                    <span class="text-gray-400">Unknown Student #<?= $grade->studentId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($course): ?>
                                    <a href="/courses/<?= $course->id ?>/results" class="font-medium text-gray-800 hover:text-brand">
                                        <?= htmlspecialchars($course->name) ?>
                                    </a>
                                    <div class="text-xs text-gray-400 font-mono"><?= htmlspecialchars($course->code) ?> (<?= $course->credits ?> cr)</div>
                                <?php else: ?>
                                    <span class="text-gray-400">Course #<?= $grade->courseId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-base <?= $grade->isPass() ? 'text-gray-900' : 'text-red-600' ?>">
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
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= $lecturer ? htmlspecialchars($lecturer->getFullName()) : '<span class="text-gray-300 italic">System Admin</span>' ?>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 text-sm">
                                <a href="/results/<?= $grade->id ?>/edit" class="text-brand hover:underline font-medium text-xs">
                                    Edit Mark
                                </a>
                                <?php if ($student): ?>
                                    <span class="text-gray-300">|</span>
                                    <a href="/students/<?= $student->id ?>/results" class="text-gray-600 hover:text-gray-900 text-xs">
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
