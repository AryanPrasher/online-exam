<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireStudentLogin();

$studentId = $_SESSION['student_id'];
$isPost    = $_SERVER['REQUEST_METHOD'] === 'POST';
$examId    = (int)($isPost ? ($_POST['exam_id'] ?? 0) : ($_GET['exam_id'] ?? 0));

if ($isPost) {
    csrfCheck();
}

$stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ?');
$stmt->execute([$examId, $studentId]);
$attempt = $stmt->fetch();

if (!$attempt || $attempt['status'] !== 'in_progress') {
    redirect('/student/dashboard.php');
}
$attemptId = $attempt['attempt_id'];

$stmt = $pdo->prepare('SELECT question_id FROM exam_questions WHERE exam_id = ?');
$stmt->execute([$examId]);
$questionIds = array_column($stmt->fetchAll(), 'question_id');

$answers = $_POST['answer'] ?? [];

// -------- Task 2: store the answer the student gave for each question --------
$insertStmt = $pdo->prepare("
    INSERT INTO attempt_answers (attempt_id, question_id, answer_text)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE answer_text = VALUES(answer_text), answered_at = NOW()
");
foreach ($questionIds as $qid) {
    $text = trim((string)($answers[$qid] ?? ''));
    $insertStmt->execute([$attemptId, $qid, $text]);
}

// -------- Task 3: automatic scoring for this attempt --------
$scoreStmt = $pdo->prepare("
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
");
$scoreStmt->execute([$attemptId]);

// -------- Task 4: calculate this attempt's total score --------
$totalStmt = $pdo->prepare("
    SELECT SUM(aa.awarded_marks) AS score, SUM(q.marks) AS max_score
    FROM attempt_answers aa
    JOIN questions q ON q.question_id = aa.question_id
    WHERE aa.attempt_id = ?
");
$totalStmt->execute([$attemptId]);
$totals = $totalStmt->fetch();
$score = (float)($totals['score'] ?? 0);
$max   = (float)($totals['max_score'] ?? 0);
$pct   = $max > 0 ? round(($score / $max) * 100, 2) : 0;

$updateStmt = $pdo->prepare("
    UPDATE exam_attempts
    SET score = ?, max_score = ?, percentage = ?, status = 'scored', submitted_at = NOW()
    WHERE attempt_id = ?
");
$updateStmt->execute([$score, $max, $pct, $attemptId]);

// -------- Task 4 (ranking): recompute rank for everyone on this exam --------
$rankStmt = $pdo->prepare("
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
");
$rankStmt->execute([$examId]);

redirect('/student/result.php?exam_id=' . $examId);
