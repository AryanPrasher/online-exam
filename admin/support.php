<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$errors = [];
$success = '';

// Handle Admin Response Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;
    $response = trim($_POST['admin_response'] ?? '');

    if (!$ticketId) {
        $errors[] = 'Invalid ticket ID.';
    }
    if (empty($response)) {
        $errors[] = 'Please enter a response before resolving the ticket.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE support_tickets
            SET admin_response = ?, status = 'resolved', resolved_at = NOW()
            WHERE ticket_id = ?
        ");
        $stmt->execute([$response, $ticketId]);

        redirect('/admin/support.php?success=1');
    }
}

if (isset($_GET['success'])) {
    $success = 'Response saved and ticket resolved successfully.';
}

// Fetch filter status (all, pending, resolved)
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['pending', 'resolved']) ? $_GET['filter'] : 'all';

// Build SQL query based on filter
$sql = "
    SELECT t.*, s.full_name, s.roll_no, e.exam_title
    FROM support_tickets t
    JOIN students s ON s.student_id = t.student_id
    LEFT JOIN exams e ON e.exam_id = t.exam_id
";

if ($filter === 'pending') {
    $sql .= " WHERE t.status = 'pending'";
} elseif ($filter === 'resolved') {
    $sql .= " WHERE t.status = 'resolved'";
}

$sql .= " ORDER BY t.created_at DESC";

$tickets = $pdo->query($sql)->fetchAll();

// Get count of pending tickets for display
$pendingCount = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'pending'")->fetchColumn();

$pageTitle = 'Manage Support Desk';
require __DIR__ . '/../includes/header.php';
?>

<span class="eyebrow">Doubt Desk & Support Desk</span>
<h1 class="mt-0">Student Queries & Doubt Resolution</h1>

<?php if ($success): ?>
    <div class="success-box"><?= h($success) ?></div>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <div class="error-box"><?= h($e) ?></div>
<?php endforeach; ?>

<!-- Filter Tabs -->
<div class="card" style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; gap: 10px;">
        <a href="<?= h(appUrl('/admin/support.php?filter=all')) ?>" class="btn <?= $filter === 'all' ? 'btn-yellow' : 'btn-outline' ?> btn-sm">All Queries</a>
        <a href="<?= h(appUrl('/admin/support.php?filter=pending')) ?>" class="btn <?= $filter === 'pending' ? 'btn-yellow' : 'btn-outline' ?> btn-sm">
            Pending (<?= (int)$pendingCount ?>)
        </a>
        <a href="<?= h(appUrl('/admin/support.php?filter=resolved')) ?>" class="btn <?= $filter === 'resolved' ? 'btn-yellow' : 'btn-outline' ?> btn-sm">Resolved</a>
    </div>
    <span class="muted" style="font-size: 0.85rem;">Showing <?= count($tickets) ?> ticket(s)</span>
</div>

<!-- Support Tickets List -->
<?php if (empty($tickets)): ?>
    <div class="card text-center">
        <p class="muted">No queries found matching the selected filter.</p>
    </div>
<?php else: ?>
    <?php foreach ($tickets as $t): ?>
        <div class="card" style="border-left: 4px solid <?= $t['status'] === 'resolved' ? 'var(--green)' : 'var(--yellow)' ?>;">
            <div class="flex-between" style="border-bottom: 1px solid var(--paper-line); padding-bottom: 8px; margin-bottom: 12px;">
                <div>
                    <span class="eyebrow" style="color: #48544c; font-size: 0.72rem; margin-bottom: 2px;">
                        Student: <?= h($t['full_name']) ?> (<?= h($t['roll_no']) ?>)
                    </span>
                    <h3 style="margin: 0; font-size: 1.15rem;"><?= h($t['subject']) ?></h3>
                </div>
                <div style="text-align: right;">
                    <?php if ($t['status'] === 'pending'): ?>
                        <span class="badge" style="background: #FBF3DF; color: #a3781b; margin-bottom: 4px;">Pending</span>
                    <?php else: ?>
                        <span class="badge badge-scored" style="margin-bottom: 4px;">Resolved</span>
                    <?php endif; ?>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #48544c;">
                        <?= date('d M Y, H:i', strtotime($t['created_at'])) ?>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <span class="eyebrow" style="font-size: 0.65rem; color: var(--green);">Exam Context</span>
                <span style="font-size: 0.9rem; font-weight: 500;"><?= h($t['exam_title'] ?? 'General / Technical support') ?></span>
                
                <div style="margin-top: 10px; background: rgba(0, 0, 0, 0.02); padding: 12px; border-radius: 6px; border: 1.5px solid var(--paper-line);">
                    <div style="font-size: 0.92rem; line-height: 1.5; white-space: pre-wrap;"><?= h($t['message']) ?></div>
                </div>
            </div>

            <!-- Resolution Section -->
            <?php if ($t['status'] === 'pending'): ?>
                <div style="background: rgba(242, 193, 78, 0.04); border: 1px dashed var(--yellow); border-radius: 8px; padding: 18px; margin-top: 14px;">
                    <h4 style="margin: 0 0 10px 0; font-family: 'Playfair Display', Georgia, serif; color: var(--chalkboard-dark);">Resolve Doubt / Submit Reply</h4>
                    <form method="post" action="<?= h(appUrl('/admin/support.php?filter=' . $filter)) ?>" novalidate style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <input type="hidden" name="ticket_id" value="<?= (int)$t['ticket_id'] ?>">
                        
                        <label for="admin_response_<?= (int)$t['ticket_id'] ?>" style="margin-top: 0;">Your Response</label>
                        <textarea id="admin_response_<?= (int)$t['ticket_id'] ?>" name="admin_response" placeholder="Type your answer, clarification, or resolution details here..." style="min-height: 100px; margin-bottom: 10px;" required></textarea>
                        
                        <button type="submit" class="btn btn-sm btn-yellow">Submit Reply & Resolve</button>
                    </form>
                </div>
            <?php else: ?>
                <div style="background: rgba(79, 157, 105, 0.08); border: 1px solid var(--green); border-radius: 8px; padding: 16px; margin-top: 14px;">
                    <div class="flex-between" style="margin-bottom: 6px;">
                        <span class="eyebrow" style="color: var(--green); font-size: 0.65rem;">Admin Resolution Response</span>
                        <?php if ($t['resolved_at']): ?>
                            <span style="font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; color: #48544c;">
                                Resolved at: <?= date('d M Y, H:i', strtotime($t['resolved_at'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.92rem; font-style: italic; line-height: 1.4; color: #1f3d2e; white-space: pre-wrap;"><?= h($t['admin_response']) ?></div>
                    
                    <!-- Option to update reply -->
                    <details style="margin-top: 12px; font-size: 0.85rem;">
                        <summary style="cursor: pointer; color: var(--green); font-weight: 600; outline: none;">Update Response</summary>
                        <form method="post" action="<?= h(appUrl('/admin/support.php?filter=' . $filter)) ?>" novalidate style="margin-top: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="ticket_id" value="<?= (int)$t['ticket_id'] ?>">
                            <textarea name="admin_response" style="min-height: 80px; margin-bottom: 8px;" required><?= h($t['admin_response']) ?></textarea>
                            <button type="submit" class="btn btn-sm btn-outline">Update Reply</button>
                        </form>
                    </details>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div style="margin-top: 24px;">
    <a href="<?= h(appUrl('/admin/dashboard.php')) ?>" class="small-link">&larr; Back to Admin Dashboard</a>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
