# Student Academic Management System (SAMS)
## Test Case Specification & Traceability Report

| Metadata | Details |
| :--- | :--- |
| **Project** | Student Academic Management System (SAMS) |
| **Group** | Group 1 |
| **Technology** | Object-Oriented PHP 8.2+ / PDO (MySQL & SQLite) |
| **Document** | System Test Cases & Traceability Matrix |
| **Role** | QA & Quality Assurance Engineer (Member 8) |
| **Version** | 1.0 |
| **Test Execution Status** | **104 / 104 Tests PASSED (100%)** |

---

## 1. Overview & Test Objectives

This document specifies the formal test cases designed and executed for the **Student Academic Management System (SAMS)**. Each test case validates one or more functional requirements (**FR-01 to FR-18**) and user interactions specified in the Use Case Specification (**UC-01 to UC-20**).

All test cases are linked to automated test methods executed via the custom automated test runner in [`tests/run_tests.php`](run_tests.php), which verified all model behaviors, service rules, data validation, authorization policies, and exception hierarchies.

---

## 2. Requirements & Use Case Traceability Matrix

| Test Case ID | Test Case Name | Target FR | Target UC | Automated Test Method | Result |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-01** | User Authentication & Role Detection | FR-01 | UC-01 | `Tests\AuthTest::test_auth_attempt_succeeds_with_valid_credentials`<br>`Tests\AuthTest::test_auth_attempt_fails_with_invalid_credentials` | **PASS** |
| **TC-02** | Student Registration & ID Uniqueness | FR-02 | UC-04 | `Tests\StudentTest::test_administrator_can_register_student_successfully`<br>`Tests\StudentTest::test_duplicate_student_ids_are_rejected` | **PASS** |
| **TC-03** | View Student Profile & Record Retrieval | FR-03 | UC-02 | `Tests\StudentTest::test_student_belongs_to_correct_department`<br>`Tests\StudentTest::test_student_can_be_searched_by_id` | **PASS** |
| **TC-04** | Update Student Information & Address | FR-04 | UC-03 | `Tests\StudentTest::test_student_information_and_address_can_be_updated`<br>`Tests\StudentTest::test_invalid_input_data_is_rejected` | **PASS** |
| **TC-05** | Department Management & Associations | FR-05 | UC-05 | `Tests\DepartmentTest::test_create_department_succeeds_with_valid_data`<br>`Tests\DepartmentTest::test_duplicate_code_is_rejected`<br>`Tests\DepartmentTest::test_get_courses_returns_courses_belonging_to_department` | **PASS** |
| **TC-06** | Course Creation, Validation & Code Uniqueness | FR-06 | UC-06 | `Tests\CourseTest::test_create_course_succeeds_with_valid_data`<br>`Tests\CourseTest::test_duplicate_course_code_is_rejected`<br>`Tests\CourseTest::test_assign_lecturer_to_course` | **PASS** |
| **TC-07** | Lecturer Registration & Department Assignment | FR-07 | UC-07 | `Tests\LecturerTest::test_register_lecturer_succeeds_with_valid_data`<br>`Tests\LecturerTest::test_duplicate_email_is_rejected`<br>`Tests\LecturerTest::test_associate_department_updates_lecturer` | **PASS** |
| **TC-08** | Course Registration & Duplicate Prevention | FR-08 | UC-08 | `Tests\EnrollmentTest::test_student_can_register_for_course_successfully`<br>`Tests\EnrollmentTest::test_duplicate_active_enrollment_is_prevented` | **PASS** |
| **TC-09** | Course Drop & Status Update | FR-09 | UC-09 | `Tests\EnrollmentTest::test_student_can_drop_course`<br>`Tests\EnrollmentTest::test_dropping_already_dropped_course_throws_exception` | **PASS** |
| **TC-10** | View Registered Courses Roster | FR-10 | UC-10 | `Tests\EnrollmentTest::test_get_student_enrollments`<br>`Tests\EnrollmentTest::test_model_relationships_work_properly` | **PASS** |
| **TC-11** | View Lecturer Assigned Courses & Course Students | FR-07, FR-10 | UC-11, UC-12 | `Tests\LecturerTest::test_get_courses_returns_assigned_courses`<br>`Tests\EnrollmentTest::test_get_students_registered_for_course` | **PASS** |
| **TC-12** | Record Academic Marks & Boundary Validation | FR-11 | UC-13 | `Tests\AcademicResultTest::test_authorized_lecturer_can_record_marks_successfully`<br>`Tests\AcademicResultTest::test_invalid_marks_below_zero_are_rejected`<br>`Tests\AcademicResultTest::test_invalid_marks_above_one_hundred_are_rejected` | **PASS** |
| **TC-13** | Update Academic Marks & Authorization Check | FR-12 | UC-14 | `Tests\AcademicResultTest::test_authorized_lecturer_can_update_marks`<br>`Tests\AcademicResultTest::test_unauthorized_lecturer_cannot_update_marks` | **PASS** |
| **TC-14** | View Student Academic Transcript & Grades | FR-13 | UC-15 | `Tests\AcademicResultTest::test_academic_record_is_correctly_associated_with_student`<br>`Tests\AcademicResultTest::test_model_relationships_work_properly` | **PASS** |
| **TC-15** | Automated GPA & Average Mark Calculation | FR-14 | UC-16 | `Tests\AcademicResultTest::test_calculate_average_from_valid_grades`<br>`Tests\AcademicResultTest::test_grade_automatically_assigned_status_and_letter_grade` | **PASS** |
| **TC-16** | Determine Student Pass/Fail Academic Status | FR-15 | UC-17 | `Tests\AcademicResultTest::test_pass_fail_status_calculated_correctly_for_individual_grade`<br>`Tests\AcademicResultTest::test_overall_academic_record_pass_fail_determination` | **PASS** |
| **TC-17** | Search Students by ID or Full Name | FR-16 | UC-18 | `Tests\SearchTest::test_search_student_by_exact_id`<br>`Tests\SearchTest::test_search_student_by_partial_id`<br>`Tests\SearchTest::test_search_student_by_first_name`<br>`Tests\SearchTest::test_search_student_by_full_name` | **PASS** |
| **TC-18** | Search Courses & Registered Course Rosters | FR-17, FR-18 | UC-19, UC-20 | `Tests\SearchTest::test_search_course_by_code_case_insensitive`<br>`Tests\SearchTest::test_search_course_by_name`<br>`Tests\SearchTest::test_get_students_registered_for_course_by_code`<br>`Tests\SearchTest::test_dropped_students_are_excluded_from_active_roster` | **PASS** |

