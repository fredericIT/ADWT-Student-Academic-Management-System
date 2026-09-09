<?php
/** @var \App\Models\Grade[] $grades */
/** @var array $stats */
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Course[] $courses */
/** @var \App\Models\Lecturer[] $lecturers */
/** @var int|null $selectedStudentId */
/** @var int|null $selectedCourseId */
/** @var \App\Models\Student|null $currentStudent */
/** @var \App\Models\Lecturer|null $currentLecturer */
/** @var \App\Models\AcademicRecord|null $studentRecord */

use App\Auth\Auth;

$role       = Auth::getRole();
$isStudent  = Auth::isStudent();
$isLecturer = Auth::isLecturer();
$isAdmin    = Auth::isAdmin();

$pageTitle = $isStudent
    ? 'My Academic Results'
    : ($isLecturer ? 'Course Grade Book' : 'Academic Results & Records');

require __DIR__ . '/../layout/header.php';
?>

<?php if ($isStudent && $currentStudent): ?>
    <!-- ============================================================= -->
    <!--                   STUDENT PERSONAL TRANSCRIPT                 -->
    <!-- ============================================================= -->
    <div class="mb-8">
        <!-- Student Header Card -->
        <div class="bg-gradient-to-r from-blue-700 via-brand to-indigo-800 rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-3xl shadow-inner border border-white/20">
                        🎓
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/20 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-1">
                            Official Student Transcript
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            <?= htmlspecialchars($currentStudent->getFullName()) ?>
                        </h1>
                        <p class="text-blue-100 text-sm mt-0.5">
                            Student ID: <span class="font-mono font-bold text-white"><?= htmlspecialchars($currentStudent->studentId) ?></span>
                            &bull; <?= htmlspecialchars($currentStudent->email) ?>
                        </p>
                        <p class="text-blue-200 text-xs mt-1">
                            <?= htmlspecialchars($currentStudent->getDepartment()?->name ?? 'Department') ?>
                        </p>
                    </div>
                </div>
                <div class="self-start md:self-auto flex items-center gap-3">
                    <a href="/my-courses"
                       class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 rounded-xl text-sm font-medium border border-white/20 backdrop-blur-sm transition-colors">
                        View Enrolled Courses →
                    </a>
                </div>
            </div>
        </div>

        <!-- Academic Performance Metrics -->
        <?php
        $avgMark      = $studentRecord ? $studentRecord->calculateAverage() : 0.0;
        $gpa          = $studentRecord ? $studentRecord->calculateGPA() : 0.0;
        $totalCredits = $studentRecord ? $studentRecord->getTotalCredits() : 0;
        $overallStatus= $studentRecord ? $studentRecord->determineOverallStatus() : 'PENDING';
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cumulative GPA</div>
                <div class="text-3xl font-extrabold text-brand mt-2"><?= number_format($gpa, 2) ?></div>
                <div class="text-xs text-gray-400 mt-1">On standard 4.0 scale</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Mark</div>
                <div class="text-3xl font-extrabold text-gray-900 mt-2"><?= number_format($avgMark, 2) ?>%</div>
                <div class="text-xs text-gray-400 mt-1">Across all graded modules</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Earned Credits</div>
                <div class="text-3xl font-extrabold text-gray-900 mt-2"><?= $totalCredits ?></div>
                <div class="text-xs text-gray-400 mt-1">Accumulated credit hours</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Academic Standing</div>
                <div class="mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?= $overallStatus === 'PASS' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $overallStatus === 'PASS' ? '✓ In Good Standing (PASS)' : '⚠️ Action Required (FAIL)' ?>
                    </span>
                </div>
                <div class="text-xs text-gray-400 mt-1">50.0% minimum threshold</div>
            </div>
        </div>

        <!-- Grades Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h2 class="text-base font-bold text-gray-900">Semester Course Grades</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    <?= count($grades) ?> Modules
                </span>
            </div>

            <?php if (empty($grades)): ?>
                <div class="text-center py-16 text-gray-400">
                    <p class="text-lg font-medium text-gray-600">No grades recorded yet.</p>
                    <p class="text-sm text-gray-400 mt-1">Your grades will be published here once your lecturers submit your marks.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course Code</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course Title</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Credits</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Mark</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Grade</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Instructor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($grades as $g):
                                $course   = $g->getCourse();
                                $lecturer = $g->getLecturer();
                                $isPass   = $g->isPass();
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-mono font-bold text-brand">
                                    <?= htmlspecialchars($course ? $course->code : '—') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                    <?= htmlspecialchars($course ? $course->name : 'Unknown Course') ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-500">
                                    <?= $course ? $course->credits : 1 ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center font-mono font-bold text-gray-900">
                                    <?= number_format($g->mark, 2) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm <?= in_array($g->letterGrade, ['A', 'B']) ? 'bg-green-100 text-green-800' : (in_array($g->letterGrade, ['C', 'D']) ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= htmlspecialchars($g->letterGrade) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $isPass ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                                        <?php if ($isPass): ?>
                                            <span class="flex h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        <?php else: ?>
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($g->status) ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= htmlspecialchars($lecturer ? $lecturer->getFullName() : 'Academic Faculty') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($isLecturer && $currentLecturer): ?>
    <!-- ============================================================= -->
    <!--                   LECTURER COURSE GRADE BOOK                  -->
    <!-- ============================================================= -->
    <div class="mb-8">
        <!-- Lecturer Banner -->
        <div class="bg-gradient-to-r from-blue-700 via-brand to-indigo-800 rounded-2xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-3xl shadow-inner border border-white/20">
                        👨‍🏫
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-white/20 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-1">
                            Lecturer Assessment Portal
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            <?= htmlspecialchars($currentLecturer->getFullName()) ?>
                        </h1>
                        <p class="text-blue-100 text-sm mt-0.5">
                            <?= htmlspecialchars($currentLecturer->email) ?> &bull; <?= htmlspecialchars($currentLecturer->getDepartment()?->name ?? 'Department') ?>
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="/results/record"
                       class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-sm transition-colors text-sm">
                        <span>📝 Record Marks</span>
                    </a>
                    <a href="/my-assigned-courses"
                       class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white px-4 py-2.5 rounded-xl text-sm font-medium border border-white/20 backdrop-blur-sm transition-colors">
                        My Courses →
                    </a>
                </div>
            </div>
        </div>

        <!-- Class Performance KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Teaching Modules</div>
                <div class="text-3xl font-bold text-brand mt-2"><?= $stats['assigned_count'] ?? count($courses) ?></div>
                <div class="text-xs text-gray-400 mt-1">Courses assigned to you</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Grades Submitted</div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_grades'] ?? 0 ?></div>
                <div class="text-xs text-gray-400 mt-1">Evaluated assessments</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Class Average</div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($stats['average_mark'] ?? 0.0, 2) ?>%</div>
                <div class="text-xs text-gray-400 mt-1">Across your teaching modules</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Class Pass Rate</div>
                <div class="text-3xl font-bold <?= ($stats['pass_rate'] ?? 0) >= 50 ? 'text-emerald-600' : 'text-red-600' ?> mt-2">
                    <?= $stats['pass_rate'] ?? 0 ?>%
                </div>
                <div class="text-xs text-gray-400 mt-1"><?= $stats['passed_count'] ?? 0 ?> passed / <?= $stats['failed_count'] ?? 0 ?> failed</div>
            </div>
        </div>

        <!-- Filter by Course (Assigned Only) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="/results" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filter by Your Course</label>
                    <select name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand">
                        <option value="">-- All My Assigned Courses --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c->id ?>" <?= $selectedCourseId === $c->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c->code) ?> — <?= htmlspecialchars($c->name) ?> (<?= $c->credits ?> cr)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Filter
                    </button>
                    <?php if ($selectedCourseId !== null): ?>
                        <a href="/results" class="text-sm text-gray-500 hover:text-gray-800 px-3 py-2 flex items-center">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Results Table (Lecturer's Students Only) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h2 class="text-base font-bold text-gray-900">Student Grade Book</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    <?= count($grades) ?> Records
                </span>
            </div>

            <?php if (empty($grades)): ?>
                <div class="text-center py-16 text-gray-400">
                    <p class="text-lg font-medium text-gray-600">No marks recorded for your courses yet.</p>
                    <a href="/results/record" class="mt-3 inline-block text-brand font-semibold hover:underline">
                        Record your first grade now →
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Mark</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Grade</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($grades as $grade):
                                $student = $grade->getStudent();
                                $course  = $grade->getCourse();
                                $isPass  = $grade->isPass();
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-medium"><?= htmlspecialchars($student ? $student->getFullName() : 'Unknown') ?></div>
                                    <div class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($student ? $student->studentId : '—') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-mono font-bold text-brand"><?= htmlspecialchars($course ? $course->code : '—') ?></div>
                                    <div class="text-xs text-gray-600"><?= htmlspecialchars($course ? $course->name : '—') ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-center font-mono font-bold text-gray-900">
                                    <?= number_format($grade->mark, 2) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-md font-bold text-xs <?= in_array($grade->letterGrade, ['A', 'B']) ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= htmlspecialchars($grade->letterGrade) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $isPass ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                                        <?php if ($isPass): ?>
                                            <span class="flex h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                        <?php else: ?>
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($grade->status) ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="/results/<?= $grade->id ?>/edit"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-dark bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
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
    </div>

<?php else: ?>
    <!-- ============================================================= -->
    <!--                   ADMINISTRATOR GLOBAL VIEW                   -->
    <!-- ============================================================= -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Academic Results & Records</h1>
            <p class="text-sm text-gray-500 mt-1">Institutional grade book, student performance tracking, and transcripts.</p>
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
            <div class="text-3xl font-bold text-gray-900 mt-2"><?= $stats['total_grades'] ?? 0 ?></div>
            <div class="text-xs text-gray-400 mt-1">Recorded assessments</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Overall Pass Rate</div>
            <div class="text-3xl font-bold <?= ($stats['pass_rate'] ?? 0) >= 50 ? 'text-emerald-600' : 'text-red-600' ?> mt-2">
                <?= $stats['pass_rate'] ?? 0 ?>%
            </div>
            <div class="text-xs text-gray-400 mt-1"><?= $stats['passed_count'] ?? 0 ?> passed / <?= $stats['failed_count'] ?? 0 ?> failed</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Average Mark</div>
            <div class="text-3xl font-bold text-brand mt-2"><?= number_format($stats['average_mark'] ?? 0.0, 2) ?></div>
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
                <select name="student_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand">
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
                <select name="course_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand">
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
                <p class="text-lg font-medium text-gray-600">No grades found.</p>
                <p class="text-sm text-gray-400 mt-1">Record student assessment marks to populate academic results.</p>
                <a href="/results/record" class="mt-4 inline-block text-brand font-medium hover:underline">
                    Record marks now →
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Mark</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Grade</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Lecturer</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($grades as $grade):
                            $student = $grade->getStudent();
                            $course  = $grade->getCourse();
                            $lecturer = $grade->getLecturer();
                            $isPass  = $grade->isPass();
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php if ($student): ?>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($student->getFullName()) ?></div>
                                    <div class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($student->studentId) ?></div>
                                <?php else: ?>
                                    <span class="text-gray-400">Unknown Student #<?= $grade->studentId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php if ($course): ?>
                                    <a href="/courses/<?= $course->id ?>/results" class="font-medium text-brand hover:underline">
                                        <?= htmlspecialchars($course->name) ?>
                                    </a>
                                    <div class="text-xs text-gray-500 font-mono">
                                        <?= htmlspecialchars($course->code) ?> (<?= $course->credits ?> cr)
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">Unknown Course #<?= $grade->courseId ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-mono font-bold text-gray-900">
                                <?= number_format($grade->mark, 2) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-semibold">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-md font-bold text-xs <?= in_array($grade->letterGrade, ['A', 'B']) ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' ?>">
                                    <?= htmlspecialchars($grade->letterGrade) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $isPass ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= htmlspecialchars($grade->status) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?= htmlspecialchars($lecturer ? $lecturer->getFullName() : '—') ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="/results/<?= $grade->id ?>/edit" class="text-brand hover:underline font-medium text-xs">
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
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
