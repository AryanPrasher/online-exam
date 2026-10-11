<?php
require __DIR__ . '/../config/db.php';

$title = 'Database Setup - Support Desk';
$error = '';
$success = '';

try {
    $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
        ticket_id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        exam_id INT NULL,
        subject VARCHAR(150) NOT NULL,
        message TEXT NOT NULL,
        admin_response TEXT NULL,
        status ENUM('pending', 'resolved') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL,
        CONSTRAINT fk_tickets_student FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
        CONSTRAINT fk_tickets_exam FOREIGN KEY (exam_id) REFERENCES exams(exam_id) ON DELETE SET NULL
    ) ENGINE=InnoDB;";

    $pdo->exec($sql);
    $success = "Table 'support_tickets' has been created or verified successfully in database '" . DB_NAME . "'.";
} catch (PDOException $e) {
    $error = "Error setting up table: " . $e->getMessage();
}

// Check if run via CLI or Browser
if (php_sapi_name() === 'cli') {
    if ($success) {
        echo "SUCCESS: $success\n";
    } else {
        echo "ERROR: $error\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .setup-card {
            max-width: 500px;
            margin: 80px auto;
            background: #FBF9F2;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(20, 42, 32, 0.15);
            padding: 30px;
            border: 1px solid #E4E0D2;
            text-align: center;
        }
        body {
            background: #1F3D2E;
            font-family: 'Inter', sans-serif;
            color: #1B2420;
        }
        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <h2>Support Desk Setup</h2>
        <?php if ($success): ?>
            <div class="success-box" style="margin-bottom: 20px; text-align: left;">
                <strong>Success!</strong><br>
                <?= htmlspecialchars($success) ?>
            </div>
            <p>You can now close this script and start using the Support Desk.</p>
            <p style="margin-top: 20px;">
                <a href="../" class="btn btn-yellow">Go to Homepage</a>
            </p>
        <?php else: ?>
            <div class="error-box" style="margin-bottom: 20px; text-align: left;">
                <strong>Database Error:</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <p>Please check your <code>config/db.php</code> settings and verify MySQL is running.</p>
        <?php endif; ?>
    </div>
</body>
</html>
