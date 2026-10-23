<?php
/**
 * Tiano Hotel & Suites - Admin Authentication & Session Security Handler
 * Enforces session regeneration, CSRF tokens, and authentication guards.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/../../include/db.php';
require_once __DIR__ . '/../../include/functions.php';

/**
 * Check if an admin is actively authenticated and verified.
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function isAdminLoggedIn(): bool
{
    return isLoggedIn();
}

/**
 * Get currently authenticated admin details.
 */
function currentAdmin(PDO $pdo): ?array
{
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT id, username, fullname, email, role, is_verified, last_login, status FROM admin_users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Route protection guard. Redirects unauthenticated users to the sign-in page.
 */
function requireAuth(): void
{
    if (!isLoggedIn()) {
        header("Location: index.php?msg=" . urlencode("Please sign in to access the administration dashboard."));
        exit;
    }
}

/**
 * Log out active admin.
 */
function logoutAdmin(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generate CSRF Token.
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token.
 */
function validateCsrf(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
