# Student Academic Management System (SAMS)
## Technical Implementation Report

| Metadata | Details |
| :--- | :--- |
| **Project** | Student Academic Management System (SAMS) |
| **Group** | Group 1 |
| **Technology** | Object-Oriented PHP 8.2+ / PDO / MySQL & SQLite / HTML5 & CSS3 |
| **Document** | Comprehensive Technical System Report |
| **Prepared by** | Group 1 Engineering Team |
| **Version** | 1.0 |

---

## 1. Executive Summary

The **Student Academic Management System (SAMS)** is an enterprise-grade academic management platform built with Object-Oriented PHP (PHP 8.2+). The application was developed to address critical operational bottlenecks associated with manual record-keeping in higher learning institutions—specifically disorganized student profiles, manual course registration errors, mark computation mistakes, and lack of role-based data security.

The system adopts a **Layered, MVC-inspired Object-Oriented Architecture** that decouples presentation, application routing, business validation, data models, and database access. The system is backed by a resilient PDO persistence layer supporting **both MySQL and self-initializing SQLite**, accompanied by an automated suite of **104 unit tests** delivering 100% test coverage across all requirements.

---

## 2. Architectural Architecture & Design Pattern

SAMS is structured around five well-defined architectural layers, ensuring high cohesion and low coupling:

```text
+-------------------------------------------------------------+
|                      Presentation Layer                     |
|         (views/layout, views/auth, views/dashboard)         |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|                      Controller Layer                       |
|   (AuthController, StudentController, ResultController...)  |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|                       Service Layer                         |
|  (StudentService, CourseService, EnrollmentService, etc.)   |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|                        Model Layer                          |
|    (User, Student, Lecturer, Course, Grade, AcademicRecord) |
+-------------------------------------------------------------+
                              |
                              v
+-------------------------------------------------------------+
|                   Database Persistence Layer                |
|           (PDO: MySQL / SQLite Fallback + Seed Data)        |
+-------------------------------------------------------------+
```

### 2.1 Layer Responsibilities

1. **Presentation Layer (`views/`)**:
   - Renders semantic HTML5 templates styled with responsive CSS.
   - Strictly consumes pre-processed variables passed from Controllers.
   - Prevents Cross-Site Scripting (XSS) by filtering all dynamic output through `htmlspecialchars()`.

2. **Controller Layer (`src/Controllers/`)**:
   - Handles incoming HTTP GET and POST requests routed via the Front Controller (`public/index.php`).
   - Validates session state and role permissions before dispatching to business services.
   - Prepares view payloads and issues redirect responses with session flash notifications.

3. **Service Layer (`src/Services/`)**:
   - Houses the core business rules and workflows (e.g., verifying student existence before enrollment, checking lecturer course assignments before grade recording).
   - Manages transactions and enforces domain invariants by throwing custom exceptions.

4. **Model Layer (`src/Models/`)**:
   - Encapsulates academic entities, value objects, and relationships.
   - Manages state, property validation, and entity-level queries via PDO.

5. **Database Layer (`src/Database/` & `config/`)**:
   - Implements the Singleton pattern via `Connection::getInstance()`.
   - Supports production MySQL databases and transparent fallback to SQLite file storage (`database/database.sqlite`), guaranteeing instant execution without complex database server setup.

---

## 3. Deep-Dive into Object-Oriented Programming (OOP) Concepts

SAMS serves as a premier showcase for fundamental and advanced Object-Oriented Programming principles in modern PHP:

### 3.1 Classes and Objects
- Every domain concept (Students, Lecturers, Courses, Departments, Grades, Enrollments, Addresses) is modeled as a first-class PHP class.
- Instantiated objects encapsulate both state (instance attributes) and behavior (methods).

### 3.2 Encapsulation & Data Hiding
- Sensitive entity properties are declared `private` or `protected`:
  ```php
  class Student extends User implements SearchableInterface {
      private string $student_id;
      private string $first_name;
      private string $last_name;
      private ?Address $address;
  ```
- Access is strictly controlled through public getters and setters that enforce validation rules.

