# Student Academic Management System (SAMS)

## System Architecture Document

**Project:** Student Academic Management System  
**Group:** Group 1  
**Technology:** Object-Oriented PHP  
**Database:** MySQL  
**Architecture:** Layered / MVC-inspired OOP Architecture  
**Document:** System Architecture  
**Version:** 1.0  
**Prepared by:** System Analyst / Project Lead  

---

# 1. Introduction

The Student Academic Management System (SAMS) will use a structured Object-Oriented architecture to separate user interface, business logic, domain models, and database operations.

The architecture is designed to make the application:

* Modular.
* Maintainable.
* Reusable.
* Testable.
* Secure.
* Easier to extend.
* Suitable for demonstrating Object-Oriented Programming concepts.

The system will follow a **layered architecture with MVC-inspired organization**.

---

# 2. Architectural Goals

The architecture has the following goals:

1. Separate different responsibilities of the application.
2. Keep business logic out of the user interface.
3. Represent academic entities using OOP classes.
4. Protect data using encapsulation.
5. Support inheritance between user types.
6. Support polymorphic behavior.
7. Make individual modules easier to test.
8. Reduce code duplication.
9. Make future features easier to add.
10. Provide a clear structure for team collaboration through GitHub.

---

# 3. Architectural Pattern

The project will use a **Layered Architecture / MVC-inspired architecture**.

The major layers are:

```text
+--------------------------------------------------+
|              Presentation Layer                  |
|        Views / HTML / CSS / Forms                |
+-------------------------+------------------------+
                          |
                          v
+--------------------------------------------------+
|              Controller Layer                   |
| StudentController / CourseController / etc.     |
+-------------------------+------------------------+
                          |
                          v
+--------------------------------------------------+
|                Service Layer                    |
| Business Rules / Validation / Operations        |
+-------------------------+------------------------+
                          |
                          v
+--------------------------------------------------+
|                 Model Layer                     |
| Student / Course / Enrollment / Grade / etc.    |
+-------------------------+------------------------+
                          |
                          v
+--------------------------------------------------+
|               Database Layer                    |
|          MySQL / PDO / Database Access          |
+--------------------------------------------------+
```

---

# 4. High-Level System Architecture

The complete system can be represented as follows:

```text
                         USERS
                           |
          +----------------+----------------+
          |                |                |
          v                v                v
       Student          Lecturer       Administrator
          |                |                |
          +----------------+----------------+
                           |
                           v
                 +-------------------+
                 |   Presentation    |
                 |      Layer        |
                 +---------+---------+
                           |
                           v
                 +-------------------+
                 |   Controllers     |
                 +---------+---------+
                           |
                           v
                 +-------------------+
                 |     Services      |
                 | Business Logic    |
                 +---------+---------+
                           |
                           v
                 +-------------------+
                 |      Models       |
                 |   OOP Entities    |
                 +---------+---------+
                           |
                           v
                 +-------------------+
                 |     Database      |
                 |       MySQL       |
                 +-------------------+
```

---

# 5. Presentation Layer

The Presentation Layer is responsible for interacting with users.

It will contain:

* HTML pages.
* Forms.
* Tables.
* Navigation.
* Dashboard pages.
* CSS.
* Client-side JavaScript where necessary.

The presentation layer should **not contain core business rules**.

For example, the student registration form should collect information and send it to the appropriate controller rather than directly performing database operations.

### Example Views

```text
views/
├── dashboard/
├── students/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   └── show.php
├── courses/
│   ├── index.php
│   ├── create.php
│   └── edit.php
├── enrollment/
│   ├── index.php
│   └── register.php
└── results/
    ├── index.php
    └── edit.php
```

---

# 6. Controller Layer

Controllers receive requests from the presentation layer and coordinate the required operations.

Controllers should not contain large amounts of business logic.

They will call appropriate services to perform operations.

### Proposed Controllers

```text
app/Controllers/
├── StudentController.php
├── CourseController.php
├── DepartmentController.php
├── LecturerController.php
├── EnrollmentController.php
├── ResultController.php
└── SearchController.php
```

### Responsibilities

For example:

`StudentController` will:

* Receive student-related requests.
* Validate basic request data.
* Call `StudentService`.
* Return the appropriate view.

`EnrollmentController` will:

* Receive course registration requests.
* Call `EnrollmentService`.
* Return registration results.

`ResultController` will:

* Receive mark-related requests.
* Call `AcademicService`.
* Return academic result information.

---

# 7. Service Layer

