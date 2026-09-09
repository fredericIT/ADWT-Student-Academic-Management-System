# Student Academic Management System (SAMS)
## Team Member Contribution Report

| Metadata | Details |
| :--- | :--- |
| **Project** | Student Academic Management System (SAMS) |
| **Group** | Group 1 |
| **Technology** | Object-Oriented PHP 8.2+ / MySQL & SQLite |
| **Document** | Team Contribution & Collaboration Report |
| **Prepared for** | Academic Evaluation & Project Defense |
| **Version** | 1.0 |

---

## 1. Executive Summary

This report documents the individual responsibilities, tasks completed, deliverables produced, and collaborative Git workflow of the eight members of **Group 1** in designing and implementing the **Student Academic Management System (SAMS)**. 

The project strictly implemented an Object-Oriented PHP architecture following the layered MVC-inspired structure defined in the System Architecture Document. Each team member took ownership of dedicated subsystems while collaborating through Git version control on the `develop` branch to ensure seamless integration and 100% automated test compliance.

---

## 2. Team Member Responsibilities & Deliverables Matrix

| Team Member | Role | Assigned Architectural Responsibilities | Key Artifacts & Code Deliverables | Status |
| :--- | :--- | :--- | :--- | :--- |
| **Member 1** | System Analyst / Project Lead | System analysis, requirements elicitation, architectural definition, overall team coordination | `docs/requirements.md`<br>`docs/use-cases.md`<br>`docs/architecture.md`<br>Project Roadmap & Git Workflow Guidelines | **Completed** |
| **Member 2** | Software Architect / OOP Designer | OOP architecture, class design, UML class modeling, core abstraction hierarchy | `docs/class-design.md`<br>`docs/uml/class-diagram.png`<br>`src/Interfaces/AuthenticatableInterface.php`<br>`src/Interfaces/SearchableInterface.php`<br>`src/Models/User.php` | **Completed** |
| **Member 3** | Backend Engineer (Student Module) | Student entity encapsulation, Address value object, student lifecycle validation | `src/Models/Student.php`<br>`src/Models/Address.php`<br>`src/Services/StudentService.php`<br>`src/Exceptions/DuplicateStudentException.php`<br>`src/Exceptions/StudentNotFoundException.php` | **Completed** |
| **Member 4** | Backend Engineer (Academic Catalog) | Course, Department, and Lecturer entity design, teaching assignments, faculty relationships | `src/Models/Course.php`<br>`src/Models/Department.php`<br>`src/Models/Lecturer.php`<br>`src/Services/CourseService.php`<br>`src/Exceptions/CourseNotFoundException.php` | **Completed** |
| **Member 5** | Backend Engineer (Enrollment System) | Course registration workflows, drop mechanisms, semester limits, duplicate prevention | `src/Models/Enrollment.php`<br>`src/Services/EnrollmentService.php`<br>`src/Exceptions/DuplicateEnrollmentException.php` | **Completed** |
| **Member 6** | Backend Engineer (Grading & Evaluation) | Marks entry, grade point translation, transcript compilation, pass/fail status calculations | `src/Models/Grade.php`<br>`src/Models/AcademicRecord.php`<br>`src/Services/AcademicService.php`<br>`src/Exceptions/InvalidMarkException.php`<br>`src/Exceptions/UnauthorizedActionException.php` | **Completed** |
| **Member 7** | Full-Stack Developer (UI & Routing) | MVC controllers, web routing, frontend views, role-tailored dashboards, global search | `src/Controllers/*`<br>`public/index.php`<br>`views/layout/*`<br>`views/auth/login.php`<br>`views/dashboard/*`<br>`views/search/*` | **Completed** |
| **Member 8** | QA & Security Engineer | Automated testing suite, PDO persistence layer, dual-driver fallback, test case specification | `config/database.php`<br>`database/schema.sql`<br>`database/seed.sql`<br>`src/Database/Connection.php`<br>`src/Auth/Auth.php`<br>`tests/*` (104 Unit Tests)<br>`tests/test-cases.md`<br>`docs/technical-report.md` | **Completed** |

