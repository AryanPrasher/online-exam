<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : null;
$exam = ['exam_title' => '', 'exam_description' => '', 'duration_minutes' => 30, 'total_marks' => 10, 'is_active' => 1];
$errors = [];

if ($examId) {
    $stmt = $pdo->prepare('SELECT * FROM exams WHERE exam_id = ?');
    $stmt->execute([$examId]);
    $found = $stmt->fetch();
    if ($found) $exam = $found;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $exam['exam_title']       = trim($_POST['exam_title'] ?? '');
    $exam['exam_description'] = trim($_POST['exam_description'] ?? '');
    $exam['duration_minutes'] = (int)($_POST['duration_minutes'] ?? 0);
    $exam['total_marks']      = (int)($_POST['total_marks'] ?? 0);
    $exam['is_active']        = isset($_POST['is_active']) ? 1 : 0;

    if ($exam['exam_title'] === '') $errors[] = 'Exam title is required.';
    if ($exam['duration_minutes'] <= 0) $errors[] = 'Duration must be a positive number of minutes.';

    if (!$errors) {
        if ($examId) {
            $stmt = $pdo->prepare(
                'UPDATE exams SET exam_title=?, exam_description=?, duration_minutes=?, total_marks=?, is_active=? WHERE exam_id=?'
            );
            $stmt->execute([$exam['exam_title'], $exam['exam_description'], $exam['duration_minutes'], $exam['total_marks'], $exam['is_active'], $examId]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO exams (exam_title, exam_description, duration_minutes, total_marks, is_active) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$exam['exam_title'], $exam['exam_description'], $exam['duration_minutes'], $exam['total_marks'], $exam['is_active']]);
            $examId = $pdo->lastInsertId();
        }
        redirect('/admin/questions.php?exam_id=' . $examId);
    }
}

$pageTitle = $examId ? 'Edit Exam' : 'New Exam';
require __DIR__ . '/../includes/header.php';
?>

<div class="card card-narrow" style="max-width:560px;">
    <span class="eyebrow"><?= $examId ? 'Edit Exam' : 'New Exam' ?></span>
    <h2 class="mt-0"><?= $examId ? h($exam['exam_title']) : 'Create an exam' ?></h2>

    <?php foreach ($errors as $e): ?><div class="error-box"><?= h($e) ?></div><?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <label for="exam_title">Exam Title</label>
        <input type="text" id="exam_title" name="exam_title" value="<?= h($exam['exam_title']) ?>" required>

        <label for="exam_description">Description</label>
        <textarea id="exam_description" name="exam_description"><?= h($exam['exam_description']) ?></textarea>

        <label for="duration_minutes">Duration (minutes)</label>
        <input type="number" id="duration_minutes" name="duration_minutes" min="1" value="<?= (int)$exam['duration_minutes'] ?>" required>

        <label for="total_marks">Total Marks (for display only — real total is the sum of question marks)</label>
        <input type="number" id="total_marks" name="total_marks" min="0" value="<?= (int)$exam['total_marks'] ?>">

        <label style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="is_active" style="width:auto;" <?= $exam['is_active'] ? 'checked' : '' ?>>
            Visible to students
        </label>

        <button type="submit" class="btn btn-yellow" style="margin-top:18px;width:100%;">
            <?= $examId ? 'Save Changes' : 'Create & Add Questions' ?>
        </button>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
