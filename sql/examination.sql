-- ================================================================
-- Online Examination System - Full Database Schema
-- Built on top of the original online_examination_system.sql,
-- extended with login/auth fields so it can power a real website.
-- Run this whole file once in MySQL (via phpMyAdmin, MySQL CLI, etc).
-- ================================================================

CREATE DATABASE IF NOT EXISTS online_examination_system;
USE online_examination_system;

DROP TABLE IF EXISTS support_tickets;
DROP TABLE IF EXISTS attempt_answers;
DROP TABLE IF EXISTS exam_attempts;
DROP TABLE IF EXISTS exam_questions;
DROP TABLE IF EXISTS question_options;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS admins;

-- =========================
-- 1) Core master tables
-- =========================

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin/teacher accounts that manage exams and questions.
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_title VARCHAR(150) NOT NULL,
    exam_description TEXT,
    duration_minutes INT NOT NULL,
    total_marks INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    question_type ENUM('mcq', 'true_false', 'short_answer') NOT NULL DEFAULT 'mcq',
    correct_answer VARCHAR(255) NOT NULL,
    marks INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE question_options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_label VARCHAR(20) NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_question_options_question
        FOREIGN KEY (question_id) REFERENCES questions(question_id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE exam_questions (
    exam_question_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_id INT NOT NULL,
    question_order INT NOT NULL,
    CONSTRAINT fk_exam_questions_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_exam_questions_question
        FOREIGN KEY (question_id) REFERENCES questions(question_id)
        ON DELETE CASCADE,
    CONSTRAINT uq_exam_question UNIQUE (exam_id, question_id),
    CONSTRAINT uq_exam_question_order UNIQUE (exam_id, question_order)
) ENGINE=InnoDB;

CREATE TABLE exam_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    student_id INT NOT NULL,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at DATETIME NULL,
    status ENUM('in_progress', 'submitted', 'scored') NOT NULL DEFAULT 'in_progress',
    score DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_score DECIMAL(10,2) NOT NULL DEFAULT 0,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    rank_position INT NULL,
    CONSTRAINT fk_exam_attempts_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempts_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE CASCADE,
    CONSTRAINT uq_student_exam_attempt UNIQUE (exam_id, student_id)
) ENGINE=InnoDB;

CREATE TABLE attempt_answers (
    attempt_answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    awarded_marks DECIMAL(10,2) NOT NULL DEFAULT 0,
    answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attempt_answers_attempt
        FOREIGN KEY (attempt_id) REFERENCES exam_attempts(attempt_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attempt_answers_question
        FOREIGN KEY (question_id) REFERENCES questions(question_id)
        ON DELETE CASCADE,
    CONSTRAINT uq_attempt_question UNIQUE (attempt_id, question_id)
) ENGINE=InnoDB;

-- =========================
-- 2) Sample data
-- =========================

INSERT INTO admins (username, full_name, password_hash) VALUES
('admin', 'System Administrator', '$2y$10$4xwdIO9CPHf0Bio8JFWApun5mB05TbC5/6xc3BVGUfDhMmqRg6/ma');

-- Demo student logins all use password: password123
-- (real students created via register.php will get their own unique hash)
INSERT INTO students (roll_no, full_name, email, password_hash) VALUES
('STU001', 'Aarav Sharma', 'aarav@example.com', '$2y$10$TpYm7an8FfqwRHCNZqvnx.dsmhB0elmaFrFAkO2bJXiq4NQwnc/LC'),
('STU002', 'Diya Patel', 'diya@example.com', '$2y$10$TpYm7an8FfqwRHCNZqvnx.dsmhB0elmaFrFAkO2bJXiq4NQwnc/LC'),
('STU003', 'Kabir Singh', 'kabir@example.com', '$2y$10$TpYm7an8FfqwRHCNZqvnx.dsmhB0elmaFrFAkO2bJXiq4NQwnc/LC');

INSERT INTO exams (exam_title, exam_description, duration_minutes, total_marks) VALUES
('DBMS Fundamentals', 'Core concepts of database systems', 60, 10);

INSERT INTO questions (question_text, question_type, correct_answer, marks) VALUES
('Which normal form removes transitive dependency?', 'mcq', '3NF', 2),
('SQL stands for Structured Query Language.', 'true_false', 'TRUE', 1),
('What is the primary key?', 'short_answer', 'Unique identifier', 2),
('Which join returns only matching rows?', 'mcq', 'INNER JOIN', 2),
('A foreign key references the primary key of another table.', 'true_false', 'TRUE', 1),
('What command is used to remove a table structure?', 'mcq', 'DROP TABLE', 2);

INSERT INTO question_options (question_id, option_label, option_text, is_correct) VALUES
(1, 'A', '1NF', 0),
(1, 'B', '2NF', 0),
(1, 'C', '3NF', 1),
(1, 'D', 'BCNF only', 0),
(2, 'A', 'TRUE', 1),
(2, 'B', 'FALSE', 0),
(4, 'A', 'LEFT JOIN', 0),
(4, 'B', 'INNER JOIN', 1),
(4, 'C', 'CROSS JOIN', 0),
(4, 'D', 'SELF JOIN', 0),
(5, 'A', 'TRUE', 1),
(5, 'B', 'FALSE', 0),
(6, 'A', 'DELETE TABLE', 0),
(6, 'B', 'REMOVE TABLE', 0),
(6, 'C', 'DROP TABLE', 1),
(6, 'D', 'TRUNCATE DATABASE', 0);

INSERT INTO exam_questions (exam_id, question_id, question_order) VALUES
(1, 1, 1),
(1, 2, 2),
(1, 3, 3),
(1, 4, 4),
(1, 5, 5),
(1, 6, 6);

-- =========================
-- 3) Reporting views (used by admin/reports.php)
-- =========================

CREATE OR REPLACE VIEW v_attempt_results AS
SELECT
    ea.attempt_id,
    ea.exam_id,
    ea.student_id,
    ea.score,
    ea.max_score,
    ea.percentage,
    ea.rank_position,
    ea.status
FROM exam_attempts ea;

CREATE OR REPLACE VIEW v_exam_summary AS
SELECT
    e.exam_id,
    e.exam_title,
    COUNT(DISTINCT ea.attempt_id) AS total_attempts,
    COUNT(DISTINCT ea.student_id) AS total_students,
    ROUND(AVG(ea.score), 2) AS average_score,
    ROUND(MAX(ea.score), 2) AS highest_score,
    ROUND(MIN(ea.score), 2) AS lowest_score
FROM exams e
LEFT JOIN exam_attempts ea ON ea.exam_id = e.exam_id
GROUP BY e.exam_id, e.exam_title;

-- ================================================================
-- 4) Support Desk & Doubt Resolution Module
-- ================================================================

CREATE TABLE IF NOT EXISTS support_tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    exam_id INT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    admin_response TEXT NULL,
    status ENUM('pending', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    CONSTRAINT fk_tickets_student
        FOREIGN KEY (student_id) REFERENCES students(student_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tickets_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