---

## 3. Detailed Test Case Specifications

### TC-01: User Authentication & Role Detection
- **Requirement Reference**: `FR-01`
- **Use Case Reference**: `UC-01` (Login)
- **Priority**: High (Critical Path)
- **Preconditions**:
  - System database initialized with valid user records in the `users` table.
  - Test accounts exist: `admin` (admin role), `lecturer1` (lecturer role), `student1` (student role).
- **Test Input Data**:
  - Valid: Username `"admin"`, Password `"admin123"`
  - Invalid: Username `"admin"`, Password `"wrongpassword"`
  - Nonexistent: Username `"ghost"`, Password `"unknown"`
- **Execution Steps**:
  1. Instantiate `App\Auth\Auth` with active database connection.
  2. Call `Auth::attempt("admin", "admin123")`.
  3. Verify session contains authenticated user ID and role (`administrator`).
  4. Call `Auth::attempt("admin", "wrongpassword")`.
  5. Call `Auth::logout()` and check session clearance.
- **Expected Results**:
  - Valid login returns `true`, stores user data in session, and `Auth::role()` returns `"administrator"`.
  - Invalid password returns `false` and sets error flash message.
  - Logout unsets session keys and sets `Auth::check()` to `false`.
- **Actual Result**: **PASSED**. Valid credentials authenticate; invalid passwords fail; session cleared on logout.
- **Automated Test**: `Tests\AuthTest::test_auth_attempt_succeeds_with_valid_credentials`, `test_auth_attempt_fails_with_invalid_credentials`, `test_auth_logout_clears_session`.

