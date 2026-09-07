<?php
/** @var \App\Models\Department|null $department */
/** @var string[] $errors */
$isEdit    = $department !== null;
$pageTitle = $isEdit ? 'Edit Department' : 'New Department';
$formAction = $isEdit ? "/departments/{$department->id}" : '/departments';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="/departments" class="text-gray-400 hover:text-gray-600">← Departments</a>
        <span class="text-gray-300">/</span>
        <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Edit Department' : 'New Department' ?></h1>
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

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                   value="<?= htmlspecialchars($department->name ?? ($_POST['name'] ?? '')) ?>"
                   placeholder="e.g. Computer Science"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-sm">
        </div>

        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
            <input type="text" id="code" name="code" required maxlength="20"
                   value="<?= htmlspecialchars($department->code ?? ($_POST['code'] ?? '')) ?>"
                   placeholder="e.g. CS"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-sm font-mono uppercase"
                   oninput="this.value = this.value.toUpperCase()">
            <p class="mt-1 text-xs text-gray-400">2–20 characters, letters/digits/hyphens only.</p>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Optional description..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-sm resize-none"><?= htmlspecialchars($department->description ?? ($_POST['description'] ?? '')) ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="/departments"
               class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-brand rounded-lg hover:bg-brand-dark transition-colors">
                <?= $isEdit ? 'Update Department' : 'Create Department' ?>
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
