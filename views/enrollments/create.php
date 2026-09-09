<?php
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Course[] $courses */
/** @var int|null $selectedStudentId */
/** @var int|null $selectedCourseId */
/** @var \App\Models\Student|null $currentStudent */
/** @var int[] $activeCourseIds */
/** @var string[] $errors */

use App\Auth\Auth;

$isStudent = Auth::isStudent();
$pageTitle = $isStudent ? 'Register for Course' : 'Enroll Student in Course';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <!-- Breadcrumb & Title -->
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= $isStudent ? '/my-courses' : '/enrollments' ?>" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
            ← <?= $isStudent ? 'My Courses' : 'Enrollments' ?>
        </a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-bold text-gray-900"><?= $isStudent ? 'Register for Course' : 'Enroll Student' ?></h1>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-xl">
            <ul class="list-disc list-inside text-sm space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Student Registration Form -->
    <form method="POST" action="/enrollments" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 space-y-6">
        <?php if ($isStudent && $currentStudent): ?>
            <!-- Personalized Student Profile Banner -->
            <input type="hidden" name="student_id" value="<?= $currentStudent->id ?>">

            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-xl p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-full bg-brand text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <?= strtoupper(substr($currentStudent->firstName, 0, 1) . substr($currentStudent->lastName, 0, 1)) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-gray-900"><?= htmlspecialchars($currentStudent->getFullName()) ?></h2>
                            <span class="bg-blue-100 text-blue-800 font-mono text-xs px-2 py-0.5 rounded-md font-semibold">
                                <?= htmlspecialchars($currentStudent->studentId) ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5"><?= htmlspecialchars($currentStudent->email) ?></p>
                        <p class="text-xs text-blue-700 font-medium mt-0.5">
                            <?= htmlspecialchars($currentStudent->getDepartment()?->name ?? 'Academic Department') ?>
                        </p>
                    </div>
                </div>
                <div class="text-right hidden sm:block">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Status</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-100 px-2.5 py-1 rounded-full mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active Student
                    </span>
                </div>
            </div>

            <!-- Single Course Selection -->
            <div>
                <label for="course_id" class="block text-sm font-bold text-gray-900 mb-2">
                    Select Course <span class="text-red-500">*</span>
                </label>
                <?php if (empty($courses)): ?>
                    <div class="p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm">
                        No courses available at this time.
                    </div>
                <?php else: ?>
                    <select id="course_id" name="course_id" required autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand text-sm bg-white font-medium shadow-sm transition-all">
                        <option value="">— Select the course you want to register —</option>
                        <?php foreach ($courses as $co): ?>
                            <?php $isAlreadyEnrolled = in_array($co->id, $activeCourseIds ?? [], true); ?>
                            <option value="<?= $co->id ?>"
                                    <?= ($selectedCourseId === $co->id) ? 'selected' : '' ?>
                                    <?= $isAlreadyEnrolled ? 'disabled class="text-gray-400 bg-gray-50"' : '' ?>>
                                <?= htmlspecialchars("{$co->code} — {$co->name} ({$co->credits} credits)") ?>
                                <?= $isAlreadyEnrolled ? ' [Already Registered]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        Select any course you wish to attend this academic term. Registered courses appear instantly on your course schedule.
                    </p>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Administrator View: Select Student & Course -->
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Select Student <span class="text-red-500">*</span>
                </label>
                <?php if (empty($students)): ?>
                    <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-sm">
                        No students found in the system.
                    </div>
                <?php else: ?>
                    <select id="student_id" name="student_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm bg-white">
                        <option value="">— Select a student —</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st->id ?>" <?= ($selectedStudentId === $st->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars("{$st->studentId} — {$st->getFullName()} ({$st->email})") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Select Course <span class="text-red-500">*</span>
                </label>
                <?php if (empty($courses)): ?>
                    <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-sm">
                        No courses available.
                    </div>
                <?php else: ?>
                    <select id="course_id" name="course_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm bg-white">
                        <option value="">— Select a course —</option>
                        <?php foreach ($courses as $co): ?>
                            <option value="<?= $co->id ?>" <?= ($selectedCourseId === $co->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars("{$co->code} — {$co->name} ({$co->credits} credits)") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Registration Notes -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3.5 text-xs text-blue-900 flex items-start gap-2.5">
            <span class="text-base leading-none">💡</span>
            <div class="space-y-0.5 text-blue-800">
                <p class="font-semibold">Quick Note</p>
                <p>You can drop or change your enrolled courses at any time from your course schedule without penalty.</p>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="<?= $isStudent ? '/my-courses' : '/enrollments' ?>"
               class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    <?= (empty($courses) || (!$isStudent && empty($students))) ? 'disabled' : '' ?>
                    class="px-6 py-2.5 text-sm font-semibold text-white bg-brand rounded-xl hover:bg-brand-dark shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <span>Register Course</span>
                <span>→</span>
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
