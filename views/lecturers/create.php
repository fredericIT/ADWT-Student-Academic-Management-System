<?php
/** @var \App\Models\Lecturer|null $lecturer */
/** @var \App\Models\Department[] $departments */
/** @var string[] $errors */
$isEdit     = $lecturer !== null;
$pageTitle  = $isEdit ? 'Edit Lecturer' : 'Register Lecturer';
$formAction = $isEdit ? "/lecturers/{$lecturer->id}" : '/lecturers';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/lecturers" class="text-gray-400 hover:text-gray-600">← Lecturers</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Edit Lecturer' : 'Register Lecturer' ?></h1>
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

    <form method="POST" action="<?= $formAction ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                <input type="text" id="first_name" name="first_name" required
                       value="<?= htmlspecialchars($lecturer->firstName ?? ($_POST['first_name'] ?? '')) ?>"
                       placeholder="e.g. Jane"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                <input type="text" id="last_name" name="last_name" required
                       value="<?= htmlspecialchars($lecturer->lastName ?? ($_POST['last_name'] ?? '')) ?>"
                       placeholder="e.g. Smith"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
            <input type="email" id="email" name="email" required
                   value="<?= htmlspecialchars($lecturer->email ?? ($_POST['email'] ?? '')) ?>"
                   placeholder="jane.smith@university.ac"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm">
        </div>

        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select id="department_id" name="department_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand text-sm bg-white">
                <option value="">— None —</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept->id ?>"
                        <?= ($lecturer?->departmentId ?? ($_POST['department_id'] ?? '')) == $dept->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars("{$dept->code} — {$dept->name}") ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/lecturers"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-dark transition-colors">
                <?= $isEdit ? 'Update Lecturer' : 'Register Lecturer' ?>
            </button>
        </div>
    </form>

    <!-- AJAX department association widget (edit mode only) -->
    <?php if ($isEdit): ?>
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-3">Quick Department Association</h2>
            <p class="text-xs text-gray-400 mb-3">
                Change the department association without reloading the full form.
            </p>
            <div class="flex gap-2">
                <select id="select-department"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="">— None (disassociate) —</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept->id ?>"
                            <?= $lecturer->departmentId == $dept->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars("{$dept->code} — {$dept->name}") ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="btn-associate-dept"
                        data-lecturer-id="<?= $lecturer->id ?>"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                    Associate
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
