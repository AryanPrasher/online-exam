<?php
// Expects (optionally) $pageTitle to be set before including this file.
$pageTitle = $pageTitle ?? 'Online Examination System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> · Online Examination System</title>
<link rel="stylesheet" href="<?= h(appUrl('/assets/css/style.css')) ?>">
</head>
<body>
<div class="topbar">
    <a href="<?= h(appUrl('/')) ?>" style="text-decoration:none;">
        <span class="brand"><span class="dot">&#9679;</span> Examination Hall</span>
    </a>
    <nav>
    <?php if (isAdminLoggedIn()): ?>
        <a href="<?= h(appUrl('/admin/dashboard.php')) ?>">Dashboard</a>
        <a href="<?= h(appUrl('/admin/exams.php')) ?>">Exams</a>
        <a href="<?= h(appUrl('/admin/reports.php')) ?>">Reports</a>
        <a href="<?= h(appUrl('/admin/support.php')) ?>">Support Desk</a>
        <a href="<?= h(appUrl('/admin/logout.php')) ?>">Log out</a>
    <?php elseif (isStudentLoggedIn()): ?>
        <a href="<?= h(appUrl('/student/dashboard.php')) ?>">My Exams</a>
        <a href="<?= h(appUrl('/student/result.php')) ?>">Results</a>
        <a href="<?= h(appUrl('/student/support.php')) ?>">Support Desk</a>
        <a href="<?= h(appUrl('/logout.php')) ?>">Log out</a>
    <?php else: ?>
        <a href="<?= h(appUrl('/login.php')) ?>">Student Login</a>
        <a href="<?= h(appUrl('/register.php')) ?>">Register</a>
        <a href="<?= h(appUrl('/admin/login.php')) ?>">Admin</a>
    <?php endif; ?>
    </nav>
</div>
<div class="page">
