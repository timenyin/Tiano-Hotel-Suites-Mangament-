<?php
/**
 * Tiano Hotel & Suites - Admin Layout Header
 */
require_once __DIR__ . '/auth.php';
requireAuth();

$admin = currentAdmin($pdo);
if (!$admin || $admin['status'] !== 'active') {
    logoutAdmin();
    header("Location: index.php?msg=" . urlencode("Account inactive or suspended."));
    exit;
}

$pageTitle = isset($pageTitle) ? $pageTitle : 'Management Console';
$activePage = isset($activePage) ? $activePage : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Tiano Hotel Admin</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
</head>
<body class="admin-body">

<div class="admin-wrapper">
    <!-- Include Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="admin-main">
        
        <!-- Top Navigation Bar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light d-lg-none border-0 p-2 rounded-3 shadow-none" id="sidebarToggleBtn" type="button" aria-label="Toggle navigation drawer">
                    <i class="bx bx-menu fs-4 text-dark"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark text-truncate"><?php echo htmlspecialchars($pageTitle); ?></h5>
            </div>

<?php
// Query live notification alerts
$unreadBookingsCount = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'confirmed' AND payment_status = 'unpaid'")->fetchColumn();
$unreadMessagesCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
$totalNotifications = $unreadBookingsCount + $unreadMessagesCount;

$recentNotifBookings = $pdo->query("SELECT booking_reference, guest_name, created_at FROM bookings ORDER BY id DESC LIMIT 3")->fetchAll();
?>
            <div class="d-flex align-items-center gap-3">
                
                <!-- 24/7 Front-Desk Audio Chime Status Pill -->
                <button type="button" id="tianoAudioPill" class="btn btn-sm btn-light border rounded-pill d-inline-flex align-items-center gap-1 py-1 px-2 text-dark shadow-sm" style="font-size: 11px; font-weight: 700;" onclick="window.TianoAlerts && window.TianoAlerts.unlockAudio()" title="24/7 Front-Desk Audio Alarm - Click to test or arm chime">
                    <i class="bx bx-volume-full text-success"></i> <span class="d-none d-md-inline">Audio Active</span>
                </button>

                <!-- Notification Bell Dropdown (No sound on click) -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-light position-relative p-2 rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Operational Alerts & Recent Reservations">
                        <i class="bx bx-bell fs-5 text-dark"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger <?php echo ($totalNotifications > 0) ? '' : 'd-none'; ?>" id="headerAlertsBadge">
                            <?php echo $totalNotifications; ?>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-0 mt-2" style="width: 350px; z-index: 1055; border-color: #e2e8f0 !important;">
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                            <div>
                                <span class="fw-bold text-dark small text-uppercase d-block">Front-Desk Alerts</span>
                                <small class="text-success fw-bold d-flex align-items-center gap-1 mt-1" style="font-size:11px;">
                                    <span class="badge-dot-live"></span> 24/7 Monitoring Active
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 22px; height: 22px; min-width: 22px; font-size: 11px; font-weight: 800; aspect-ratio: 1/1;">
                                    <?php echo $totalNotifications; ?>
                                </span>
                                <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2 py-1" style="font-size: 11px;">
                                    Pending
                                </span>
                            </div>
                        </div>

                        <div class="p-2" style="max-height: 280px; overflow-y: auto;">
                            <?php if ($unreadBookingsCount > 0): ?>
                                <a href="bookings.php" class="dropdown-item p-2 rounded-3 mb-1 d-flex align-items-start gap-2 text-wrap">
                                    <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; aspect-ratio: 1/1;">
                                        <i class="bx bx-book-bookmark fs-6"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-dark d-block"><?php echo $unreadBookingsCount; ?> Pending Reservation(s)</small>
                                        <small class="text-muted" style="font-size: 11px;">Guests awaiting check-in settlement</small>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php if ($unreadMessagesCount > 0): ?>
                                <a href="messages.php" class="dropdown-item p-2 rounded-3 mb-1 d-flex align-items-start gap-2 text-wrap">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; aspect-ratio: 1/1;">
                                        <i class="bx bx-envelope fs-6"></i>
                                    </div>
                                    <div>
                                        <small class="fw-bold text-dark d-block"><?php echo $unreadMessagesCount; ?> Inbound Inquiry</small>
                                        <small class="text-muted" style="font-size: 11px;">Unread contact desk messages</small>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <div id="headerRecentBookingsList">
                                <?php foreach ($recentNotifBookings as $nb): ?>
                                    <a href="bookings.php?search=<?php echo urlencode($nb['booking_reference']); ?>" class="dropdown-item p-2 rounded-3 d-flex align-items-start gap-2 text-wrap">
                                        <div class="rounded-circle bg-light text-secondary d-inline-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 36px; height: 36px; min-width: 36px; aspect-ratio: 1/1;">
                                            <i class="bx bx-user fs-6"></i>
                                        </div>
                                        <div>
                                            <small class="fw-bold text-dark d-block">#<?php echo htmlspecialchars($nb['booking_reference']); ?> &bull; <?php echo htmlspecialchars($nb['guest_name']); ?></small>
                                            <small class="text-muted" style="font-size: 10px;"><?php echo date('d M, H:i', strtotime($nb['created_at'])); ?></small>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="p-2 border-top text-center bg-light rounded-bottom-4">
                            <a href="bookings.php" class="small fw-bold text-danger text-decoration-none">Open Reservations Desk &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="vr opacity-25 d-none d-sm-block my-auto" style="height: 28px;"></div>

                <div class="text-end d-none d-sm-block ps-1">
                    <span class="d-block fw-bold small text-dark"><?php echo htmlspecialchars($admin['fullname']); ?></span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:10px;">
                        <?php echo strtoupper(str_replace('_', ' ', $admin['role'])); ?>
                    </span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-0" type="button" data-bs-toggle="dropdown" style="width: 36px; height: 36px; aspect-ratio: 1/1;">
                        <i class="bx bx-user fs-5"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                        <li class="px-3 py-2 border-bottom">
                            <small class="text-muted d-block">Signed in as</small>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($admin['email']); ?></span>
                        </li>
                        <li><a class="dropdown-item py-2" href="../index.php" target="_blank"><i class="bx bx-link-external me-2"></i> Visit Website</a></li>
                        <li><a class="dropdown-item py-2" href="settings.php"><i class="bx bx-slider-alt me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="bx bx-log-out me-2"></i> Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Dynamic Page Content Starts Here -->
        <main class="admin-content">
