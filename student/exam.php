<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireStudentLogin();

$studentId = $_SESSION['student_id'];
$examId    = (int)($_GET['exam_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM exams WHERE exam_id = ? AND is_active = 1');
$stmt->execute([$examId]);
$exam = $stmt->fetch();
if (!$exam) {
    redirect('/student/dashboard.php');
}

// Find or create this student's attempt for the exam.
$stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ?');
$stmt->execute([$examId, $studentId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    $stmt = $pdo->prepare(
        'INSERT INTO exam_attempts (exam_id, student_id, started_at, status) VALUES (?, ?, NOW(), "in_progress")'
    );
    $stmt->execute([$examId, $studentId]);
    $stmt = $pdo->prepare('SELECT * FROM exam_attempts WHERE exam_id = ? AND student_id = ?');
    $stmt->execute([$examId, $studentId]);
    $attempt = $stmt->fetch();
}

// Already finished? Send them to the result page.
if ($attempt['status'] !== 'in_progress') {
    redirect('/student/result.php?exam_id=' . $examId);
}

// Work out how many seconds remain based on the server clock.
$deadline = strtotime($attempt['started_at']) + $exam['duration_minutes'] * 60;
$secondsLeft = $deadline - time();

if ($secondsLeft <= 0) {
    // Time is already up (e.g. tab was closed) — auto submit with whatever was saved, or blank.
    redirect('/student/submit.php?exam_id=' . $examId . '&auto=1');
}

// Load questions + options in order.
$stmt = $pdo->prepare("
    SELECT q.question_id, q.question_text, q.question_type, q.marks, eq.question_order
    FROM exam_questions eq
    JOIN questions q ON q.question_id = eq.question_id
    WHERE eq.exam_id = ?
    ORDER BY eq.question_order
");
$stmt->execute([$examId]);
$questions = $stmt->fetchAll();

$optionsByQuestion = [];
if ($questions) {
    $ids = array_column($questions, 'question_id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id IN ($in) ORDER BY option_label");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $opt) {
        $optionsByQuestion[$opt['question_id']][] = $opt;
    }
}

$pageTitle = $exam['exam_title'];
require __DIR__ . '/../includes/header.php';
?>

<div class="flex-between">
    <div>
        <span class="eyebrow">Exam In Progress</span>
        <h1 class="mt-0"><?= h($exam['exam_title']) ?></h1>
    </div>
    <div class="timer" id="timer">--:--</div>
</div>

<form method="post" action="<?= h(appUrl('/student/submit.php')) ?>" id="examForm">
    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
    <input type="hidden" name="exam_id" value="<?= (int)$examId ?>">

    <?php foreach ($questions as $i => $q): ?>
        <div class="card q-card">
            <span class="q-number">Question <?= $i + 1 ?> of <?= count($questions) ?> &middot; <?= (int)$q['marks'] ?> mark<?= $q['marks'] == 1 ? '' : 's' ?></span>
            <div class="q-text"><?= h($q['question_text']) ?></div>

            <?php if ($q['question_type'] === 'short_answer'): ?>
                <input type="text" name="answer[<?= (int)$q['question_id'] ?>]" placeholder="Type your answer">
            <?php else: ?>
                <?php foreach ($optionsByQuestion[$q['question_id']] ?? [] as $opt): ?>
                    <label class="option-row">
                        <input type="radio" name="answer[<?= (int)$q['question_id'] ?>]" value="<?= h($opt['option_text']) ?>">
                        <span><strong><?= h($opt['option_label']) ?>.</strong> <?= h($opt['option_text']) ?></span>
                    </label>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-yellow" style="width:100%;padding:14px;">Submit Exam</button>
</form>

<script>
let secondsLeft = <?= (int)$secondsLeft ?>;
const timerEl = document.getElementById('timer');
const formEl = document.getElementById('examForm');

function render() {
    const m = Math.floor(secondsLeft / 60).toString().padStart(2, '0');
    const s = (secondsLeft % 60).toString().padStart(2, '0');
    timerEl.textContent = m + ':' + s;
    timerEl.classList.toggle('low', secondsLeft <= 60);
}
render();

const tick = setInterval(() => {
    secondsLeft--;
    if (secondsLeft <= 0) {
        clearInterval(tick);
        timerEl.textContent = '00:00';
        formEl.submit(); // time is up — auto-submit whatever has been answered
        return;
    }
    render();
}, 1000);
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
