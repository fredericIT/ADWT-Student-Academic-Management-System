# Student Academic Management System (SAMS)

## Use Case Specification

**Project:** Student Academic Management System  
**Group:** Group 1  
**Technology:** Object-Oriented PHP  
**Document:** Use Case Specification  
**Version:** 1.0  

---

# 1. Introduction

This document defines the main interactions between users and the Student Academic Management System (SAMS).

The purpose of the use-case analysis is to identify:

* System actors.
* Actor responsibilities.
* Main system functions.
* User interactions.
* Preconditions.
* Main flows.
* Alternative flows.
* Expected outcomes.

---

# 2. System Actors

The system has three primary actors.

## 2.1 Student

The Student interacts with the system to:

* Log in.
* View personal information.
* Update personal information.
* Register for courses.
* Drop courses.
* View registered courses.
* View academic results.

---

## 2.2 Lecturer

The Lecturer interacts with the system to:

* Log in.
* View assigned courses.
* View students registered for courses.
* Record marks.
* Update marks.
* View academic results related to assigned courses.

---

## 2.3 Administrator

The Administrator interacts with the system to:

* Log in.
* Manage students.
* Manage lecturers.
* Manage departments.
* Manage courses.
* Search records.
* View academic information.
* Perform administrative operations.

---

# 3. Use Case Diagram

The following diagram represents the main system actors and their interactions.

```text
                         +--------------------------------------+
                         | Student Academic Management System  |
                         |                                      |
 Student ----------------|--> Login                            |
    |                    |                                      |
    |--------------------|--> View Profile                     |
    |--------------------|--> Update Profile                   |
    |--------------------|--> Register Course                  |
    |--------------------|--> Drop Course                      |
    |--------------------|--> View Registered Courses           |
    |--------------------|--> View Academic Results             |
                         |                                      |
 Lecturer ---------------|--> Login                            |
    |                    |--> View Assigned Courses             |
    |--------------------|--> View Course Students              |
    |--------------------|--> Record Marks                     |
    |--------------------|--> Update Marks                     |
    |--------------------|--> View Academic Results             |
                         |                                      |
 Administrator ----------|--> Login                            |
    |                    |--> Manage Students                   |
    |--------------------|--> Manage Lecturers                  |
    |--------------------|--> Manage Departments                |
    |--------------------|--> Manage Courses                   |
    |--------------------|--> Search Students                  |
    |--------------------|--> Search Courses                   |
    |--------------------|--> View Academic Information         |
                         |                                      |
                         +--------------------------------------+
```

---

# 4. Use Case Summary

| ID    | Use Case                  | Actor(s)                         |
| ----- | ------------------------- | -------------------------------- |
| UC-01 | Login                     | Student, Lecturer, Administrator |
| UC-02 | View Student Profile      | Student, Administrator           |
| UC-03 | Update Student Profile    | Student, Administrator           |
| UC-04 | Register Student          | Administrator                    |
| UC-05 | Manage Departments        | Administrator                    |
| UC-06 | Manage Courses            | Administrator                    |
| UC-07 | Manage Lecturers          | Administrator                    |
| UC-08 | Register Course           | Student                          |
| UC-09 | Drop Course               | Student                          |
| UC-10 | View Registered Courses   | Student                          |
| UC-11 | View Assigned Courses     | Lecturer                         |
| UC-12 | View Course Students      | Lecturer                         |
| UC-13 | Record Marks              | Lecturer                         |
| UC-14 | Update Marks              | Lecturer                         |
| UC-15 | View Academic Results     | Student, Lecturer, Administrator |
| UC-16 | Calculate Average         | System                           |
| UC-17 | Determine Pass/Fail       | System                           |
| UC-18 | Search Student            | Administrator                    |
| UC-19 | Search Course             | Administrator                    |
| UC-20 | Search Students by Course | Administrator, Lecturer          |

---

# 5. Detailed Use Cases

## UC-01: Login

**Actor:** Student, Lecturer, Administrator

**Description:**
Allows a registered user to access the system using valid credentials.

### Preconditions

* The user must have a registered account.
* The system must be available.

### Main Flow

