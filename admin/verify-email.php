<?php
/**
 * Tiano Hotel & Suites - Staff Email Verification Portal
 * Verifies 6-digit OTP code or secret token sent via PHPMailer.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../include/mailer.php';

$email = trim($_GET['email'] ?? '');
$token = trim($_GET['token'] ?? '');
$error = '';
$success = isset($_GET['msg']) ? trim($_GET['msg']) : '';

// Direct verification via token link
if (!empty($token) && !empty($email)) {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND verification_token = ? AND verification_expires >= NOW() LIMIT 1");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if ($user) {
        $update = $pdo->prepare("UPDATE admin_users SET is_verified = 1, verification_token = NULL, verification_code = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        // Auto login
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['fullname'];
        $_SESSION['admin_role'] = $user['role'];

        header("Location: dashboard.php?msg=" . urlencode("Email verified successfully! Welcome to the management console."));
        exit;
    } else {
        $error = "This verification link is invalid or has expired. Please submit your 6-digit code or request a new one.";
    }
}

// Handle 6-Digit OTP Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = trim($_POST['email'] ?? '');
    $otp   = trim($_POST['otp_code'] ?? '');
    $csrf  = $_POST['csrf_token'] ?? '';

    if (!validateCsrf($csrf)) {
        $error = "Security token expired. Please try again.";
    } elseif (empty($email) || empty($otp)) {
        $error = "Please enter your registered email and the 6-digit verification code.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND verification_code = ? AND verification_expires >= NOW() LIMIT 1");
        $stmt->execute([$email, $otp]);
        $user = $stmt->fetch();

        if ($user) {
            $update = $pdo->prepare("UPDATE admin_users SET is_verified = 1, verification_token = NULL, verification_code = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            // Auto login
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['fullname'];
            $_SESSION['admin_role'] = $user['role'];

            header("Location: dashboard.php?msg=" . urlencode("Email verified successfully!"));
            exit;
        } else {
            $error = "Invalid or expired verification code. Please check your inbox or request a new code.";
        }
    }
}

// Handle Resend Code Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_code'])) {
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $code = (string)random_int(100000, 999999);
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $token = bin2hex(random_bytes(24));

            $pdo->prepare("UPDATE admin_users SET verification_code = ?, verification_token = ?, verification_expires = ? WHERE id = ?")
                ->execute([$code, $token, $expires, $user['id']]);

            $isLocal = (isset($_SERVER['HTTP_HOST']) && in_array(parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST), ['localhost', '127.0.0.1']));
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'tianohotelandsuites.com';
            $basePath = $isLocal ? '/Tiano-Hotel' : '';
            $verifyUrl = $protocol . $host . $basePath . "/admin/verify-email.php?email=" . urlencode($email) . "&token=" . urlencode($token);
            @sendAdminVerificationEmail($user, $code, $verifyUrl);


            $success = "A fresh 6-digit verification code has been dispatched to " . htmlspecialchars($email);
        } else {
            $error = "No staff account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Staff Email | Tiano Hotel Management</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-auth-wrap">
    <div class="admin-auth-card">
        
        <div class="admin-auth-brand">
            <div class="stat-icon-wrap stat-icon-red mb-3 mx-auto" style="width:58px;height:58px;font-size:28px;">
                <i class="bx bx-envelope-open"></i>
            </div>
            <h3>Verify Your Email</h3>
            <p>Enter the 6-digit code sent to your staff inbox</p>
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

        <!-- OTP Verification Form -->
        <form method="POST" action="verify-email.php">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
            <input type="hidden" name="verify_otp" value="1">

            <div class="form-floating mb-3">
                <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Staff Email" required value="<?php echo htmlspecialchars($email); ?>">
                <label for="email"><i class="bx bx-envelope me-1"></i> Staff Email Address</label>
            </div>

            <div class="mb-4 text-center">
                <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2" style="letter-spacing:1px;">6-Digit Verification Code</label>
                <input type="text" class="form-control text-center fw-bold fs-3 rounded-3" id="otp_code" name="otp_code" maxlength="6" pattern="[0-9]{6}" placeholder="&bull; &bull; &bull; &bull; &bull; &bull;" style="letter-spacing:8px; font-family:monospace;" required autofocus>
            </div>

            <button type="submit" class="admin-btn-primary mb-3">
                <i class="bx bx-badge-check me-1"></i> Verify &amp; Launch Dashboard
            </button>
        </form>

        <!-- Resend Code Form -->
        <form method="POST" action="verify-email.php" class="text-center pt-2 border-top">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
            <input type="hidden" name="resend_code" value="1">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <span class="small text-muted">Didn't receive the email? </span>
            <button type="submit" class="btn btn-link p-0 text-danger fw-bold small text-decoration-none">
                Resend Code
            </button>
        </form>

        <div class="text-center mt-3 small">
            <a href="index.php" class="text-secondary text-decoration-none">
                <i class="bx bx-arrow-back me-1"></i> Back to Sign In
            </a>
        </div>

    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