---

### TC-02: Student Registration & ID Uniqueness
- **Requirement Reference**: `FR-02`
- **Use Case Reference**: `UC-04` (Register Student)
- **Priority**: High
- **Preconditions**:
  - Administrator is logged in.
  - A valid Department exists in the database.
- **Test Input Data**:
  - Student ID: `"STD2026001"`, Name: `"Alice" "Smith"`, Email: `"alice@institution.edu"`, DOB: `"2002-05-15"`, Programme: `"BSc Computer Science"`, Department ID: `1`.
  - Duplicate Attempt: Student ID `"STD2026001"`, Name: `"Bob" "Jones"`, Email: `"bob@institution.edu"`.
- **Execution Steps**:
  1. Call `StudentService::registerStudent()` with valid data.
  2. Query database to confirm student and address record creation.
  3. Attempt to register another student with the identical Student ID `"STD2026001"`.
- **Expected Results**:
  - Initial registration succeeds and returns populated `Student` instance.
  - Duplicate registration raises `App\Exceptions\DuplicateStudentException`.
- **Actual Result**: **PASSED**. Record persisted with generated primary key; duplicate student ID threw `DuplicateStudentException`.
- **Automated Test**: `Tests\StudentTest::test_administrator_can_register_student_successfully`, `test_duplicate_student_ids_are_rejected`.

---

### TC-03: View Student Profile & Record Retrieval
- **Requirement Reference**: `FR-03`
- **Use Case Reference**: `UC-02` (View Student Profile)
- **Priority**: Medium
- **Preconditions**:
  - Student record `"STD-1001"` exists in the database.
- **Test Input Data**:
  - Student ID: `"STD-1001"`.
- **Execution Steps**:
  1. Call `Student::findById("STD-1001")`.
  2. Inspect returned object attributes: `student_id`, `first_name`, `last_name`, `email`, `programme`, `department_id`.
  3. Call `$student->getDepartment()`.
- **Expected Results**:
  - Returns `Student` model instance matching ID `"STD-1001"`.
  - `$student->getDepartment()` returns associated `Department` instance.
- **Actual Result**: **PASSED**. Student profile and department relationship loaded accurately.
- **Automated Test**: `Tests\StudentTest::test_student_belongs_to_correct_department`, `test_student_can_be_searched_by_id`.

---

### TC-04: Update Student Information & Address
- **Requirement Reference**: `FR-04`
- **Use Case Reference**: `UC-03` (Update Student Profile)
- **Priority**: High
- **Preconditions**:
  - Target student exists.
- **Test Input Data**:
  - Updated Email: `"alice.new@institution.edu"`, Updated Address: `"456 University Ave"`, City: `"Kigali"`, Country: `"Rwanda"`.
  - Invalid Email Attempt: `"invalid-email-format"`.
- **Execution Steps**:
  1. Call `StudentService::updateStudent()` with valid modifications.
  2. Re-fetch student and assert properties reflect updated fields.
  3. Call `StudentService::updateStudent()` with invalid email.
- **Expected Results**:
  - Valid updates persist in both `students` and `addresses` tables.
  - Invalid inputs throw `\InvalidArgumentException`.
- **Actual Result**: **PASSED**. Profile fields and address updated; invalid format rejected.
- **Automated Test**: `Tests\StudentTest::test_student_information_and_address_can_be_updated`, `test_invalid_input_data_is_rejected`.

---

### TC-05: Department Management & Associations
- **Requirement Reference**: `FR-05`
- **Use Case Reference**: `UC-05` (Manage Departments)
- **Priority**: High
- **Preconditions**:
  - Administrator privileges active.
