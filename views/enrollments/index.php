<?php
/** @var \App\Models\Enrollment[] $enrollments */
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Course[] $courses */
/** @var int|null $selectedStudentId */
/** @var int|null $selectedCourseId */
$pageTitle = 'Course Enrollments';
require __DIR__ . '/../layout/header.php';
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Course Enrollments</h1>
        <p class="text-sm text-gray-500 mt-1">Manage student course registrations and view enrollment statuses.</p>
    </div>
    <a href="/enrollments/create"
       class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Register for Course
    </a>
</div>

<!-- Filter controls -->
<form method="GET" action="/enrollments" class="mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-[200px]">
        <label for="filter_student" class="block text-xs font-semibold text-gray-600 mb-1">Filter by Student</label>
        <select id="filter_student" name="student_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Students</option>
            <?php foreach ($students as $st): ?>
                <option value="<?= $st->id ?>" <?= $selectedStudentId === $st->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($st->studentId . ' — ' . $st->getFullName()) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="flex-1 min-w-[200px]">
        <label for="filter_course" class="block text-xs font-semibold text-gray-600 mb-1">Filter by Course</label>
        <select id="filter_course" name="course_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Courses</option>
            <?php foreach ($courses as $co): ?>
                <option value="<?= $co->id ?>" <?= $selectedCourseId === $co->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($co->code . ' — ' . $co->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors">
            Filter
        </button>
        <?php if ($selectedStudentId !== null || $selectedCourseId !== null): ?>
            <a href="/enrollments" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 flex items-center">
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
        <p class="text-lg font-medium text-gray-600">No enrollments found.</p>
        <p class="text-sm text-gray-400 mt-1">Get started by registering students into courses.</p>
        <a href="/enrollments/create" class="mt-4 inline-block text-brand font-medium hover:underline">
            Enroll a student now →
        </a>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Enrolled Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($enrollments as $enrollment):
                    $student = $enrollment->getStudent();
                    $course  = $enrollment->getCourse();
                    $isActive = $enrollment->isActive();
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php if ($student): ?>
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($student->getFullName()) ?></div>
                            <div class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($student->studentId) ?></div>
                        <?php else: ?>
                            <span class="text-gray-400">Unknown Student (#<?= $enrollment->studentId ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php if ($course): ?>
                            <div class="font-mono font-semibold text-brand"><?= htmlspecialchars($course->code) ?></div>
                            <div class="text-xs text-gray-600"><?= htmlspecialchars($course->name) ?></div>
                        <?php else: ?>
                            <span class="text-gray-400">Unknown Course (#<?= $enrollment->courseId ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= $course ? $course->credits : '—' ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= htmlspecialchars($enrollment->enrollmentDate ? date('M j, Y H:i', strtotime($enrollment->enrollmentDate)) : '—') ?>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <?php if ($isActive): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                Dropped
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <?php if ($isActive): ?>
                            <form method="POST" action="/enrollments/<?= $enrollment->id ?>/drop" class="inline" onsubmit="return confirm('Are you sure you want to drop this course?');">
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors">
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
