<?php
/**
 * Tiano Hotel & Suites - Admin Sign Out Action
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../include/functions.php';

if (!empty($_SESSION['admin_id'])) {
    log_activity($pdo, $_SESSION['admin_id'], $_SESSION['admin_name'] ?? 'Staff', 'staff_logout', 'auth', $_SESSION['admin_id'], "Staff operator " . ($_SESSION['admin_name'] ?? 'Staff') . " signed out from management console");
}

logoutAdmin();
header("Location: index.php?msg=" . urlencode("You have been signed out successfully."));
exit;
