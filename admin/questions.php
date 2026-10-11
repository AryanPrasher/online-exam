<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$examId = (int)($_GET['exam_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM exams WHERE exam_id = ?');
$stmt->execute([$examId]);
$exam = $stmt->fetch();
if (!$exam) redirect('/admin/exams.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = $_POST['action'] ?? '';

    if ($action === 'remove') {
        // Detach question from this exam only (question stays in the bank).
        $pdo->prepare('DELETE FROM exam_questions WHERE exam_id = ? AND question_id = ?')
            ->execute([$examId, (int)$_POST['question_id']]);
        redirect('/admin/questions.php?exam_id=' . $examId);
    }

    if ($action === 'add') {
        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] ?? 'mcq';
        $correct = trim($_POST['correct_answer'] ?? '');
        $marks = max(1, (int)($_POST['marks'] ?? 1));

        if ($text === '' || $correct === '') {
            $errors[] = 'Question text and correct answer are required.';
        }

        if (!$errors) {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO questions (question_text, question_type, correct_answer, marks) VALUES (?, ?, ?, ?)');
            $stmt->execute([$text, $type, $correct, $marks]);
            $questionId = $pdo->lastInsertId();

            if ($type === 'mcq') {
                $labels = ['A', 'B', 'C', 'D'];
                foreach ($labels as $label) {
                    $optText = trim($_POST['option_' . $label] ?? '');
                    if ($optText === '') continue;
                    $isCorrect = (strcasecmp($optText, $correct) === 0) ? 1 : 0;
                    $pdo->prepare('INSERT INTO question_options (question_id, option_label, option_text, is_correct) VALUES (?, ?, ?, ?)')
                        ->execute([$questionId, $label, $optText, $isCorrect]);
                }
            } elseif ($type === 'true_false') {
                foreach (['TRUE', 'FALSE'] as $val) {
                    $isCorrect = (strcasecmp($val, $correct) === 0) ? 1 : 0;
                    $pdo->prepare('INSERT INTO question_options (question_id, option_label, option_text, is_correct) VALUES (?, ?, ?, ?)')
                        ->execute([$questionId, substr($val, 0, 1), $val, $isCorrect]);
                }
            }

            $nextOrder = (int)$pdo->query("SELECT COALESCE(MAX(question_order),0)+1 FROM exam_questions WHERE exam_id = $examId")->fetchColumn();
            $pdo->prepare('INSERT INTO exam_questions (exam_id, question_id, question_order) VALUES (?, ?, ?)')
                ->execute([$examId, $questionId, $nextOrder]);

            $pdo->commit();
            redirect('/admin/questions.php?exam_id=' . $examId);
        }
    }
}

$stmt = $pdo->prepare("
    SELECT q.*, eq.question_order
    FROM exam_questions eq
    JOIN questions q ON q.question_id = eq.question_id
    WHERE eq.exam_id = ?
    ORDER BY eq.question_order
");
$stmt->execute([$examId]);
$questions = $stmt->fetchAll();

$pageTitle = 'Questions · ' . $exam['exam_title'];
require __DIR__ . '/../includes/header.php';
?>

<span class="eyebrow"><?= h($exam['exam_title']) ?></span>
<h1 class="mt-0">Questions</h1>

<div class="card">
    <table>
        <tr><th>#</th><th>Question</th><th>Type</th><th>Correct Answer</th><th>Marks</th><th></th></tr>
        <?php foreach ($questions as $q): ?>
        <tr>
            <td><?= (int)$q['question_order'] ?></td>
            <td><?= h($q['question_text']) ?></td>
            <td><?= h($q['question_type']) ?></td>
            <td><?= h($q['correct_answer']) ?></td>
            <td><?= (int)$q['marks'] ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Remove this question from the exam?');">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="question_id" value="<?= (int)$q['question_id'] ?>">
                    <button type="submit" class="small-link" style="background:none;border:none;padding:0;cursor:pointer;font-family:inherit;color:var(--red);">Remove</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$questions): ?>
        <tr><td colspan="6" class="muted">No questions added yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h2>Add a Question</h2>
    <?php foreach ($errors as $e): ?><div class="error-box"><?= h($e) ?></div><?php endforeach; ?>

    <form method="post" id="qform">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <input type="hidden" name="action" value="add">

        <label for="question_text">Question Text</label>
        <textarea id="question_text" name="question_text" required></textarea>

        <label for="question_type">Type</label>
        <select id="question_type" name="question_type" onchange="toggleFields()">
            <option value="mcq">Multiple Choice</option>
            <option value="true_false">True / False</option>
            <option value="short_answer">Short Answer</option>
        </select>

        <div id="mcqFields">
            <label>Option A</label><input type="text" name="option_A">
            <label>Option B</label><input type="text" name="option_B">
            <label>Option C</label><input type="text" name="option_C">
            <label>Option D</label><input type="text" name="option_D">
        </div>

        <label for="correct_answer">Correct Answer (must exactly match one option, or TRUE/FALSE, or the expected short answer)</label>
        <input type="text" id="correct_answer" name="correct_answer" required>

        <label for="marks">Marks</label>
        <input type="number" id="marks" name="marks" min="1" value="1" required>

        <button type="submit" class="btn btn-yellow" style="margin-top:18px;">Add Question</button>
    </form>
</div>

<a class="small-link" href="<?= h(appUrl('/admin/exams.php')) ?>">&larr; Back to exams</a>

<script>
function toggleFields() {
    const type = document.getElementById('question_type').value;
    document.getElementById('mcqFields').style.display = (type === 'mcq') ? 'block' : 'none';
}
toggleFields();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
