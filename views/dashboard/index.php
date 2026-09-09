<?php
/**
 * Main Academic Management Dashboard View.
 *
 * @var array{
 *     total_students: int,
 *     total_courses: int,
 *     total_departments: int,
 *     total_lecturers: int,
 *     total_enrollments: int,
 *     active_enrollments: int,
 *     dropped_enrollments: int
 * } $stats
 * @var \App\Models\Student[] $recentStudents
 * @var array<array{enrollment: \App\Models\Enrollment, student: ?\App\Models\Student, course: ?\App\Models\Course}> $recentEnrollments
 * @var array<array{department: \App\Models\Department, course_count: int, lecturer_count: int}> $departmentBreakdown
 * @var array<array{course: \App\Models\Course, department: ?\App\Models\Department, enrolled_count: int}> $courseSummary
 */

use App\Auth\Auth;

$pageTitle = 'Dashboard — ADWT Academic';
require __DIR__ . '/../layout/header.php';
$role = Auth::getRole();
?>

<!-- Header Banner -->
<div class="bg-gradient-to-r from-blue-700 via-brand to-indigo-800 rounded-2xl p-6 sm:p-8 text-white shadow-lg mb-8 relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-semibold backdrop-blur-sm mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Role: <?= htmlspecialchars(ucfirst($role)) ?> Mode</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Academic Management Dashboard</h1>
            <p class="text-blue-100 text-sm mt-1 max-w-xl">
                Integrated system overview for academic departments, course catalog, student profiles, and course registrations.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="/search"
               class="inline-flex items-center gap-2 bg-white text-brand hover:bg-blue-50 px-4 py-2.5 rounded-xl font-semibold shadow transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Global Search
            </a>
            <?php if (Auth::can('enroll_course')): ?>
                <a href="/enrollments/create"
                   class="inline-flex items-center gap-2 bg-blue-900/60 hover:bg-blue-900 text-white px-4 py-2.5 rounded-xl font-medium border border-blue-400/30 transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Enroll Student
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <!-- Students Card -->
    <a href="/students" class="group bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-blue-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Students</span>
            <span class="p-2 rounded-lg bg-blue-50 text-brand group-hover:bg-brand group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold text-gray-900"><?= $stats['total_students'] ?></span>
            <p class="text-xs text-gray-500 mt-1">Total registered students</p>
        </div>
    </a>

    <!-- Courses Card -->
    <a href="/courses" class="group bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Courses</span>
            <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold text-gray-900"><?= $stats['total_courses'] ?></span>
            <p class="text-xs text-gray-500 mt-1">Active courses offered</p>
        </div>
    </a>

    <!-- Enrollments Card -->
    <a href="/enrollments" class="group bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-purple-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Enrollments</span>
            <span class="p-2 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold text-gray-900"><?= $stats['active_enrollments'] ?></span>
            <span class="text-xs text-gray-400 font-normal"> / <?= $stats['total_enrollments'] ?> total</span>
            <p class="text-xs text-gray-500 mt-1"><?= $stats['dropped_enrollments'] ?> dropped courses</p>
        </div>
    </a>

    <!-- Departments Card -->
    <a href="/departments" class="group bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-amber-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Departments</span>
            <span class="p-2 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold text-gray-900"><?= $stats['total_departments'] ?></span>
            <p class="text-xs text-gray-500 mt-1">Academic faculties</p>
        </div>
    </a>

    <!-- Lecturers Card -->
    <a href="/lecturers" class="group bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:border-rose-400 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lecturers</span>
            <span class="p-2 rounded-lg bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
        </div>
        <div class="mt-3">
            <span class="text-2xl font-bold text-gray-900"><?= $stats['total_lecturers'] ?></span>
            <p class="text-xs text-gray-500 mt-1">Teaching faculty staff</p>
        </div>
    </a>
</div>

<!-- Quick Actions Section -->
<div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm mb-8">
    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Quick Module Actions
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <?php if (Auth::can('manage_students')): ?>
            <a href="/students/create"
               class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-brand hover:bg-blue-50/50 transition-all text-center group">
                <span class="w-9 h-9 rounded-lg bg-blue-100 text-brand flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-800">Register Student</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::can('manage_courses')): ?>
            <a href="/courses/create"
               class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all text-center group">
                <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-800">New Course</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::can('enroll_course')): ?>
            <a href="/enrollments/create"
               class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-purple-500 hover:bg-purple-50/50 transition-all text-center group">
                <span class="w-9 h-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-800">Course Enrollment</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::can('manage_departments')): ?>
            <a href="/departments/create"
               class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-amber-500 hover:bg-amber-50/50 transition-all text-center group">
                <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-800">Add Department</span>
            </a>
        <?php endif; ?>

        <?php if (Auth::can('manage_lecturers')): ?>
            <a href="/lecturers/create"
               class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-rose-500 hover:bg-rose-50/50 transition-all text-center group">
                <span class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-800">Register Lecturer</span>
            </a>
        <?php endif; ?>

        <a href="/search"
           class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-200 hover:border-blue-500 hover:bg-blue-50/50 transition-all text-center group">
            <span class="w-9 h-9 rounded-lg bg-blue-100 text-brand flex items-center justify-center group-hover:scale-110 transition-transform mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <span class="text-xs font-semibold text-gray-800">Global Search</span>
        </a>
    </div>
