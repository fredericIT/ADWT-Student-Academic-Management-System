<?php
/**
 * Global Search and Students-by-Course View.
 *
 * @var string $query
 * @var string $type
 * @var \App\Models\Course[] $courses
 * @var \App\Models\Student[] $students
 * @var \App\Models\Course[] $courseHits
 * @var ?\App\Models\Course $selectedCourse
 * @var \App\Models\Student[] $courseStudents
 */

$pageTitle = 'Global Search — ADWT Academic';
require __DIR__ . '/../layout/header.php';
?>

<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Academic Search & System Query</h1>
    <p class="text-sm text-gray-500 mt-1">
        Search students by registration ID or name, search courses by code, and inspect course rosters.
    </p>
</div>

<!-- Primary Search Box & Filter Tabs -->
<div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
    <form method="GET" action="/search" class="space-y-4">
        <!-- Search Input Bar -->
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text"
                       name="q"
                       value="<?= htmlspecialchars($query) ?>"
                       placeholder="Enter Student ID (e.g. STU2026), Student Name, or Course Code (e.g. CS101)..."
                       class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-brand focus:border-brand shadow-sm">
                <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit"
                    class="bg-brand hover:bg-brand-dark text-white px-6 py-3 rounded-xl text-sm font-semibold shadow-sm transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search System
            </button>
            <?php if ($query !== '' || !empty($_GET['course_id']) || $type !== 'all'): ?>
                <a href="/search"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-xl text-sm font-medium transition-colors flex items-center justify-center">
                    Clear
                </a>
            <?php endif; ?>
        </div>

        <!-- Filter Criteria Pills -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-gray-400 font-medium mr-1">Search Mode:</span>
            <?php
            $filters = [
                'all'                => 'All Categories',
                'student_id'         => 'Student by ID',
                'student_name'       => 'Student by Name',
                'course_code'        => 'Course by Code',
                'students_by_course' => 'Students by Course Code',
            ];
            foreach ($filters as $k => $label):
                $active = ($type === $k);
            ?>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="<?= $k ?>" <?= $active ? 'checked' : '' ?> class="sr-only" onchange="this.form.submit()">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-medium transition-all <?= $active ? 'bg-brand text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                        <?= $label ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<!-- Dedicated Tool: Find Students Registered for a Course -->
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200/80 rounded-2xl p-6 shadow-sm mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Find Students Registered for a Course
            </h2>
            <p class="text-xs text-gray-600 mt-0.5">
                Quickly lookup the active student enrollment roster for any academic course.
            </p>
        </div>

        <form method="GET" action="/search" class="flex items-center gap-2">
            <select name="course_id"
                    class="bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-medium text-gray-800 focus:ring-2 focus:ring-brand focus:border-brand shadow-sm">
                <option value="">— Select a course —</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c->id ?>" <?= ($selectedCourse && $selectedCourse->id === $c->id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c->code) ?> &mdash; <?= htmlspecialchars($c->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                    class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-medium transition-colors shadow-sm">
                View Roster
            </button>
        </form>
    </div>

    <!-- If a course roster was queried via selector or students_by_course search -->
    <?php if ($selectedCourse !== null): ?>
        <div class="mt-4 bg-white rounded-xl border border-blue-200 p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100">
                <div>
                    <span class="text-xs text-gray-400 uppercase font-semibold">Course Roster</span>
                    <h3 class="text-lg font-bold text-gray-900 mt-0.5">
                        <span class="font-mono text-brand"><?= htmlspecialchars($selectedCourse->code) ?></span> &mdash; <?= htmlspecialchars($selectedCourse->name) ?>
                    </h3>
                    <p class="text-xs text-gray-500">
                        Credits: <?= $selectedCourse->credits ?> | Enrolled Students: <strong class="text-brand"><?= count($courseStudents) ?></strong>
                    </p>
                </div>
                <a href="/enrollments/create?course_id=<?= $selectedCourse->id ?>"
                   class="text-xs font-semibold text-brand hover:underline">
                    + Enroll New Student
                </a>
            </div>

            <?php if (empty($courseStudents)): ?>
                <div class="text-center py-6 text-gray-400 text-xs">
                    No active students are currently enrolled in this course.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-2.5">Student ID</th>
                                <th class="px-4 py-2.5">Name</th>
                                <th class="px-4 py-2.5">Email</th>
                                <th class="px-4 py-2.5">Department</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($courseStudents as $st):
                                $dept = $st->getDepartment();
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2.5 font-mono font-bold text-brand">
                                        <?= htmlspecialchars($st->studentId) ?>
                                    </td>
                                    <td class="px-4 py-2.5 font-semibold text-gray-900">
                                        <?= htmlspecialchars($st->getFullName()) ?>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500">
                                        <?= htmlspecialchars($st->email) ?>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-500">
                                        <?= $dept ? htmlspecialchars($dept->name) : '—' ?>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="/students/<?= $st->id ?>" class="text-brand hover:underline font-medium">View Profile</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif (!empty($_GET['course_id'])): ?>
        <div class="mt-4 bg-amber-50 border border-amber-200 text-amber-800 text-xs p-3 rounded-lg">
            Course not found for ID <?= htmlspecialchars((string) $_GET['course_id']) ?>.
        </div>
    <?php endif; ?>
