# Online Examination System (PHP + MySQL)

A working website built on top of your `online_examination_system.sql` schema. It covers all five tasks/outcomes end‑to‑end:

| Task | Where it lives |
|---|---|
| 1. Question & exam tables | `sql/examination.sql` |
| 2. Store attempts & answers | `student/exam.php`, `student/submit.php` |
| 3. Automatic scoring queries | `student/submit.php` (the `UPDATE ... JOIN questions` block) |
| 4. Results & rankings | `student/submit.php` (`DENSE_RANK()` block), `student/result.php` |
| 5. Result summary reports | `admin/reports.php` (uses `v_exam_summary` view) |

## What you get

- **Student side:** register, log in, see available exams, take a timed exam (auto-submits when the clock hits 0), instant scoring, per-question breakdown, and a leaderboard for each exam.
- **Admin side:** log in, dashboard with stats, create/edit/hide/delete exams, add questions (MCQ / True-False / Short answer) to any exam, and summary + detailed reports.

## 1. Requirements

- PHP 8.0+ with the `pdo_mysql` extension (any shared host, XAMPP, WAMP, MAMP, or Laragon works)
- MySQL 8.0+ (needed for the `DENSE_RANK()` window function used in ranking)

## 2. Install

1. This project is already placed in XAMPP's `htdocs` directory. Keep the files
   together in this folder, or copy the folder into another server web root.
2. Create the database by importing **`sql/examination.sql`** — either:
   - phpMyAdmin → Import → choose the file → Go, or
   - CLI: `mysql -u root -p < sql/examination.sql`

   This creates the `online_examination_system` database, all tables, sample exam/questions, and a demo admin + 3 demo students.
   In phpMyAdmin, refresh the left database tree after import. The SQL file itself does not appear as an object; the imported tables and views appear under the selected database.
3. Open **`config/db.php`** and set your MySQL host/username/password if they differ from the defaults (`localhost` / `root` / empty password).
4. Visit the site in your browser using this folder's path. For this installation:
   `http://localhost/php-practice/online-exam-system%20(1)/`.

   The application now detects its own web-root-relative location, so its links
   and redirects continue to work if you rename or move this project folder.

## 3. Demo logins

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Student | roll `STU001` (or email `aarav@example.com`) | `password123` |

New students can also just click **Register** and create their own account.

## 4. How the pieces fit together

- `config/db.php` — single PDO connection used everywhere.
- `includes/functions.php` — session helpers (`requireStudentLogin`, `requireAdminLogin`), CSRF token helpers, `h()` escaping.
- `includes/header.php` / `footer.php` — shared layout + nav.
- Root: `index.php`, `login.php`, `register.php`, `logout.php` — public/student auth.
- `student/` — dashboard, exam-taking (with JS countdown timer), submission/scoring, results.
- `admin/` — login, dashboard, exam CRUD, question CRUD, reports.

## 5. Notes for your report / viva

- Passwords are hashed with PHP's `password_hash()` (bcrypt) — never stored in plain text.
- All queries use PDO **prepared statements**, so the app is protected against SQL injection.
- Every form includes a **CSRF token** checked on submit.
- The exam timer is enforced **server-side** too: `student/exam.php` computes the deadline from `started_at + duration_minutes` on the server, so a student can't cheat by editing the JavaScript countdown.
- Scoring and ranking SQL is exactly your original logic (case-insensitive trimmed match, `DENSE_RANK() OVER (PARTITION BY exam_id ORDER BY score DESC, submitted_at ASC, attempt_id ASC)`), just scoped to run per-attempt/per-exam instead of on the whole table each time.

## 6. Extending it

- Add more question types by extending the `question_type` ENUM and the rendering logic in `student/exam.php` + scoring form in `admin/questions.php`.
- Add a "negative marking" rule by changing the `CASE` expression in `student/submit.php`.
- Export reports to CSV/Excel by adapting the query in `admin/reports.php`.
