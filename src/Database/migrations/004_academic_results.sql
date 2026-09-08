-- ============================================================
-- ADWT Academic Management System — Academic Results & Records Schema
-- Migration: 004_academic_results.sql
-- ============================================================

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
    student_id         INT UNSIGNED NOT NULL,
    course_id          INT UNSIGNED NOT NULL,
    lecturer_id        INT UNSIGNED NULL,
    mark               DECIMAL(5, 2) NOT NULL,
    letter_grade       VARCHAR(5)   NOT NULL,
    status             VARCHAR(10)  NOT NULL DEFAULT 'PASS',
    remarks            TEXT         NULL,
    created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_grades_student_course UNIQUE (student_id, course_id),
    CONSTRAINT fk_grades_academic_record FOREIGN KEY (academic_record_id)
        REFERENCES academic_records (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_student FOREIGN KEY (student_id)
        REFERENCES students (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_course FOREIGN KEY (course_id)
        REFERENCES courses (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_lecturer FOREIGN KEY (lecturer_id)
        REFERENCES lecturers (id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_grades_student (student_id),
    INDEX idx_grades_course (course_id),
    INDEX idx_grades_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
