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
$homeUrl = match ($currentRole) {
    'administrator' => '/students',
    'lecturer'      => '/courses',
    'student'       => '/my-courses',
    default         => '/login',
};
?>
<!-- Navigation -->
<nav class="bg-brand shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <!-- Left: Brand Logo -->
            <div class="flex items-center gap-6">
                <a href="<?= $homeUrl ?>" class="text-white text-xl font-bold tracking-tight flex items-center gap-2">
                    <span>🎓</span>
                    <span>SAMS</span>
                </a>

                <!-- Desktop Nav Links (Streamlined: Admin=3, Lecturer=2, Student=3) -->
                <div class="hidden md:flex items-center gap-1">
                    <?php if ($currentRole === 'administrator'): ?>
                        <a href="/students" class="<?= $navClass('/students') ?>">
                            👨‍🎓 Students
                        </a>
                        <a href="/lecturers" class="<?= $navClass('/lecturers') ?>">
                            👨‍🏫 Lecturers
                        </a>
                        <a href="/departments" class="<?= $navClass('/departments') ?>">
                            🏛️ Departments
                        </a>
                    <?php elseif ($currentRole === 'lecturer'): ?>
                        <a href="/courses" class="<?= $navClass('/courses') ?>">
                            📖 Manage Courses
                        </a>
                        <a href="/results" class="<?= $navClass('/results') ?>">
                            📊 Student Grades
                        </a>
                    <?php elseif ($currentRole === 'student'): ?>
                        <a href="/my-courses" class="<?= $navClass('/my-courses') ?>">
                            📚 Course Registration
                        </a>
                        <a href="/results" class="<?= $navClass('/results') ?>">
                            📊 Academic Results
                        </a>
                        <a href="/profile" class="<?= $navClass('/profile') ?>">
                            👤 My Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: User Badge & Logout -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-white text-xs font-semibold hidden md:inline">
                        <?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars(ucfirst($currentRole)) ?>)
                    </span>
                    <a href="/logout"
                       title="Sign Out"
                       class="text-blue-200 hover:text-white bg-blue-800/80 hover:bg-red-600 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Sub-row -->
        <div class="flex md:hidden overflow-x-auto py-2 border-t border-blue-600/40 gap-1 text-xs">
            <?php if ($currentRole === 'administrator'): ?>
                <a href="/students" class="<?= $navClass('/students') ?>">👨‍🎓 Students</a>
                <a href="/lecturers" class="<?= $navClass('/lecturers') ?>">👨‍🏫 Lecturers</a>
                <a href="/departments" class="<?= $navClass('/departments') ?>">🏛️ Departments</a>
            <?php elseif ($currentRole === 'lecturer'): ?>
                <a href="/courses" class="<?= $navClass('/courses') ?>">📖 Courses</a>
                <a href="/results" class="<?= $navClass('/results') ?>">📊 Student Grades</a>
            <?php elseif ($currentRole === 'student'): ?>
                <a href="/my-courses" class="<?= $navClass('/my-courses') ?>">📚 Register Courses</a>
                <a href="/results" class="<?= $navClass('/results') ?>">📊 Academic Results</a>
                <a href="/profile" class="<?= $navClass('/profile') ?>">👤 Profile</a>
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
    'deleted'        => 'Record deleted successfully.',
    'enrolled'       => ($currentRole === 'student') ? 'You have registered for the course successfully!' : 'Student enrolled in course successfully.',
    'dropped'        => ($currentRole === 'student') ? 'You have dropped the course from your schedule.' : 'Course dropped successfully.',
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
