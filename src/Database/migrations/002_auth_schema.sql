-- ============================================================
-- ADWT Academic Management System — Authentication & User Schema
-- Migration: 002_auth_schema.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255)  NOT NULL,
    email         VARCHAR(255)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('student', 'lecturer', 'administrator') NOT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE lecturers
    ADD COLUMN user_id INT UNSIGNED NULL AFTER email,
    ADD CONSTRAINT uq_lecturers_user_id UNIQUE (user_id),
    ADD CONSTRAINT fk_lecturers_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE SET NULL ON UPDATE CASCADE;
