<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'ADWT Academic Management') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#1d4ed8', dark: '#1e3a8a' }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans text-gray-800">

<!-- Toast container -->
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-2"></div>

<?php
use App\Auth\Auth;

$currentUri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$currentRole = class_exists(Auth::class) ? Auth::getRole() : 'administrator';
$currentUser = class_exists(Auth::class) ? Auth::user() : null;
$displayName = $currentUser ? ($currentUser->getUsername() ?: ucfirst($currentRole)) : ucfirst($currentRole);

$isNavActive = function (string $target, bool $exact = false) use ($currentUri): bool {
    if ($exact) {
        return $currentUri === $target || ($target === '/' && $currentUri === '/dashboard');
    }
    return str_starts_with($currentUri, $target);
};

$navClass = function (string $target, bool $exact = false) use ($isNavActive): string {
    $active = $isNavActive($target, $exact);
    return $active
        ? 'bg-blue-800 text-white font-semibold px-3 py-1.5 rounded-lg text-sm shadow-inner transition-colors'
        : 'text-blue-100 hover:text-white hover:bg-blue-600/40 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors';
};
?>
<!-- Navigation -->
<nav class="bg-brand shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <!-- Left: Brand Logo -->
            <div class="flex items-center gap-6">
                <a href="/" class="text-white text-xl font-bold tracking-tight flex items-center gap-2">
                    <span>🎓</span>
                    <span>SAMS</span>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="/" class="<?= $navClass('/', true) ?>">
                        Dashboard
                    </a>
                    <a href="/search" class="<?= $navClass('/search') ?>">
                        Search
                    </a>

                    <?php if ($currentRole === 'administrator'): ?>
                        <a href="/students" class="<?= $navClass('/students') ?>">
                            Students
                        </a>
                        <a href="/courses" class="<?= $navClass('/courses') ?>">
                            Courses
                        </a>
                        <a href="/enrollments" class="<?= $navClass('/enrollments') ?>">
                            Enrollments
                        </a>
                        <a href="/results" class="<?= $navClass('/results') ?>">
                            Results
                        </a>
                        <a href="/departments" class="<?= $navClass('/departments') ?>">
                            Departments
                        </a>
                        <a href="/lecturers" class="<?= $navClass('/lecturers') ?>">
                            Lecturers
                        </a>
                    <?php elseif ($currentRole === 'lecturer'): ?>
                        <a href="/my-assigned-courses" class="<?= $navClass('/my-assigned-courses') ?>">
                            My Courses
                        </a>
                        <a href="/courses" class="<?= $navClass('/courses') ?>">
                            All Courses
                        </a>
                        <a href="/results/record" class="<?= $navClass('/results/record') ?>">
                            Record Marks
                        </a>
                        <a href="/results" class="<?= $navClass('/results') ?>">
                            Results
                        </a>
                    <?php elseif ($currentRole === 'student'): ?>
                        <a href="/my-courses" class="<?= $navClass('/my-courses') ?>">
                            My Courses
                        </a>
                        <a href="/enrollments/create" class="<?= $navClass('/enrollments/create') ?>">
                            Register Course
                        </a>
                        <a href="/results" class="<?= $navClass('/results') ?>">
                            Academic Results
                        </a>
                        <a href="/profile" class="<?= $navClass('/profile') ?>">
                            My Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Search, Role Switcher, User Badge & Logout -->
            <div class="flex items-center gap-3">
                <!-- Quick Global Search Input -->
                <form action="/search" method="GET" class="relative hidden sm:block">
                    <input type="text"
                           name="q"
                           placeholder="Search system..."
                           class="w-36 lg:w-48 bg-blue-800/80 text-white placeholder-blue-200 text-xs rounded-lg pl-8 pr-3 py-1.5 border border-blue-600 focus:outline-none focus:ring-2 focus:ring-white focus:bg-blue-900 transition-all">
                    <svg class="w-3.5 h-3.5 text-blue-200 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>

                <!-- Role Switcher Pill for Demo -->
                <div class="flex items-center bg-blue-800/60 rounded-lg p-1 border border-blue-500/50 text-xs">
                    <span class="text-blue-200 px-1.5 font-medium hidden lg:inline">Role:</span>
                    <select onchange="window.location.href='?switch_role=' + this.value"
                            class="bg-blue-900 text-white font-medium rounded px-2 py-0.5 text-xs border border-blue-600 focus:outline-none focus:ring-1 focus:ring-white cursor-pointer">
                        <option value="administrator" <?= $currentRole === 'administrator' ? 'selected' : '' ?>>Admin</option>
                        <option value="lecturer" <?= $currentRole === 'lecturer' ? 'selected' : '' ?>>Lecturer</option>
                        <option value="student" <?= $currentRole === 'student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-2 pl-2 border-l border-blue-600/50">
                    <span class="text-white text-xs font-semibold hidden md:inline">
                        <?= htmlspecialchars($displayName) ?>
                    </span>
                    <a href="/logout"
                       title="Sign Out"
                       class="text-blue-200 hover:text-white bg-blue-800/80 hover:bg-red-600 px-2.5 py-1 rounded-lg text-xs font-medium transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Sub-row -->
        <div class="flex md:hidden overflow-x-auto py-2 border-t border-blue-600/40 gap-1 text-xs">
            <a href="/" class="<?= $navClass('/', true) ?>">Dashboard</a>
            <a href="/search" class="<?= $navClass('/search') ?>">Search</a>
            <?php if ($currentRole === 'administrator'): ?>
                <a href="/students" class="<?= $navClass('/students') ?>">Students</a>
                <a href="/courses" class="<?= $navClass('/courses') ?>">Courses</a>
                <a href="/enrollments" class="<?= $navClass('/enrollments') ?>">Enrollments</a>
                <a href="/results" class="<?= $navClass('/results') ?>">Results</a>
                <a href="/departments" class="<?= $navClass('/departments') ?>">Departments</a>
                <a href="/lecturers" class="<?= $navClass('/lecturers') ?>">Lecturers</a>
            <?php elseif ($currentRole === 'lecturer'): ?>
                <a href="/my-assigned-courses" class="<?= $navClass('/my-assigned-courses') ?>">My Courses</a>
                <a href="/results/record" class="<?= $navClass('/results/record') ?>">Record Marks</a>
                <a href="/results" class="<?= $navClass('/results') ?>">Results</a>
            <?php elseif ($currentRole === 'student'): ?>
                <a href="/my-courses" class="<?= $navClass('/my-courses') ?>">My Courses</a>
                <a href="/enrollments/create" class="<?= $navClass('/enrollments/create') ?>">Register</a>
                <a href="/results" class="<?= $navClass('/results') ?>">Results</a>
                <a href="/profile" class="<?= $navClass('/profile') ?>">Profile</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Page content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<?php
// Display success flash message from redirect query param
$successMessages = [
    'created'        => 'Record created successfully.',
    'updated'        => 'Record updated successfully.',
    'registered'     => 'Record registered successfully.',
    'enrolled'       => 'Student enrolled in course successfully.',
    'dropped'        => 'Course dropped successfully.',
    'grade_recorded' => 'Academic mark recorded successfully.',
    'grade_updated'  => 'Academic mark updated successfully.',
    'login'          => 'Welcome back! You have signed in successfully.',
];
$successKey = $_GET['success'] ?? '';
if (isset($successMessages[$successKey])): ?>
    <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <?= htmlspecialchars($successMessages[$successKey]) ?>
    </div>
<?php endif; ?>

<?php
$errorMessage = $_GET['error'] ?? '';
if ($errorMessage !== ''): ?>
    <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>