1. User opens the login page.
2. User enters username/email and password.
3. User submits the login form.
4. System validates the credentials.
5. System identifies the user's role.
6. System creates an authenticated session.
7. System redirects the user to the appropriate dashboard.

### Alternative Flow

If the credentials are invalid:

1. System rejects the login.
2. System displays an error message.
3. User is allowed to try again.

### Postconditions

The user is authenticated and has access to role-specific functionality.

---

# 6. UC-02: View Student Profile

**Actor:** Student, Administrator

**Description:**
Allows an authorized user to view student information.

### Preconditions

* Student must exist.
* User must have permission to view the record.

### Main Flow

1. User selects a student profile.
2. System retrieves the student's information.
3. System displays the profile.

### Information Displayed

* Student ID.
* Name.
* Email.
* Date of birth.
* Programme.
* Department.
* Address.

### Alternative Flow

If the student does not exist:

```text
Student record not found.
```

---

# 7. UC-03: Update Student Profile

**Actor:** Student, Administrator

**Description:**
Allows authorized users to update student information.

### Preconditions

* Student record must exist.
* User must have permission.

### Main Flow

1. User opens the student profile.
2. User selects update.
3. System displays editable information.
4. User changes the required information.
5. User submits the changes.
6. System validates the information.
7. System saves the updated information.
8. System displays a success message.

### Alternative Flow

If validation fails:

1. System rejects the submitted information.
2. System displays the relevant validation error.
3. User corrects the information.

---

# 8. UC-04: Register Student

**Actor:** Administrator

**Description:**
Allows an administrator to create a new student record.

### Preconditions

* Administrator must be authenticated.

### Main Flow

1. Administrator opens student registration.
2. Administrator enters student information.
3. System validates the information.
4. System checks whether the student ID already exists.
5. System creates the student record.
6. System saves the record.
7. System displays a success message.

### Alternative Flow

If the student ID already exists:

```text
Student ID already exists.
```

The system does not create a duplicate record.

---

# 9. UC-05: Manage Departments

**Actor:** Administrator

**Description:**
Allows the administrator to manage academic departments.

### Main Flow

1. Administrator opens department management.
2. Administrator creates, views, or updates a department.
3. System validates the information.
4. System stores or updates the department.
5. System displays the result.

### Possible Operations

* Add department.
* View department.
* Update department.
* Associate courses.
* Associate students.
* Associate lecturers.

---

# 10. UC-06: Manage Courses

**Actor:** Administrator

**Description:**
Allows the administrator to manage courses.

### Main Flow

1. Administrator opens course management.
2. Administrator enters course information.
3. System validates the information.
4. System checks course code uniqueness.
5. System creates or updates the course.
6. System saves the information.

### Course Information

* Course code.
* Course name.
* Credit hours.
* Department.
* Lecturer.

### Alternative Flow

If the course code already exists:

```text
Course code already exists.
```

---

# 11. UC-07: Manage Lecturers

**Actor:** Administrator

**Description:**
Allows the administrator to manage lecturer information.

### Main Flow

1. Administrator opens lecturer management.
2. Administrator enters lecturer information.
3. System validates the information.
4. System creates or updates the lecturer record.
5. System saves the record.

---

# 12. UC-08: Register Course

**Actor:** Student

**Description:**
Allows a student to register for an available course.

### Preconditions

* Student must be authenticated.
* Course must exist.
* Course must be available for registration.

### Main Flow

1. Student opens course registration.
2. Student selects a course.
3. System verifies the student.
4. System verifies the course.
5. System checks whether the student is already enrolled.
6. System creates an enrollment record.
7. System confirms successful enrollment.

### Alternative Flow

If the student is already enrolled:

```text
Student is already enrolled in this course.
```

The system does not create another enrollment.

---

# 13. UC-09: Drop Course

**Actor:** Student

**Description:**
Allows a student to drop an active course enrollment.

### Preconditions

* Student must be authenticated.
* Student must be enrolled in the course.

### Main Flow

1. Student views registered courses.
2. Student selects a course.
3. Student chooses the drop option.
4. System verifies the enrollment.
5. System changes the enrollment status.
6. System confirms the course has been dropped.

### Alternative Flow

If no active enrollment exists:

```text
Active enrollment not found.
```

---

