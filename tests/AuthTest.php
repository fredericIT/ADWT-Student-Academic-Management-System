<?php

declare(strict_types=1);

namespace Tests;

use App\Auth\Auth;
use App\Database\Connection;
use App\Exceptions\CourseNotFoundException;
use App\Exceptions\DuplicateEnrollmentException;
use App\Exceptions\DuplicateStudentException;
use App\Exceptions\InvalidMarkException;
use App\Exceptions\StudentNotFoundException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\Administrator;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests for User model hierarchy, role polymorphism, Auth, and custom exceptions.
 */
class AuthTest extends TestCase
{
    private static PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('sqlite::memory:');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::$pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL,
                student_id INTEGER NULL,
                lecturer_id INTEGER NULL,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        ');

        // Insert seed test user
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = self::$pdo->prepare('
            INSERT INTO users (username, email, password_hash, role)
            VALUES (:u, :e, :p, :r)
        ');
        $stmt->execute([
            ':u' => 'testadmin',
            ':e' => 'admin@test.rw',
            ':p' => $hash,
            ':r' => 'administrator',
        ]);
    }

    protected function setUp(): void
    {
        Connection::setInstance(self::$pdo);
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        $_SESSION = [];
    }

    public function test_user_hierarchy_and_role_polymorphism(): void
    {
        $admin    = new Administrator(id: 1, username: 'admin', email: 'admin@test.rw');
        $student  = new Student(id: 1, studentId: 'ST001', firstName: 'John', lastName: 'Doe', email: 'john@test.rw');
        $lecturer = new Lecturer(id: 1, firstName: 'Jane', lastName: 'Smith', email: 'jane@test.rw');

        $this->assertTrue($admin instanceof User);
        $this->assertTrue($student instanceof User);
        $this->assertTrue($lecturer instanceof User);

        $this->assertSame('administrator', $admin->getRole());
        $this->assertSame('student', $student->getRole());
        $this->assertSame('lecturer', $lecturer->getRole());
    }

    public function test_password_hashing_and_verification(): void
    {
        $admin = new Administrator(id: 2, username: 'tester', email: 'tester@test.rw');
        $admin->setPassword('securePass99');

        $this->assertTrue($admin->verifyPassword('securePass99'));
        $this->assertFalse($admin->verifyPassword('wrongPassword'));
    }

    public function test_auth_attempt_succeeds_with_valid_credentials(): void
    {
        $success = Auth::attempt('testadmin', 'password123');
        $this->assertTrue($success);
        $this->assertTrue(Auth::check());
        $this->assertSame('administrator', Auth::getRole());
        $this->assertTrue(Auth::isAdmin());
    }

    public function test_auth_attempt_fails_with_invalid_credentials(): void
    {
        $this->assertFalse(Auth::attempt('testadmin', 'badpass'));
        $this->assertFalse(Auth::attempt('nonexistent_user', 'password123'));
    }

    public function test_auth_logout_clears_session(): void
    {
        Auth::attempt('testadmin', 'password123');
        $this->assertTrue(Auth::check());

        Auth::logout();
        $this->assertSame([], $_SESSION);
        $this->assertFalse(Auth::check());
    }

    public function test_custom_domain_exceptions_are_catchable_as_invalid_argument(): void
    {
        try {
            throw new DuplicateStudentException('Student duplicate ID');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Student duplicate ID', $e->getMessage());
        }

        try {
            throw new DuplicateEnrollmentException('Already enrolled');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Already enrolled', $e->getMessage());
        }

        try {
            throw new StudentNotFoundException('Student missing');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Student missing', $e->getMessage());
        }

        try {
            throw new CourseNotFoundException('Course missing');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Course missing', $e->getMessage());
        }

        try {
            throw new InvalidMarkException('Mark out of range');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Mark out of range', $e->getMessage());
        }

        try {
            throw new UnauthorizedActionException('Unauthorized action');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('Unauthorized action', $e->getMessage());
        }
    }
}