The Service Layer contains the application's business logic.

This layer is important because it prevents business rules from being scattered throughout controllers and views.

### Proposed Services

```text
app/Services/
├── StudentService.php
├── EnrollmentService.php
├── AcademicService.php
├── CourseService.php
└── SearchService.php
```

---

## 7.1 StudentService

Responsible for student-related operations such as:

* Registering students.
* Updating student information.
* Retrieving student profiles.
* Validating student-related operations.
* Searching students.

---

## 7.2 EnrollmentService

Responsible for:

* Registering students for courses.
* Checking duplicate enrollment.
* Dropping courses.
* Retrieving registered courses.
* Validating enrollment operations.

---

## 7.3 AcademicService

Responsible for:

* Recording marks.
* Updating marks.
* Retrieving grades.
* Calculating averages.
* Determining pass/fail status.
* Applying academic rules.

---

## 7.4 CourseService

Responsible for:

* Creating courses.
* Updating courses.
* Retrieving courses.
* Assigning lecturers.
* Associating courses with departments.
* Searching courses.

---

## 7.5 SearchService

Responsible for:

* Student search.
* Course search.
* Finding students registered in courses.

---

# 8. Model Layer

The Model Layer represents the main academic entities of the system.

The project must contain at least eight meaningful classes. The proposed architecture contains ten core classes.

```text
app/Models/
├── User.php
├── Student.php
├── Lecturer.php
├── Administrator.php
├── Course.php
├── Department.php
├── Enrollment.php
├── Grade.php
├── AcademicRecord.php
└── Address.php
```

---

# 9. User Class Hierarchy

The system will use inheritance to represent different types of users.

```text
                    +----------------+
                    |   <<abstract>> |
                    |      User      |
                    +-------+--------+
                            |
             +--------------+--------------+
             |              |              |
             v              v              v
        +---------+    +----------+   +---------------+
        | Student |    | Lecturer |   | Administrator |
        +---------+    +----------+   +---------------+
```

---

# 10. User Class

`User` will be an abstract base class containing common properties and behavior shared by system users.

### Responsibilities

* Store common user information.
* Provide common authentication-related behavior.
* Define common user operations.
* Define role-related behavior.

### Possible Properties

```text
userId
name
email
password
```

### Possible Methods

```text
login()
logout()
getRole()
```

`getRole()` can be abstract so that each child class provides its own implementation.

---

# 11. Student Class

The `Student` class represents a student within the academic system.

### Responsibilities

* Store student-specific information.
* Manage student profile.
* Register for courses.
* Drop courses.
* View registered courses.
* Access academic records.

### Possible Properties

```text
studentId
dateOfBirth
programme
department
address
```

### Possible Methods

```text
registerCourse()
dropCourse()
viewRegisteredCourses()
updateProfile()
getProfile()
getRole()
```

---

# 12. Lecturer Class

The `Lecturer` class represents an academic lecturer.

### Responsibilities

* Store lecturer information.
* Manage assigned academic activities.
* View assigned courses.
* View students in courses.
* Record marks.
* Update marks.

### Possible Properties

```text
lecturerId
department
```

### Possible Methods

```text
recordMark()
updateMark()
viewMarks()
getRole()
```

---

# 13. Administrator Class

The `Administrator` class represents a system administrator.

### Responsibilities

* Manage students.
* Manage lecturers.
* Manage departments.
* Manage courses.
* Access administrative information.

### Possible Methods

```text
manageStudent()
manageCourse()
manageDepartment()
manageLecturer()
getRole()
```

---

# 14. Department Class

The `Department` class represents an academic department.

### Responsibilities

* Store department information.
* Manage associated courses.
* Associate students.
* Associate lecturers.

### Possible Properties

```text
departmentId
name
description
```

### Possible Methods

```text
addCourse()
addStudent()
addLecturer()
getCourses()
getStudents()
getLecturers()
```

---

# 15. Course Class

The `Course` class represents an academic course.

### Responsibilities

* Store course information.
* Associate the course with a department.
* Associate a lecturer.
* Provide course details.

### Possible Properties

```text
courseCode
courseName
creditHours
department
lecturer
```

### Possible Methods

```text
getCourseDetails()
assignLecturer()
getCourseCode()
```

---

# 16. Enrollment Class

The `Enrollment` class represents the relationship between a student and a course.

This is an important domain class because a student can enroll in many courses and a course can have many students.

### Possible Properties

```text
enrollmentId
student
course
enrollmentDate
status
```

### Possible Methods

