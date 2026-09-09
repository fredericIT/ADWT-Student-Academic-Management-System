-- ============================================================
-- Student Academic Management System (SAMS)
-- Initial Seed Data
-- ============================================================

-- 1. Departments
INSERT INTO departments (id, name, code, description) VALUES
(1, 'Computer Science & Software Engineering', 'CSSE', 'Department of Computer Science, Software Engineering and Information Systems'),
(2, 'Information Technology', 'IT', 'Department of Applied Information Technology and Networking'),
(3, 'Electronics & Telecommunication', 'ET', 'Department of Electronics and Telecommunication Engineering')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 2. Lecturers
INSERT INTO lecturers (id, first_name, last_name, email, department_id) VALUES
(1, 'Jean', 'Mugisha', 'jean.mugisha@adwt.ac.rw', 1),
(2, 'Marie', 'Uwase', 'marie.uwase@adwt.ac.rw', 1),
(3, 'David', 'Kwizera', 'david.kwizera@adwt.ac.rw', 2)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- 3. Courses
INSERT INTO courses (id, name, code, description, credits, department_id) VALUES
(1, 'Object-Oriented Programming (PHP)', 'CS201', 'Core Object-Oriented software engineering in PHP', 4, 1),
(2, 'Database Management Systems', 'CS202', 'Relational database design, SQL, normalization and transactions', 3, 1),
(3, 'Web Technologies & Frameworks', 'IT201', 'Modern web architecture, REST APIs and frontend design', 3, 2),
(4, 'Data Structures & Algorithms', 'CS203', 'Algorithms, asymptotic analysis, search and graph structures', 4, 1)
ON DUPLICATE KEY UPDATE code=VALUES(code);

-- 4. Course-Lecturer Allocations
INSERT INTO course_lecturer (course_id, lecturer_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 1)
ON DUPLICATE KEY UPDATE assigned_at=CURRENT_TIMESTAMP;

-- 5. Students
INSERT INTO students (id, student_id, first_name, last_name, email, department_id) VALUES
(1, 'ST001', 'Eric', 'Habimana', 'eric.habimana@student.adwt.ac.rw', 1),
(2, 'ST002', 'Aline', 'Mutoni', 'aline.mutoni@student.adwt.ac.rw', 1),
(3, 'ST003', 'Kevin', 'Ndahiro', 'kevin.ndahiro@student.adwt.ac.rw', 2)
ON DUPLICATE KEY UPDATE student_id=VALUES(student_id);

-- 6. Addresses
INSERT INTO addresses (id, student_id, province, district, sector, cell) VALUES
(1, 1, 'Kigali City', 'Nyarugenge', 'Nyamirambo', 'Rugarama'),
(2, 2, 'Kigali City', 'Gasabo', 'Kimironko', 'Kibagabaga'),
(3, 3, 'Northern Province', 'Musanze', 'Muhoza', 'Ruhengeri')
ON DUPLICATE KEY UPDATE student_id=VALUES(student_id);

-- 7. Enrollments
INSERT INTO enrollments (id, student_id, course_id, status) VALUES
(1, 1, 1, 'active'),
(2, 1, 2, 'active'),
(3, 2, 1, 'active'),
(4, 2, 3, 'active'),
(5, 3, 3, 'active')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- 8. Academic Records & Grades
INSERT INTO academic_records (id, student_id) VALUES
(1, 1),
(2, 2),
(3, 3)
ON DUPLICATE KEY UPDATE student_id=VALUES(student_id);

INSERT INTO grades (id, academic_record_id, student_id, course_id, lecturer_id, mark, letter_grade, status) VALUES
(1, 1, 1, 1, 1, 85.50, 'A', 'PASS'),
(2, 1, 1, 2, 2, 74.00, 'B', 'PASS'),
(3, 2, 2, 1, 1, 91.00, 'A', 'PASS'),
(4, 2, 2, 3, 3, 68.50, 'C', 'PASS')
ON DUPLICATE KEY UPDATE mark=VALUES(mark);

-- 9. Users (passwords: admin123, lecturer123, student123)
INSERT INTO users (id, username, email, password_hash, role, student_id, lecturer_id) VALUES
(1, 'admin', 'admin@adwt.ac.rw', '$2y$10$3LYwbclG2XjokJaE6tlvVOqjD5SW425wzsorIgc2e8a9L8lyX8ydy', 'administrator', NULL, NULL),
(2, 'lecturer1', 'jean.mugisha@adwt.ac.rw', '$2y$10$awYL2UQTZWF00SlhfrzrYegZRu8fxmQ974EHzw38jck/GxNTaIj1e', 'lecturer', NULL, 1),
(3, 'student1', 'eric.habimana@student.adwt.ac.rw', '$2y$10$/o8pgCfGmxmyYiCNtsZVSOLLa8GAmesdTzVAzQPK2LsJletna.Aza', 'student', 1, NULL)
ON DUPLICATE KEY UPDATE username=VALUES(username);
