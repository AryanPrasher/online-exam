<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

// Per-exam summary (uses the v_exam_summary view created by sql/examination.sql).
$summary = $pdo->query("
    SELECT * FROM v_exam_summary ORDER BY exam_id
")->fetchAll();

// Full ranked result list (every scored attempt across every exam).
$results = $pdo->query("
    SELECT s.roll_no, s.full_name, e.exam_title, ea.score, ea.max_score, ea.percentage, ea.rank_position, ea.status
    FROM exam_attempts ea
    JOIN students s ON s.student_id = ea.student_id
    JOIN exams e ON e.exam_id = ea.exam_id
    ORDER BY ea.exam_id, ea.rank_position, ea.score DESC
")->fetchAll();

$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-0">Result Summary Reports</h1>

<div class="card">
    <h2>Exam Summary</h2>
    <table>
        <tr><th>Exam</th><th>Attempts</th><th>Students</th><th>Avg Score</th><th>Highest</th><th>Lowest</th></tr>
        <?php foreach ($summary as $s): ?>
        <tr>
            <td><?= h($s['exam_title']) ?></td>
            <td><?= (int)$s['total_attempts'] ?></td>
            <td><?= (int)$s['total_students'] ?></td>
            <td><?= h($s['average_score']) ?></td>
            <td><?= h($s['highest_score']) ?></td>
            <td><?= h($s['lowest_score']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$summary): ?>
        <tr><td colspan="6" class="muted">No exam data yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h2>Detailed Student Results</h2>
    <table>
        <tr><th>Roll No</th><th>Student</th><th>Exam</th><th>Score</th><th>%</th><th>Rank</th><th>Status</th></tr>
        <?php foreach ($results as $r): ?>
        <tr>
            <td><?= h($r['roll_no']) ?></td>
            <td><?= h($r['full_name']) ?></td>
            <td><?= h($r['exam_title']) ?></td>
            <td><?= h($r['score']) ?>/<?= h($r['max_score']) ?></td>
            <td><?= h($r['percentage']) ?>%</td>
            <td><?= $r['rank_position'] ? '#' . (int)$r['rank_position'] : '—' ?></td>
            <td><span class="badge badge-<?= h($r['status']) === 'in_progress' ? 'progress' : h($r['status']) ?>"><?= h($r['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$results): ?>
        <tr><td colspan="7" class="muted">No attempts yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
