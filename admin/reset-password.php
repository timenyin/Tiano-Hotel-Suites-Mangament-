<?php
/**
 * Tiano Hotel & Suites - Set New Admin Password
 * Validates 6-digit OTP code or reset token.
 */
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$email = trim($_GET['email'] ?? '');
$token = trim($_GET['token'] ?? '');
$error = '';
$success = isset($_GET['msg']) ? trim($_GET['msg']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $otp         = trim($_POST['otp_code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    $csrfToken   = $_POST['csrf_token'] ?? '';

    if (!validateCsrf($csrfToken)) {
        $error = "Security token mismatch. Please try again.";
    } elseif (empty($email) || empty($otp) || empty($newPassword)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($newPassword) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($newPassword !== $confirmPass) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND (reset_code = ? OR reset_token = ?) AND reset_expires >= NOW() LIMIT 1");
        $stmt->execute([$email, $otp, $otp]);
        $user = $stmt->fetch();

        if ($user) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admin_users SET password_hash = ?, reset_code = NULL, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $update->execute([$newHash, $user['id']]);

            header("Location: index.php?msg=" . urlencode("Password reset successfully! You can now sign in with your new password."));
            exit;
        } else {
            $error = "Invalid or expired reset code. Please request a new recovery link.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <title>Set New Password | Tiano Hotel Management</title>
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
                <i class="bx bx-lock-open"></i>
            </div>
            <h3>Create New Password</h3>
            <p>Enter the 6-digit reset OTP from your email</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-error-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-info py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-info-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="reset-password.php">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

            <div class="form-floating mb-3">
                <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Staff Email" required value="<?php echo htmlspecialchars($email); ?>">
                <label for="email"><i class="bx bx-envelope me-1"></i> Staff Email Address</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control rounded-3 text-center fw-bold fs-4" id="otp_code" name="otp_code" placeholder="6-Digit OTP" maxlength="6" pattern="[0-9]{6}" style="letter-spacing:6px; font-family:monospace;" required value="<?php echo htmlspecialchars($token ?: ''); ?>" autofocus>
                <label for="otp_code"><i class="bx bx-key me-1"></i> 6-Digit Reset OTP</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control rounded-3" id="new_password" name="new_password" placeholder="New Password" required>
                <label for="new_password"><i class="bx bx-lock-alt me-1"></i> New Password</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" class="form-control rounded-3" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <label for="confirm_password"><i class="bx bx-check-double me-1"></i> Confirm New Password</label>
            </div>

            <button type="submit" class="admin-btn-primary mb-3">
                <i class="bx bx-save me-1"></i> Update Password &amp; Sign In
            </button>
        </form>

        <div class="text-center pt-2 border-top small">
            <a href="index.php" class="text-secondary text-decoration-none">
                <i class="bx bx-arrow-back me-1"></i> Back to Sign In
            </a>
        </div>

    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
