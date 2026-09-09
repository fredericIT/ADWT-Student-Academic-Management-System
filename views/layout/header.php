<?php
use App\Services\SessionManager;

$sessionManager = new SessionManager();
$currentUser    = $sessionManager->getUser();
$userRole       = strtolower((string) ($currentUser['role'] ?? ''));
?>
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

<!-- Navigation -->
<nav class="bg-brand shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?= $currentUser ? '/dashboard' : '/login' ?>" class="text-white text-xl font-bold tracking-tight">
                🎓 ADWT Academic
            </a>

            <div class="flex items-center gap-6">
                <?php if ($currentUser): ?>
                    <a href="/dashboard" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                        Dashboard
                    </a>

                    <?php if ($userRole === 'student'): ?>
                        <a href="/student-area" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Student Portal
                        </a>
                    <?php elseif ($userRole === 'lecturer'): ?>
                        <a href="/courses" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Courses
                        </a>
                        <a href="/lecturer-area" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Lecturer Portal
                        </a>
                    <?php elseif ($userRole === 'administrator'): ?>
                        <a href="/departments" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Departments
                        </a>
                        <a href="/courses" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Courses
                        </a>
                        <a href="/lecturers" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Lecturers
                        </a>
                        <a href="/admin-area" class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                            Admin Console
                        </a>
                    <?php endif; ?>

                    <div class="flex items-center gap-3 border-l border-blue-600 pl-6 ml-2">
                        <span class="text-xs text-blue-200 font-medium hidden sm:inline">
                            <?= htmlspecialchars($currentUser['name']) ?> (<?= htmlspecialchars($currentUser['role']) ?>)
                        </span>
                        <a href="/logout" class="bg-blue-800 hover:bg-blue-900 text-white text-xs font-semibold px-3 py-1.5 rounded transition-colors">
                            Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/login" class="bg-white text-brand hover:bg-blue-50 text-sm font-semibold px-4 py-1.5 rounded shadow transition-colors">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Page content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

<?php
// Display success flash message from redirect query param
$successMessages = [
    'created'    => 'Record created successfully.',
    'updated'    => 'Record updated successfully.',
    'registered' => 'Lecturer registered successfully.',
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
