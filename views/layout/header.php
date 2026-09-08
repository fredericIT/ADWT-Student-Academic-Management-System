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
            <a href="/departments" class="text-white text-xl font-bold tracking-tight">
                🎓 ADWT Academic
            </a>
            <div class="flex gap-6">
                <a href="/departments"
                   class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                    Departments
                </a>
                <a href="/courses"
                   class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                    Courses
                </a>
                <a href="/lecturers"
                   class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                    Lecturers
                </a>
                <a href="/students"
                   class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                    Students
                </a>
                <a href="/enrollments"
                   class="text-blue-100 hover:text-white text-sm font-medium transition-colors">
                    Enrollments
                </a>
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
    'enrolled'   => 'Student enrolled in course successfully.',
    'dropped'    => 'Course dropped successfully.',
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
