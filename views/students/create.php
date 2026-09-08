<?php
$pageTitle = 'Register Student — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6">
    <a href="/students" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
        &larr; Back to Student Directory
    </a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Register New Student</h1>
    <p class="text-sm text-gray-500">Add a student to the academic system with personal, departmental, and address information.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm space-y-1">
        <div class="font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            Please fix the following validation issues:
        </div>
        <ul class="list-disc list-inside pl-2">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-3xl">
    <form method="POST" action="/students" class="space-y-6">

        <!-- Section: Academic Details -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">1. Student Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student Registration ID <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="student_id"
                           value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
                           placeholder="e.g. STU2026001"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                    <p class="text-xs text-gray-400 mt-1">Must be unique across the system.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept->id ?>" <?= (($_POST['department_id'] ?? '') == $dept->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept->name) ?> (<?= htmlspecialchars($dept->code) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="first_name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                           placeholder="e.g. Jean"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text"
                           name="last_name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                           placeholder="e.g. Mugisha"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email"
                           name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="e.g. jean.mugisha@student.ac.rw"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
            </div>
        </div>

        <!-- Section: Address Details -->
        <div>
            <h2 class="text-lg font-semibold text-gray-900 pb-2 border-b border-gray-200">2. Residential Address Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                    <input type="text"
                           name="province"
                           value="<?= htmlspecialchars($_POST['province'] ?? '') ?>"
                           placeholder="e.g. Kigali City"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                    <input type="text"
                           name="district"
                           value="<?= htmlspecialchars($_POST['district'] ?? '') ?>"
                           placeholder="e.g. Nyarugenge"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
                    <input type="text"
                           name="sector"
                           value="<?= htmlspecialchars($_POST['sector'] ?? '') ?>"
                           placeholder="e.g. Nyamirambo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cell</label>
                    <input type="text"
                           name="cell"
                           value="<?= htmlspecialchars($_POST['cell'] ?? '') ?>"
                           placeholder="e.g. Rwezamenyo"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="/students"
               class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white bg-brand hover:bg-brand-dark rounded-lg shadow-sm transition-colors">
                Register Student
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
