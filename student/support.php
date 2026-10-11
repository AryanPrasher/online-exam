<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireStudentLogin();

$studentId = $_SESSION['student_id'];
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $examId = isset($_POST['exam_id']) && $_POST['exam_id'] !== '' ? (int)$_POST['exam_id'] : null;
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject)) {
        $errors[] = 'Please enter a subject.';
    }
    if (empty($message)) {
        $errors[] = 'Please describe your doubt or issue.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets (student_id, exam_id, subject, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$studentId, $examId, $subject, $message]);
        
        // Redirect to avoid form resubmission
        redirect('/student/support.php?success=1');
    }
}

if (isset($_GET['success'])) {
    $success = 'Your support ticket has been submitted successfully. Admin will review it soon.';
}

// Fetch student's tickets
$stmt = $pdo->prepare("
    SELECT t.*, e.exam_title
    FROM support_tickets t
    LEFT JOIN exams e ON e.exam_id = t.exam_id
    WHERE t.student_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$studentId]);
$tickets = $stmt->fetchAll();

// Fetch active exams for selection dropdown
$exams = $pdo->query("SELECT exam_id, exam_title FROM exams WHERE is_active = 1 ORDER BY exam_title")->fetchAll();

$pageTitle = 'Support Desk';
require __DIR__ . '/../includes/header.php';
?>

<span class="eyebrow">Doubt Desk & Help Center</span>
<h1 class="mt-0">Support Desk</h1>

<?php if ($success): ?>
    <div class="success-box"><?= h($success) ?></div>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <div class="error-box"><?= h($e) ?></div>
<?php endforeach; ?>

<div class="card">
    <h2>Submit a Query or Doubt</h2>
    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <label for="exam_id">Associated Exam (Optional)</label>
        <select id="exam_id" name="exam_id">
            <option value="">General Inquiry / Technical Support</option>
            <?php foreach ($exams as $ex): ?>
                <option value="<?= (int)$ex['exam_id'] ?>"><?= h($ex['exam_title']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="subject">Subject / Summary</label>
        <input type="text" id="subject" name="subject" placeholder="e.g. Issue with question 5 in DBMS, Exam timed out early" required>

        <label for="message">Detailed Description</label>
        <textarea id="message" name="message" placeholder="Explain your doubt, query, or problem in detail here..." style="min-height: 120px;" required></textarea>

        <button type="submit" class="btn btn-yellow" style="margin-top: 18px;">Submit Ticket</button>
    </form>
</div>

<div class="card">
    <h2>Your Support Tickets</h2>
    <?php if (!$tickets): ?>
        <p class="muted">You have not submitted any queries or feedback yet.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="width: 15%;">Submitted</th>
                    <th style="width: 25%;">Exam Context</th>
                    <th style="width: 40%;">Query & Response</th>
                    <th style="width: 20%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr style="vertical-align: top;">
                        <td class="muted" style="padding-top: 14px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">
                            <?= date('d M Y, H:i', strtotime($t['created_at'])) ?>
                        </td>
                        <td style="padding-top: 14px;">
                            <strong><?= h($t['exam_title'] ?? 'General Inquiry') ?></strong>
                        </td>
                        <td style="padding-top: 14px; padding-bottom: 14px;">
                            <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 4px;"><?= h($t['subject']) ?></div>
                            <div style="font-size: 0.9rem; color: #354238; line-height: 1.4; white-space: pre-wrap; margin-bottom: 8px;"><?= h($t['message']) ?></div>
                            
                            <?php if ($t['status'] === 'resolved' && !empty($t['admin_response'])): ?>
                                <div style="background: rgba(79, 157, 105, 0.08); border-left: 3px solid var(--green); padding: 10px 14px; border-radius: 4px; margin-top: 10px;">
                                    <span class="eyebrow" style="color: var(--green); font-size: 0.65rem; margin-bottom: 2px;">Admin Response</span>
                                    <div style="font-size: 0.9rem; font-style: italic; line-height: 1.4; color: #1f3d2e; white-space: pre-wrap;"><?= h($t['admin_response']) ?></div>
                                    <?php if ($t['resolved_at']): ?>
                                        <div style="font-size: 0.72rem; color: #48544c; text-align: right; margin-top: 4px; font-family: 'JetBrains Mono', monospace;">
                                            Resolved on: <?= date('d M Y, H:i', strtotime($t['resolved_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding-top: 14px;">
                            <?php if ($t['status'] === 'pending'): ?>
                                <span class="badge" style="background: #FBF3DF; color: #a3781b;">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-scored">Resolved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<a href="<?= h(appUrl('/student/dashboard.php')) ?>" class="small-link">&larr; Back to Dashboard</a>

<?php require __DIR__ . '/../includes/footer.php'; ?>
