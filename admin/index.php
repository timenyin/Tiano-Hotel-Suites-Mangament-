<?php
/**
 * Tiano Hotel & Suites - Admin Login Portal
 */
ob_start();

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../include/mailer.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (ob_get_level() > 0) ob_end_clean();
    header("Location: dashboard.php");
    echo '<script>window.location.href="dashboard.php";</script>';
    exit;
}

$error = '';
$success = isset($_GET['msg']) ? trim($_GET['msg']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';
        $csrfToken  = $_POST['csrf_token'] ?? '';

        if (!validateCsrf($csrfToken)) {
            $error = "Security token expired. Please try submitting again.";
        } elseif (empty($identifier) || empty($password)) {
            $error = "Please enter your staff email or username and password.";
        } else {
            // Query user by username or email
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $error = "This staff account is currently inactive. Please contact management.";
                } elseif ((int)$user['is_verified'] === 0) {
                    // Generate fresh 6-digit code and dispatch verification email
                    $code = (string)random_int(100000, 999999);
                    $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    
                    $update = $pdo->prepare("UPDATE admin_users SET verification_code = ?, verification_expires = ? WHERE id = ?");
                    $update->execute([$code, $expires, $user['id']]);

                    $isLocal = (isset($_SERVER['HTTP_HOST']) && in_array(parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST), ['localhost', '127.0.0.1']));
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                    $host = $_SERVER['HTTP_HOST'] ?? 'tianohotelandsuites.com';
                    $basePath = $isLocal ? '/Tiano-Hotel' : '';
                    $verifyUrl = $protocol . $host . $basePath . "/admin/verify-email.php?email=" . urlencode($user['email']);

                    try {
                        sendAdminVerificationEmail($user, $code, $verifyUrl);
                    } catch (\Throwable $mailErr) {
                        error_log("[Staff Verification Mailer] " . $mailErr->getMessage());
                    }

                    $verRedir = "verify-email.php?email=" . urlencode($user['email']) . "&msg=" . urlencode("Please verify your email address to complete sign in. A 6-digit code was sent to " . $user['email']);
                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: " . $verRedir);
                    echo '<script>window.location.href=' . json_encode($verRedir) . ';</script>';
                    exit;
                } else {
                    // Success - regenerate session ID and log in
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_name'] = $user['fullname'];
                    $_SESSION['admin_role'] = $user['role'];

                    // Update last login
                    $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

                    // Audit log staff authentication
                    log_activity($pdo, $user['id'], $user['fullname'], 'staff_login', 'auth', $user['id'], "Staff operator {$user['fullname']} ({$user['role']}) successfully authenticated into management portal");

                    if (ob_get_level() > 0) ob_end_clean();
                    header("Location: dashboard.php");
                    echo '<!DOCTYPE html><html><head><meta http-equiv="refresh" content="0;url=dashboard.php"></head><body>';
                    echo '<script>window.location.href="dashboard.php";</script>';
                    echo '<p>Redirecting to dashboard... <a href="dashboard.php">Click here</a></p></body></html>';
                    exit;
                }
            } else {
                $error = "Invalid staff credentials. Please check your username/email and password.";
                log_activity($pdo, null, $identifier, 'failed_login', 'auth', null, "Failed login attempt with identifier: {$identifier}");
            }
        }
    } catch (\Throwable $e) {
        error_log("[Admin Login Error] " . $e->getMessage());
        $error = "An unexpected error occurred during authentication. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <title>Staff Sign In | Tiano Hotel Management</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-auth-wrap">
    <div class="admin-auth-card">
        
        <!-- Brand Header -->
        <div class="admin-auth-brand">
            <img src="../assets/img/compnay_logo.JPG" alt="Tiano Hotel Logo">
            <h3>Staff Access Portal</h3>
            <p>Tiano Hotel &amp; Suites &bull; Front Desk &amp; Management</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-error-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-check-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <!-- Sign In Form -->
        <form method="POST" action="index.php">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

            <div class="form-floating mb-3">
                <input type="text" class="form-control rounded-3" id="identifier" name="identifier" placeholder="Username or Email" required autofocus value="<?php echo htmlspecialchars($_POST['identifier'] ?? ''); ?>">
                <label for="identifier"><i class="bx bx-user me-1"></i> Username or Email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="bx bx-lock-alt me-1"></i> Password</label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 small">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
                </div>
                <a href="forgot-password.php" class="text-danger fw-semibold text-decoration-none">Forgot Password?</a>
            </div>

            <button type="submit" class="admin-btn-primary mb-3">
                <i class="bx bx-log-in me-1"></i> Sign In to Dashboard
            </button>
        </form>


        <div class="text-center pt-2 border-top small text-muted">
            New team member? <a href="signup.php" class="text-danger fw-bold text-decoration-none">Register Staff Account</a>
        </div>

        <div class="text-center mt-3 small">
            <a href="../index.php" class="text-secondary text-decoration-none">
                <i class="bx bx-arrow-back me-1"></i> Return to Hotel Website
            </a>
        </div>

    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
