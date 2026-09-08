-- ============================================================
-- ADWT Academic Management System — Address Schema
-- Migration: 003_address.sql
-- ============================================================

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
