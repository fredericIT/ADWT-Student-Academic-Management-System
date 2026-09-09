<?php
/** @var \App\Models\Enrollment[] $enrollments */
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Course[] $courses */
/** @var int|null $selectedStudentId */
/** @var int|null $selectedCourseId */

use App\Auth\Auth;

$isStudent = Auth::isStudent();
$pageTitle = $isStudent ? 'My Registered Courses' : 'Course Enrollments';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?= $isStudent ? 'My Registered Courses' : 'Course Enrollments' ?></h1>
        <p class="text-sm text-gray-500 mt-1">
            <?= $isStudent
                ? 'View your active registered courses, earned credits, and register for new offerings.'
                : 'Manage student course registrations and view enrollment statuses.' ?>
        </p>
    </div>
    <a href="/enrollments/create"
       class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-dark shadow-sm transition-all self-start sm:self-auto">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <?= $isStudent ? 'Register for Course' : 'Enroll Student' ?>
    </a>
</div>

<!-- Filter controls -->
<form method="GET" action="<?= $isStudent ? '/my-courses' : '/enrollments' ?>" class="mb-6 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex flex-wrap gap-4 items-end">
    <?php if (!$isStudent): ?>
        <div class="flex-1 min-w-[200px]">
            <label for="filter_student" class="block text-sm font-semibold text-gray-700 mb-2">Filter by Student</label>
            <select id="filter_student" name="student_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white font-medium">
                <option value="">All Students</option>
                <?php foreach ($students as $st): ?>
                    <option value="<?= $st->id ?>" <?= $selectedStudentId === $st->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($st->studentId . ' — ' . $st->getFullName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="flex-1 min-w-[200px]">
        <label for="filter_course" class="block text-sm font-semibold text-gray-700 mb-2">Filter by Course</label>
        <select id="filter_course" name="course_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand bg-white font-medium">
            <option value="">All Courses</option>
            <?php foreach ($courses as $co): ?>
                <option value="<?= $co->id ?>" <?= $selectedCourseId === $co->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($co->code . ' — ' . $co->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="px-5 py-2.5 bg-brand hover:bg-brand-dark text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
            Filter
        </button>
        <?php if ($selectedStudentId !== null || $selectedCourseId !== null): ?>
            <a href="<?= $isStudent ? '/my-courses' : '/enrollments' ?>" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center">
                Clear
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Enrollments Table -->
<?php if (empty($enrollments)): ?>
    <div class="text-center py-16 bg-white rounded-xl border border-gray-200 text-gray-400">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <p class="text-lg font-medium text-gray-600">
            <?= $isStudent ? 'You are not enrolled in any courses yet.' : 'No enrollments found.' ?>
        </p>
        <p class="text-sm text-gray-400 mt-1">
            <?= $isStudent ? 'Select and register for courses to build your semester schedule.' : 'Get started by registering students into courses.' ?>
        </p>
        <a href="/enrollments/create" class="mt-4 inline-flex items-center gap-1.5 text-brand font-semibold hover:underline text-sm">
            <span><?= $isStudent ? 'Register for your first course now' : 'Enroll a student now' ?></span>
            <span>→</span>
        </a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-2xl shadow-sm border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-brand/5 to-brand/3 border-b-2 border-brand">
                <tr>
                    <?php if (!$isStudent): ?>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Student</th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Course</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Credits</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Enrolled Date</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-900 uppercase tracking-wide">Status</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-900 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($enrollments as $enrollment):
                    $student = $enrollment->getStudent();
                    $course  = $enrollment->getCourse();
                    $isActive = $enrollment->isActive();
                ?>
                <tr class="hover:bg-brand/3 transition-colors duration-150">
                    <?php if (!$isStudent): ?>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($student): ?>
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($student->getFullName()) ?></div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5"><?= htmlspecialchars($student->studentId) ?></div>
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">Unknown Student (#<?= $enrollment->studentId ?>)</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td class="px-6 py-4">
                        <?php if ($course): ?>
                            <div class="font-mono font-bold text-base text-brand"><?= htmlspecialchars($course->code) ?></div>
                            <div class="text-sm text-gray-700 mt-0.5 font-medium"><?= htmlspecialchars($course->name) ?></div>
                        <?php else: ?>
                            <span class="text-gray-400 text-sm">Unknown Course (#<?= $enrollment->courseId ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                        <?= $course ? $course->credits : '—' ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <?= htmlspecialchars($enrollment->enrollmentDate ? date('M j, Y H:i', strtotime($enrollment->enrollmentDate)) : '—') ?>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <?php if ($isActive): ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full font-semibold text-green-800 bg-green-50 border border-green-200">
                                <span class="flex h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                <span>Active</span>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full font-semibold text-gray-700 bg-gray-100 border border-gray-200">
                                <span class="h-2.5 w-2.5 rounded-full bg-gray-400"></span>
                                <span>Dropped</span>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <?php if ($isActive): ?>
                            <form method="POST" action="/enrollments/<?= $enrollment->id ?>/drop" class="inline" onsubmit="return confirm('Are you sure you want to drop this course?');">
                                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm bg-red-50 hover:bg-red-100 px-3.5 py-1.5 rounded-lg transition-all border border-red-200 hover:border-red-300">
                                    Drop Course
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-xs text-gray-400 italic">No action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