- **Test Input Data**:
  - Department Name: `"Computer Science"`, Department Code: `"CS"`.
  - Duplicate Attempt: Code `"CS"`.
- **Execution Steps**:
  1. Call `Department::create(["name" => "Computer Science", "code" => "CS"])`.
  2. Verify persisted department ID.
  3. Attempt creating another department with code `"CS"`.
  4. Call `$department->getCourses()` and `$department->getLecturers()`.
- **Expected Results**:
  - Unique department created successfully.
  - Duplicate department code rejected with `\InvalidArgumentException`.
  - Related courses and lecturers collections returned accurately.
- **Actual Result**: **PASSED**. Department created; duplicate rejected; relationships intact.
- **Automated Test**: `Tests\DepartmentTest::test_create_department_succeeds_with_valid_data`, `test_duplicate_code_is_rejected`, `test_get_courses_returns_courses_belonging_to_department`.

---

### TC-06: Course Creation, Validation & Code Uniqueness
- **Requirement Reference**: `FR-06`
- **Use Case Reference**: `UC-06` (Manage Courses)
- **Priority**: High
- **Preconditions**:
  - Department exists with ID `1`.
- **Test Input Data**:
  - Course Code: `"CS101"`, Title: `"Object-Oriented Programming"`, Credits: `3`, Department ID: `1`.
  - Invalid Credit Attempt: `credits = 0` or `-2`.
  - Duplicate Course Attempt: `"CS101"` (case-insensitive `"cs101"`).
- **Execution Steps**:
  1. Call `CourseService::createCourse()` with valid data.
  2. Call `CourseService::createCourse()` with credits = 0.
  3. Call `CourseService::createCourse()` with existing code `"cs101"`.
- **Expected Results**:
  - Course created and retrievable by code.
  - Zero/negative credits rejected.
  - Duplicate course code rejected regardless of case.
- **Actual Result**: **PASSED**. Course created with valid credits; invalid credits and duplicate codes rejected.
- **Automated Test**: `Tests\CourseTest::test_create_course_succeeds_with_valid_data`, `test_create_course_fails_when_credits_is_zero`, `test_duplicate_course_code_is_rejected`.

---

### TC-07: Lecturer Registration & Department Assignment
- **Requirement Reference**: `FR-07`
- **Use Case Reference**: `UC-07` (Manage Lecturers)
- **Priority**: High
- **Preconditions**:
  - Department exists.
- **Test Input Data**:
  - Staff ID: `"LEC001"`, Name: `"Dr. Alan" "Turing"`, Email: `"alan.turing@institution.edu"`, Department ID: `1`.
  - Invalid Email: `"alan-at-turing"`.
- **Execution Steps**:
  1. Create lecturer with valid attributes.
  2. Assign lecturer to department.
  3. Attempt registration with duplicate or malformed email.
- **Expected Results**:
  - Lecturer record created and linked to department.
  - Malformed email throws `\InvalidArgumentException`.
  - Duplicate email rejected.
- **Actual Result**: **PASSED**. Lecturer registered; department associated; validation enforced.
- **Automated Test**: `Tests\LecturerTest::test_register_lecturer_succeeds_with_valid_data`, `test_register_fails_with_invalid_email`, `test_duplicate_email_is_rejected`.

---

### TC-08: Course Registration & Duplicate Prevention
- **Requirement Reference**: `FR-08`
- **Use Case Reference**: `UC-08` (Register Course)
- **Priority**: High
- **Preconditions**:
  - Student `"STD-1001"` exists.
  - Course `"CS101"` exists.
- **Test Input Data**:
  - Student ID: `"STD-1001"`, Course ID: `1`, Semester: `"Semester 1"`, Academic Year: `"2025/2026"`.
- **Execution Steps**:
  1. Call `EnrollmentService::enrollStudent(studentId, courseId, semester, year)`.
  2. Verify enrollment status is `"active"`.
  3. Call `EnrollmentService::enrollStudent()` again with identical parameters.
