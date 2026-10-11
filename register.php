<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/functions.php';

if (isStudentLoggedIn()) redirect('/student/dashboard.php');

$errors = [];
$rollNo = $fullName = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $rollNo   = trim($_POST['roll_no'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($rollNo === '' || $fullName === '' || $email === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        // Check uniqueness
        $stmt = $pdo->prepare('SELECT student_id FROM students WHERE roll_no = ? OR email = ?');
        $stmt->execute([$rollNo, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'A student with that roll number or email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO students (roll_no, full_name, email, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$rollNo, $fullName, $email, $hash]);

        $_SESSION['student_id']   = $pdo->lastInsertId();
        $_SESSION['student_name'] = $fullName;
        redirect('/student/dashboard.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>

<div class="card card-narrow">
    <span class="eyebrow">Student Sign Up</span>
    <h2 class="mt-0">Create your account</h2>

    <?php foreach ($errors as $e): ?>
        <div class="error-box"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <label for="roll_no">Roll Number</label>
        <input type="text" id="roll_no" name="roll_no" value="<?= h($rollNo) ?>" required>

        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?= h($fullName) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">

        <button type="submit" class="btn btn-yellow" style="margin-top:18px;width:100%;">Create Account</button>
    </form>
    <p class="muted" style="margin-top:14px;">Already have an account? <a class="small-link" href="<?= h(appUrl('/login.php')) ?>">Log in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
