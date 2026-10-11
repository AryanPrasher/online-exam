<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

// Handle delete / toggle-active actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $examId = (int)($_POST['exam_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM exams WHERE exam_id = ?')->execute([$examId]);
    } elseif (($_POST['action'] ?? '') === 'toggle') {
        $pdo->prepare('UPDATE exams SET is_active = 1 - is_active WHERE exam_id = ?')->execute([$examId]);
    }
    redirect('/admin/exams.php');
}

$exams = $pdo->query("
    SELECT e.*, COUNT(DISTINCT eq.question_id) AS question_count
    FROM exams e
    LEFT JOIN exam_questions eq ON eq.exam_id = e.exam_id
    GROUP BY e.exam_id
    ORDER BY e.exam_id DESC
")->fetchAll();

$pageTitle = 'Manage Exams';
require __DIR__ . '/../includes/header.php';
?>

<div class="flex-between">
    <h1 class="mt-0">Exams</h1>
    <a href="<?= h(appUrl('/admin/exam_form.php')) ?>" class="btn btn-yellow">+ New Exam</a>
</div>

<div class="card">
    <table>
        <tr><th>Title</th><th>Duration</th><th>Marks</th><th>Questions</th><th>Status</th><th>Actions</th></tr>
        <?php foreach ($exams as $ex): ?>
        <tr>
            <td><?= h($ex['exam_title']) ?></td>
            <td><?= (int)$ex['duration_minutes'] ?> min</td>
            <td><?= (int)$ex['total_marks'] ?></td>
            <td><?= (int)$ex['question_count'] ?></td>
            <td>
                <span class="badge <?= $ex['is_active'] ? 'badge-scored' : 'badge-progress' ?>">
                    <?= $ex['is_active'] ? 'Active' : 'Hidden' ?>
                </span>
            </td>
            <td style="white-space:nowrap;">
                <a class="small-link" href="<?= h(appUrl('/admin/questions.php?exam_id=' . (int)$ex['exam_id'])) ?>">Questions</a>
                &nbsp;|&nbsp;
                <a class="small-link" href="<?= h(appUrl('/admin/exam_form.php?exam_id=' . (int)$ex['exam_id'])) ?>">Edit</a>
                &nbsp;|&nbsp;
                <form method="post" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="exam_id" value="<?= (int)$ex['exam_id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="small-link" style="background:none;border:none;padding:0;cursor:pointer;font-family:inherit;">
                        <?= $ex['is_active'] ? 'Hide' : 'Unhide' ?>
                    </button>
                </form>
                &nbsp;|&nbsp;
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete this exam and all its attempts?');">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="exam_id" value="<?= (int)$ex['exam_id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="small-link" style="background:none;border:none;padding:0;cursor:pointer;font-family:inherit;color:var(--red);">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$exams): ?>
        <tr><td colspan="6" class="muted">No exams yet. Create one to get started.</td></tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