</div>

<!-- Search Results Display Area -->
<?php if ($query !== ''): ?>
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">
            Search Results for "<span class="text-brand font-semibold"><?= htmlspecialchars($query) ?></span>"
        </h2>
        <span class="text-xs text-gray-500">
            Found <?= count($students) ?> student(s), <?= count($courseHits) ?> course(s)
        </span>
    </div>

    <!-- No results at all -->
    <?php if (empty($students) && empty($courseHits) && empty($courseStudents)): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center shadow-sm">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-base font-semibold text-gray-800">No matching records found</h3>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                No students or courses matched your search query. Try searching with a different ID, name, or course code.
            </p>
        </div>
    <?php endif; ?>

    <!-- Student Search Results Table -->
    <?php if (!empty($students)): ?>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <h3 class="font-bold text-gray-900 text-sm">Matching Students (<?= count($students) ?>)</h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/50 text-gray-500 uppercase font-semibold border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Student ID</th>
                            <th class="px-5 py-3">Full Name</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3">Address</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($students as $s):
                            $dept = $s->getDepartment();
                            $addr = $s->getAddress();
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-brand">
                                    <?= htmlspecialchars($s->studentId) ?>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-gray-900">
                                    <?= htmlspecialchars($s->getFullName()) ?>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    <?= htmlspecialchars($s->email) ?>
                                </td>
                                <td class="px-5 py-3.5">
                                    <?php if ($dept): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            <?= htmlspecialchars($dept->code) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate">
                                    <?= $addr ? htmlspecialchars($addr->getFullAddress()) : '—' ?>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-2">
                                    <a href="/students/<?= $s->id ?>" class="font-semibold text-brand hover:underline">
                                        Profile
                                    </a>
                                    <a href="/enrollments?student_id=<?= $s->id ?>" class="text-gray-500 hover:text-gray-800">
                                        Enrollments
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Course Search Results Table -->
    <?php if (!empty($courseHits)): ?>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-8">
            <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <h3 class="font-bold text-gray-900 text-sm">Matching Courses (<?= count($courseHits) ?>)</h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/50 text-gray-500 uppercase font-semibold border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Course Code</th>
                            <th class="px-5 py-3">Course Name</th>
                            <th class="px-5 py-3">Credits</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3">Assigned Lecturers</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($courseHits as $c):
                            $dept      = $c->getDepartment();
                            $lecturers = $c->getLecturers();
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-brand">
                                    <?= htmlspecialchars($c->code) ?>
                                </td>
                                <td class="px-5 py-3.5 font-medium text-gray-900">
                                    <?= htmlspecialchars($c->name) ?>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    <?= $c->credits ?> credits
                                </td>
                                <td class="px-5 py-3.5">
                                    <?= $dept ? htmlspecialchars($dept->name) : '<span class="text-gray-400 italic">None</span>' ?>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600">
                                    <?php if (!empty($lecturers)): ?>
                                        <?= htmlspecialchars(implode(', ', array_map(fn($l) => $l->getFullName(), $lecturers))) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-3">
                                    <a href="/courses/<?= $c->id ?>/students" class="font-semibold text-emerald-700 hover:underline">
                                        View Roster
                                    </a>
                                    <a href="/enrollments/create?course_id=<?= $c->id ?>" class="text-brand hover:underline">
                                        Enroll
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Search Hints & Guide when query is empty -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-brand flex items-center justify-center font-bold text-xs mb-3">
                ID
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-1">Search Student by ID</h3>
            <p class="text-xs text-gray-500 mb-3">
                Type any part of a student registration ID (e.g. "STU", "2026", "CS01") to instantly find student profile and address.
            </p>
            <a href="/search?q=STU&type=student_id" class="text-xs font-semibold text-brand hover:underline">Try "STU" →</a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs mb-3">
                NAME
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-1">Search Student by Name</h3>
            <p class="text-xs text-gray-500 mb-3">
                Search by student first name, surname, or combined full name across all registered university departments.
            </p>
            <a href="/search?type=student_name" class="text-xs font-semibold text-brand hover:underline">Search by Name →</a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs mb-3">
                CODE
            </div>
            <h3 class="font-bold text-gray-900 text-sm mb-1">Search Course & Roster</h3>
            <p class="text-xs text-gray-500 mb-3">
                Search course by unique course code (e.g. "CS101", "MA201") and instantly view all enrolled students.
            </p>
            <a href="/search?type=course_code" class="text-xs font-semibold text-brand hover:underline">Search Course Codes →</a>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
