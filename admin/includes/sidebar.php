<?php
/**
 * Tiano Hotel & Suites - Admin Sidebar Navigation
 */
if (!isset($unreadBookingsCount)) {
    $unreadBookingsCount = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'confirmed' AND payment_status = 'unpaid'")->fetchColumn();
}
if (!isset($unreadMessagesCount)) {
    $unreadMessagesCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
}
?>
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="admin-sidebar-header justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="admin-sidebar-logo-wrap">
                <img src="../assets/img/compnay_logo.JPG" alt="Tiano Logo">
            </div>
            <div class="admin-sidebar-brand-text">
                <h4>TIANO HOTEL</h4>
                <small>MANAGEMENT PORTAL</small>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-link text-white-50 d-lg-none p-1 ms-auto" id="sidebarCloseBtn" aria-label="Close sidebar" style="text-decoration:none;">
            <i class="bx bx-x fs-2 text-white"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="admin-nav">
        <div class="admin-nav-category">
            <span>Main Console</span>
        </div>
        <a href="dashboard.php" class="admin-nav-link <?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-grid-alt"></i>
                <span>Dashboard</span>
            </div>
        </a>
        <a href="bookings.php" class="admin-nav-link <?php echo ($activePage === 'bookings') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-book-bookmark"></i>
                <span>Reservations &amp; Bookings</span>
            </div>
            <span class="admin-nav-badge admin-nav-badge-danger <?php echo ($unreadBookingsCount > 0) ? '' : 'd-none'; ?>" id="sidebarBookingsBadge">
                <?php echo $unreadBookingsCount; ?>
            </span>
        </a>

        <div class="admin-nav-category">
            <span>Operations &amp; Content</span>
        </div>
        <a href="rooms.php" class="admin-nav-link <?php echo ($activePage === 'rooms') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-bed"></i>
                <span>Rooms &amp; Inventory</span>
            </div>
        </a>
        <a href="services.php" class="admin-nav-link <?php echo ($activePage === 'services') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-cog"></i>
                <span>Services CMS</span>
            </div>
        </a>
        <a href="gallery.php" class="admin-nav-link <?php echo ($activePage === 'gallery') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-images"></i>
                <span>Gallery CMS</span>
            </div>
        </a>
        <a href="blog.php" class="admin-nav-link <?php echo ($activePage === 'blog') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-news"></i>
                <span>Journal &amp; Blog CMS</span>
            </div>
        </a>

        <div class="admin-nav-category">
            <span>Guest Communications</span>
        </div>
        <a href="messages.php" class="admin-nav-link <?php echo ($activePage === 'messages') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-envelope"></i>
                <span>Inbound Inquiries</span>
            </div>
            <span class="admin-nav-badge admin-nav-badge-amber <?php echo ($unreadMessagesCount > 0) ? '' : 'd-none'; ?>" id="sidebarMessagesBadge">
                <?php echo $unreadMessagesCount; ?>
            </span>
        </a>
        <a href="newsletter.php" class="admin-nav-link <?php echo ($activePage === 'newsletter') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-paper-plane"></i>
                <span>Newsletter Desk</span>
            </div>
        </a>

        <div class="admin-nav-category">
            <span>System &amp; Settings</span>
        </div>
        <a href="activity-logs.php" class="admin-nav-link <?php echo ($activePage === 'activity_logs') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-history"></i>
                <span>Activity Audit Logs</span>
            </div>
        </a>
        <a href="settings.php" class="admin-nav-link <?php echo ($activePage === 'settings') ? 'active' : ''; ?>">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-slider-alt"></i>
                <span>Hotel Settings</span>
            </div>
        </a>

        <div class="admin-nav-category">
            <span>External</span>
        </div>
        <a href="../index.php" target="_blank" class="admin-nav-link">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-globe"></i>
                <span>Live Hotel Website</span>
            </div>
            <i class="bx bx-link-external text-white-50" style="font-size:12px;"></i>
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="admin-sidebar-footer">
        <div class="admin-user-pill">
            <div class="position-relative">
                <div class="admin-user-avatar">
                    <i class="bx bx-user-check"></i>
                </div>
                <span class="admin-online-indicator" title="24/7 Session Active"></span>
            </div>
            <div class="overflow-hidden flex-grow-1">
                <div class="text-white fw-bold text-truncate" style="font-size:13px;"><?php echo htmlspecialchars($admin['fullname']); ?></div>
                <div class="text-white-50 text-truncate" style="font-size:10.5px;"><?php echo htmlspecialchars($admin['role'] ?? 'Staff Administrator'); ?></div>
            </div>
        </div>
        <a href="logout.php" class="admin-logout-btn" title="Sign out of administrative session">
            <i class="bx bx-log-out-circle fs-5"></i>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
