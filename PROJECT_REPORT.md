# Online Examination System — Project Report

authentication by Kumail Abbas registration id 12524542
student exam module by Aryan Prasher id 12524232
admin module by divansh sharma id 12527898
student support by Shani Kumar gupta

## 1. Project Details

| Item | Details |
|---|---|
| Project title | Online Examination System |
| Technology | PHP 8+, MySQL 8+, HTML, CSS, JavaScript, PDO |
| Server | XAMPP / Apache |
| Database | `online_examination_system` |
| Purpose | Conduct timed online exams, automatically evaluate answers, generate results and rankings, and manage student support queries. |

## 2. Abstract

The Online Examination System is a web-based application that allows students to register, log in, take time-bound exams, receive instant results, view question-wise performance, and check exam rankings. Administrators can create exams, add questions, manage exam visibility, view performance reports, and respond to student support tickets. The system reduces manual evaluation work through automatic scoring and centralized result reporting.

## 3. Objectives

- Provide secure student and administrator authentication.
- Conduct online examinations with a countdown timer.
- Support MCQ, True/False, and short-answer questions.
- Automatically calculate marks, percentage, and rank.
- Give students detailed result analysis.
- Enable administrators to create and manage exams and questions.
- Generate performance reports.
- Provide a support desk for student doubts and technical issues.

## 4. User Roles

| Role | Main functions |
|---|---|
| Student | Register, log in, attempt exams, view results and rankings, submit support tickets. |
| Administrator | Log in, create/edit/delete exams, add/remove questions, view reports, and resolve tickets. |

## 5. Main Modules

### 5.1 Authentication Module

- Student registration and login using roll number or email.
- Separate administrator login.
- Password hashing using PHP `password_hash()` and verification with `password_verify()`.

### 5.2 Exam Management Module

- Create, edit, hide/unhide, and delete exams.
- Set exam title, description, duration, total marks, and visibility.
- Main files: `admin/exams.php` and `admin/exam_form.php`.

### 5.3 Question Management Module

- Add MCQ, True/False, and short-answer questions.
- Set marks and correct answers.
- Attach questions to exams with a defined order.
- Main file: `admin/questions.php`.

### 5.4 Online Examination Module

- A student can start or resume an exam.
- The server calculates the deadline using `started_at + duration_minutes`.
- A JavaScript countdown displays remaining time and auto-submits when time ends.
- Main file: `student/exam.php`.

### 5.5 Automatic Evaluation Module

- Stores each submitted answer.
- Compares answers with correct answers using trimmed, case-insensitive matching.
- Calculates awarded marks, total score, maximum score, and percentage.
- Main file: `student/submit.php`.

### 5.6 Result and Ranking Module

- Shows detailed question-wise results.
- Displays pass/fail status using 40% as the pass mark.
- Shows a leaderboard for each exam.
- Uses MySQL `DENSE_RANK()` to generate ranks.
- Main file: `student/result.php`.

### 5.7 Reporting Module

- Shows total attempts, total students, average score, highest score, and lowest score for each exam.
- Shows detailed student results with score, percentage, rank, and status.
- Main file: `admin/reports.php`.

### 5.8 Support Desk Module

- Students create tickets for exam-related or general issues.
- Administrators can view, filter, respond to, and resolve tickets.
- Main files: `student/support.php` and `admin/support.php`.

## 6. Database Design

| Table | Purpose |
|---|---|
| `students` | Stores student profile, roll number, email, and password hash. |
| `admins` | Stores administrator credentials. |
| `exams` | Stores exam title, duration, marks, and visibility. |
| `questions` | Stores question text, type, correct answer, and marks. |
| `question_options` | Stores options for MCQ and True/False questions. |
| `exam_questions` | Links questions to exams and stores question order. |
| `exam_attempts` | Stores each student's exam session, score, percentage, rank, and status. |
| `attempt_answers` | Stores student answers and awarded marks. |
| `support_tickets` | Stores student queries and administrator responses. |

### Entity Relationships

```text
Student ──< Exam Attempts >── Exam
Exam ──< Exam Questions >── Questions
Questions ──< Question Options
Exam Attempts ──< Attempt Answers >── Questions
Student ──< Support Tickets >── Exam (optional)
```

## 7. Important SQL Concepts

- Primary keys uniquely identify records.
- Foreign keys maintain relationships and data consistency.
- Cascade deletion removes related data when an exam or question is deleted.
- Unique constraints prevent one student from attempting the same exam more than once.
- Database views used by the project:
  - `v_attempt_results`
  - `v_exam_summary`
- The `DENSE_RANK()` window function generates exam-wise rankings.

## 8. Automatic Scoring Logic

```text
If student answer = correct answer:
    is_correct = 1
    awarded_marks = question marks
Else:
    is_correct = 0
    awarded_marks = 0

Percentage = (score / maximum score) × 100
```

Ranking is calculated by highest score first, then earliest submission time, then attempt ID.

## 9. Security Features

- Passwords are stored as secure hashes rather than plain text.
- PDO prepared statements protect against SQL injection.
- CSRF tokens protect form submissions.
- Output is escaped with `htmlspecialchars()` to reduce XSS risk.
- Student and administrator pages use session-based role checks.
- Exam time is checked on the server, not only in JavaScript.

## 10. Hardware and Software Requirements

- Windows, Linux, or macOS.
- XAMPP, WAMP, MAMP, or Laragon.
- Apache web server.
- PHP 8.0 or higher.
- MySQL 8.0 or higher.
- `pdo_mysql` PHP extension.
- A modern browser such as Chrome, Edge, or Firefox.

## 11. Installation Steps

1. Start Apache and MySQL in XAMPP.
2. Import `sql/examination.sql` through phpMyAdmin.
3. Confirm database credentials in `config/db.php`.
4. Open the application in a browser:

```text
http://localhost/php-practice/online-exam-system%20(1)/
```

## 12. Demo Credentials

| User | Username / Roll Number | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Student | `STU001` | `password123` |

## 13. Suggested Screenshots

Include screenshots of the following pages in the final printed report:

1. Home page.
2. Student registration page.
3. Student login page.
4. Student dashboard.
5. Timed examination screen.
6. Result page with score and leaderboard.
7. Administrator login page.
8. Administrator dashboard.
9. Exam creation form.
10. Question management page.
11. Administrator reports page.
12. Student and administrator support desk pages.

## 14. Limitations

- Only one attempt per student is allowed for each exam.
- Short answers use exact text matching, so equivalent answers may be marked incorrect.
- Email notifications are not available.
- Reports cannot currently be exported to PDF, CSV, or Excel.
- Questions and options are not randomized.
- Webcam proctoring is not available.
- Password reset is not available.

## 15. Future Enhancements

- Add negative marking and configurable pass marks.
- Export reports to CSV, PDF, or Excel.
- Randomize questions and answer options.
- Add manual review for descriptive answers.
- Add password reset and email notifications.
- Add exam schedules with start and end dates.
- Add proctoring and activity monitoring.
- Add charts for student and exam performance analytics.

## 16. Testing Status

All PHP source files passed PHP syntax validation. The local PHP environment displayed a non-blocking warning that the OpenSSL module was already loaded.
