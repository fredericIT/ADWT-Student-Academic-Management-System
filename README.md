# Student Academic Management System (SAMS)

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://www.php.net/)
[![Architecture](https://img.shields.io/badge/Architecture-MVC%20%2F%20Layered%20OOP-green.svg)](docs/architecture.md)
[![Tests Status](https://img.shields.io/badge/Tests-104%2F104%20Passing%20(100%25)-brightgreen.svg)](tests/test-cases.md)
[![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite%20Fallback-orange.svg)](config/database.php)

The **Student Academic Management System (SAMS)** is an enterprise-grade academic record management platform developed by **Group 1** using Object-Oriented PHP (PHP 8.2+). The system enables higher education institutions to manage students, courses, academic departments, lecturers, course enrollments, and academic transcripts through a secure, role-based web application.

---

## 🌟 Key Features

- **Role-Based Authentication**: Secure login system for Administrators, Lecturers, and Students with Bcrypt password hashing (`PASSWORD_DEFAULT`) and session protection.
- **Student Profile Management**: Full student lifecycle tracking with address composition, department affiliation, and profile updates.
- **Academic Catalog**: Complete management of departments and course offerings with unique course codes and credit validation.
- **Course Enrollment & Drop**: Self-service registration for available courses with duplicate enrollment prevention and drop capabilities.
- **Academic Grading & Evaluation**: Authorized mark entry by assigned lecturers, automated letter grade assignment (`A`-`F`), GPA calculation, and pass/fail determination.
- **Flexible Search System**: Search students by ID or name, courses by code or title, and view enrolled student rosters.
- **Zero-Configuration Ready**: Seamless fallback to SQLite (`database/database.sqlite`) with auto-seeding if MySQL is unavailable.
- **100% Automated Test Coverage**: Built-in test runner with 104 unit tests covering 100% of requirements and use cases.

---

## 🏗️ Object-Oriented Architecture

SAMS adheres to strict Object-Oriented Programming (OOP) and a Layered / MVC-inspired architectural pattern:

- **Encapsulation**: Class properties are private/protected with public accessor and mutator methods enforcing validation.
- **Inheritance**: Abstract base class `App\Models\User` extended by `Administrator`, `Lecturer`, and `Student`.
- **Abstraction**: High-level services decouple business rules from presentation and raw SQL queries.
- **Polymorphism**: Unified role identification via `getRole()` and polymorphic search indexing via `SearchableInterface`.
- **Interfaces**: Contracts defined for authentication (`AuthenticatableInterface`) and search indexing (`SearchableInterface`).
- **Composition & Aggregation**: Student aggregates an Address value object; AcademicRecord aggregates Grade entries to compute GPAs.
- **Custom Exception Hierarchy**: Six domain exceptions extending `\InvalidArgumentException` for precise, backward-compatible error handling.

For complete architectural details, see the [Class Design Specification](docs/class-design.md) and [System Architecture](docs/architecture.md).

---

## 🚀 Quick Start Guide

### Prerequisites
- **PHP 8.2 or higher** with `pdo`, `pdo_mysql`, and `pdo_sqlite` extensions enabled.
- Git.

### 1. Clone the Repository
```bash
git clone https://github.com/fredericIT/ADWT-Student-Academic-Management-System.git
cd ADWT-Student-Academic-Management-System
git checkout develop
```

### 2. Run the Application
You can run the application immediately using PHP's built-in server without manually setting up MySQL:
```bash
php -S localhost:8000 -t public
```

Open your browser to:
```
http://localhost:8000
```
> **Note**: The system will automatically initialize the database schema and populate seed data in `database/database.sqlite` on first access.

### 3. (Optional) Configure MySQL
To use MySQL instead of SQLite:
1. Import `database/schema.sql` and `database/seed.sql` into your MySQL server:
   ```bash
   mysql -u root -p sams_db < database/schema.sql
   mysql -u root -p sams_db < database/seed.sql
   ```
2. Configure credentials in `config/database.php`:
   ```php
   'driver'   => 'mysql',
   'host'     => '127.0.0.1',
   'port'     => 3306,
   'database' => 'sams_db',
   'username' => 'root',
   'password' => '',
   ```

---

## 🔑 Demo Credentials

The application includes pre-seeded demo accounts with one-click login pills on the login page:

| Role | Username | Password | Dashboard Features |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Full administrative control, student registration, courses, departments, lecturers |
| **Lecturer** | `lecturer1` | `lecturer123` | View assigned courses, course rosters, record and update student marks |
| **Student** | `student1` | `student123` | Personal profile, course registration, drop courses, academic transcript & GPA |

---

## 🧪 Automated Testing

The project includes an automated test runner validating all model rules, calculations, exceptions, and authentication flows.

To run the complete test suite:
```bash
php tests/run_tests.php
```

### Test Results Summary
```text
===============================================
       ADWT SAMS System Unit Test Suite        
===============================================
--- Testing Tests\StudentTest -------- [PASS 8/8]
--- Testing Tests\EnrollmentTest ----- [PASS 12/12]
--- Testing Tests\CourseTest --------- [PASS 17/17]
--- Testing Tests\DepartmentTest ----- [PASS 12/12]
--- Testing Tests\LecturerTest ------- [PASS 17/17]
--- Testing Tests\AcademicResultTest - [PASS 15/15]
--- Testing Tests\SearchTest --------- [PASS 13/13]
--- Testing Tests\DashboardTest ------ [PASS 4/4]
--- Testing Tests\AuthTest ----------- [PASS 6/6]
===============================================
TOTAL SUMMARY: 104 Passed, 0 Failed (100%)
===============================================
```

---

## 📁 Project Directory Structure

```text
ADWT-Student-Academic-Management-System/
├── config/
│   └── database.php              # Centralized database configuration
├── database/
│   ├── schema.sql                # Relational table schema
│   ├── seed.sql                  # Initial seed data & demo accounts
│   └── database.sqlite           # SQLite storage file (auto-generated)
├── docs/
│   ├── requirements.md           # System Requirements Document (FR-01 to FR-18)
│   ├── use-cases.md              # Use Case Specification (UC-01 to UC-20)
│   ├── architecture.md           # Layered MVC System Architecture
│   ├── class-design.md           # Detailed OOP Class Design & Mermaid Diagram
│   ├── contribution-report.md    # 8-Member Team Contribution Report
│   ├── technical-report.md       # Full System Technical Report
│   └── uml/
│       └── class-diagram.png     # UML Class Diagram image
├── public/
│   ├── css/                      # Application stylesheets
│   ├── js/                       # Application JavaScript
│   └── index.php                 # Front Controller & HTTP router
├── src/
│   ├── Auth/                     # Authentication & session manager (Auth.php)
│   ├── Controllers/              # MVC Web Controllers
│   ├── Database/                 # PDO Singleton connection manager
│   ├── Exceptions/               # 6 Custom domain exceptions
│   ├── Interfaces/               # AuthenticatableInterface & SearchableInterface
│   ├── Models/                   # Domain entities (User, Student, Course, etc.)
│   └── Services/                 # Business logic & workflows
├── tests/
│   ├── run_tests.php             # Automated CLI test runner
│   ├── test-cases.md             # 18 Test Cases Specification & Traceability
│   └── *Test.php                 # 9 Automated unit test suites
└── README.md                     # Project documentation
```

---

## 📚 Project Documentation

- [System Requirements Document](docs/requirements.md)
- [Use Case Specification](docs/use-cases.md)
- [System Architecture Document](docs/architecture.md)
- [Class Design Document](docs/class-design.md)
- [Test Cases & Traceability Matrix](tests/test-cases.md)
- [Team Member Contribution Report](docs/contribution-report.md)
- [Technical Implementation Report](docs/technical-report.md)

---

## 👥 Group 1 Development Team

| Member | Assigned Role |
| :--- | :--- |
| **Member 1** | System Analyst / Project Lead |
| **Member 2** | Software Architect / OOP Designer |
| **Member 3** | Backend Engineer (Student Module) |
| **Member 4** | Backend Engineer (Academic Catalog) |
| **Member 5** | Backend Engineer (Enrollment System) |
| **Member 6** | Backend Engineer (Grading & Evaluation) |
| **Member 7** | Full-Stack Developer (UI & Routing) |
| **Member 8** | QA & Security Engineer |

---

## 📄 License

This project was developed for academic purposes under the Advanced Database & Web Technologies (ADWT) curriculum.