```text
register()
drop()
isActive()
```

---

# 17. Grade Class

The `Grade` class represents a student's mark for a course.

### Possible Properties

```text
gradeId
mark
course
```

### Possible Methods

```text
updateMark()
getMark()
getStatus()
```

The class may also be extended later if the project introduces additional grading information.

---

# 18. AcademicRecord Class

The `AcademicRecord` class represents the academic performance of a student.

### Responsibilities

* Store student grades.
* Add grades.
* Update grades.
* Calculate average.
* Determine academic status.

### Possible Properties

```text
recordId
student
grades[]
```

### Possible Methods

```text
addGrade()
updateGrade()
calculateAverage()
getOverallStatus()
```

---

# 19. Address Class

The `Address` class represents a student's address information.

### Possible Properties

```text
addressId
province
district
sector
cell
```

### Possible Methods

```text
updateAddress()
getAddress()
```

---

# 20. Model Relationships

The major relationships between the classes are:

```text
Student 1 -------- * Enrollment
Course  1 -------- * Enrollment

Student 1 -------- 1 Address

Student 1 -------- 1 AcademicRecord

AcademicRecord 1 -- * Grade

Department 1 ----- * Course

Department 1 ----- * Student

Department 1 ----- * Lecturer

Course 1 ---------- 1 Lecturer
```

---

# 21. Relationship Explanation

## Student → Enrollment

One student can have multiple enrollments.

```text
Student 1 -------- * Enrollment
```

---

## Course → Enrollment

One course can have multiple enrollments.

```text
Course 1 -------- * Enrollment
```

Together, `Student` and `Course` form a many-to-many relationship through `Enrollment`.

```text
Student
   |
   | 1
   |
   | *
Enrollment
   |
   | *
   |
   | 1
Course
```

---

## Student → Address

Each student has one associated address in the proposed design.

```text
Student 1 -------- 1 Address
```

---

## Student → AcademicRecord

A student has an academic record.

```text
Student 1 -------- 1 AcademicRecord
```

---

## AcademicRecord → Grade

An academic record contains multiple grades.

```text
AcademicRecord 1 -------- * Grade
```

---

## Department → Course

A department can contain multiple courses.

```text
Department 1 -------- * Course
```

---

## Department → Student

A department can contain multiple students.

```text
Department 1 -------- * Student
```

---

## Department → Lecturer

A department can contain multiple lecturers.

```text
Department 1 -------- * Lecturer
```

---

## Course → Lecturer

A course can be assigned to a lecturer.

```text
Course 1 -------- 1 Lecturer
```

The final cardinality should be confirmed by the team based on the implementation requirements.

---

# 22. OOP Architecture

The architecture intentionally demonstrates the required OOP principles.

## 22.1 Encapsulation

Sensitive properties will be private or protected.

Example:

```php
class Student
{
    private string $studentId;
    private string $name;

    public function getStudentId(): string
    {
        return $this->studentId;
    }
}
```

The internal state of an object is therefore controlled through methods.

---

# 23. Inheritance

Inheritance will be used for user types.

```text
User
 ├── Student
 ├── Lecturer
 └── Administrator
```

The child classes inherit common functionality from `User`.

---

# 24. Abstraction

The `User` class will be abstract.

Example conceptual design:

```php
abstract class User
{
    abstract public function getRole(): string;
}
```

Child classes must provide their own implementation of `getRole()`.

---

# 25. Polymorphism

Polymorphism will allow different user objects to respond differently to the same method.

Example:

```php
$users = [
    new Student(...),
    new Lecturer(...),
    new Administrator(...)
];

foreach ($users as $user) {
    echo $user->getRole();
}
```

The same method:

```text
getRole()
```

can produce different results depending on the object.

---

# 26. Interfaces

Interfaces may be introduced where multiple classes need to follow a common contract.

For example:

```php
interface Authenticatable
{
    public function login(): bool;
    public function logout(): void;
}
```

Classes that implement the interface must provide the required methods.

The final interfaces should be selected by the OOP Architect based on the actual implementation.

---

# 27. Constructors

Constructors will be used to initialize objects.

Example:

```php
$student = new Student(
    "ST001",
    "John Doe",
    "john@example.com"
);
```

The constructor ensures that required object information is initialized when the object is created.

---

# 28. Destructors

A destructor may be implemented where object cleanup is necessary.

Example:

```php
public function __destruct()
{
    // Cleanup operations if required
}
```

A destructor should not be used unnecessarily.

---

# 29. Static Members

