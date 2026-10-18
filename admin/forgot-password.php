<?php
/**
 * Tiano Hotel & Suites - Admin Forgot Password Request
 * Dispatches 6-digit OTP code to verified staff email.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../include/mailer.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!validateCsrf($csrfToken)) {
        $error = "Security token mismatch. Please try again.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid staff email address.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $code = (string)random_int(100000, 999999);
            $token = bin2hex(random_bytes(24));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));

            $update = $pdo->prepare("UPDATE admin_users SET reset_code = ?, reset_token = ?, reset_expires = ? WHERE id = ?");
            $update->execute([$code, $token, $expires, $user['id']]);

            $resetUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/Tiano-Hotel/admin/reset-password.php?email=" . urlencode($email) . "&token=" . urlencode($token);
            @sendAdminPasswordResetEmail($user, $code, $resetUrl);

            header("Location: reset-password.php?email=" . urlencode($email) . "&msg=" . urlencode("A 6-digit password reset OTP has been sent to your email."));
            exit;
        } else {
            // For security, don't disclose non-existent accounts, or give helpful note
            $error = "No active staff account found with this email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Tiano Hotel Management</title>
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
                <i class="bx bx-key"></i>
            </div>
            <h3>Recover Password</h3>
            <p>Enter your staff email to receive a recovery OTP</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-error-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="forgot-password.php">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

            <div class="form-floating mb-3">
                <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Staff Email" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <label for="email"><i class="bx bx-envelope me-1"></i> Registered Staff Email</label>
            </div>

            <button type="submit" class="admin-btn-primary mb-3">
                <i class="bx bx-send me-1"></i> Send Password Reset OTP
            </button>
        </form>

        <div class="text-center pt-2 border-top small">
            Remembered your credentials? <a href="index.php" class="text-danger fw-bold text-decoration-none">Return to Sign In</a>
        </div>

    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