- **Expected Results**:
  - First enrollment succeeds and returns `Enrollment` record.
  - Duplicate active enrollment is blocked with `App\Exceptions\DuplicateEnrollmentException`.
- **Actual Result**: **PASSED**. Course registration successful; duplicate registration throws `DuplicateEnrollmentException`.
- **Automated Test**: `Tests\EnrollmentTest::test_student_can_register_for_course_successfully`, `test_duplicate_active_enrollment_is_prevented`.

---

### TC-09: Course Drop & Status Update
- **Requirement Reference**: `FR-09`
- **Use Case Reference**: `UC-09` (Drop Course)
- **Priority**: High
- **Preconditions**:
  - Student has an active enrollment in course ID `1`.
- **Test Input Data**:
  - Enrollment ID: `1`.
- **Execution Steps**:
  1. Call `EnrollmentService::dropCourse(enrollmentId)`.
  2. Verify database record status changed from `"active"` to `"dropped"`.
  3. Attempt calling `EnrollmentService::dropCourse(enrollmentId)` a second time.
- **Expected Results**:
  - Enrollment status updated to `"dropped"`.
  - Dropping an already dropped enrollment throws `\InvalidArgumentException`.
- **Actual Result**: **PASSED**. Enrollment marked dropped; subsequent drop attempts rejected.
- **Automated Test**: `Tests\EnrollmentTest::test_student_can_drop_course`, `test_dropping_already_dropped_course_throws_exception`.

---

### TC-10: View Registered Courses Roster
- **Requirement Reference**: `FR-10`
- **Use Case Reference**: `UC-10` (View Registered Courses)
- **Priority**: Medium
- **Preconditions**:
  - Student enrolled in courses `CS101` (active) and `CS102` (dropped).
- **Test Input Data**:
  - Student ID: `"STD-1001"`.
- **Execution Steps**:
  1. Call `EnrollmentService::getStudentEnrollments("STD-1001")`.
  2. Verify returned list contains Course Code, Title, Credits, and Enrollment Status.
- **Expected Results**:
  - List contains both active and historic enrollments with course details.
- **Actual Result**: **PASSED**. Complete enrollment history retrieved with course objects.
- **Automated Test**: `Tests\EnrollmentTest::test_get_student_enrollments`, `test_model_relationships_work_properly`.

---

### TC-11: View Lecturer Assigned Courses & Course Students
- **Requirement Reference**: `FR-07`, `FR-10`
- **Use Case Reference**: `UC-11` (View Assigned Courses), `UC-12` (View Course Students)
- **Priority**: Medium
- **Preconditions**:
  - Lecturer assigned to course ID `1`.
  - Multiple students enrolled in course ID `1`.
- **Test Input Data**:
  - Lecturer ID: `1`, Course ID: `1`.
- **Execution Steps**:
  1. Call `Lecturer::getCourses(1)`.
  2. Call `EnrollmentService::getEnrolledStudents(1)`.
- **Expected Results**:
  - Lecturer assigned course list includes Course ID `1`.
  - Student roster returns all actively enrolled students in the course.
- **Actual Result**: **PASSED**. Assigned courses and active student roster retrieved.
- **Automated Test**: `Tests\LecturerTest::test_get_courses_returns_assigned_courses`, `Tests\EnrollmentTest::test_get_students_registered_for_course`.

---

### TC-12: Record Academic Marks & Boundary Validation
- **Requirement Reference**: `FR-11`
- **Use Case Reference**: `UC-13` (Record Marks)
- **Priority**: High (Academic Integrity)
- **Preconditions**:
  - Lecturer assigned to Course `CS101`.
  - Student `"STD-1001"` actively enrolled in `CS101`.
- **Test Input Data**:
  - Valid: Mark = `85.5`, Lecturer ID = `1`.
  - Boundary Low: Mark = `-5.0`.
  - Boundary High: Mark = `105.0`.
- **Execution Steps**:
  1. Call `AcademicService::recordMark(studentId, courseId, lecturerId, 85.5)`.
  2. Attempt recording mark `-5.0`.
  3. Attempt recording mark `105.0`.