</div>

<!-- 2-Column Feed: Recent Students & Recent Enrollments -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Recent Students Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Recent Student Registrations</h3>
                <p class="text-xs text-gray-500">Newly added student profiles</p>
            </div>
            <a href="/students" class="text-xs font-semibold text-brand hover:underline">View All →</a>
        </div>

        <div class="overflow-x-auto flex-1">
            <?php if (empty($recentStudents)): ?>
                <div class="p-8 text-center text-gray-400 text-xs">No students registered yet.</div>
            <?php else: ?>
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                        <tr>
                            <th class="px-5 py-3">Student ID</th>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentStudents as $st):
                            $dept = $st->getDepartment();
                        ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-3 font-mono font-semibold text-brand">
                                    <?= htmlspecialchars($st->studentId) ?>
                                </td>
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    <?= htmlspecialchars($st->getFullName()) ?>
                                </td>
                                <td class="px-5 py-3 text-gray-500">
                                    <?= $dept ? htmlspecialchars($dept->code) : '—' ?>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="/students/<?= $st->id ?>" class="text-brand hover:underline font-medium">Profile</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Enrollments Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Recent Course Enrollments</h3>
                <p class="text-xs text-gray-500">Latest course registrations and status</p>
            </div>
            <a href="/enrollments" class="text-xs font-semibold text-brand hover:underline">View All →</a>
        </div>

        <div class="overflow-x-auto flex-1">
            <?php if (empty($recentEnrollments)): ?>
                <div class="p-8 text-center text-gray-400 text-xs">No course enrollments recorded yet.</div>
            <?php else: ?>
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                        <tr>
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3">Course</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentEnrollments as $item):
                            $enrollment = $item['enrollment'];
                            $st         = $item['student'];
                            $cr         = $item['course'];
                        ?>
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    <?= $st ? htmlspecialchars($st->getFullName()) : '<span class="text-gray-400">Unknown</span>' ?>
                                </td>
                                <td class="px-5 py-3 text-gray-600 font-mono">
                                    <?= $cr ? htmlspecialchars($cr->code) : '—' ?>
                                </td>
                                <td class="px-5 py-3">
                                    <?php if ($enrollment->isActive()): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-800 border border-green-200">
                                            <span class="flex h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                                            <span>Active</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            <span class="h-2 w-2 rounded-full bg-gray-400"></span>
                                            <span>Dropped</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-400">
                                    <?= substr($enrollment->enrollmentDate ?? '', 0, 10) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Department Overview & Course Roster Cards -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Departments Breakdown -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Department Overview</h3>
                <p class="text-xs text-gray-500">Academic units and resource allocations</p>
            </div>
            <a href="/departments" class="text-xs font-semibold text-brand hover:underline">All Departments →</a>
        </div>
        <div class="space-y-3">
            <?php if (empty($departmentBreakdown)): ?>
                <p class="text-gray-400 text-xs py-4 text-center">No departments found.</p>
            <?php else: ?>
                <?php foreach ($departmentBreakdown as $dItem):
                    $dept = $dItem['department'];
                ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50/40 transition-colors">
                        <div>
                            <span class="font-bold text-gray-900 text-xs"><?= htmlspecialchars($dept->name) ?></span>
                            <span class="ml-1.5 px-1.5 py-0.5 rounded bg-blue-100 text-brand font-mono text-[10px] font-bold"><?= htmlspecialchars($dept->code) ?></span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span><strong class="text-gray-800"><?= $dItem['course_count'] ?></strong> Courses</span>
                            <span><strong class="text-gray-800"><?= $dItem['lecturer_count'] ?></strong> Lecturers</span>
                            <a href="/departments/<?= $dept->id ?>/courses" class="text-brand hover:underline text-xs font-medium">Courses</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Course Enrollment Density -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Active Course Enrollments</h3>
                <p class="text-xs text-gray-500">Courses with student registration rosters</p>
            </div>
            <a href="/courses" class="text-xs font-semibold text-brand hover:underline">All Courses →</a>
        </div>
        <div class="space-y-3">
            <?php if (empty($courseSummary)): ?>
                <p class="text-gray-400 text-xs py-4 text-center">No courses found.</p>
            <?php else: ?>
                <?php foreach ($courseSummary as $cItem):
                    $course = $cItem['course'];
                    $dept   = $cItem['department'];
                ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-emerald-50/30 transition-colors">
                        <div>
                            <span class="font-mono font-bold text-brand text-xs"><?= htmlspecialchars($course->code) ?></span>
                            <span class="ml-1 text-gray-800 text-xs font-medium"><?= htmlspecialchars($course->name) ?></span>
                            <span class="text-gray-400 text-[10px] ml-1">(<?= $course->credits ?> credits)</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1 text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">
                                <strong class="text-gray-900"><?= $cItem['enrolled_count'] ?></strong> students
                            </span>
                            <a href="/courses/<?= $course->id ?>/students"
                               class="text-brand hover:underline font-semibold text-xs">
                                Roster →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
