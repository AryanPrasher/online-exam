<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

if (isStudentLoggedIn()) redirect('/student/dashboard.php');
if (isAdminLoggedIn()) redirect('/admin/dashboard.php');

$pageTitle = 'Welcome';
require __DIR__ . '/includes/header.php';
?>

<div class="card text-center" style="max-width:640px;margin:60px auto;">
    <span class="eyebrow">Online Examination System</span>
    <h1 class="mt-0">Take exams. Get graded instantly.</h1>
    <p class="muted">Log in as a student to attempt timed exams, or as an administrator to build exams, add questions, and review results.</p>
    <div style="margin-top:22px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
        <a href="<?= h(appUrl('/login.php')) ?>" class="btn btn-yellow">Student Login</a>
        <a href="<?= h(appUrl('/register.php')) ?>" class="btn btn-outline">Create Student Account</a>
        <a href="<?= h(appUrl('/admin/login.php')) ?>" class="btn">Admin Login</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
