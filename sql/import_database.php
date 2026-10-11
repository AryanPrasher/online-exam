<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

$title = 'Database Import Utility';
$error = '';
$success = '';

if (isset($_GET['run'])) {
    try {
        // Connect to MySQL server without specifying db name (since we will create it)
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        $sqlFile = __DIR__ . '/examination.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found at: $sqlFile");
        }

        $sql = file_get_contents($sqlFile);
        
        // Execute the whole SQL file
        $pdo->exec($sql);
        
        $success = "Database 'online_examination_system' and all tables, views, demo accounts, and the support desk module have been successfully created and imported!";
    } catch (Exception $e) {
        $error = "Import failed: " . $e->getMessage();
    }
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
            max-width: 550px;
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
        <h2>Online Exam Database Setup</h2>
        
        <?php if ($success): ?>
            <div class="success-box" style="margin-bottom: 20px; text-align: left;">
                <strong>Import Successful!</strong><br>
                <?= htmlspecialchars($success) ?>
            </div>
            <p>Your database setup is complete and all demo data has been loaded.</p>
            <p style="margin-top: 20px;">
                <a href="../" class="btn btn-yellow">Go to Homepage</a>
            </p>
        <?php elseif ($error): ?>
            <div class="error-box" style="margin-bottom: 20px; text-align: left;">
                <strong>Database Import Error:</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <p>Please ensure that MySQL is running in your <strong>XAMPP Control Panel</strong> and try again.</p>
            <p style="margin-top: 20px;">
                <a href="import_database.php?run=1" class="btn btn-yellow">Retry Database Import</a>
            </p>
        <?php else: ?>
            <p style="text-align: left; line-height: 1.5; margin-bottom: 20px;">
                This script will drop any existing <code>online_examination_system</code> database, create a clean one, and populate it with:
                <ul style="text-align: left; font-size: 0.95rem;">
                    <li>Demo Admin account (<code>admin</code> / <code>admin123</code>)</li>
                    <li>Demo Students (e.g. <code>STU001</code> / <code>password123</code>)</li>
                    <li>Sample exams, questions, and structural views</li>
                    <li>The new **Support Desk** module table</li>
                </ul>
            </p>
            <p style="margin-top: 25px;">
                <a href="import_database.php?run=1" class="btn btn-yellow">Create & Import Database Now</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
