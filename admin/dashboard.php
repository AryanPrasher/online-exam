<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$examCount    = $pdo->query('SELECT COUNT(*) FROM exams')->fetchColumn();
$studentCount = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$attemptCount = $pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status = 'scored'")->fetchColumn();
$pendingTickets = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'pending'")->fetchColumn();

$recent = $pdo->query("
    SELECT s.full_name, s.roll_no, e.exam_title, ea.score, ea.max_score, ea.percentage, ea.submitted_at
    FROM exam_attempts ea
    JOIN students s ON s.student_id = ea.student_id
    JOIN exams e ON e.exam_id = ea.exam_id
    WHERE ea.status = 'scored'
    ORDER BY ea.submitted_at DESC
    LIMIT 8
")->fetchAll();

$pageTitle = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<span class="eyebrow">Administrator</span>
<h1 class="mt-0">Welcome, <?= h($_SESSION['admin_name']) ?></h1>

<div class="exam-grid" style="margin-bottom:24px;">
    <div class="exam-tile"><span class="eyebrow">Exams</span><h2 class="mt-0"><?= (int)$examCount ?></h2></div>
    <div class="exam-tile"><span class="eyebrow">Students</span><h2 class="mt-0"><?= (int)$studentCount ?></h2></div>
    <div class="exam-tile"><span class="eyebrow">Completed Attempts</span><h2 class="mt-0"><?= (int)$attemptCount ?></h2></div>
    <div class="exam-tile" style="<?= $pendingTickets > 0 ? 'border-color: var(--yellow);' : '' ?>">
        <span class="eyebrow">Pending Doubts</span>
        <h2 class="mt-0"><?= (int)$pendingTickets ?></h2>
    </div>
</div>

<div class="flex-between" style="margin-bottom:12px;">
    <h2 class="mt-0">Quick Actions</h2>
</div>
<div style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:28px;">
    <a href="<?= h(appUrl('/admin/exams.php')) ?>" class="btn">Manage Exams</a>
    <a href="<?= h(appUrl('/admin/exam_form.php')) ?>" class="btn btn-yellow">+ New Exam</a>
    <a href="<?= h(appUrl('/admin/reports.php')) ?>" class="btn btn-outline">View Reports</a>
    <a href="<?= h(appUrl('/admin/support.php')) ?>" class="btn btn-outline">Support Desk <?= $pendingTickets > 0 ? '('.$pendingTickets.')' : '' ?></a>
</div>

<div class="card">
    <h2>Recent Submissions</h2>
    <table>
        <tr><th>Student</th><th>Exam</th><th>Score</th><th>%</th><th>Submitted</th></tr>
        <?php foreach ($recent as $r): ?>
        <tr>
            <td><?= h($r['full_name']) ?> (<?= h($r['roll_no']) ?>)</td>
            <td><?= h($r['exam_title']) ?></td>
            <td><?= h($r['score']) ?>/<?= h($r['max_score']) ?></td>
            <td><?= h($r['percentage']) ?>%</td>
            <td><?= h($r['submitted_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?>
        <tr><td colspan="5" class="muted">No submissions yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
