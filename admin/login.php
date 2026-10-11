<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) redirect('/admin/dashboard.php');

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']   = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        redirect('/admin/dashboard.php');
    } else {
        $errors[] = 'Incorrect username or password.';
    }
}

$pageTitle = 'Admin Login';
require __DIR__ . '/../includes/header.php';
?>

<div class="card card-narrow">
    <span class="eyebrow">Administrator</span>
    <h2 class="mt-0">Admin Login</h2>

    <?php foreach ($errors as $e): ?>
        <div class="error-box"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= h($username) ?>" required autofocus>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit" class="btn" style="margin-top:18px;width:100%;">Log In</button>
    </form>
    <p class="muted" style="margin-top:14px;">Demo login: <strong>admin</strong> / <strong>admin123</strong></p>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
