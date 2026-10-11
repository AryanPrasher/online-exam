<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Redirect helper */
function redirect(string $url): void {
    header("Location: " . appUrl($url));
    exit;
}

/**
 * Return a URL relative to this application's folder in the web root.
 * This lets the project run from any folder inside XAMPP's htdocs directory.
 */
function appUrl(string $path = '/'): string {
    static $baseUrl;

    if ($baseUrl === null) {
        $projectRoot = realpath(__DIR__ . '/..');
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

        if ($projectRoot && $documentRoot && str_starts_with(strtolower($projectRoot), strtolower($documentRoot))) {
            $relativePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
            $baseUrl = rtrim($relativePath, '/');
        } else {
            $baseUrl = '';
        }
    }

    return $baseUrl . '/' . ltrim($path, '/');
}

/** Escape output for HTML */
function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** ---------- Student auth ---------- */
function isStudentLoggedIn(): bool {
    return isset($_SESSION['student_id']);
}

function requireStudentLogin(): void {
    if (!isStudentLoggedIn()) {
        redirect('/login.php');
    }
}

/** ---------- Admin auth ---------- */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        redirect('/admin/login.php');
    }
}

/** Simple CSRF token helpers */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Invalid or expired form submission. Please go back and try again.');
    }
}