- **Expected Results**:
  - Valid mark stored; letter grade assigned (`"A"`), status set to `"PASS"`.
  - Marks `< 0` and `> 100` rejected with `App\Exceptions\InvalidMarkException`.
- **Actual Result**: **PASSED**. Valid mark recorded; boundary violations threw `InvalidMarkException`.
- **Automated Test**: `Tests\AcademicResultTest::test_authorized_lecturer_can_record_marks_successfully`, `test_invalid_marks_below_zero_are_rejected`, `test_invalid_marks_above_one_hundred_are_rejected`.

---

### TC-13: Update Academic Marks & Authorization Check
- **Requirement Reference**: `FR-12`
- **Use Case Reference**: `UC-14` (Update Marks)
- **Priority**: High
- **Preconditions**:
  - Existing grade record exists for Course `1` assigned to Lecturer `1`.
  - Lecturer `2` is NOT assigned to Course `1`.
- **Test Input Data**:
  - Authorized Update: Lecturer ID `1`, New Mark = `92.0`.
  - Unauthorized Attempt: Lecturer ID `2`, New Mark = `75.0`.
- **Execution Steps**:
  1. Attempt updating grade using Lecturer ID `2`.
  2. Update grade using assigned Lecturer ID `1`.
- **Expected Results**:
  - Unauthorized lecturer attempt raises `App\Exceptions\UnauthorizedActionException`.
  - Authorized lecturer successfully updates mark, letter grade, and grade points.
- **Actual Result**: **PASSED**. Unauthorized modification blocked; authorized update persisted.
- **Automated Test**: `Tests\AcademicResultTest::test_unauthorized_lecturer_cannot_update_marks`, `test_authorized_lecturer_can_update_marks`.

---

### TC-14: View Student Academic Transcript & Grades
- **Requirement Reference**: `FR-13`
- **Use Case Reference**: `UC-15` (View Academic Results)
- **Priority**: Medium
- **Preconditions**:
  - Student has recorded grades across multiple courses.
- **Test Input Data**:
  - Student ID: `"STD-1001"`.
- **Execution Steps**:
  1. Call `AcademicService::getStudentResults("STD-1001")`.
  2. Inspect returned `AcademicRecord` model.
- **Expected Results**:
  - Contains array of `Grade` objects with course code, title, credits, numerical mark, letter grade, and status.
- **Actual Result**: **PASSED**. Full academic transcript retrieved with relational metadata.
- **Automated Test**: `Tests\AcademicResultTest::test_academic_record_is_correctly_associated_with_student`, `test_model_relationships_work_properly`.

---

### TC-15: Automated GPA & Average Mark Calculation
- **Requirement Reference**: `FR-14`
- **Use Case Reference**: `UC-16` (Calculate Average)
- **Priority**: High
- **Preconditions**:
  - Student has grades: Course 1 (Credits: 3, Mark: 80.0), Course 2 (Credits: 4, Mark: 70.0).
- **Test Input Data**:
  - Grade 1: Mark `80.0`, Grade 2: Mark `70.0`.
- **Execution Steps**:
  1. Instantiate `AcademicRecord` for student with these grades.
  2. Call `$record->calculateAverage()`.
  3. Call `$record->calculateGPA()`.
- **Expected Results**:
  - Unweighted Average = `(80 + 70) / 2 = 75.00`.
  - Weighted GPA computed based on credit weights.
- **Actual Result**: **PASSED**. Arithmetic average calculated as `75.00` without manual calculation.
- **Automated Test**: `Tests\AcademicResultTest::test_calculate_average_from_valid_grades`, `test_grade_automatically_assigned_status_and_letter_grade`.

---

### TC-16: Determine Student Pass/Fail Academic Status
- **Requirement Reference**: `FR-15`
- **Use Case Reference**: `UC-17` (Determine Pass/Fail)
- **Priority**: High
- **Preconditions**:
  - Minimum passing grade benchmark = `50.0`.
