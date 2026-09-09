<?php
$pageTitle = 'Record Academic Marks';
require __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/results" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
            &larr; Back to Results
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">📝 Record Student Marks</h1>
        <p class="text-sm text-gray-500">Record course examination or coursework marks for an enrolled student.</p>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <form method="POST" action="/results/record" class="space-y-6">

            <!-- 1. Select Course -->
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Course <span class="text-red-500">*</span>
                </label>
                <select name="course_id" id="course_id" required
                        onchange="if(this.value) { window.location.href = '/results/record?course_id=' + this.value + '<?= $selectedLecturerId ? '&lecturer_id=' . $selectedLecturerId : '' ?>'; }"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                    <option value="">-- Choose a course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c->id ?>" <?= $selectedCourseId === $c->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->code) ?> — <?= htmlspecialchars($c->name) ?> (<?= $c->credits ?> credits)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">Selecting a course filters the student list to active enrollments.</p>
            </div>

            <!-- 2. Select Student -->
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Student <span class="text-red-500">*</span>
                </label>
                <?php if ($selectedCourseId === null): ?>
                    <select disabled class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2.5 text-sm text-gray-400">
                        <option>← Select a course first to view enrolled students</option>
                    </select>
                <?php elseif (empty($enrolledStudents)): ?>
                    <div class="border border-amber-200 bg-amber-50 rounded-lg p-3 text-xs text-amber-800">
                        No active students are currently enrolled in this course.
                        <a href="/enrollments/create?course_id=<?= $selectedCourseId ?>" class="font-bold underline ml-1">Enroll students now →</a>
                    </div>
                <?php else: ?>
                    <select name="student_id" id="student_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                        <option value="">-- Choose an enrolled student --</option>
                        <?php foreach ($enrolledStudents as $st): ?>
                            <option value="<?= $st->id ?>" <?= $selectedStudentId === $st->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st->studentId) ?> — <?= htmlspecialchars($st->getFullName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <!-- 3. Mark Input -->
            <div>
                <label for="mark" class="block text-sm font-medium text-gray-700 mb-1">
                    Academic Mark (0.00 – 100.00) <span class="text-red-500">*</span>
                </label>
                <div class="relative rounded-lg">
                    <input type="number" step="0.01" min="0" max="100" name="mark" id="mark" required
                           value="<?= isset($_POST['mark']) ? htmlspecialchars((string)$_POST['mark']) : '' ?>"
                           placeholder="e.g. 78.50"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand focus:border-brand font-mono font-semibold">
                </div>
                <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                    <span>Range: 0.00 to 100.00</span>
                    <span>Pass Mark: <strong>50.00</strong></span>
                </div>
            </div>

            <!-- 4. Recording Lecturer (Authorization) -->
            <?php if (!empty($isLecturer) && !empty($currentLecturer)): ?>
                <input type="hidden" name="lecturer_id" value="<?= $currentLecturer->id ?>">
                <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                    <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">Grading Faculty Member</div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            <?= strtoupper(substr($currentLecturer->firstName, 0, 1) . substr($currentLecturer->lastName, 0, 1)) ?>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($currentLecturer->getFullName()) ?></div>
                            <div class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($currentLecturer->staffNumber ?? $currentLecturer->email) ?> &bull; Authorized Instructor</div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div>
                    <label for="lecturer_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Recording Lecturer
                    </label>
                    <select name="lecturer_id" id="lecturer_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-brand focus:border-brand">
                        <option value="">-- System / Admin --</option>
                        <?php foreach ($lecturers as $lec): ?>
                            <option value="<?= $lec->id ?>" <?= $selectedLecturerId === $lec->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lec->getFullName()) ?> (<?= htmlspecialchars($lec->email) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">If specified, the lecturer must be assigned to this course.</p>
                </div>
            <?php endif; ?>

            <!-- 5. Remarks -->
            <div>
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks / Comments (Optional)
                </label>
                <textarea name="remarks" id="remarks" rows="2"
                          placeholder="e.g. Excellent mid-term examination and coursework submission."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand focus:border-brand"><?= isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : '' ?></textarea>
            </div>

            <!-- Grading Reference Legend -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider block mb-2">Grading Scale Reference</span>
                <div class="grid grid-cols-5 gap-2 text-center text-xs">
                    <div class="bg-white p-1.5 rounded border border-gray-200"><span class="font-bold text-emerald-700">A</span><br><span class="text-gray-500">80–100</span></div>
                    <div class="bg-white p-1.5 rounded border border-gray-200"><span class="font-bold text-blue-700">B</span><br><span class="text-gray-500">70–79</span></div>
                    <div class="bg-white p-1.5 rounded border border-gray-200"><span class="font-bold text-yellow-700">C</span><br><span class="text-gray-500">60–69</span></div>
                    <div class="bg-white p-1.5 rounded border border-gray-200"><span class="font-bold text-orange-700">D</span><br><span class="text-gray-500">50–59</span></div>
                    <div class="bg-white p-1.5 rounded border border-red-200 bg-red-50"><span class="font-bold text-red-700">F</span><br><span class="text-gray-500">&lt; 50 (Fail)</span></div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="/results" class="text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm transition-colors">
                    Save Academic Mark
                </button>
            </div>

        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
