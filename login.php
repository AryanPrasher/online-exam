<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

if (isStudentLoggedIn()) redirect('/student/dashboard.php');

$errors = [];
$identity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM students WHERE roll_no = ? OR email = ? LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        $_SESSION['student_id']   = $student['student_id'];
        $_SESSION['student_name'] = $student['full_name'];
        redirect('/student/dashboard.php');
    } else {
        $errors[] = 'Incorrect roll number/email or password.';
    }
}

$pageTitle = 'Student Login';
require __DIR__ . '/includes/header.php';
?>

<div class="card card-narrow">
    <span class="eyebrow">Student Login</span>
    <h2 class="mt-0">Welcome back</h2>

    <?php foreach ($errors as $e): ?>
        <div class="error-box"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <label for="identity">Roll Number or Email</label>
        <input type="text" id="identity" name="identity" value="<?= h($identity) ?>" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-yellow" style="margin-top:18px;width:100%;">Log In</button>
    </form>
    <p class="muted" style="margin-top:14px;">New here? <a class="small-link" href="<?= h(appUrl('/register.php')) ?>">Create an account</a></p>
    <p class="muted">Demo login: roll <strong>STU001</strong> / password <strong>password123</strong></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
