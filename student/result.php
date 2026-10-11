<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireStudentLogin();

$studentId = $_SESSION['student_id'];
$examId    = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;

$pageTitle = 'Results';
require __DIR__ . '/../includes/header.php';

if ($examId === null) {
    // -------- Overview: every exam this student has completed --------
    $stmt = $pdo->prepare("
        SELECT ea.exam_id, e.exam_title, ea.score, ea.max_score, ea.percentage, ea.rank_position, ea.status
        FROM exam_attempts ea
        JOIN exams e ON e.exam_id = ea.exam_id
        WHERE ea.student_id = ? AND ea.status = 'scored'
        ORDER BY ea.submitted_at DESC
    ");
    $stmt->execute([$studentId]);
    $rows = $stmt->fetchAll();
    ?>
    <span class="eyebrow">Result History</span>
    <h1 class="mt-0">Your Results</h1>
    <div class="card">
        <table>
            <tr><th>Exam</th><th>Score</th><th>Percentage</th><th>Rank</th><th></th></tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= h($r['exam_title']) ?></td>
                <td><?= h($r['score']) ?> / <?= h($r['max_score']) ?></td>
                <td><?= h($r['percentage']) ?>%</td>
                <td>#<?= (int)$r['rank_position'] ?></td>
                <td><a class="small-link" href="<?= h(appUrl('/student/result.php?exam_id=' . (int)$r['exam_id'])) ?>">Details &rarr;</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
            <tr><td colspan="5" class="muted">You haven't completed any exams yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
} else {
    // -------- Detail: one exam's breakdown + leaderboard --------
    $stmt = $pdo->prepare("
        SELECT ea.*, e.exam_title FROM exam_attempts ea
        JOIN exams e ON e.exam_id = ea.exam_id
        WHERE ea.exam_id = ? AND ea.student_id = ?
    ");
    $stmt->execute([$examId, $studentId]);
    $attempt = $stmt->fetch();

    if (!$attempt || $attempt['status'] !== 'scored') {
        echo '<div class="card">You have not completed this exam yet. <a class="small-link" href="' . h(appUrl('/student/dashboard.php')) . '">Back to exams</a></div>';
        require __DIR__ . '/../includes/footer.php';
        exit;
    }

    $pass = $attempt['percentage'] >= 40; // simple pass mark for the stamp

    // Per-question breakdown.
    $stmt = $pdo->prepare("
        SELECT q.question_text, q.correct_answer, q.marks, aa.answer_text, aa.is_correct, aa.awarded_marks
        FROM attempt_answers aa
        JOIN questions q ON q.question_id = aa.question_id
        JOIN exam_questions eq ON eq.question_id = aa.question_id AND eq.exam_id = ?
        WHERE aa.attempt_id = ?
        ORDER BY eq.question_order
    ");
    $stmt->execute([$examId, $attempt['attempt_id']]);
    $breakdown = $stmt->fetchAll();

    // Leaderboard for this exam.
    $stmt = $pdo->prepare("
        SELECT s.full_name, s.roll_no, ea.score, ea.percentage, ea.rank_position, ea.student_id
        FROM exam_attempts ea
        JOIN students s ON s.student_id = ea.student_id
        WHERE ea.exam_id = ? AND ea.status = 'scored'
        ORDER BY ea.rank_position ASC, ea.score DESC
        LIMIT 10
    ");
    $stmt->execute([$examId]);
    $leaderboard = $stmt->fetchAll();
    ?>

    <span class="eyebrow">Result &middot; <?= h($attempt['exam_title']) ?></span>
    <div class="card flex-between">
        <div>
            <h1 class="mt-0"><?= h($attempt['score']) ?> / <?= h($attempt['max_score']) ?> marks</h1>
            <p class="muted">Rank #<?= (int)$attempt['rank_position'] ?> on this exam</p>
        </div>
        <div class="stamp <?= $pass ? 'stamp-pass' : 'stamp-fail' ?>">
            <span class="pct"><?= h($attempt['percentage']) ?>%</span>
            <span class="label"><?= $pass ? 'Pass' : 'Fail' ?></span>
        </div>
    </div>

    <div class="card">
        <h2>Question Breakdown</h2>
        <table>
            <tr><th>Question</th><th>Your Answer</th><th>Correct Answer</th><th>Marks</th></tr>
            <?php foreach ($breakdown as $b): ?>
            <tr>
                <td><?= h($b['question_text']) ?></td>
                <td><?= h($b['answer_text'] ?: '—') ?></td>
                <td><?= h($b['correct_answer']) ?></td>
                <td><?= $b['is_correct'] ? '✅' : '❌' ?> <?= h($b['awarded_marks']) ?>/<?= h($b['marks']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>Leaderboard</h2>
        <table>
            <tr><th>Rank</th><th>Student</th><th>Score</th><th>%</th></tr>
            <?php foreach ($leaderboard as $l): ?>
            <tr style="<?= $l['student_id'] == $studentId ? 'font-weight:700;background:rgba(79,157,105,0.08);' : '' ?>">
                <td class="<?= $l['rank_position'] <= 3 ? 'rank-' . $l['rank_position'] : '' ?>">#<?= (int)$l['rank_position'] ?></td>
                <td><?= h($l['full_name']) ?> (<?= h($l['roll_no']) ?>)</td>
                <td><?= h($l['score']) ?></td>
                <td><?= h($l['percentage']) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <a class="small-link" href="<?= h(appUrl('/student/dashboard.php')) ?>">&larr; Back to exams</a>
    <?php
}

require __DIR__ . '/../includes/footer.php';
