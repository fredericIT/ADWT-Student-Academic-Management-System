<?php

declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────────────────────

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

// Load .env file if it exists (simple key=value parser)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2)) + ['', ''];
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

// ── Routing ───────────────────────────────────────────────────────────────────

use App\Router;
use App\Controllers\DepartmentController;
use App\Controllers\CourseController;
use App\Controllers\LecturerController;
use App\Controllers\EnrollmentController;
use App\Controllers\StudentController;
use App\Controllers\ResultController;

$router = new Router();

// --- Students ---
$router->get('/students',                                [StudentController::class, 'index']);
$router->get('/students/create',                         [StudentController::class, 'create']);
$router->post('/students',                               [StudentController::class, 'store']);
$router->get('/students/{id}',                           [StudentController::class, 'show']);
$router->get('/students/{id}/edit',                      [StudentController::class, 'edit']);
$router->post('/students/{id}',                          [StudentController::class, 'update']);

// --- Enrollments ---
$router->get('/enrollments',                             [EnrollmentController::class, 'index']);
$router->get('/enrollments/create',                      [EnrollmentController::class, 'create']);
$router->post('/enrollments',                            [EnrollmentController::class, 'store']);
$router->post('/enrollments/{id}/drop',                  [EnrollmentController::class, 'drop']);
$router->get('/courses/{id}/students',                   [EnrollmentController::class, 'courseStudents']);

// --- Departments ---
$router->get('/departments',                        [DepartmentController::class, 'index']);
$router->get('/departments/create',                 [DepartmentController::class, 'create']);
$router->post('/departments',                       [DepartmentController::class, 'store']);
$router->get('/departments/{id}/edit',              [DepartmentController::class, 'edit']);
$router->post('/departments/{id}',                  [DepartmentController::class, 'update']);
$router->get('/departments/{id}/courses',           [DepartmentController::class, 'courses']);
$router->get('/departments/{id}/lecturers',         [DepartmentController::class, 'lecturers']);

// --- Courses ---
$router->get('/courses',                            [CourseController::class, 'index']);
$router->get('/courses/create',                     [CourseController::class, 'create']);
$router->post('/courses',                           [CourseController::class, 'store']);
$router->get('/courses/{id}/edit',                  [CourseController::class, 'edit']);
$router->post('/courses/{id}',                      [CourseController::class, 'update']);
$router->post('/courses/{id}/assign-lecturer',      [CourseController::class, 'assignLecturer']);
$router->post('/courses/{id}/remove-lecturer',      [CourseController::class, 'removeLecturer']);

// --- Lecturers ---
$router->get('/lecturers',                              [LecturerController::class, 'index']);
$router->get('/lecturers/create',                       [LecturerController::class, 'create']);
$router->post('/lecturers',                             [LecturerController::class, 'store']);
$router->get('/lecturers/{id}/edit',                    [LecturerController::class, 'edit']);
$router->post('/lecturers/{id}',                        [LecturerController::class, 'update']);
$router->get('/lecturers/{id}/courses',                 [LecturerController::class, 'courses']);
$router->post('/lecturers/{id}/associate-department',   [LecturerController::class, 'associateDepartment']);

// --- Academic Results & Records ---
$router->get('/results',                                 [ResultController::class, 'index']);
$router->get('/results/record',                          [ResultController::class, 'record']);
$router->post('/results/record',                         [ResultController::class, 'store']);
$router->get('/results/{id}/edit',                       [ResultController::class, 'edit']);
$router->post('/results/{id}',                           [ResultController::class, 'update']);
$router->get('/students/{id}/results',                   [ResultController::class, 'studentResults']);
$router->get('/courses/{id}/results',                    [ResultController::class, 'courseResults']);

// --- Root redirect ---
$router->get('/', function () {
    header('Location: /departments');
    exit;
});

$router->dispatch();
