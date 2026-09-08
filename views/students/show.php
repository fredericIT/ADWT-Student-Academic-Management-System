<?php
$pageTitle = htmlspecialchars($student->getFullName()) . ' — Student Profile';
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <a href="/students" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1">
            &larr; Back to Student Directory
        </a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($student->getFullName()) ?></h1>
        <p class="text-sm text-gray-500 font-mono">Registration ID: <span class="text-brand font-semibold"><?= htmlspecialchars($student->studentId) ?></span></p>
    </div>
    <div class="flex gap-3">
        <a href="/students/<?= $student->id ?>/edit"
           class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Profile
        </a>
        <a href="/enrollments/create?student_id=<?= $student->id ?>"
           class="inline-flex items-center gap-2 bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors">
            + Enroll in Course
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Profile Info Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 text-brand flex items-center justify-center font-bold text-xl mb-4">
                <?= strtoupper(substr($student->firstName, 0, 1) . substr($student->lastName, 0, 1)) ?>
            </div>
            <h2 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($student->getFullName()) ?></h2>
            <p class="text-sm text-gray-500 mb-4"><?= htmlspecialchars($student->email) ?></p>

            <div class="space-y-3 pt-4 border-t border-gray-100 text-sm">
                <div>
                    <span class="text-gray-400 block text-xs font-semibold uppercase">Department</span>
                    <?php if ($department): ?>
                        <span class="font-medium text-gray-900"><?= htmlspecialchars($department->name) ?></span>
                        <span class="text-xs text-gray-500 font-mono block">(<?= htmlspecialchars($department->code) ?>)</span>
                    <?php else: ?>
                        <span class="text-gray-400 italic">No department assigned</span>
                    <?php endif; ?>
                </div>

                <div>
                    <span class="text-gray-400 block text-xs font-semibold uppercase">Registered On</span>
                    <span class="font-medium text-gray-700"><?= htmlspecialchars(date('M d, Y', strtotime($student->createdAt ?? 'now'))) ?></span>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3 pb-2 border-b border-gray-100">
                Residential Address
            </h3>
            <?php if ($address): ?>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400">Province</dt>
                        <dd class="font-medium text-gray-800"><?= htmlspecialchars($address->province ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">District</dt>
                        <dd class="font-medium text-gray-800"><?= htmlspecialchars($address->district ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Sector</dt>
                        <dd class="font-medium text-gray-800"><?= htmlspecialchars($address->sector ?: '-') ?></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Cell</dt>
                        <dd class="font-medium text-gray-800"><?= htmlspecialchars($address->cell ?: '-') ?></dd>
                    </div>
                </dl>
                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                    <span class="font-semibold">Full Address:</span> <?= htmlspecialchars($address->getFullAddress()) ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 italic">No address information recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Course Enrollments Table -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h2 class="text-base font-bold text-gray-900">Enrolled Courses</h2>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    <?= count($enrollments) ?> Courses
                </span>
            </div>

            <?php if (empty($enrollments)): ?>
                <div class="p-8 text-center">
                    <p class="text-sm text-gray-500">This student is not currently enrolled in any courses.</p>
                    <a href="/enrollments/create?student_id=<?= $student->id ?>"
                       class="inline-block mt-3 text-sm font-medium text-brand hover:underline">
                        + Register for a Course
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3">Course Code</th>
                                <th class="px-6 py-3">Course Name</th>
                                <th class="px-6 py-3">Credits</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Enrolled Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php foreach ($enrollments as $e): ?>
                                <?php $c = $e->getCourse(); ?>
                                <tr>
                                    <td class="px-6 py-4 font-mono font-semibold text-gray-900">
                                        <?= $c ? htmlspecialchars($c->code) : 'N/A' ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        <?= $c ? htmlspecialchars($c->name) : 'Course #' . $e->courseId ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?= $c ? htmlspecialchars((string) $c->credits) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($e->isActive()): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                <?= htmlspecialchars(ucfirst($e->status)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-xs">
                                        <?= htmlspecialchars(date('M d, Y', strtotime($e->enrollmentDate))) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