Static properties or methods may be used for functionality that belongs to the class rather than a particular object.

Example use cases may include:

* Generating counters.
* Shared utility operations.
* Configuration-related functionality.

Static functionality should only be introduced where it provides a clear design benefit.

---

# 30. Exception Handling

The architecture shall support exception handling between the service and controller layers.

Example flow:

```text
User Request
     |
     v
Controller
     |
     v
Service
     |
     +---- Exception
     |
     v
Controller
     |
     v
User-friendly Error Message
```

Possible exceptions include:

```text
DuplicateStudentException
DuplicateEnrollmentException
StudentNotFoundException
CourseNotFoundException
InvalidMarkException
UnauthorizedActionException
```

The exact custom exception classes can be decided during implementation.

---

# 31. Database Layer

The database layer will provide controlled communication between the application and MySQL.

The application should use **PDO** for database access.

A proposed structure is:

```text
config/
└── database.php
```

The database connection should not be created directly inside model classes.

A reusable database connection should be provided to the appropriate data-access components.

---

# 32. Database Responsibilities

The database layer will be responsible for:

* Connecting to MySQL.
* Executing prepared statements.
* Retrieving records.
* Inserting records.
* Updating records.
* Deleting records.
* Handling database-related exceptions.

---

# 33. Security Architecture

Security will be considered at multiple levels.

## Authentication

Users must authenticate before accessing protected features.

## Authorization

Access will depend on the user's role.

```text
Student
   ↓
Student functions

Lecturer
   ↓
Lecturer functions

Administrator
   ↓
Administrative functions
```

## Password Security

Passwords must not be stored as plain text.

PHP password hashing functions should be used.

## SQL Injection Prevention

Database queries should use prepared statements.

## Input Validation

User input should be validated before processing or storage.

---

# 34. Request Flow

A typical request should follow this process:

```text
1. User submits form
          |
          v
2. Controller receives request
          |
          v
3. Controller validates request
          |
          v
4. Controller calls Service
          |
          v
5. Service applies business rules
          |
          v
6. Service uses Model/Data Access
          |
          v
7. Database operation
          |
          v
8. Result returned
          |
          v
9. Controller selects response/view
          |
          v
10. User sees result
```

---

# 35. Example: Course Registration Flow

When a student registers for a course:

```text
Student
   |
   v
Registration Form
   |
   v
EnrollmentController
   |
   v
EnrollmentService
   |
   +---- Check Student
   |
   +---- Check Course
   |
   +---- Check Duplicate Enrollment
   |
   +---- Validate Enrollment
   |
   v
Enrollment Model
   |
   v
MySQL Database
   |
   v
Success / Error Response
   |
   v
Student
```

---

# 36. Example: Recording Marks Flow

```text
Lecturer
   |
   v
Marks Form
   |
   v
ResultController
   |
   v
AcademicService
   |
   +---- Verify Lecturer Authorization
   |
   +---- Verify Student Enrollment
   |
   +---- Validate Mark
   |
   v
Grade / AcademicRecord
   |
   v
MySQL Database
   |
   v
Success / Error Response
```

---

# 37. Example: Academic Average Flow

```text
Student
   |
   v
View Results
   |
   v
ResultController
   |
   v
AcademicService
   |
   v
AcademicRecord
   |
   v
Grade Objects
   |
   v
calculateAverage()
   |
   v
Average Result
```

---

# 38. Proposed Project Structure

The complete project structure is:

```text
student-academic-management-system/
│
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Student.php
│   │   ├── Lecturer.php
│   │   ├── Administrator.php
│   │   ├── Course.php
│   │   ├── Department.php
│   │   ├── Enrollment.php
│   │   ├── Grade.php
│   │   ├── AcademicRecord.php
│   │   └── Address.php
│   │
│   ├── Controllers/
│   │   ├── StudentController.php
│   │   ├── CourseController.php
│   │   ├── DepartmentController.php
│   │   ├── LecturerController.php
│   │   ├── EnrollmentController.php
│   │   ├── ResultController.php
│   │   └── SearchController.php
│   │
│   ├── Services/
│   │   ├── StudentService.php
│   │   ├── CourseService.php
│   │   ├── EnrollmentService.php
│   │   ├── AcademicService.php
│   │   └── SearchService.php
│   │
│   └── Exceptions/
│       ├── DuplicateStudentException.php
│       ├── DuplicateEnrollmentException.php
│       ├── StudentNotFoundException.php
│       ├── CourseNotFoundException.php
│       ├── InvalidMarkException.php
│       └── UnauthorizedActionException.php
│
├── config/
│   └── database.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── assets/
│
├── views/
│   ├── dashboard/
│   ├── students/
│   ├── courses/
│   ├── departments/
│   ├── lecturers/
│   ├── enrollment/
│   └── results/
│
├── tests/
│   └── test-cases.md
│
├── docs/
│   ├── requirements.md
│   ├── use-cases.md
│   ├── architecture.md
│   ├── class-design.md
│   ├── contribution-report.md
│   ├── technical-report.md
│   └── uml/
│       └── class-diagram.png
│
├── README.md
└── .gitignore
```