# 14. UC-10: View Registered Courses

**Actor:** Student

**Description:**
Allows a student to view their registered courses.

### Main Flow

1. Student logs in.
2. Student opens registered courses.
3. System retrieves active enrollments.
4. System displays the registered courses.

### Displayed Information

* Course code.
* Course name.
* Credit hours.
* Lecturer.
* Enrollment status.

---

# 15. UC-11: View Assigned Courses

**Actor:** Lecturer

**Description:**
Allows a lecturer to view courses assigned to them.

### Preconditions

* Lecturer must be authenticated.

### Main Flow

1. Lecturer opens assigned courses.
2. System identifies the lecturer.
3. System retrieves assigned courses.
4. System displays the courses.

---

# 16. UC-12: View Course Students

**Actor:** Lecturer

**Description:**
Allows a lecturer to view students registered in an assigned course.

### Preconditions

* Lecturer must be authenticated.
* Course must be assigned to the lecturer.

### Main Flow

1. Lecturer selects an assigned course.
2. System retrieves active enrollments.
3. System retrieves associated students.
4. System displays the students.

---

# 17. UC-13: Record Marks

**Actor:** Lecturer

**Description:**
Allows an authorized lecturer to record a student's academic mark.

### Preconditions

* Lecturer must be authenticated.
* Lecturer must be authorized for the course.
* Student must be registered for the course.

### Main Flow

1. Lecturer selects a course.
2. Lecturer selects a student.
3. Lecturer enters the student's mark.
4. System validates the mark.
5. System verifies the enrollment.
6. System creates the grade.
7. System saves the grade.
8. System confirms successful recording.

### Alternative Flow

If the mark is invalid:

```text
Invalid mark. Mark must be within the allowed range.
```

No invalid mark is saved.

---

# 18. UC-14: Update Marks

**Actor:** Lecturer

**Description:**
Allows an authorized lecturer to update an existing academic mark.

### Preconditions

* Existing grade must exist.
* Lecturer must be authorized.
* Student must be associated with the course.

### Main Flow

1. Lecturer selects the course.
2. Lecturer selects the student.
3. System retrieves the existing mark.
4. Lecturer enters the new mark.
5. System validates the new mark.
6. System updates the grade.
7. System confirms the update.

### Alternative Flow

If the lecturer is not authorized:

```text
You are not authorized to update this result.
```

The update is rejected.

---

# 19. UC-15: View Academic Results

**Actor:** Student, Lecturer, Administrator

**Description:**
Allows authorized users to view academic results.

### Main Flow

1. User opens academic results.
2. System verifies authorization.
3. System retrieves relevant academic records.
4. System displays grades and academic information.

### Access Rules

* Student: Can view their own results.
* Lecturer: Can view results related to authorized courses.
* Administrator: Can view appropriate academic records.

---

# 20. UC-16: Calculate Average

**Actor:** System

**Description:**
The system calculates the student's average academic mark.

### Preconditions

* Academic results must exist.

### Main Flow

1. System retrieves applicable grades.
2. System processes the marks.
3. System calculates the average.
4. System returns the calculated average.

### Example

```text
Marks:
70
80
60

Average:
(70 + 80 + 60) / 3 = 70
```

The exact academic calculation rules should follow the grading rules adopted by the project.

---

# 21. UC-17: Determine Pass/Fail

**Actor:** System

**Description:**
Determines whether a student has passed or failed based on the configured pass criteria.

### Main Flow

1. System retrieves the student's relevant marks.
2. System calculates the required academic result.
3. System compares the result with the configured pass threshold.
4. System determines the status.
5. System displays:

```text
PASS
```

or

```text
FAIL
```

The exact pass threshold should be configured according to the academic rules adopted by the institution/project.

---

# 22. UC-18: Search Student

**Actor:** Administrator

**Description:**
Allows an administrator to search for a student.

### Search Criteria

* Student ID.
* Student name.

### Main Flow

1. Administrator opens student search.
2. Administrator enters a search value.
3. System searches student records.
4. System displays matching records.

### Alternative Flow

If no student is found:

```text
No student records found.
```

---

# 23. UC-19: Search Course

**Actor:** Administrator

