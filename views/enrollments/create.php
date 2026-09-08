<?php
/** @var \App\Models\Student[] $students */
/** @var \App\Models\Course[] $courses */
/** @var int|null $selectedStudentId */
/** @var int|null $selectedCourseId */
/** @var string[] $errors */
$pageTitle = 'Register for Course';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/enrollments" class="text-gray-400 hover:text-gray-600">← Enrollments</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-bold text-gray-900">Register Course</h1>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside text-sm space-y-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="/enrollments" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        <div>
            <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                Select Student <span class="text-red-500">*</span>
            </label>
            <?php if (empty($students)): ?>
                <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-sm">
                    No students found. Please ensure students are registered in the system.
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
                    No courses available for registration. <a href="/courses/create" class="underline text-brand">Create a course</a> first.
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

        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-800">
            <p class="font-semibold mb-1">ℹ️ Registration Rules</p>
            <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                <li>Students cannot be actively enrolled in the same course more than once.</li>
                <li>Dropping a course changes its status to <em>Dropped</em> and preserves records.</li>
            </ul>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/enrollments"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    <?= (empty($students) || empty($courses)) ? 'disabled' : '' ?>
                    class="px-5 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Complete Registration
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
