# Online Examination System — Comprehensive Viva Preparation & Architecture Guide

> **Prepared for:** Final Project Viva Voce & Technical Defense  
> **Student:** Aryan Prasher (Registration ID: 12524232) — *Lead: Student Examination & Evaluation Module*  
> **Project Title:** Online Examination System  
> **Tech Stack:** PHP 8.0+, MySQL 8.0+, HTML5, CSS3, JavaScript (ES6), Apache / XAMPP, PDO  
> **Database Name:** `online_examination_system` (9 Tables + 2 Views)

---

## Table of Contents
1. [The 30-Second Elevator Pitch (How to Introduce Your Project)](#1-the-30-second-elevator-pitch)
2. [Project Team & Contribution Breakdown](#2-project-team--contribution-breakdown)
3. [System Architecture & Technology Stack](#3-system-architecture--technology-stack)
4. [Complete Database Engineering (All 9 Tables + 2 Views)](#4-complete-database-engineering)
5. [End-to-End Operational Workflow & Module Mechanics](#5-end-to-end-operational-workflow)
6. [Core Technical Algorithms (Timer, Evaluation, Ranking)](#6-core-technical-algorithms)
7. [Security & Defensive Programming Implementation](#7-security--defensive-programming)
8. [Complete File-by-File Codebase Directory Map](#8-complete-file-by-file-directory-map)
9. [Top 35 Viva Questions & Model Answers](#9-top-35-viva-questions--model-answers)
10. [Quick Revision Cheat Sheet (Last-Minute Flashcards)](#10-quick-revision-cheat-sheet)

---

## 1. The 30-Second Elevator Pitch

When the external examiner asks: **"Tell me about your project in brief."**

> **Your Answer:**  
> *"Good morning/afternoon, Sir/Ma'am. Our project is an **Online Examination System** built with **PHP 8, MySQL 8, and PDO**. It is a full-cycle educational platform that automates the entire examination workflow:
> - **Administrators** can design timed exams, manage an extensive multi-type question bank (MCQ, True/False, Short Answer), track student performance through analytical reports, and resolve student doubts via a dedicated Support Desk.
> - **Students** can register, log in, attempt exams with an anti-cheat server-synchronized countdown timer, receive instant automated evaluation upon submission, view detailed question-by-question answer keys, and check their relative standing on a live leaderboard powered by MySQL's `DENSE_RANK()` window function.
> My individual contribution was developing the core **Student Examination and Evaluation Module**, which handles the active exam session, secure server-side timer enforcement, automated SQL grading, and rank computation."*

---

## 2. Project Team & Contribution Breakdown

| Team Member | Registration ID | Assigned Module | Key Files Developed |
|---|---|---|---|
| **Aryan Prasher** *(You)* | **12524232** | **Student Exam & Evaluation Engine** | `student/exam.php`, `student/submit.php`, `student/result.php`, `student/dashboard.php` |
| **Kumail Abbas** | 12524542 | Authentication & Access Control | `login.php`, `register.php`, `logout.php`, `admin/login.php`, `includes/functions.php` |
| **Divansh Sharma** | 12527898 | Admin & Examination Management | `admin/dashboard.php`, `admin/exams.php`, `admin/exam_form.php`, `admin/questions.php`, `admin/reports.php` |
| **Shani Kumar Gupta** | *(Team Member)* | Student Support & Doubt Resolution | `student/support.php`, `admin/support.php`, `sql/setup_support.php` |

### What to say about your individual role (Aryan Prasher):
> *"I built the examination engine from the ground up. My responsibilities included:
> 1. Creating the stateful exam interface in `student/exam.php` with a dual-layer timer (client-side visual countdown and server-side timestamp validation).
> 2. Engineering the auto-submission and auto-grading script in `student/submit.php`, implementing atomic multi-row answer persistence (`ON DUPLICATE KEY UPDATE`), case-insensitive answer validation in SQL, and aggregate mark calculations.
> 3. Calculating dynamic rank positions using MySQL 8's `DENSE_RANK() OVER (...)` window function with tie-breakers.
> 4. Designing the student results and leaderboard interface in `student/result.php`."*

---

## 3. System Architecture & Technology Stack

```
[ Client Browser ] 
       │  HTTP / HTTPS (GET & POST with CSRF tokens)
       ▼
[ Web Server: Apache 2.4 (XAMPP) ]
       │  Executes PHP scripts
       ▼
[ Application Layer: PHP 8.x ]
  ├── Session Management (RBAC: Student vs Admin)
  ├── CSRF Validation & HTML Entity Escaping
  └── Business Logic (Timer, Grading Math, Exam State)
       │  PDO (PHP Data Objects) with Prepared Statements
       ▼
[ Database Layer: MySQL 8.0+ ]
  ├── 9 Relational Tables (InnoDB, Foreign Keys, CASCADE rules)
  ├── 2 Views (v_attempt_results, v_exam_summary)
  └── Analytical Window Functions (DENSE_RANK)
```

### Why this Tech Stack?
- **PHP 8+**: Server-side scripting language known for fast execution, native session handling, and robust database abstraction.
- **MySQL 8+**: Relational database supporting strict foreign key constraints, ACID compliance (InnoDB), and advanced SQL window functions like `DENSE_RANK()`.
- **PDO (PHP Data Objects)**: Used instead of `mysqli` because it offers database independence, native prepared statements to neutralize SQL injection, and exception-based error reporting (`PDO::ERRMODE_EXCEPTION`).
- **Vanilla CSS & JavaScript**: No heavy third-party UI dependencies (like Bootstrap or React), resulting in zero external build steps, instantaneous load times, and complete code ownership.

---

## 4. Complete Database Engineering

The database `online_examination_system` contains **9 tables** and **2 views**. All tables use the **InnoDB** storage engine to support transactions and foreign key referential integrity.

### 4.1 Summary of All 9 Tables

| # | Table Name | Primary Key | Description / Purpose |
|---|---|---|---|
| 1 | `students` | `student_id` | Stores student accounts, credentials, and profile information. |
| 2 | `admins` | `admin_id` | Stores administrator accounts for back-office exam management. |
| 3 | `exams` | `exam_id` | Master table for exams (title, description, duration, visibility). |
| 4 | `questions` | `question_id` | Central question repository (text, question type, correct answer, marks). |
| 5 | `question_options` | `option_id` | Stores choices (A, B, C, D or True/False) linked to questions. |
| 6 | `exam_questions` | `exam_question_id` | Associative/junction table linking questions to exams with a display sequence. |
| 7 | `exam_attempts` | `attempt_id` | Stores exam attempt sessions, start/end timestamps, scores, and ranks. |
| 8 | `attempt_answers` | `attempt_answer_id` | Stores the student's individual answers and awarded marks per question. |
| 9 | `support_tickets` | `ticket_id` | Stores student queries/doubts and administrative responses. |

---

### 4.2 Detailed Schema of Each Table

#### 1. `students` (Student Master)
```sql
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```
- **Key Constraints:** `roll_no` and `email` are `UNIQUE` to prevent duplicate registrations.
- **Security:** `password_hash` stores 60-character bcrypt strings generated by PHP's `password_hash()`.

#### 2. `admins` (Admin Master)
```sql
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```
- **Demo Record:** `username: admin`, `password: admin123`.

#### 3. `exams` (Exam Master)
```sql
CREATE TABLE exams (
    exam_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_title VARCHAR(150) NOT NULL,
    exam_description TEXT,
    duration_minutes INT NOT NULL,
    total_marks INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```
- **Field Note:** `is_active = 1` makes the exam visible to students; `0` hides it without deleting attempt history. `duration_minutes` controls the countdown.

#### 4. `questions` (Question Repository)
```sql
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    question_type ENUM('mcq', 'true_false', 'short_answer') NOT NULL DEFAULT 'mcq',
    correct_answer VARCHAR(255) NOT NULL,
    marks INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```
- **Enum Values:** Supports 3 question formats (`mcq`, `true_false`, `short_answer`).

#### 5. `question_options` (Options for MCQ & True/False)
```sql
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
```
- **Relationship:** Many-to-One with `questions`. If a question is deleted, its options are automatically deleted (`ON DELETE CASCADE`).

#### 6. `exam_questions` (Junction Table: Exams ↔ Questions)
```sql
CREATE TABLE exam_questions (
    exam_question_id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_id INT NOT NULL,
    question_order INT NOT NULL,
    CONSTRAINT fk_exam_questions_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_questions_question
        FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    CONSTRAINT uq_exam_question UNIQUE (exam_id, question_id),
    CONSTRAINT uq_exam_question_order UNIQUE (exam_id, question_order)
) ENGINE=InnoDB;
```
- **Critical Constraints:**
  - `uq_exam_question (exam_id, question_id)`: Prevents adding the same question twice to an exam.
  - `uq_exam_question_order (exam_id, question_order)`: Guarantees unique question numbers (no duplicate question #1, #2, etc.).

#### 7. `exam_attempts` (Student Exam Sessions)
```sql
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
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_attempts_student
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    CONSTRAINT uq_student_exam_attempt UNIQUE (exam_id, student_id)
) ENGINE=InnoDB;
```
- **Critical Constraint:** `uq_student_exam_attempt UNIQUE (exam_id, student_id)` ensures **one attempt per student per exam** at the database level.

#### 8. `attempt_answers` (Student Responses & Item Scores)
```sql
CREATE TABLE attempt_answers (
    attempt_answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    awarded_marks DECIMAL(10,2) NOT NULL DEFAULT 0,
    answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_attempt_answers_attempt
        FOREIGN KEY (attempt_id) REFERENCES exam_attempts(attempt_id) ON DELETE CASCADE,
    CONSTRAINT fk_attempt_answers_question
        FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    CONSTRAINT uq_attempt_question UNIQUE (attempt_id, question_id)
) ENGINE=InnoDB;
```
- **Constraint:** `uq_attempt_question UNIQUE (attempt_id, question_id)` guarantees one recorded answer per question in a given attempt.

#### 9. `support_tickets` (Support & Doubt Resolution)
```sql
CREATE TABLE support_tickets (
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
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_exam
        FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE SET NULL
) ENGINE=InnoDB;
```
- **Foreign Key Logic:** `exam_id` is nullable. If an exam is deleted, the ticket remains for auditing and inquiry history (`ON DELETE SET NULL`).

---

### 4.3 Database Views (2 Virtual Tables)

#### View 1: `v_attempt_results`
```sql
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
```
- **Why used:** Provides a simplified read-only projection of attempt metrics for reports without exposing raw timestamps or session internals.

#### View 2: `v_exam_summary`
```sql
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
```
- **Why used:** Encapsulates aggregation calculations (`AVG`, `MAX`, `MIN`, `COUNT`) into an optimized virtual table for the Admin Reports dashboard (`admin/reports.php`).

---

### 4.4 Entity-Relationship (ER) Diagram

```text
  +------------------+         1 : N         +--------------------+
  |     students     | ────────────────────< |   exam_attempts    |
  +------------------+                       +--------------------+
           │ 1                                         │ 1
           │                                           │
           │ 1 : N                                     │ 1 : N
           ▼                                           ▼
  +------------------+                       +--------------------+
  |  support_tickets | >── [0..1:N] ──┐      |  attempt_answers   |
  +------------------+                │      +--------------------+
                                      │                │ N : 1
                                      ▼                ▼
                               +--------------+  1:N +--------------------+
                               |    exams     | ───< |   exam_questions   |
                               +--------------+      +--------------------+
                                                               │ N : 1
                                                               ▼
                               +--------------------+ 1:N    +--------------------+
                               |  question_options  | >────  |     questions      |
                               +--------------------+        +--------------------+
```

---

## 5. End-to-End Operational Workflow

### Workflow 1: Exam Creation (Admin Flow)
1. **Admin Login:** Admin enters credentials (`admin/login.php`). Authenticated via `password_verify()`. Admin session initialized (`$_SESSION['admin_id']`).
2. **Create Exam:** Admin navigates to `admin/exam_form.php`. Inputs title, description, duration (minutes), total marks, and visibility (`is_active`). Inserts record into `exams`.
3. **Add Questions:** Admin redirected to `admin/questions.php?exam_id=X`. Selects question type (`mcq`, `true_false`, or `short_answer`), inputs prompt, options, correct answer, and allocated marks.
4. **Transaction Execution:** Inside a database transaction (`$pdo->beginTransaction()`):
   - Inserts into `questions`.
   - Inserts choices into `question_options`.
   - Attaches question to exam in `exam_questions` with sequential `question_order`.
   - Commits transaction (`$pdo->commit()`).

---

### Workflow 2: Taking an Exam & Anti-Cheat Timer (Student Flow)
1. **Student Login:** Student signs in using Roll Number or Email (`login.php`). Session initialized (`$_SESSION['student_id']`).
2. **Dashboard:** Student views active exams (`student/dashboard.php`). Status indicator shows *Start Exam*, *Resume Exam*, or *Scored: X%*.
3. **Initiating / Resuming Exam:** Student opens `student/exam.php?exam_id=X`.
   - Checks if attempt exists in `exam_attempts`.
   - If not, inserts row with `started_at = NOW()` and `status = 'in_progress'`.
   - **Deadline Math:** The server calculates remaining seconds:
     $$\text{Deadline} = \text{strtotime}(started\_at) + (duration\_minutes \times 60)$$
     $$\text{SecondsLeft} = \text{Deadline} - \text{time}()$$
   - If `SecondsLeft <= 0`, immediately redirects to `student/submit.php?auto=1`.
   - If `SecondsLeft > 0`, renders questions and initializes JavaScript countdown with `secondsLeft`.
4. **Timer Expiry:** When client timer hits `00:00`, JavaScript triggers `formEl.submit()`. Even if the student tampers with JavaScript or disables it, the server enforces the deadline upon submission.

---

### Workflow 3: Automated Grading & Ranking (Evaluation Flow)
When submitted to `student/submit.php`:
1. **CSRF Validation:** Validates form token via `csrfCheck()`.
2. **Answer Persistence:** Iterates through all questions associated with the exam. Writes to `attempt_answers` using `ON DUPLICATE KEY UPDATE`.
3. **Automated Marking (SQL Batch):**
   ```sql
   UPDATE attempt_answers aa
   JOIN questions q ON q.question_id = aa.question_id
   SET aa.is_correct = CASE
           WHEN UPPER(TRIM(aa.answer_text)) = UPPER(TRIM(q.correct_answer)) THEN 1
           ELSE 0
       END,
       aa.awarded_marks = CASE
           WHEN UPPER(TRIM(aa.answer_text)) = UPPER(TRIM(q.correct_answer)) THEN q.marks
           ELSE 0
       END
   WHERE aa.attempt_id = ?
   ```
4. **Total & Percentage Calculation:** Sums awarded marks and maximum possible marks. Computes percentage rounded to 2 decimals.
5. **Session Finalization:** Updates `exam_attempts` setting `status = 'scored'`, `submitted_at = NOW()`, score, and percentage.
6. **Dynamic Ranking:** Recalculates `rank_position` for all students who completed that exam using MySQL's `DENSE_RANK()`.
7. **Instant Feedback:** Redirects student to `student/result.php?exam_id=X` displaying score, Pass/Fail status (40% threshold), question breakdown with checkmarks/crosses, and the top 10 leaderboard.

---

### Workflow 4: Doubt Resolution (Support Desk Flow)
1. Student opens `student/support.php`, selects an exam (optional), enters subject and detailed doubt.
2. Saved in `support_tickets` with `status = 'pending'`.
3. Admin dashboard (`admin/dashboard.php`) shows real-time badge with pending ticket count.
4. Admin opens `admin/support.php`, filters by Pending/Resolved, writes an explanation or answer, and clicks *Submit Reply & Resolve*.
5. Ticket updates to `status = 'resolved'` with `resolved_at = NOW()`.
6. Student immediately sees the admin's answer highlighted in green on their support screen.

---

## 6. Core Technical Algorithms

### 6.1 Server-Synchronized Timer Algorithm
```text
Step 1: On page load, fetch attempt.started_at from MySQL.
Step 2: Compute $deadline = strtotime($attempt['started_at']) + ($exam['duration_minutes'] * 60).
Step 3: Compute $secondsLeft = $deadline - time().
Step 4: If $secondsLeft <= 0:
            Force auto-submission.
        Else:
            Pass $secondsLeft to JavaScript as integer literal.
Step 5: Client JS runs setInterval every 1000ms:
            secondsLeft--;
            Render MM:SS;
            If secondsLeft <= 0:
                clearInterval;
                form.submit();
```
*Why this prevents cheating:* If a student refreshes the browser, closes the tab, or edits the JavaScript variable in Developer Tools, the server recalculates the remaining time against the immutable `started_at` database timestamp. Time lost while away is never refunded.

---

### 6.2 Case-Insensitive String Matching Logic
In `student/submit.php`, answers are normalized:
```sql
UPPER(TRIM(aa.answer_text)) = UPPER(TRIM(q.correct_answer))
```
- `TRIM()` strips leading and trailing whitespaces.
- `UPPER()` eliminates case discrepancies (e.g., `"true"`, `"True"`, and `"TRUE"` all evaluate identically).

---

### 6.3 Ranking Algorithm with Tie-Breaker
In `student/submit.php`:
```sql
UPDATE exam_attempts ea
JOIN (
    SELECT
        attempt_id,
        DENSE_RANK() OVER (
            PARTITION BY exam_id
            ORDER BY score DESC, submitted_at ASC, attempt_id ASC
        ) AS rank_position
    FROM exam_attempts
    WHERE exam_id = ? AND status = 'scored'
) r ON r.attempt_id = ea.attempt_id
SET ea.rank_position = r.rank_position
```
- `PARTITION BY exam_id`: Isolates ranking per exam (scores in Exam A do not affect ranks in Exam B).
- `ORDER BY score DESC`: Highest score gets Rank 1.
- `submitted_at ASC` *(First Tie-Breaker)*: If two students achieve identical scores, the student who finished faster gets the better rank.
- `attempt_id ASC` *(Second Tie-Breaker)*: Deterministic tie-breaker in case of identical timestamps.
- **Why `DENSE_RANK()` instead of `RANK()`:**
  - `RANK()` leaves gaps after ties (e.g., 1, 2, 2, 4).
  - `DENSE_RANK()` produces continuous rank numbers without gaps (e.g., 1, 2, 2, 3), which is standard academic grading practice.

---

## 7. Security & Defensive Programming

| Vulnerability / Threat | How the System Defends Against It | Code Location |
|---|---|---|
| **SQL Injection (SQLi)** | 100% of queries use PDO Prepared Statements with bound parameters (`?`). PDO emulation is disabled (`PDO::ATTR_EMULATE_PREPARES => false`). | `config/db.php`, throughout all files |
| **Password Theft / Breach** | Passwords hashed using modern bcrypt algorithm via PHP's `password_hash($pwd, PASSWORD_DEFAULT)`. No plain-text passwords stored. | `register.php:42`, `login.php:19` |
| **Cross-Site Scripting (XSS)** | All user-supplied outputs rendered in HTML pass through the helper function `h()`, which applies `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. | `includes/functions.php:35` |
| **Cross-Site Request Forgery (CSRF)** | Hidden cryptographically secure token generated per session (`bin2hex(random_bytes(32))`). Validated on all POST requests using timing-attack-safe `hash_equals()`. | `includes/functions.php:62-74` |
| **Broken Access Control (RBAC)** | Role-based authentication enforced at the top of every sensitive script: `requireStudentLogin()` and `requireAdminLogin()`. Unauthorized requests redirected to login. | `includes/functions.php:44, 55` |
| **Exam Time Tampering** | Countdown deadline is computed against the database timestamp `started_at` on the server clock, preventing client-side timer manipulation. | `student/exam.php:37-43` |
| **Duplicate Exam Attempts** | Protected at both application level and database level via `UNIQUE KEY (exam_id, student_id)`. | `sql/examination.sql:105` |

---

## 8. Complete File-by-File Codebase Directory Map

```text
online-exam-system/
├── config/
│   └── db.php                  # PDO database connection, charset config, exception handling
├── includes/
│   ├── functions.php           # Global helpers: sessions, RBAC guards, CSRF tokens, XSS escaping, appUrl
│   ├── header.php              # Global HTML head, meta tags, responsive navigation bar
│   └── footer.php              # Global footer tag & closing HTML tags
├── assets/
│   └── css/
│       └── style.css           # Custom CSS styling (retro scholastic theme, cards, badges, responsive grid)
├── sql/
│   ├── examination.sql         # Master database DDL schema, tables, foreign keys, views, demo data
│   ├── import_database.php     # One-click browser/CLI database installer utility
│   ├── setup_support.php       # Dedicated setup script for support_tickets table
│   └── support_tickets.sql     # Standalone support ticket schema migration
├── student/
│   ├── dashboard.php           # Student portal showing available, ongoing, and completed exams
│   ├── exam.php                # Active exam interface with question rendering and synchronized countdown
│   ├── submit.php              # Exam submission handler, auto-evaluator, total calculator, rank generator
│   ├── result.php              # Question-by-question answer key breakdown & exam leaderboard
│   └── support.php             # Student query desk to create doubts and view admin replies
├── admin/
│   ├── login.php               # Administrator credential authentication
│   ├── logout.php              # Administrator session termination
│   ├── dashboard.php           # Admin metrics overview (exams, students, attempts, pending doubts)
│   ├── exams.php               # Exam management table (hide/unhide, delete, edit link, questions link)
│   ├── exam_form.php           # Create and edit exam metadata form
│   ├── questions.php           # Question manager (add MCQ/TF/Short answer, attach to exam, remove)
│   ├── reports.php             # Comprehensive score reports using database views (v_exam_summary)
│   └── support.php             # Administrative doubt resolution console with filter tabs
├── index.php                   # Public landing page with role-based routing
├── login.php                   # Student login page
├── logout.php                  # Student session destruction
├── register.php                # Student account creation with validation and hashing
├── PROJECT_REPORT.md           # Formal project documentation report
└── VIVA_GUIDE.md               # This ultimate viva preparation guide
```

---

## 9. Top 35 Viva Questions & Model Answers

### Category A: Database & SQL Questions

#### Q1: How many tables are in your database? Name them.
**Answer:** There are **9 tables** and **2 views**. The tables are:
1. `students` (Student credentials and profile)
2. `admins` (Admin credentials)
3. `exams` (Exam titles, durations, marks)
4. `questions` (Question text, type, correct answers)
5. `question_options` (MCQ / True-False choices)
6. `exam_questions` (Junction table linking questions to exams with ordering)
7. `exam_attempts` (Student exam session records, scores, ranks)
8. `attempt_answers` (Student answers and awarded marks)
9. `support_tickets` (Student queries and admin resolutions)

#### Q2: What is the purpose of the junction table `exam_questions`?
**Answer:** It resolves a **Many-to-Many (M:N) relationship** between `exams` and `questions`. One exam can have many questions, and a single question can theoretically be reused across multiple exams. It also stores `question_order` so questions appear in an exact sequence.

#### Q3: What is the difference between `ON DELETE CASCADE` and `ON DELETE SET NULL` in your project?
**Answer:**
- `ON DELETE CASCADE` is used where child records cannot exist without the parent. For example, if an `exam` is deleted, its `exam_attempts`, `exam_questions`, and `attempt_answers` are automatically deleted to prevent orphaned records.
- `ON DELETE SET NULL` is used in `support_tickets.exam_id`. If an exam is deleted, we still want to keep the student's support query history for institutional records, so `exam_id` becomes `NULL` while preserving the ticket.

#### Q4: Why did you use `DENSE_RANK()` instead of `RANK()` or `ROW_NUMBER()`?
**Answer:**
- `ROW_NUMBER()` arbitrarily assigns distinct numbers (1, 2, 3) even if students have the exact same score and submit time.
- `RANK()` leaves gaps after a tie (e.g., if two students tie for rank 1, the next student gets rank 3: 1, 1, 3).
- `DENSE_RANK()` does not leave gaps (1, 1, 2), which is the standard, fair ranking convention in academic institutions.

#### Q5: What database views did you create, and why?
**Answer:** We created two views:
1. `v_attempt_results`: Projects scored attempts for reporting.
2. `v_exam_summary`: Aggregates total attempts, distinct students, average score, highest score, and lowest score per exam using `GROUP BY`.
Views simplify complex multi-table SQL queries, optimize code reusability across pages, and encapsulate business metrics.

#### Q6: How do you prevent a student from taking an exam more than once?
**Answer:** At the database level, we defined a unique constraint:
`CONSTRAINT uq_student_exam_attempt UNIQUE (exam_id, student_id)`.
At the application level in `student/exam.php`, we check if an attempt exists and whether its status is already `submitted` or `scored`; if so, we redirect the student to their result page.

---

### Category B: PHP & Backend Logic Questions

#### Q7: Why did you choose PDO over `mysqli`?
**Answer:**
1. **Database Portability:** PDO supports 12 different database drivers (MySQL, PostgreSQL, SQLite, Oracle), while `mysqli` is strictly MySQL-only.
2. **Named Parameters:** PDO allows cleaner prepared statements.
3. **Exception Handling:** PDO provides comprehensive object-oriented error handling via `PDOException`.
4. **Fetch Modes:** Built-in fetch modes like `FETCH_ASSOC` return clean associative arrays automatically.

#### Q8: How does the countdown timer work, and what happens if a student refreshes the browser?
**Answer:** The timer is calculated on the server clock:
When the student first enters, `started_at` is recorded in MySQL. On every page load, `student/exam.php` calculates:
$$\text{Deadline} = \text{started\_at} + (\text{duration\_minutes} \times 60)$$
$$\text{Seconds Left} = \text{Deadline} - \text{time}()$$
If the student refreshes or closes the browser, the clock continues ticking on the server. When they reopen the page, `Seconds Left` reflects the remaining time. If the deadline has passed, the server immediately triggers auto-submission.

#### Q9: How does the automatic grading logic handle short-answer and case sensitivity?
**Answer:** In `student/submit.php`, the evaluation query uses:
`UPPER(TRIM(aa.answer_text)) = UPPER(TRIM(q.correct_answer))`.
This strips accidental spaces at the beginning and end, and converts both strings to uppercase so case variations like `"sql"`, `"Sql"`, and `"SQL"` match correctly.

#### Q10: What does `ON DUPLICATE KEY UPDATE` do in `student/submit.php`?
**Answer:** Because `attempt_answers` has a unique constraint on `(attempt_id, question_id)`, using `INSERT ... ON DUPLICATE KEY UPDATE` allows us to either insert a new answer row or update an existing answer if one was previously recorded. This prevents duplicate key errors during resubmissions.

#### Q11: How do you handle database transactions, and where?
**Answer:** We use transactions in `admin/questions.php` when adding a new question. Creating a question requires inserting into `questions`, then inserting into `question_options`, and finally inserting into `exam_questions`. We wrap these in `$pdo->beginTransaction()` and `$pdo->commit()`. If any step fails, we can rollback, ensuring no partial or corrupted data is saved.

---

### Category C: Security Questions

#### Q12: How do you prevent SQL Injection?
**Answer:** We use **PDO Prepared Statements** with parameter binding for all queries with dynamic data. In prepared statements, the SQL query template is compiled by the database engine first. User inputs are sent separately as pure data parameters, meaning malicious SQL input (like `' OR '1'='1`) is treated strictly as string data, never as executable SQL commands. We also disabled emulated prepares (`PDO::ATTR_EMULATE_PREPARES => false`).

#### Q13: How do you store and verify passwords?
**Answer:**
- **Storage:** We use `password_hash($password, PASSWORD_DEFAULT)`, which generates a salted bcrypt hash.
- **Verification:** During login, we verify the plain password against the stored hash using `password_verify($password, $hash)`.
We never use outdated, vulnerable algorithms like plain MD5 or SHA1.

#### Q14: What is CSRF, and how did you prevent it?
**Answer:** **CSRF (Cross-Site Request Forgery)** occurs when an unauthorized website tricks an authenticated user's browser into executing an unwanted action on our application.
We prevent this by generating a unique cryptographic token per session using `bin2hex(random_bytes(32))`. This token is embedded as a hidden input in every form and validated in POST requests using timing-safe `hash_equals()`.

#### Q15: What is XSS, and how is it mitigated in your code?
**Answer:** **XSS (Cross-Site Scripting)** occurs when malicious scripts are injected into web pages and executed in other users' browsers.
We mitigate this using our custom helper function `h($string)` which runs `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. It escapes HTML characters (`<`, `>`, `&`, `"`, `'`) into safe HTML entities (`&lt;`, `&gt;`, etc.), preventing script execution.

#### Q16: How do you protect administrative pages from unauthorized student access?
**Answer:** We implemented **Role-Based Access Control (RBAC)** using session authentication guards. At the start of every admin script, we call `requireAdminLogin()`, which checks `isset($_SESSION['admin_id'])`. If false, execution terminates immediately with a redirect to `/admin/login.php`. Students have a separate `$_SESSION['student_id']` guard (`requireStudentLogin()`).

---

### Category D: System Design & Project Architecture Questions

#### Q17: Why did you not use a framework like Laravel or CodeIgniter?
**Answer:** Developing in Core PHP 8 with PDO demonstrates an in-depth understanding of fundamental web mechanics: HTTP request/response lifecycles, manual session handling, direct relational database modeling, and raw SQL queries (including window functions and joins). It also keeps the application lightweight and dependency-free, running out-of-the-box on any standard XAMPP server.

#### Q18: What is the role of `includes/functions.php`?
**Answer:** It acts as the central utility library for the application. It initializes the PHP session, defines redirection helpers (`redirect()`), relative path resolvers (`appUrl()`), sanitization helpers (`h()`), session guard functions (`requireStudentLogin`, `requireAdminLogin`), and CSRF token generation and validation.

#### Q19: How does `appUrl()` work, and why was it necessary?
**Answer:** Because XAMPP projects can be hosted in different folder names (e.g., `localhost/online-exam/` or `localhost/php-practice/online-exam-system (1)/`), hardcoding absolute links like `/login.php` breaks navigation. `appUrl()` dynamically compares `__DIR__` against `$_SERVER['DOCUMENT_ROOT']` to determine the relative project path and prefixes all internal links and redirects correctly.

#### Q20: What are the current limitations of this project?
**Answer:**
1. Short answers require exact text matches; descriptive or semantic answers cannot be evaluated.
2. No negative marking support (incorrect answers yield 0 marks, not negative).
3. Questions and choices are presented in a fixed order rather than randomized.
4. No automated webcam or tab-switch proctoring.
5. Reports cannot currently be exported directly to PDF or Excel files.

#### Q21: What future enhancements would you propose?
**Answer:**
1. Adding configurable negative marking per exam.
2. Question and option shuffling per student using randomized seeds.
3. Real-time proctoring with tab-switch detection and webcam snapshots.
4. AI-based NLP grading for descriptive and essay answers.
5. Automated PDF report card generation using libraries like FPDF or Dompdf.

---

### Category E: Viva Scenarios & Edge Cases

#### Q22: What happens if a student loses internet connection during the exam?
**Answer:** Because the countdown is computed from the server's `started_at` timestamp, the clock continues running. If the student reconnects before the deadline, they can return to `student/exam.php` and resume their exam. However, if they reconnect after the deadline, the server detects that `secondsLeft <= 0` and automatically redirects them to submission.

#### Q23: Can a student inspect the browser and change their score before submitting?
**Answer:** No. The student form only submits their raw selected answers (`answer[question_id] = "option"`). Scores are **never calculated on the client side**. The server independently reads the submitted answers, fetches the answer key from the `questions` table in the database, awards marks, and calculates the total.

#### Q24: What happens if an admin deletes an exam while students are taking it?
**Answer:** Because of foreign key constraints with `ON DELETE CASCADE`, deleting the exam record would remove the exam, attempts, and associated answers. In production, we recommend using the `is_active` toggle (`Hide/Unhide`) instead of hard deletion to maintain historical integrity.

#### Q25: Why did you use `DECIMAL(10,2)` for scores instead of `FLOAT` or `DOUBLE`?
**Answer:** `FLOAT` and `DOUBLE` use binary floating-point representation, which is prone to rounding inaccuracies (e.g., `0.1 + 0.2 != 0.3`). `DECIMAL(10,2)` is an exact numeric data type that guarantees precision for marks and percentages.

---

## 10. Quick Revision Cheat Sheet

### 1. Default Credentials
- **Admin:** `admin` / `admin123`
- **Demo Student:** `STU001` / `password123`
- **Database Name:** `online_examination_system`

### 2. Key Tables & Primary Keys
1. `students` (`student_id`)
2. `admins` (`admin_id`)
3. `exams` (`exam_id`)
4. `questions` (`question_id`)
5. `question_options` (`option_id`)
6. `exam_questions` (`exam_question_id`)
7. `exam_attempts` (`attempt_id`)
8. `attempt_answers` (`attempt_answer_id`)
9. `support_tickets` (`ticket_id`)

### 3. Key SQL Functions Used
- `DENSE_RANK() OVER (PARTITION BY exam_id ORDER BY score DESC, submitted_at ASC)`
- `ON DUPLICATE KEY UPDATE`
- `UPPER(TRIM(...))`
- `COUNT(DISTINCT ...)`, `AVG()`, `MAX()`, `MIN()`

### 4. Golden Rules to Remember
- **Timer:** Server deadline = `started_at + duration`. Client JS only renders the countdown.
- **Security:** PDO prepared statements (SQLi), `password_hash` (Passwords), `csrf_token` (CSRF), `h()` / `htmlspecialchars` (XSS).
- **Your module:** **Aryan Prasher** developed the **Student Exam & Evaluation Engine** (`student/exam.php`, `submit.php`, `result.php`, `dashboard.php`).
