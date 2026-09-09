-- ============================================================
-- Student Academic Management System (SAMS)
-- Unified Master Database Schema
-- Compatible with MySQL and SQLite
-- ============================================================

CREATE TABLE IF NOT EXISTS departments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255)  NOT NULL,
    code        VARCHAR(20)   NOT NULL,
    description TEXT          NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_departments_name UNIQUE (name),
    CONSTRAINT uq_departments_code UNIQUE (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lecturers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(100)  NOT NULL,
    last_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL,
    department_id INT UNSIGNED  NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_lecturers_email UNIQUE (email),
    CONSTRAINT fk_lecturers_department FOREIGN KEY (department_id)
        REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255)  NOT NULL,
    code          VARCHAR(20)   NOT NULL,
    description   TEXT          NULL,
    credits       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    department_id INT UNSIGNED  NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_courses_code UNIQUE (code),
    CONSTRAINT fk_courses_department FOREIGN KEY (department_id)
        REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS course_lecturer (
    course_id   INT UNSIGNED NOT NULL,
    lecturer_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (course_id, lecturer_id),
    CONSTRAINT fk_cl_course   FOREIGN KEY (course_id)   REFERENCES courses   (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cl_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS students (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(50)   NOT NULL,
    first_name    VARCHAR(100)  NOT NULL,
    last_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL,
    department_id INT UNSIGNED  NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_students_student_id UNIQUE (student_id),
    CONSTRAINT uq_students_email      UNIQUE (email),
    CONSTRAINT fk_students_department FOREIGN KEY (department_id)
        REFERENCES departments (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS addresses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    province    VARCHAR(100) NOT NULL,
    district    VARCHAR(100) NOT NULL,
    sector      VARCHAR(100) NOT NULL,
    cell        VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_addresses_student UNIQUE (student_id),
    CONSTRAINT fk_addresses_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS enrollments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    course_id       INT UNSIGNED NOT NULL,
    enrollment_date TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status          VARCHAR(20)  NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_enrollments_course FOREIGN KEY (course_id)
        REFERENCES courses (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS academic_records (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_academic_records_student UNIQUE (student_id),
    CONSTRAINT fk_academic_records_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS grades (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    academic_record_id INT UNSIGNED NOT NULL,
    course_id          INT UNSIGNED NOT NULL,
    lecturer_id        INT UNSIGNED NULL,
    mark               DECIMAL(5,2) NOT NULL,
    grade_letter       VARCHAR(2)   NOT NULL,
    recorded_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_grade_record_course UNIQUE (academic_record_id, course_id),
    CONSTRAINT fk_grades_record FOREIGN KEY (academic_record_id)
        REFERENCES academic_records (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_course FOREIGN KEY (course_id)
        REFERENCES courses (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_lecturer FOREIGN KEY (lecturer_id)
        REFERENCES lecturers (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(20)  NOT NULL,
    student_id    INT UNSIGNED NULL,
    lecturer_id   INT UNSIGNED NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_username UNIQUE (username),
    CONSTRAINT uq_users_email    UNIQUE (email),
    CONSTRAINT fk_users_student  FOREIGN KEY (student_id)  REFERENCES students (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_users_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturers (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
