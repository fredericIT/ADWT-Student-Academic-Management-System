<?php
$pageTitle = 'Update Academic Mark';
$student = $grade->getStudent();
$course  = $grade->getCourse();
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/results" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
            &larr; Back to Results
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">✏️ Update Academic Mark</h1>
        <p class="text-sm text-gray-500">Modify a recorded examination mark for this student.</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm space-y-1">
            <?php foreach ($errors as $error): ?>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Context Card -->
    <div class="bg-gray-100 rounded-xl p-5 mb-6 border border-gray-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Student</span>
            <span class="font-bold text-gray-900"><?= $student ? htmlspecialchars($student->getFullName()) : 'N/A' ?></span>
            <span class="text-xs text-brand font-mono block"><?= $student ? htmlspecialchars($student->studentId) : '' ?></span>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Course</span>
            <span class="font-bold text-gray-900"><?= $course ? htmlspecialchars($course->name) : 'N/A' ?></span>
            <span class="text-xs text-gray-500 font-mono block"><?= $course ? htmlspecialchars($course->code) : '' ?> (<?= $course ? $course->credits : 1 ?> credits)</span>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Current Mark & Grade</span>
            <span class="text-base font-bold <?= $grade->isPass() ? 'text-gray-900' : 'text-red-600' ?>">
                <?= number_format($grade->mark, 2) ?>
            </span>
            <span class="ml-2 px-2 py-0.5 rounded text-xs font-bold bg-white border border-gray-300">
                Grade: <?= htmlspecialchars($grade->letterGrade) ?> (<?= htmlspecialchars($grade->status) ?>)
            </span>
        </div>
        <div>
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Recorded Date</span>
            <span class="text-gray-700"><?= date('M d, Y H:i', strtotime($grade->createdAt ?? 'now')) ?></span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="/results/<?= $grade->id ?>" class="space-y-6">

            <!-- Mark Input -->
            <div>
                <label for="mark" class="block text-sm font-medium text-gray-700 mb-1">
                    New Academic Mark (0.00 – 100.00) <span class="text-red-500">*</span>
                </label>
                <input type="number" step="0.01" min="0" max="100" name="mark" id="mark" required
                       value="<?= isset($_POST['mark']) ? htmlspecialchars((string)$_POST['mark']) : number_format($grade->mark, 2, '.', '') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand focus:border-brand font-mono font-bold text-lg">
                <p class="text-xs text-gray-400 mt-1">Passing standard: marks &ge; 50.00 are awarded PASS status.</p>
            </div>

            <!-- Lecturer Authorization Attribution -->
            <div>
                <label for="lecturer_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Updating Lecturer
                </label>
                <select name="lecturer_id" id="lecturer_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                    <option value="">-- System / Admin --</option>
                    <?php foreach ($lecturers as $lec): ?>
                        <option value="<?= $lec->id ?>" <?= ($grade->lecturerId === $lec->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lec->getFullName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">Authorized lecturer assigned to <?= $course ? htmlspecialchars($course->code) : 'this course' ?>.</p>
            </div>

            <!-- Remarks -->
            <div>
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks / Revision Reason
                </label>
                <textarea name="remarks" id="remarks" rows="3"
                          placeholder="Reason for mark change (e.g. mark recalculation after appeal)."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand focus:border-brand"><?= isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : htmlspecialchars((string)$grade->remarks) ?></textarea>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="/results" class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm transition-colors">
                    Update Mark
                </button>
            </div>

        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
