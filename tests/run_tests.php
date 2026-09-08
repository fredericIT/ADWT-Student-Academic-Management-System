<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefixes = [
            'App\\'   => __DIR__ . '/../src/',
            'Tests\\' => __DIR__ . '/../tests/',
        ];
        foreach ($prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }
            $relative = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    });
}

// Stub PHPUnit TestCase if PHPUnit package is not installed locally
if (!class_exists('PHPUnit\Framework\TestCase')) {
    abstract class SimpleTestCase {
        protected ?string $expectedExceptionClass = null;
        protected ?string $expectedExceptionMessage = null;
        protected ?string $expectedExceptionMessagePattern = null;

        public function expectException(string $exception): void {
            $this->expectedExceptionClass = $exception;
        }

        public function expectExceptionMessage(string $message): void {
            $this->expectedExceptionMessage = $message;
        }

        public function expectExceptionMessageMatches(string $regularExpression): void {
            $this->expectedExceptionMessagePattern = $regularExpression;
        }

        public function resetExpectedException(): void {
            $this->expectedExceptionClass = null;
            $this->expectedExceptionMessage = null;
            $this->expectedExceptionMessagePattern = null;
        }

        public function getExpectedExceptionClass(): ?string {
            return $this->expectedExceptionClass;
        }

        public function getExpectedExceptionMessage(): ?string {
            return $this->expectedExceptionMessage;
        }

        public function getExpectedExceptionMessagePattern(): ?string {
            return $this->expectedExceptionMessagePattern;
        }

        protected function assertSame($expected, $actual, string $msg = ''): void {
            if ($expected !== $actual) {
                throw new Exception("Assertion Failed: Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . ($msg ? " ($msg)" : ""));
            }
        }
        protected function assertNotNull($actual, string $msg = ''): void {
            if ($actual === null) {
                throw new Exception("Assertion Failed: Expected non-null value ($msg)");
            }
        }
        protected function assertNull($actual, string $msg = ''): void {
            if ($actual !== null) {
                throw new Exception("Assertion Failed: Expected null, got " . var_export($actual, true) . ($msg ? " ($msg)" : ""));
            }
        }
        protected function assertTrue($actual, string $msg = ''): void {
            if ($actual !== true) {
                throw new Exception("Assertion Failed: Expected true, got " . var_export($actual, true) . ($msg ? " ($msg)" : ""));
            }
        }
        protected function assertFalse($actual, string $msg = ''): void {
            if ($actual !== false) {
                throw new Exception("Assertion Failed: Expected false, got " . var_export($actual, true) . ($msg ? " ($msg)" : ""));
            }
        }
        protected function assertCount(int $expectedCount, $array, string $msg = ''): void {
            $actualCount = is_countable($array) ? count($array) : 0;
            if ($expectedCount !== $actualCount) {
                throw new Exception("Assertion Failed: Expected count {$expectedCount}, got {$actualCount} ($msg)");
            }
        }
        protected function assertContains($needle, $haystack, string $msg = ''): void {
            if (!is_iterable($haystack) || !in_array($needle, (array)$haystack, true)) {
                throw new Exception("Assertion Failed: Expected array to contain " . var_export($needle, true) . ($msg ? " ($msg)" : ""));
            }
        }
    }
    class_alias('SimpleTestCase', 'PHPUnit\Framework\TestCase');
}

$testClasses = [
    'Tests\StudentTest'        => __DIR__ . '/StudentTest.php',
    'Tests\EnrollmentTest'     => __DIR__ . '/EnrollmentTest.php',
    'Tests\CourseTest'         => __DIR__ . '/CourseTest.php',
    'Tests\DepartmentTest'     => __DIR__ . '/DepartmentTest.php',
    'Tests\LecturerTest'       => __DIR__ . '/LecturerTest.php',
    'Tests\AcademicResultTest' => __DIR__ . '/AcademicResultTest.php',
];

$totalPassed = 0;
$totalFailed = 0;

echo "===============================================\n";
echo "       ADWT SAMS System Unit Test Suite        \n";
echo "===============================================\n\n";

foreach ($testClasses as $className => $filePath) {
    if (!file_exists($filePath)) continue;
    require_once $filePath;

    echo "--- Testing {$className} ---\n";
    $test = new $className();
    $className::setUpBeforeClass();

    $methods = get_class_methods($test);
    $passed = 0;
    $failed = 0;

    foreach ($methods as $method) {
        if (str_starts_with($method, 'test')) {
            try {
                $setUp = new ReflectionMethod($test, 'setUp');
                $setUp->setAccessible(true);
                $setUp->invoke($test);
                if (method_exists($test, 'resetExpectedException')) {
                    $test->resetExpectedException();
                }

                $ref = new ReflectionMethod($test, $method);
                
                $exceptionThrown = null;
                try {
                    $ref->invoke($test);
                } catch (Throwable $e) {
                    $exceptionThrown = $e;
                    if ($e instanceof ReflectionException && $e->getPrevious()) {
                        $exceptionThrown = $e->getPrevious();
                    }
                }

                $expectedClass   = method_exists($test, 'getExpectedExceptionClass') ? $test->getExpectedExceptionClass() : null;
                $expectedMsg     = method_exists($test, 'getExpectedExceptionMessage') ? $test->getExpectedExceptionMessage() : null;
                $expectedPattern = method_exists($test, 'getExpectedExceptionMessagePattern') ? $test->getExpectedExceptionMessagePattern() : null;

                if ($expectedClass !== null) {
                    if ($exceptionThrown === null) {
                        throw new Exception("Failed asserting that exception {$expectedClass} was thrown.");
                    }
                    if (!($exceptionThrown instanceof $expectedClass)) {
                        throw new Exception("Failed asserting that " . get_class($exceptionThrown) . " is instance of {$expectedClass}.");
                    }
                    if ($expectedMsg !== null && !str_contains($exceptionThrown->getMessage(), $expectedMsg)) {
                        throw new Exception("Failed asserting that exception message '{$exceptionThrown->getMessage()}' contains '{$expectedMsg}'.");
                    }
                    if ($expectedPattern !== null && !preg_match($expectedPattern, $exceptionThrown->getMessage())) {
                        throw new Exception("Failed asserting that exception message '{$exceptionThrown->getMessage()}' matches pattern '{$expectedPattern}'.");
                    }
                } elseif ($exceptionThrown !== null) {
                    throw $exceptionThrown;
                }

                echo "  [PASS] {$method}\n";
                $passed++;
            } catch (Throwable $e) {
                echo "  [FAIL] {$method}: {$e->getMessage()}\n";
                $failed++;
            }
        }
    }
    $totalPassed += $passed;
    $totalFailed += $failed;
    echo "\n";
}

echo "===============================================\n";
echo "TOTAL SUMMARY: {$totalPassed} Passed, {$totalFailed} Failed.\n";
echo "===============================================\n";

if ($totalFailed > 0) {
    exit(1);
}
