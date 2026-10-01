<?php

declare(strict_types=1);

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

use App\Database\Database;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Grade;

echo "=== Testing Simple PDO CRUD & Search ===\n";

// 1. Test Database::connect()
$pdo = Database::connect();
assert($pdo instanceof PDO, "Database::connect() must return PDO instance");
echo "[PASS] Database::connect() works.\n";

// 2. Test Department Create, Read, Update, Delete
$dept = Department::create([
    'name' => 'Temporary Dept',
    'code' => 'TEMP99',
    'description' => 'For testing CRUD'
]);
assert($dept instanceof Department, "Department::create failed");
$deptId = $dept->id;
assert($deptId !== null, "Department id is null");
echo "[PASS] Department::create() works (ID: $deptId).\n";

$foundDept = Department::findById($deptId);
assert($foundDept !== null && $foundDept->code === 'TEMP99', "Department::findById failed");
echo "[PASS] Department::findById() works.\n";

$delDept = Department::deleteById($deptId);
assert($delDept === true, "Department::deleteById failed");
assert(Department::findById($deptId) === null, "Department was not deleted");
echo "[PASS] Department::deleteById() works.\n";

// 3. Test Lecturer Create, Search, Update, Delete
$lec = Lecturer::create([
    'first_name' => 'Simple',
    'last_name' => 'Tester',
    'email' => 'simple.tester@example.com'
]);
assert($lec instanceof Lecturer, "Lecturer::create failed");
$lecId = $lec->id;
echo "[PASS] Lecturer::create() works (ID: $lecId).\n";

$lecSearch = Lecturer::search('Tester');
assert(count($lecSearch) >= 1, "Lecturer::search failed to find Tester");
$found = false;
foreach ($lecSearch as $l) {
    if ($l->id === $lecId) {
        $found = true;
        break;
    }
}
assert($found, "Lecturer::search did not return created lecturer");
echo "[PASS] Lecturer::search() works with PDO LIKE.\n";

$delLec = Lecturer::deleteById($lecId);
assert($delLec === true, "Lecturer::deleteById failed");
assert(Lecturer::findById($lecId) === null, "Lecturer was not deleted");
echo "[PASS] Lecturer::deleteById() works.\n";

// 4. Test Course Create, Search, Delete
$course = Course::create([
    'code' => 'TEST101',
    'name' => 'Intro to Testing',
    'credits' => 3
]);
assert($course instanceof Course, "Course::create failed");
$courseId = $course->id;
echo "[PASS] Course::create() works (ID: $courseId).\n";

$courseSearch = Course::search('TEST101');
assert(count($courseSearch) >= 1, "Course::search failed to find TEST101");
echo "[PASS] Course::search() works with PDO LIKE.\n";

$delCourse = Course::deleteById($courseId);
assert($delCourse === true, "Course::deleteById failed");
assert(Course::findById($courseId) === null, "Course was not deleted");
echo "[PASS] Course::deleteById() works.\n";

// 5. Test Student Search & Delete
$students = Student::search('ST001');
assert(count($students) >= 1, "Student::search failed for ST001");
echo "[PASS] Student::search() works with PDO LIKE.\n";

echo "=========================================\n";
echo "All Simple PDO CRUD & Search Tests Passed!\n";
echo "=========================================\n";