---

# 39. Separation of Responsibilities

The architecture follows the principle that each layer should have a clear responsibility.

| Layer        | Responsibility                                 |
| ------------ | ---------------------------------------------- |
| Presentation | Display information and collect user input     |
| Controller   | Handle requests and coordinate operations      |
| Service      | Apply business rules and workflows             |
| Model        | Represent academic entities and their behavior |
| Database     | Persist and retrieve data                      |

This separation prevents one class from becoming responsible for the entire system.

---

# 40. Dependency Direction

Dependencies should generally flow downward:

```text
Presentation
      ↓
Controllers
      ↓
Services
      ↓
Models / Data Access
      ↓
Database
```

Lower-level components should not depend on views.

For example:

```text
Student Model
      X
should NOT directly render HTML
```

Instead:

```text
Student
  ↓
StudentService
  ↓
StudentController
  ↓
Student View
```

---

# 41. Team Development Alignment

The architecture supports the eight-member development team.

| Member   | Primary Architectural Responsibility                       |
| -------- | ---------------------------------------------------------- |
| Member 1 | System analysis, requirements, architecture, coordination  |
| Member 2 | OOP architecture, class design, UML                        |
| Member 3 | Student and Address models/module                          |
| Member 4 | Course, Department and Lecturer models/module              |
| Member 5 | Enrollment model and enrollment service                    |
| Member 6 | Grade, AcademicRecord and AcademicService                  |
| Member 7 | Controllers, views, dashboard and search                   |
| Member 8 | Testing, validation, documentation and integration support |

All members should understand the complete architecture even when working on individual modules.

---

# 42. Development Sequence

Development should proceed in the following order:

```text
System Requirements
        |
        v
Use Cases
        |
        v
Architecture
        |
        v
Class Design
        |
        v
UML
        |
        v
Database Design
        |
        v
Core OOP Models
        |
        v
Services
        |
        v
Controllers
        |
        v
Views / UI
        |
        v
Testing
        |
        v
Integration
        |
        v
Documentation
        |
        v
Final Presentation
```

---

# 43. Architectural Constraints

The development team should follow these constraints:

1. Use Object-Oriented PHP.
2. Maintain at least eight meaningful classes.
3. Use appropriate access modifiers.
4. Avoid unnecessary global variables.
5. Avoid placing database queries directly inside views.
6. Avoid placing large business rules inside controllers.
7. Use prepared statements for database operations.
8. Validate user input.
9. Apply role-based authorization.
10. Use exceptions for exceptional conditions.
11. Keep classes focused on their responsibilities.
12. Follow the agreed GitHub structure.

---

# 44. Future Extensibility

The architecture should allow future features such as:

* Attendance management.
* Transcript generation.
* GPA calculation.
* Semester management.
* Notifications.
* Course prerequisites.
* Academic reports.
* Student performance analytics.
* API integration.
* Email notifications.

These features should be added through new modules rather than rewriting the entire system.

---

# 45. Architecture Success Criteria

The architecture will be considered successful if:

* The major system responsibilities are separated.
* At least eight meaningful OOP classes are implemented.
* User types use inheritance.
* Encapsulation is applied.
* Abstraction is demonstrated.
* Polymorphism is demonstrated.
* Controllers coordinate requests.
* Services contain business rules.
* Models represent academic entities.
* Database operations are separated from presentation.
* Exceptions and errors are handled appropriately.
* The architecture supports independent team development.
* The final UML reflects the implemented architecture.

---

# 46. Conclusion

The proposed architecture provides a structured foundation for the Student Academic Management System.

The layered approach separates presentation, request handling, business logic, domain models, and database operations. The OOP model further represents the real-world academic entities and relationships within the institution.

This architecture will serve as the foundation for the next development stage: **detailed class design and UML modeling**.

Member 2 should use this document to create the final class specifications, relationships, inheritance hierarchy, and UML class diagram.