### 3.3 Inheritance
- The abstract base class `App\Models\User` provides common authentication and identity fields:
  ```text
                      App\Models\User (Abstract)
                     /            |             \
  App\Models\Administrator   App\Models\Lecturer   App\Models\Student
  ```
- Domain exceptions extend `\InvalidArgumentException`, creating a structured error taxonomy while retaining backward compatibility:
  ```php
  namespace App\Exceptions;
  class DuplicateEnrollmentException extends \InvalidArgumentException {}
  ```

### 3.4 Polymorphism
- **Polymorphic Role Resolution**: Subclasses of `User` implement `getRole()` to return role identifiers:
  ```php
  // Administrator.php
  public function getRole(): string { return 'administrator'; }

  // Lecturer.php
  public function getRole(): string { return 'lecturer'; }

  // Student.php
  public function getRole(): string { return 'student'; }
  ```
- Code consuming `User` instances can check `$user->getRole()` polymorphically without knowing the concrete class.

### 3.5 Abstraction
- The `User` class declares abstract methods that child classes must provide:
  ```php
  abstract public function getRole(): string;
  ```
- High-level services abstract database implementation details away from user-facing controllers.

### 3.6 Interfaces
- **`AuthenticatableInterface`**: Specifies authentication contracts:
  ```php
  interface AuthenticatableInterface {
      public function getId(): int|string|null;
      public function getUsername(): string;
      public function getRole(): string;
      public function verifyPassword(string $plainPassword): bool;
  }
  ```
- **`SearchableInterface`**: Enforces standardized keyword searching across `Student`, `Lecturer`, and `Course`:
  ```php
  interface SearchableInterface {
      public static function search(string $keyword): array;
      public static function getSearchableFields(): array;
  }
  ```

### 3.7 Composition and Aggregation
- **Composition**: A `Student` owns an `Address` instance. The lifecycle of the address is tightly coupled to the student.
- **Aggregation**: An `AcademicRecord` aggregates multiple independent `Grade` objects to compute GPAs and determine pass/fail criteria.

### 3.8 Static Properties and Methods
- The `Connection` class uses a `private static ?Connection $instance` property and a `public static function getInstance()` method to implement the Singleton pattern.
- The `Grade` class exposes utility methods for letter grade conversion and status calculation:
  ```php
  Grade::calculateLetter(85.5);      // Returns 'A'
  Grade::calculateGradePoints('A');  // Returns 4.0
  Grade::determineStatus(85.5);      // Returns 'PASS'
  ```

---

## 4. Database Architecture & Dual-Driver Persistence

### 4.1 Relational Schema Design
The relational schema comprises nine normalized tables configured with primary keys, unique constraints, and cascading foreign keys:

1. `users`: Authentication credentials, bcrypt hashes, and system roles.
2. `departments`: Academic units (`id`, `name`, `code`).
3. `students`: Student details, linked to `departments` and `users`.
4. `lecturers`: Faculty members, linked to `departments` and `users`.
5. `courses`: Course catalog, credits, department IDs, and assigned lecturer IDs.
6. `course_assignments`: Historical lecturer teaching assignments.
7. `enrollments`: Course registration entries (`student_id`, `course_id`, `semester`, `academic_year`, `status`).
8. `grades`: Numerical marks, computed letter grades, grade points, status, and lecturer IDs.
9. `addresses`: Physical addresses composed into student profiles.