**Description:**
Allows an administrator to search for a course by course code.

### Main Flow

1. Administrator opens course search.
2. Administrator enters a course code.
3. System searches course records.
4. System displays the matching course.

### Alternative Flow

If no course exists:

```text
Course not found.
```

---

# 24. UC-20: Search Students by Course

**Actor:** Administrator, Lecturer

**Description:**
Allows authorized users to find students registered for a specific course.

### Main Flow

1. User selects or searches for a course.
2. System verifies that the course exists.
3. System retrieves active enrollments.
4. System retrieves associated students.
5. System displays the list of students.

### Alternative Flow

If no students are registered:

```text
No students are currently registered for this course.
```

---

# 25. Main System Workflow

The overall academic workflow is:

```text
                    +------------------+
                    | Register Student |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Student Profile  |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Course Selection |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Course Enrollment|
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Course Attendance|
                    | / Academic Work  |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Record Marks     |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Academic Record  |
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Calculate Average|
                    +--------+---------+
                             |
                             v
                    +------------------+
                    | Determine        |
                    | Pass / Fail      |
                    +------------------+
```

---

# 26. Role-Based Access Summary

| Function                  | Student | Lecturer | Administrator |
| ------------------------- | :-----: | :------: | :-----------: |
| Login                     |    ✓    |     ✓    |       ✓       |
| View Own Profile          |    ✓    |     ✓    |       ✓       |
| Update Own Profile        |    ✓    |     ✓    |       ✓       |
| Register Student          |    —    |     —    |       ✓       |
| Manage Departments        |    —    |     —    |       ✓       |
| Manage Courses            |    —    |     —    |       ✓       |
| Manage Lecturers          |    —    |     —    |       ✓       |
| Register Course           |    ✓    |     —    |       —       |
| Drop Course               |    ✓    |     —    |       —       |
| View Registered Courses   |    ✓    |     —    |       ✓       |
| View Assigned Courses     |    —    |     ✓    |       ✓       |
| View Course Students      |    —    |     ✓    |       ✓       |
| Record Marks              |    —    |     ✓    |       ✓*      |
| Update Marks              |    —    |     ✓    |       ✓*      |
| View Academic Results     |    ✓    |     ✓    |       ✓       |
| Calculate Average         |  System |  System  |     System    |
| Determine Pass/Fail       |  System |  System  |     System    |
| Search Students           |    —    |     ✓    |       ✓       |
| Search Courses            |    —    |     ✓    |       ✓       |
| Search Students by Course |    —    |     ✓    |       ✓       |

`*` Administrator access should follow the authorization rules implemented by the team.

---

# 27. Use Case Dependencies

Some use cases depend on others.

```text
Login
  |
  +----> Student Profile
  |
  +----> Course Registration
  |
  +----> Academic Results


Register Student
  |
  v
Student Profile
  |
  v
Register Course
  |
  v
Enrollment
  |
  v
Record Marks
  |
  v
Academic Record
  |
  +----> Calculate Average
  |
  +----> Determine Pass/Fail
```

---

# 28. Important System Rules

The following rules apply across the use cases:

1. Users must authenticate before accessing protected functions.
2. Users must only access functionality permitted by their role.
3. Student IDs must be unique.
4. Course codes must be unique.
5. Students cannot have duplicate active enrollment in the same course.
6. Marks must be validated before storage.
7. Only authorized users can modify academic results.
8. Students can view their own academic results.
9. Academic calculations must be performed by the system.
10. Invalid operations must generate appropriate errors or exceptions.

---

# 29. Expected Use Case Outcomes

After successful implementation:

* Students can manage their academic registration.
* Lecturers can manage marks for their courses.
* Administrators can manage academic information.
* The system can calculate academic averages.
* The system can determine pass/fail status.
* Users can retrieve academic information efficiently.
* Unauthorized operations are rejected.
* Invalid data is prevented from entering the system.

---

# 30. Conclusion

The use cases defined in this document provide the functional foundation for the Student Academic Management System.

They will guide:

* Class design.
* UML development.
* Database design.
* PHP implementation.
* User interface development.
* Testing.
* Demonstration and final presentation.

The use cases should be reviewed against the implemented system to ensure that every required function is covered.