- **Test Input Data**:
  - Case A (Passing): Marks `65.0`, `72.0`, `55.0` (All >= 50.0, Average >= 50.0).
  - Case B (Failing): Marks `42.0`, `38.0`, `48.0` (All < 50.0).
- **Execution Steps**:
  1. Evaluate `Grade::determineStatus()` for mark `65.0` and mark `42.0`.
  2. Evaluate `$record->determineOverallStatus()` for Case A and Case B.
- **Expected Results**:
  - Mark `65.0` -> `"PASS"`; Mark `42.0` -> `"FAIL"`.
  - Case A overall status -> `"PASS"`; Case B overall status -> `"FAIL"`.
- **Actual Result**: **PASSED**. Individual and cumulative pass/fail status determined accurately.
- **Automated Test**: `Tests\AcademicResultTest::test_pass_fail_status_calculated_correctly_for_individual_grade`, `test_overall_academic_record_pass_fail_determination`.

---

### TC-17: Search Students by ID or Full Name
- **Requirement Reference**: `FR-16`
- **Use Case Reference**: `UC-18` (Search Student)
- **Priority**: Medium
- **Preconditions**:
  - Students exist: `"Alice Smith"` (`"STD2026001"`), `"Bob Johnson"` (`"STD2026002"`).
- **Test Input Data**:
  - Exact ID: `"STD2026001"`.
  - Partial ID: `"2026002"`.
  - First Name: `"Alice"`.
  - Full Name: `"Bob Johnson"`.
- **Execution Steps**:
  1. Call `StudentService::searchStudents()` with each query string.
- **Expected Results**:
  - Returns matching student records for exact ID, partial ID, first name, and full name.
- **Actual Result**: **PASSED**. Flexible matching succeeds across ID and name parameters.
- **Automated Test**: `Tests\SearchTest::test_search_student_by_exact_id`, `test_search_student_by_partial_id`, `test_search_student_by_first_name`, `test_search_student_by_full_name`.

---

### TC-18: Search Courses & Registered Course Rosters
- **Requirement Reference**: `FR-17`, `FR-18`
- **Use Case Reference**: `UC-19` (Search Course), `UC-20` (Search Students by Course)
- **Priority**: Medium
- **Preconditions**:
  - Course `"CS101"` exists with active students and one dropped student.
- **Test Input Data**:
  - Course Code Search: `"cs101"` (lowercase).
  - Course Name Search: `"Object-Oriented"`.
  - Course Roster Query: Code `"CS101"`.
- **Execution Steps**:
  1. Call `CourseService::searchByCode("cs101")`.
  2. Call `CourseService::searchByName("Object-Oriented")`.
  3. Call `EnrollmentService::getStudentsByCourseCode("CS101")`.
- **Expected Results**:
  - Course search is case-insensitive and supports substring search.
  - Enrolled student roster returns currently active students and excludes dropped students.
- **Actual Result**: **PASSED**. Case-insensitive search matches course; dropped students excluded from active roster.
- **Automated Test**: `Tests\SearchTest::test_search_course_by_code_case_insensitive`, `test_search_course_by_name`, `test_get_students_registered_for_course_by_code`, `test_dropped_students_are_excluded_from_active_roster`.

---

## 4. Test Execution Summary

```text
================================================================================
                    SAMS AUTOMATED TEST EXECUTION SUMMARY
================================================================================
Test Suite Engine:         PHP CLI (tests/run_tests.php)
Database Engine Tested:    SQLite & MySQL Compatibility Driver (PDO)
Total Test Cases Defined:  18 (TC-01 through TC-18)
Total Automated Unit Tests:104
Automated Tests Passed:    104
Automated Tests Failed:    0
Test Coverage:             100% of Functional Requirements (FR-01 to FR-18)
                           100% of Use Cases (UC-01 to UC-20)
Execution Result:          ALL PASS (Zero Regressions)
================================================================================
```
