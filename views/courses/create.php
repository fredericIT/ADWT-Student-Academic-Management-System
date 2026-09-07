<?php
/** @var \App\Models\Course|null $course */
/** @var \App\Models\Department[] $departments */
/** @var \App\Models\Lecturer[] $lecturers */
/** @var string[] $errors */
$isEdit     = $course !== null;
$pageTitle  = $isEdit ? 'Edit Course' : 'New Course';
$formAction = $isEdit ? "/courses/{$course->id}" : '/courses';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/courses" class="text-gray-400 hover:text-gray-600">← Courses</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Edit Course' : 'New Course' ?></h1>
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

    <!-- Core course form -->
    <form method="POST" action="<?= $formAction ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Course Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($course->name ?? ($_POST['name'] ?? '')) ?>"
                       placeholder="e.g. Introduction to Programming"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Course Code <span class="text-red-500">*</span></label>
                <input type="text" id="code" name="code" required maxlength="20"
                       value="<?= htmlspecialchars($course->code ?? ($_POST['code'] ?? '')) ?>"
                       placeholder="e.g. CS101"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm font-mono uppercase"
                       oninput="this.value = this.value.toUpperCase()">
            </div>

            <div>
                <label for="credits" class="block text-sm font-medium text-gray-700 mb-1">Credits <span class="text-red-500">*</span></label>
                <input type="number" id="credits" name="credits" required min="1" max="30"
                       value="<?= htmlspecialchars((string) ($course->credits ?? ($_POST['credits'] ?? '1'))) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
            </div>
        </div>

        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select id="department_id" name="department_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm bg-white">
                <option value="">— None —</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept->id ?>"
                        <?= ($course?->departmentId ?? ($_POST['department_id'] ?? '')) == $dept->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars("{$dept->code} — {$dept->name}") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Optional course description…"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm resize-none"><?= htmlspecialchars($course->description ?? ($_POST['description'] ?? '')) ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/courses"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-dark transition-colors">
                <?= $isEdit ? 'Update Course' : 'Create Course' ?>
            </button>
        </div>
    </form>

    <!-- Lecturer assignment panel (edit mode only) -->
    <?php if ($isEdit): ?>
        <?php $assignedLecturers = $course->getLecturers(); ?>
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Assigned Lecturers</h2>

            <?php if (!empty($assignedLecturers)): ?>
                <ul class="divide-y divide-gray-100 mb-4">
                    <?php foreach ($assignedLecturers as $lec): ?>
                    <li class="flex items-center justify-between py-2">
                        <div>
                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($lec->getFullName()) ?></span>
                            <span class="text-xs text-gray-400 ml-2"><?= htmlspecialchars($lec->email) ?></span>
                        </div>
                        <button type="button"
                                class="btn-remove-lecturer text-red-500 hover:text-red-700 text-xs font-medium"
                                data-course-id="<?= $course->id ?>"
                                data-lecturer-id="<?= $lec->id ?>">
                            Remove
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-sm text-gray-400 mb-4">No lecturers assigned yet.</p>
            <?php endif; ?>

            <!-- Assign new lecturer -->
            <?php
            $assignedIds = array_map(fn($l) => $l->id, $assignedLecturers);
            $available   = array_filter($lecturers, fn($l) => !in_array($l->id, $assignedIds));
            ?>
            <?php if (!empty($available)): ?>
                <div class="flex gap-2">
                    <select id="select-lecturer"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">— Select a lecturer —</option>
                        <?php foreach ($available as $lec): ?>
                            <option value="<?= $lec->id ?>"><?= htmlspecialchars($lec->getFullName()) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-assign-lecturer"
                            data-course-id="<?= $course->id ?>"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                        Assign
                    </button>
                </div>
            <?php else: ?>
                <p class="text-xs text-gray-400 mt-2">All available lecturers are already assigned.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