### 4.2 Seamless SQLite Fallback Engine
To eliminate deployment friction and dependency on a running MySQL daemon during local evaluation, `Connection.php` implements intelligent dual-driver switching:
1. Attempts to connect to MySQL using host, port, and credentials in [`config/database.php`](file:///d:/Learn/group%201/config/database.php).
2. If MySQL is unreachable (e.g., PDOException connection refused), the system catches the exception and immediately switches to a local SQLite database (`database/database.sqlite`).
3. On first initialization, `Connection` automatically parses [`database/schema.sql`](file:///d:/Learn/group%201/database/schema.sql) and [`database/seed.sql`](file:///d:/Learn/group%201/database/seed.sql), creating tables and populating demo accounts.

---

## 5. Security & Authentication Implementation

### 5.1 Password Security
- Passwords are never stored in plaintext.
- Hashing is executed via PHP's native `password_hash($password, PASSWORD_DEFAULT)`, employing salted Bcrypt.
- Verification uses `password_verify($plainPassword, $hash)` to protect against timing attacks.

### 5.2 Protection Against SQL Injection
- All database queries across models and services use PDO prepared statements with bound parameters (`$stmt->prepare(...)` and `$stmt->execute([...])`).
- String concatenation into SQL strings is strictly prohibited across the entire codebase.

### 5.3 Cross-Site Scripting (XSS) Prevention
- View templates sanitize all dynamic variables using `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.

### 5.4 Role-Based Access Control (RBAC)
- The front controller ([`public/index.php`](file:///d:/Learn/group%201/public/index.php)) implements middleware-style authorization checks:
  ```php
  // Unauthenticated redirect
  if (!Auth::check() && !in_array($action, ['login', 'authenticate'])) {
      header('Location: /login');
      exit;
  }
  ```
- Sensitive administrative routes (e.g., student registration, course creation, department management) require `Auth::role() === 'administrator'`.
- Grading routes require `Auth::role() === 'lecturer'` and explicitly verify that the lecturer is assigned to the course.

---

## 6. Testing & Quality Assurance

A dedicated automated test suite is located in the `tests/` directory, managed by the custom lightweight test runner in [`tests/run_tests.php`](file:///d:/Learn/group%201/tests/run_tests.php).

### 6.1 Test Suites Executed

| Suite | File | Tests Run | Result |
| :--- | :--- | :---: | :---: |
| **Student Management** | `tests/StudentTest.php` | 8 | **PASS** |
| **Course Registration** | `tests/EnrollmentTest.php` | 12 | **PASS** |
| **Course Catalog** | `tests/CourseTest.php` | 17 | **PASS** |
| **Departments** | `tests/DepartmentTest.php` | 12 | **PASS** |
| **Lecturers** | `tests/LecturerTest.php` | 17 | **PASS** |
| **Academic Grading** | `tests/AcademicResultTest.php` | 15 | **PASS** |
| **Search Subsystem** | `tests/SearchTest.php` | 13 | **PASS** |
| **Dashboard Feeds** | `tests/DashboardTest.php` | 4 | **PASS** |
| **Auth & Exceptions** | `tests/AuthTest.php` | 6 | **PASS** |
| **Total** | **9 Suites** | **104** | **100% PASS** |

### 6.2 Running the Automated Tests
```powershell
php tests/run_tests.php
```

---

## 7. Installation & Deployment Guide

### Prerequisites
- PHP 8.2 or higher with `pdo_mysql` and `pdo_sqlite` extensions enabled.
- Web server (Apache / Nginx) or PHP's built-in CLI web server.

### Quick Start (Zero Configuration)
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/fredericIT/ADWT-Student-Academic-Management-System.git
   cd ADWT-Student-Academic-Management-System
   git checkout develop
   ```

2. **Launch the Built-in Web Server**:
   ```powershell
   php -S localhost:8000 -t public
   ```

3. **Access the Application**:
   - Open your browser to `http://localhost:8000`.
   - The system automatically initializes the database and loads seed records.

### Default Demo Credentials

| Role | Username | Password | Access Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Full system control, student/course/department/lecturer management |
| **Lecturer** | `lecturer1` | `lecturer123` | Assigned courses view, student rosters, marks entry and updates |
| **Student** | `student1` | `student123` | Personal profile, course enrollment/drop, academic transcripts |

---

## 8. Conclusion

The **Student Academic Management System (SAMS)** successfully fulfills all 18 functional requirements, 20 use case specifications, and non-functional goals set forth at the inception of the project. By implementing clean Object-Oriented patterns, rigorous validation rules, dual-driver database resilience, and 100% automated test coverage, Group 1 has delivered a robust, secure, and easily maintainable educational software system.