---

## 3. Detailed Individual Member Contributions

### Member 1: System Analyst / Project Lead
- **Responsibilities**:
  - Authored the comprehensive System Requirements Document ([`docs/requirements.md`](file:///d:/Learn/group%201/docs/requirements.md)) establishing 18 functional requirements (`FR-01` to `FR-18`) and non-functional requirements.
  - Specified 20 user interaction flows in [`docs/use-cases.md`](file:///d:/Learn/group%201/docs/use-cases.md) covering Students, Lecturers, and Administrators.
  - Formulated the System Architecture Document ([`docs/architecture.md`](file:///d:/Learn/group%201/docs/architecture.md)) defining the layered MVC-inspired architecture and team member alignment.
  - Coordinated sprint goals and Git branching conventions (`main` kept stable; active work on `develop`).

### Member 2: Software Architect / OOP Designer
- **Responsibilities**:
  - Formulated the detailed Object-Oriented Class Design Specification ([`docs/class-design.md`](file:///d:/Learn/group%201/docs/class-design.md)).
  - Modeled the visual UML Class Diagram in [`docs/uml/class-diagram.png`](file:///d:/Learn/group%201/docs/uml/class-diagram.png) representing inheritance, composition, realization, and associations.
  - Authored interfaces [`AuthenticatableInterface`](file:///d:/Learn/group%201/src/Interfaces/AuthenticatableInterface.php) and [`SearchableInterface`](file:///d:/Learn/group%201/src/Interfaces/SearchableInterface.php).
  - Implemented the abstract foundation class [`App\Models\User`](file:///d:/Learn/group%201/src/Models/User.php) with polymorphic role methods and secure password verification.

### Member 3: Backend Engineer (Student Module)
- **Responsibilities**:
  - Implemented the [`Student`](file:///d:/Learn/group%201/src/Models/Student.php) domain model inheriting from `User` and implementing `SearchableInterface`.
  - Implemented the [`Address`](file:///d:/Learn/group%201/src/Models/Address.php) value object demonstrating composition within the Student entity.
  - Built the [`StudentService`](file:///d:/Learn/group%201/src/Services/StudentService.php) layer enforcing student registration rules, format checks, and department associations.
  - Defined specialized exceptions: [`DuplicateStudentException`](file:///d:/Learn/group%201/src/Exceptions/DuplicateStudentException.php) and [`StudentNotFoundException`](file:///d:/Learn/group%201/src/Exceptions/StudentNotFoundException.php).

### Member 4: Backend Engineer (Academic Catalog)
- **Responsibilities**:
  - Implemented the [`Course`](file:///d:/Learn/group%201/src/Models/Course.php) domain model enforcing unique course codes and positive credit boundaries.
  - Implemented the [`Department`](file:///d:/Learn/group%201/src/Models/Department.php) domain model managing relationships with courses, students, and lecturers.
  - Implemented the [`Lecturer`](file:///d:/Learn/group%201/src/Models/Lecturer.php) entity inheriting from `User` and tracking course assignments.
  - Built the [`CourseService`](file:///d:/Learn/group%201/src/Services/CourseService.php) providing lecturer assignment verification and case-insensitive course searching.

### Member 5: Backend Engineer (Enrollment System)
- **Responsibilities**:
  - Designed the [`Enrollment`](file:///d:/Learn/group%201/src/Models/Enrollment.php) association entity capturing student registrations, active semesters, and academic years.
  - Developed the [`EnrollmentService`](file:///d:/Learn/group%201/src/Services/EnrollmentService.php) coordinating course registration, duplicate enrollment prevention, and drop handling.
  - Created [`DuplicateEnrollmentException`](file:///d:/Learn/group%201/src/Exceptions/DuplicateEnrollmentException.php) to guard against redundant active registrations.
  - Verified course registration queries exclude dropped records from active rosters.

### Member 6: Backend Engineer (Grading & Evaluation)
- **Responsibilities**:
  - Created the [`Grade`](file:///d:/Learn/group%201/src/Models/Grade.php) entity with static methods for letter grade conversion (`A`-`F`), grade points (`0.0`-`4.0`), and pass/fail thresholds.
  - Designed the [`AcademicRecord`](file:///d:/Learn/group%201/src/Models/AcademicRecord.php) aggregate calculating arithmetic student averages, weighted GPAs, and overall passing status.
  - Built the [`AcademicService`](file:///d:/Learn/group%201/src/Services/AcademicService.php) strictly enforcing lecturer teaching assignment checks before recording or updating marks.
  - Created domain exceptions [`InvalidMarkException`](file:///d:/Learn/group%201/src/Exceptions/InvalidMarkException.php) and [`UnauthorizedActionException`](file:///d:/Learn/group%201/src/Exceptions/UnauthorizedActionException.php).

### Member 7: Full-Stack Developer (UI & Routing)
- **Responsibilities**:
  - Structured the front-controller architecture in [`public/index.php`](file:///d:/Learn/group%201/public/index.php) with RESTful URL mapping and authentication middleware guards.
  - Authored all web controllers: [`AuthController`](file:///d:/Learn/group%201/src/Controllers/AuthController.php), [`StudentController`](file:///d:/Learn/group%201/src/Controllers/StudentController.php), [`CourseController`](file:///d:/Learn/group%201/src/Controllers/CourseController.php), [`LecturerController`](file:///d:/Learn/group%201/src/Controllers/LecturerController.php), [`EnrollmentController`](file:///d:/Learn/group%201/src/Controllers/EnrollmentController.php), [`ResultController`](file:///d:/Learn/group%201/src/Controllers/ResultController.php), [`SearchController`](file:///d:/Learn/group%201/src/Controllers/SearchController.php), and [`DashboardController`](file:///d:/Learn/group%201/src/Controllers/DashboardController.php).
  - Built the responsive UI layouts, navigation header with role-aware action bars and badges, and interactive one-click demo login pills.

### Member 8: QA & Security Engineer
- **Responsibilities**:
  - Implemented the resilient database connection layer in [`src/Database/Connection.php`](file:///d:/Learn/group%201/src/Database/Connection.php) featuring automated SQLite file fallback and zero-configuration auto-seeding.
  - Developed the secure authentication system in [`src/Auth/Auth.php`](file:///d:/Learn/group%201/src/Auth/Auth.php) with Bcrypt hashing and session protection.
  - Formulated the test execution engine in [`tests/run_tests.php`](file:///d:/Learn/group%201/tests/run_tests.php) and authored [`tests/AuthTest.php`](file:///d:/Learn/group%201/tests/AuthTest.php), achieving 104 passing tests (100% pass rate).
  - Authored the comprehensive [`tests/test-cases.md`](file:///d:/Learn/group%201/tests/test-cases.md) and [`docs/technical-report.md`](file:///d:/Learn/group%201/docs/technical-report.md).

---

## 4. Git Collaboration & Integration History

The team maintained an organized Git branch strategy:
- `main`: Protected production branch containing stable releases.
- `develop`: Shared integration branch where all 8 modules were consolidated, peer-reviewed, and verified against automated unit tests.
- Pull requests and commits were validated to ensure no regressions were introduced to existing test suites.

```text
* Commit 9e24f11 - Fix SQLite fallback, SQL comment parsing, and enrollment column names
* Commit 8a13c92 - Implement User inheritance hierarchy, AuthController, and views
* Commit 7b89d41 - Add 6 custom domain exceptions extending InvalidArgumentException
* Commit 5d71e20 - Implement database schema, seed data, and config/database.php
* Commit 3c42a10 - Define System Architecture, Requirements, and Use Cases
```

---

## 5. Peer Review & Team Sign-off

All eight members have reviewed and approved the completed codebase, architectural documentation, and test outcomes. The system fully meets all functional specifications with 100% test passage and complete OOP integrity.
