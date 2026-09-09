<?php
/** @var \App\Models\Course[] $courses */
/** @var \App\Models\Department[] $departments */
/** @var \App\Models\Course|null $searchResult */
/** @var string $searchCode */
$isStudent         = $isStudent ?? \App\Auth\Auth::isStudent();
$isLecturer        = $isLecturer ?? \App\Auth\Auth::isLecturer();
$isAdmin           = $isAdmin ?? \App\Auth\Auth::isAdmin();
$enrolledCourseIds = $enrolledCourseIds ?? [];
$assignedCourseIds = $assignedCourseIds ?? [];

$pageTitle = $isStudent ? 'Course Catalog' : ($isLecturer ? 'Course Directory' : 'Courses');
require __DIR__ . '/../layout/header.php';
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <?php if ($isStudent): ?>
            <h1 class="text-2xl font-bold text-gray-900">📚 Course Catalog</h1>
            <p class="text-sm text-gray-500">Explore institutional course offerings and register directly for your semester.</p>
        <?php elseif ($isLecturer): ?>
            <h1 class="text-2xl font-bold text-gray-900">📖 Course Directory</h1>
            <p class="text-sm text-gray-500">View department curriculum, enrolled student rosters, and your assigned teaching modules.</p>
        <?php else: ?>
            <h1 class="text-2xl font-bold text-gray-900">Courses</h1>
            <p class="text-sm text-gray-500">Manage institutional curriculum, department assignments, and credit structures.</p>
        <?php endif; ?>
    </div>

    <div>
        <?php if ($isStudent): ?>
            <a href="/my-courses"
               class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors shadow-sm">
                <span>My Registered Courses</span>
                <span>&rarr;</span>
            </a>
        <?php elseif ($isLecturer): ?>
            <a href="/my-assigned-courses"
               class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shadow-sm">
                <span>My Teaching Assignments</span>
                <span>&rarr;</span>
            </a>
        <?php else: ?>
            <a href="/courses/create"
               class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-dark transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Course
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search by code -->
<form method="GET" action="/courses" class="mb-6 flex gap-2 max-w-md">
    <input type="text" name="code"
           value="<?= htmlspecialchars($searchCode) ?>"
           placeholder="Search by course code (e.g. CS101)…"
           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
    <button type="submit"
            class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
        Search
    </button>
    <?php if ($searchCode): ?>
        <a href="/courses" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
    <?php endif; ?>
</form>

<!-- Search result -->
<?php if ($searchCode !== ''): ?>
    <?php if ($searchResult): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-2">Search result for "<?= htmlspecialchars($searchCode) ?>"</p>
            <div class="flex items-center justify-between">
                <div>
                    <span class="font-mono font-bold text-brand text-base"><?= htmlspecialchars($searchResult->code) ?></span>
                    <span class="text-gray-900 font-semibold ml-2"><?= htmlspecialchars($searchResult->name) ?></span>
                    <span class="text-gray-500 text-xs ml-2">(<?= $searchResult->credits ?> credit hours)</span>
                </div>
                <div>
                    <?php if ($isStudent): ?>
                        <?php if (!empty($enrolledCourseIds[$searchResult->id])): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                ✓ Enrolled
                            </span>
                        <?php else: ?>
                            <a href="/enrollments/create?course_id=<?= $searchResult->id ?>"
                               class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-brand text-white hover:bg-brand-dark transition-colors shadow-sm">
                                + Register Now
                            </a>
                        <?php endif; ?>
                    <?php elseif ($isLecturer): ?>
                        <?php if (!empty($assignedCourseIds[$searchResult->id])): ?>
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 mr-2">Assigned Instructor</span>
                            <a href="/results/record?course_id=<?= $searchResult->id ?>" class="text-indigo-600 hover:underline text-sm font-bold mr-2">Grade Sheet</a>
                        <?php endif; ?>
                        <a href="/courses/<?= $searchResult->id ?>/students" class="text-gray-700 hover:underline text-sm font-medium">View Roster</a>
                    <?php else: ?>
                        <a href="/courses/<?= $searchResult->id ?>/students" class="text-gray-600 hover:underline text-sm font-medium mr-3">Students</a>
                        <a href="/courses/<?= $searchResult->id ?>/edit" class="text-brand hover:underline text-sm font-medium">Edit</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl text-sm">
            No course found with code "<?= htmlspecialchars($searchCode) ?>".
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Full course table -->
<?php if (empty($courses)): ?>
    <div class="text-center py-16 text-gray-400 bg-white rounded-xl border border-gray-200">
        <p class="text-lg">No courses available in the catalog yet.</p>
        <?php if ($isAdmin): ?>
            <a href="/courses/create" class="mt-3 inline-block text-brand hover:underline font-medium">Create the first course →</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Credits</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">
                        <?= $isStudent ? 'Registration' : 'Actions' ?>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                // Index departments by id for quick lookup
                $deptMap = [];
                foreach ($departments as $d) { $deptMap[$d->id] = $d; }
                foreach ($courses as $course):
                    $dept = $course->departmentId ? ($deptMap[$course->departmentId] ?? null) : null;
                    $isEnrolled = !empty($enrolledCourseIds[$course->id]);
                    $isAssigned = !empty($assignedCourseIds[$course->id]);
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?= ($isStudent && $isEnrolled) || ($isLecturer && $isAssigned) ? 'bg-indigo-50/20' : '' ?>">
                    <td class="px-6 py-4 text-sm font-mono font-semibold text-brand"><?= htmlspecialchars($course->code) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        <?= htmlspecialchars($course->name) ?>
                        <?php if ($isLecturer && $isAssigned): ?>
                            <span class="ml-2 inline-flex items-center text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                                My Class
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= $course->credits ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= $dept ? htmlspecialchars($dept->name) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if ($isStudent): ?>
                            <?php if ($isEnrolled): ?>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Enrolled
                                </span>
                            <?php else: ?>
                                <a href="/enrollments/create?course_id=<?= $course->id ?>"
                                   class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-brand hover:bg-brand-dark text-white transition-colors shadow-sm">
                                    + Register
                                </a>
                            <?php endif; ?>
                        <?php elseif ($isLecturer): ?>
                            <?php if ($isAssigned): ?>
                                <a href="/courses/<?= $course->id ?>/students" class="text-gray-700 hover:underline text-sm font-medium mr-3">Roster</a>
                                <a href="/results/record?course_id=<?= $course->id ?>" class="text-indigo-600 hover:underline text-sm font-bold">Grade Sheet</a>
                            <?php else: ?>
                                <a href="/courses/<?= $course->id ?>/students" class="text-gray-500 hover:underline text-sm font-medium">View Roster</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/courses/<?= $course->id ?>/students" class="text-gray-600 hover:underline text-sm font-medium mr-3">Students</a>
                            <a href="/courses/<?= $course->id ?>/edit" class="text-brand hover:underline text-sm font-medium">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
