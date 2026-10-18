<?php
/**
 * Tiano Hotel & Suites - Staff Registration / Sign Up Portal
 * Dispatches 6-digit email verification code via PHPMailer.
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
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? 'receptionist');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $csrfToken= $_POST['csrf_token'] ?? '';

    if (!validateCsrf($csrfToken)) {
        $error = "Security token mismatch. Please try again.";
    } elseif (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $check = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $error = "A staff member with this username or email already exists.";
        } else {
            // Generate 6-digit verification code
            $code = (string)random_int(100000, 999999);
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(24));

            $insert = $pdo->prepare("
                INSERT INTO admin_users (
                    username, fullname, email, password_hash, role,
                    is_verified, verification_code, verification_token, verification_expires,
                    status, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, 0, ?, ?, ?, 'active', NOW()
                )
            ");

            if ($insert->execute([$fullname, $username, $email, $passwordHash, $role, $code, $token, $expires])) {
                $isLocal = (isset($_SERVER['HTTP_HOST']) && in_array(parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST), ['localhost', '127.0.0.1']));
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'] ?? 'tianohotelandsuites.com';
                $basePath = $isLocal ? '/Tiano-Hotel' : '';
                $verifyUrl = $protocol . $host . $basePath . "/admin/verify-email.php?email=" . urlencode($email) . "&token=" . urlencode($token);

                $mailResult = sendAdminVerificationEmail([
                    'fullname' => $fullname,
                    'username' => $username,
                    'email'    => $email
                ], $code, $verifyUrl);

                header("Location: verify-email.php?email=" . urlencode($email) . "&msg=" . urlencode("Account registered! We've sent a 6-digit verification code to your email."));
                exit;
            } else {
                $error = "An error occurred while creating your account. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Staff Account | Tiano Hotel Management</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-auth-wrap">
    <div class="admin-auth-card" style="max-width: 520px;">
        
        <div class="admin-auth-brand">
            <img src="../assets/img/compnay_logo.JPG" alt="Tiano Hotel Logo">
            <h3>Staff Registration</h3>
            <p>Create staff credentials for Tiano Hotel &amp; Suites</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                <i class="bx bx-error-circle fs-5 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="signup.php">
            <input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">

            <div class="row g-2 mb-2">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control rounded-3" id="fullname" name="fullname" placeholder="Full Name" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                        <label for="fullname">Full Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control rounded-3" id="username" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        <label for="username">Username</label>
                    </div>
                </div>
            </div>

            <div class="form-floating mb-2">
                <input type="email" class="form-control rounded-3" id="email" name="email" placeholder="Staff Email Address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <label for="email"><i class="bx bx-envelope me-1"></i> Staff Email Address</label>
            </div>

            <div class="form-floating mb-2">
                <select class="form-select rounded-3" id="role" name="role" required>
                    <option value="receptionist" selected>Front Desk Receptionist</option>
                    <option value="manager">Operations Manager</option>
                </select>
                <label for="role"><i class="bx bx-badge-check me-1"></i> Staff Position / Role</label>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control rounded-3" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <label for="confirm_password">Confirm</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="admin-btn-primary mb-3">
                <i class="bx bx-user-plus me-1"></i> Register &amp; Verify Email
            </button>
        </form>

        <div class="text-center pt-2 border-top small text-muted">
            Already have an active staff account? <a href="index.php" class="text-danger fw-bold text-decoration-none">Sign In Here</a>
        </div>

    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
