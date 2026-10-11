<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireStudentLogin();

$studentId = $_SESSION['student_id'];

// Pull every active exam plus this student's attempt status (if any).
$stmt = $pdo->prepare("
    SELECT
        e.exam_id, e.exam_title, e.exam_description, e.duration_minutes, e.total_marks,
        ea.attempt_id, ea.status, ea.score, ea.percentage
    FROM exams e
    LEFT JOIN exam_attempts ea ON ea.exam_id = e.exam_id AND ea.student_id = ?
    WHERE e.is_active = 1
    ORDER BY e.exam_id
");
$stmt->execute([$studentId]);
$exams = $stmt->fetchAll();

$pageTitle = 'My Exams';
require __DIR__ . '/../includes/header.php';
?>

<span class="eyebrow">Hello, <?= h($_SESSION['student_name']) ?></span>
<h1 class="mt-0">Available Exams</h1>

<div class="exam-grid">
<?php foreach ($exams as $ex): ?>
    <div class="exam-tile">
        <h3><?= h($ex['exam_title']) ?></h3>
        <p><?= h($ex['exam_description']) ?></p>
        <div class="exam-meta">
            <span><?= (int)$ex['duration_minutes'] ?> min</span>
            <span><?= (int)$ex['total_marks'] ?> marks</span>
        </div>
        <?php if ($ex['attempt_id'] === null): ?>
            <a href="<?= h(appUrl('/student/exam.php?exam_id=' . (int)$ex['exam_id'])) ?>" class="btn btn-yellow">Start Exam</a>
        <?php elseif ($ex['status'] === 'in_progress'): ?>
            <a href="<?= h(appUrl('/student/exam.php?exam_id=' . (int)$ex['exam_id'])) ?>" class="btn">Resume Exam</a>
        <?php else: ?>
            <span class="badge badge-scored">Scored: <?= h($ex['score']) ?> (<?= h($ex['percentage']) ?>%)</span>
            <br><br>
            <a href="<?= h(appUrl('/student/result.php?exam_id=' . (int)$ex['exam_id'])) ?>" class="small-link">View detailed result &rarr;</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if (!$exams): ?>
    <p class="muted">No exams are available right now. Check back later.</p>
<?php endif; ?>
</div>

<div class="card" style="margin-top: 28px; background: rgba(242, 193, 78, 0.08); border-color: var(--yellow);">
    <div class="flex-between">
        <div>
            <h3 style="margin: 0 0 4px 0; color: var(--chalkboard-dark);">Have doubts or facing issues?</h3>
            <p class="muted" style="margin: 0; font-size: 0.9rem;">You can raise a support query to the administrator directly from our Support Desk.</p>
        </div>
        <a href="<?= h(appUrl('/student/support.php')) ?>" class="btn btn-yellow btn-sm">Open Support Desk</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
