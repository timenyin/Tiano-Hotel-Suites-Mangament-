<?php
/**
 * Tiano Hotel & Suites - Reservations & Front Desk Manager
 * Allows receptionists and managers to filter, search, assign rooms, update payment,
 * check in guests, message guests via WhatsApp, and print digital passes.
 */
$pageTitle = 'Reservations & Bookings';
$activePage = 'bookings';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Self-healing migration: Ensure ENUM supports 'completed' and repair any blank booking status
try {
    $pdo->exec("ALTER TABLE bookings MODIFY COLUMN booking_status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'completed', 'cancelled', 'no_show') DEFAULT 'confirmed'");
} catch (Exception $e) {}

try {
    $pdo->exec("UPDATE bookings SET booking_status = 'confirmed' WHERE booking_status = '' OR booking_status IS NULL");
} catch (Exception $e) {}

// Handle Deleting a Booking (Permanently remove record and release allocated room space)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_booking') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    try {
        $stmtB = $pdo->prepare("SELECT id, booking_reference, assigned_room_id, guest_name FROM bookings WHERE id = ?");
        $stmtB->execute([$bookingId]);
        $bRow = $stmtB->fetch(PDO::FETCH_ASSOC);

        if ($bRow) {
            // If physical room was allocated, release back to vacant
            if (!empty($bRow['assigned_room_id'])) {
                $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant' WHERE id = ?")->execute([$bRow['assigned_room_id']]);
            }

            // Permanently remove booking from database
            $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bookingId]);

            // Log activity
            log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_booking', 'booking', $bookingId, "Deleted reservation #{$bRow['booking_reference']} (Guest: {$bRow['guest_name']}) and released room inventory");

            $success = "Reservation #{$bRow['booking_reference']} permanently deleted and room inventory released.";
        } else {
            $error = "Reservation record not found.";
        }
    } catch (Exception $e) {
        $error = "Error deleting reservation: " . $e->getMessage();
    }
}

// Handle Batch Deleting Bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_delete_bookings') {
    $selectedIds = $_POST['selected_booking_ids'] ?? [];
    if (!empty($selectedIds) && is_array($selectedIds)) {
        $delCount = 0;
        foreach ($selectedIds as $bId) {
            $bId = (int)$bId;
            if ($bId > 0) {
                try {
                    $stmtB = $pdo->prepare("SELECT id, booking_reference, assigned_room_id, guest_name FROM bookings WHERE id = ?");
                    $stmtB->execute([$bId]);
                    $bRow = $stmtB->fetch(PDO::FETCH_ASSOC);
                    if ($bRow) {
                        if (!empty($bRow['assigned_room_id'])) {
                            $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant' WHERE id = ?")->execute([$bRow['assigned_room_id']]);
                        }
                        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bId]);
                        log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_booking', 'booking', $bId, "Batch deleted reservation #{$bRow['booking_reference']} (Guest: {$bRow['guest_name']})");
                        $delCount++;
                    }
                } catch (Exception $e) {}
            }
        }
        $success = "Successfully deleted {$delCount} reservation(s) and restored room inventory.";
    } else {
        $error = "No reservations were selected for deletion.";
    }
}

// Handle Status & Room Assignment Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking') {
    $bookingId       = (int)($_POST['booking_id'] ?? 0);
    $bookingStatus   = trim($_POST['booking_status'] ?? 'confirmed');
    $assignedRoomId  = !empty($_POST['assigned_room_id']) ? (int)$_POST['assigned_room_id'] : null;
    $idType          = trim($_POST['id_type'] ?? 'None');
    if ($idType === 'None' || empty($idType)) {
        $idType = 'None';
        $idNumber = 'None';
    } else {
        $idNumber = trim($_POST['id_number'] ?? '');
    }
    $paymentStatus   = trim($_POST['payment_status'] ?? 'unpaid');
    $paymentMethod   = trim($_POST['payment_method'] ?? 'pay_on_arrival');
    $adminNotes      = trim($_POST['admin_notes'] ?? '');

    try {
        // Fetch previous booking state to handle room reassignments and track payment approver
        $stmtOld = $pdo->prepare("SELECT assigned_room_id, booking_status, booking_reference, payment_status, payment_approved_by, payment_approved_at FROM bookings WHERE id = ?");
        $stmtOld->execute([$bookingId]);
        $oldBooking = $stmtOld->fetch(PDO::FETCH_ASSOC);
        $oldRoomId = $oldBooking ? $oldBooking['assigned_room_id'] : null;

        $paymentApprovedBy = $oldBooking['payment_approved_by'] ?? null;
        $paymentApprovedAt = $oldBooking['payment_approved_at'] ?? null;

        if ($paymentStatus === 'paid') {
            if (empty($paymentApprovedBy) || ($oldBooking && $oldBooking['payment_status'] !== 'paid')) {
                $paymentApprovedBy = $admin['fullname'];
                $paymentApprovedAt = date('Y-m-d H:i:s');
            }
        } elseif ($paymentStatus === 'unpaid') {
            $paymentApprovedBy = null;
            $paymentApprovedAt = null;
        }

        $stmt = $pdo->prepare("
            UPDATE bookings SET
                booking_status = ?,
                assigned_room_id = ?,
                id_type = ?,
                id_number = ?,
                payment_status = ?,
                payment_method = ?,
                admin_notes = ?,
                payment_approved_by = ?,
                payment_approved_at = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $bookingStatus,
            $assignedRoomId,
            $idType,
            $idNumber,
            $paymentStatus,
            $paymentMethod,
            $adminNotes,
            $paymentApprovedBy,
            $paymentApprovedAt,
            $bookingId
        ]);

        // If assigned room changed, release previous room back to vacant
        if ($oldRoomId && $oldRoomId != $assignedRoomId) {
            $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant' WHERE id = ?")->execute([$oldRoomId]);
        }

        // Update current assigned physical room occupancy and cleanliness state
        if ($assignedRoomId) {
            if ($bookingStatus === 'checked_in') {
                $pdo->prepare("UPDATE rooms SET occupancy_status = 'occupied' WHERE id = ?")->execute([$assignedRoomId]);
            } elseif ($bookingStatus === 'completed' || $bookingStatus === 'checked_out') {
                // Completed guest checkout: release room to vacant and flag as dirty for turnover
                $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant', cleanliness_status = 'dirty' WHERE id = ?")->execute([$assignedRoomId]);
            } elseif ($bookingStatus === 'cancelled' || $bookingStatus === 'no_show') {
                $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant' WHERE id = ?")->execute([$assignedRoomId]);
            }
        }

        $ref = $oldBooking ? $oldBooking['booking_reference'] : $bookingId;
        log_activity($pdo, $admin['id'], $admin['fullname'], 'update_booking', 'booking', $bookingId, "Updated booking #{$ref} (Status: {$bookingStatus}, Room: " . ($assignedRoomId ?: 'None') . ")");

        $success = "Reservation #{$ref} updated successfully.";
    } catch (Exception $e) {
        $error = "Error updating booking: " . $e->getMessage();
    }
}

// Filters, Search & Sorting
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy       = trim($_GET['sort_by'] ?? 'newest');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$sortMap = [
    'newest'       => 'b.id DESC',
    'oldest'       => 'b.id ASC',
    'checkin_asc'  => 'b.check_in_date ASC',
    'amount_desc'  => 'b.total_amount DESC',
    'amount_asc'   => 'b.total_amount ASC',
    'guest_asc'    => 'b.guest_name ASC',
    'paid_first'   => 'b.payment_status = "paid" DESC, b.id DESC',
    'unpaid_first' => 'b.payment_status = "unpaid" DESC, b.id DESC',
];
$orderBySql = $sortMap[$sortBy] ?? 'b.id DESC';

// Count Total Filtered Records
$countSql = "
    SELECT COUNT(*) 
    FROM bookings b
    JOIN room_categories c ON b.category_id = c.id
    LEFT JOIN rooms r ON b.assigned_room_id = r.id
    WHERE 1=1
";
$params = [];

if ($statusFilter === 'confirmed') {
    $countSql .= " AND (b.booking_status = 'confirmed' OR b.booking_status IS NULL OR b.booking_status = '' OR b.booking_status = 'pending')";
} elseif ($statusFilter === 'completed') {
    $countSql .= " AND b.booking_status IN ('completed', 'checked_out')";
} elseif ($statusFilter === 'cancelled') {
    $countSql .= " AND b.booking_status IN ('cancelled', 'no_show')";
} elseif ($statusFilter !== 'all') {
    $countSql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $countSql .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ? OR b.guest_phone LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Build Main Query with Pagination and Sorting
$sql = "
    SELECT b.*, c.name AS category_name, r.room_number, r.floor_number
    FROM bookings b
    JOIN room_categories c ON b.category_id = c.id
    LEFT JOIN rooms r ON b.assigned_room_id = r.id
    WHERE 1=1
";
if ($statusFilter === 'confirmed') {
    $sql .= " AND (b.booking_status = 'confirmed' OR b.booking_status IS NULL OR b.booking_status = '' OR b.booking_status = 'pending')";
} elseif ($statusFilter === 'completed') {
    $sql .= " AND b.booking_status IN ('completed', 'checked_out')";
} elseif ($statusFilter === 'cancelled') {
    $sql .= " AND b.booking_status IN ('cancelled', 'no_show')";
} elseif ($statusFilter !== 'all') {
    $sql .= " AND b.booking_status = ?";
}

if (!empty($search)) {
    $sql .= " AND (b.booking_reference LIKE ? OR b.guest_name LIKE ? OR b.guest_email LIKE ? OR b.guest_phone LIKE ?)";
}
$sql .= " ORDER BY {$orderBySql} LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
$bindIdx = 1;
if ($statusFilter !== 'all' && !in_array($statusFilter, ['confirmed', 'completed', 'cancelled'])) {
    $stmt->bindValue($bindIdx++, $statusFilter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $like = "%{$search}%";
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
}
$stmt->bindValue($bindIdx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIdx++, $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll();

// Fetch all physical rooms with category name for the dropdown
$allRooms = $pdo->query("
    SELECT r.id, r.category_id, r.room_number, r.floor_number, r.cleanliness_status, r.occupancy_status,
           rc.name AS category_name
    FROM rooms r
    LEFT JOIN room_categories rc ON r.category_id = rc.id
    ORDER BY CAST(r.room_number AS UNSIGNED) ASC, r.room_number ASC
")->fetchAll();
?>

<!-- Alerts -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bx bx-check-circle fs-5"></i>
        <div><?php echo htmlspecialchars($success); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bx bx-error-circle fs-5"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Top Filter Tabs -->
<div class="d-flex flex-wrap gap-2 mb-3" style="font-size:13.5px;">
    <a class="btn fw-bold px-3 py-2 rounded-3 <?php echo ($statusFilter === 'all') ? 'btn-danger text-white' : 'btn-light text-dark border'; ?>" style="<?php echo ($statusFilter === 'all') ? 'background:#E73539; border-color:#E73539;' : 'background:#fff;'; ?>" href="bookings.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>">
        All Bookings
    </a>
    <a class="btn fw-bold px-3 py-2 rounded-3 <?php echo ($statusFilter === 'confirmed') ? 'btn-danger text-white' : 'btn-light text-dark border'; ?>" style="<?php echo ($statusFilter === 'confirmed') ? 'background:#E73539; border-color:#E73539;' : 'background:#fff;'; ?>" href="bookings.php?status=confirmed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
        Confirmed
    </a>
    <a class="btn fw-bold px-3 py-2 rounded-3 <?php echo ($statusFilter === 'checked_in') ? 'btn-danger text-white' : 'btn-light text-dark border'; ?>" style="<?php echo ($statusFilter === 'checked_in') ? 'background:#E73539; border-color:#E73539;' : 'background:#fff;'; ?>" href="bookings.php?status=checked_in<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
        Checked In
    </a>
    <a class="btn fw-bold px-3 py-2 rounded-3 <?php echo ($statusFilter === 'completed') ? 'btn-danger text-white' : 'btn-light text-dark border'; ?>" style="<?php echo ($statusFilter === 'completed') ? 'background:#E73539; border-color:#E73539;' : 'background:#fff;'; ?>" href="bookings.php?status=completed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
        Completed
    </a>
    <a class="btn fw-bold px-3 py-2 rounded-3 <?php echo ($statusFilter === 'cancelled') ? 'btn-danger text-white' : 'btn-light text-dark border'; ?>" style="<?php echo ($statusFilter === 'cancelled') ? 'background:#E73539; border-color:#E73539;' : 'background:#fff;'; ?>" href="bookings.php?status=cancelled<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
        Cancelled
    </a>
</div>

<!-- Search & Sort Filter Bar -->
<div class="admin-card mb-4 p-3">
    <form method="GET" action="bookings.php" class="row g-2 align-items-center">
        <?php if ($statusFilter !== 'all'): ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px; height:46px;"><i class="bx bx-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search reference, guest name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius:0 10px 10px 0; height:46px;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="position-relative">
                <i class="bx bx-sort position-absolute text-muted" style="left:14px; top:50%; transform:translateY(-50%); pointer-events:none; font-size:16px; z-index:2;"></i>
                <select name="sort_by" class="form-select no-custom-select" data-no-custom-select="true" onchange="this.form.submit()" style="border-radius:10px; height:46px; font-weight:600; font-size:13.5px; padding-left:38px; cursor:pointer; background-color:#ffffff;">
                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="checkin_asc" <?php echo $sortBy === 'checkin_asc' ? 'selected' : ''; ?>>Check-In Date</option>
                    <option value="amount_desc" <?php echo $sortBy === 'amount_desc' ? 'selected' : ''; ?>>Amount: High-Low</option>
                    <option value="amount_asc" <?php echo $sortBy === 'amount_asc' ? 'selected' : ''; ?>>Amount: Low-High</option>
                    <option value="guest_asc" <?php echo $sortBy === 'guest_asc' ? 'selected' : ''; ?>>Guest Name (A-Z)</option>
                    <option value="paid_first" <?php echo $sortBy === 'paid_first' ? 'selected' : ''; ?>>Paid Bookings First</option>
                    <option value="unpaid_first" <?php echo $sortBy === 'unpaid_first' ? 'selected' : ''; ?>>Unpaid First</option>
                </select>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark fw-bold px-3 flex-grow-1" style="border-radius:10px; height:46px;">
                <i class="bx bx-filter-alt me-1"></i> Apply Filter
            </button>
            <?php if (!empty($search) || $sortBy !== 'newest'): ?>
                <a href="bookings.php<?php echo $statusFilter !== 'all' ? '?status=' . urlencode($statusFilter) : ''; ?>" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius:10px; height:46px; width:46px; flex-shrink:0;" title="Reset Filters">
                    <i class="bx bx-x fs-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Bookings Table Card -->
<div class="admin-card" style="overflow:visible;">
    <div class="admin-card-header">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Guest Reservations &amp; Bookings</h5>
            <small class="text-muted">Displaying <?php echo count($bookings); ?> record(s)</small>
        </div>
        <div>
            <a href="../booking.php" target="_blank" class="btn btn-sm btn-danger fw-bold rounded-pill px-3" style="background:#E73539;">
                <i class="bx bx-plus-circle me-1"></i> New Walk-In Reservation
            </a>
        </div>
    </div>

    <!-- Batch Action Bar -->
    <div id="bookingsBatchActionBar" class="d-none bg-danger bg-opacity-10 border-bottom border-danger px-3 py-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 text-danger fw-bold small">
            <i class="bx bx-check-square fs-5"></i>
            <span id="bookingsSelectedCount">0</span> reservation(s) selected
        </div>
        <button type="button" class="btn btn-sm btn-danger fw-bold px-3 py-1" style="border-radius:6px;" onclick="submitBatchDeleteBookings()">
            <i class="bx bx-trash me-1"></i> Delete Selected
        </button>
    </div>

    <form method="POST" action="bookings.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" id="batchBookingsForm">
        <input type="hidden" name="action" value="batch_delete_bookings">

        <div class="table-responsive" style="min-height:330px; overflow-y:visible;">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th style="width:40px;" class="text-center">
                            <input type="checkbox" id="masterBookingsCheckbox" class="form-check-input" onclick="toggleAllBookings(this)" title="Select All">
                        </th>
                        <th style="width:55px;" class="text-center">S/N</th>
                        <th>Reference</th>
                        <th>Guest Information</th>
                        <th>Room Tier &amp; Allocated</th>
                        <th>Schedule</th>
                        <th>Financials</th>
                        <th>Status</th>
                        <th class="text-end" style="width:80px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="bookingsTableTbody">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bx bx-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                            No reservations found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $idx => $b): 
                        $totalDue = $b['total_amount'] + ($b['caution_deposit'] ?? 0);
                        $cleanPhone = preg_replace('/[^0-9]/', '', $b['guest_phone']);
                        $waMsg = "Hello " . $b['guest_name'] . ", this is front desk at Tiano Hotel & Suites regarding your reservation (" . $b['booking_reference'] . ").";
                        $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($waMsg);

                        // Normalize and resolve status badge styling and text
                        $rawStatus = strtolower(trim((string)($b['booking_status'] ?? '')));
                        if (empty($rawStatus) || $rawStatus === 'pending') {
                            $bStatus = 'confirmed';
                        } else {
                            $bStatus = $rawStatus;
                        }

                        $bBadgeCls = 'status-badge-warning';
                        $bStatusLabel = 'Confirmed';

                        if ($bStatus === 'confirmed') {
                            $bBadgeCls = 'status-badge-warning';
                            $bStatusLabel = 'Confirmed';
                        } elseif ($bStatus === 'checked_in') {
                            $bBadgeCls = 'status-badge-primary';
                            $bStatusLabel = 'Checked In';
                        } elseif ($bStatus === 'completed' || $bStatus === 'checked_out') {
                            $bBadgeCls = 'status-badge-success';
                            $bStatusLabel = 'Completed';
                        } elseif ($bStatus === 'cancelled' || $bStatus === 'no_show') {
                            $bBadgeCls = 'status-badge-danger';
                            $bStatusLabel = 'Cancelled';
                        } else {
                            $bBadgeCls = 'status-badge-secondary';
                            $bStatusLabel = ucfirst(str_replace('_', ' ', $bStatus));
                        }

                        // Payment Status & Method Resolution
                        $pStatus = strtolower(trim((string)($b['payment_status'] ?? 'unpaid')));
                        $pMethod = strtolower(trim((string)($b['payment_method'] ?? 'pay_on_arrival')));
                        $approverName = !empty($b['payment_approved_by']) ? $b['payment_approved_by'] : ($pStatus === 'paid' ? 'Front Desk Admin' : null);
                    ?>
                        <tr>
                            <!-- Checkbox -->
                            <td class="text-center">
                                <input type="checkbox" name="selected_booking_ids[]" value="<?php echo $b['id']; ?>" class="form-check-input booking-row-checkbox" onchange="updateBookingsSelection()">
                            </td>

                            <!-- S/N -->
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border fw-bold" style="font-size:12px;">#<?php echo ($offset + $idx + 1); ?></span>
                            </td>

                            <!-- Reference -->
                            <td>
                                <strong class="text-danger fw-bold fs-6 d-block"><?php echo htmlspecialchars($b['booking_reference']); ?></strong>
                                <small class="text-muted" style="font-size:11px;">Booked <?php echo date('d M, H:i', strtotime($b['created_at'])); ?></small>
                            </td>

                            <!-- Guest Info -->
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($b['guest_name']); ?></div>
                                <div class="small text-muted"><i class="bx bx-phone me-1"></i> <?php echo htmlspecialchars($b['guest_phone']); ?></div>
                                <div class="small text-muted"><i class="bx bx-envelope me-1"></i> <?php echo htmlspecialchars($b['guest_email']); ?></div>
                                <small class="badge bg-light text-secondary border mt-1">
                                    <?php echo $b['adults']; ?> Adult<?php echo $b['adults'] > 1 ? 's' : ''; ?>, <?php echo $b['children']; ?> Child
                                </small>
                                
                                <!-- Prominent Guest Special Requests -->
                                <?php if (!empty($b['special_requests'])): ?>
                                    <div class="mt-2">
                                        <span class="badge text-start p-2 d-inline-flex align-items-start gap-1" style="background:#fff5f5; border:1px solid #fecaca; color:#991b1b; font-size:11px; white-space:normal; max-width:260px;" title="<?php echo htmlspecialchars($b['special_requests']); ?>">
                                            <i class="bx bx-message-rounded-dots mt-1 flex-shrink-0 text-danger"></i>
                                            <span><strong>Special Request:</strong> <?php echo htmlspecialchars($b['special_requests']); ?></span>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Front Desk Staff Notes Display -->
                                <?php if (!empty($b['admin_notes'])): ?>
                                    <div class="mt-1">
                                        <span class="badge text-start p-2 d-inline-flex align-items-start gap-1" style="background:#f8fafc; border:1px solid #cbd5e1; color:#334155; font-size:11px; white-space:normal; max-width:260px;" title="<?php echo htmlspecialchars($b['admin_notes']); ?>">
                                            <i class="bx bx-notepad mt-1 flex-shrink-0 text-primary"></i>
                                            <span><strong>Staff Note:</strong> <?php echo htmlspecialchars($b['admin_notes']); ?></span>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Room Allocation -->
                            <td>
                                <div class="d-flex flex-column gap-1" style="min-width: 165px;">
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 13.5px;">
                                        <i class="bx bx-hotel text-danger" style="font-size: 15px;"></i>
                                        <span><?php echo htmlspecialchars($b['category_name']); ?></span>
                                    </div>
                                    <?php if (!empty($b['room_number'])): ?>
                                        <div class="room-allocated-badge" style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46;" title="Physical Room Allocated">
                                            <i class="bx bx-door-open" style="color:#059669; font-size:15px;"></i>
                                            <span>Room <strong><?php echo htmlspecialchars($b['room_number']); ?></strong> &bull; <?php echo ((int)($b['floor_number'] ?? 1) === 0 ? 'Ground Fl.' : 'Fl. ' . htmlspecialchars($b['floor_number'] ?? '1')); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="room-unallocated-badge" style="background:#fffbeb; border:1px solid #fde68a; color:#92400e;" title="No Physical Room Assigned Yet">
                                            <i class="bx bx-error-circle" style="color:#d97706; font-size:15px;"></i>
                                            <span>Room Unassigned</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Schedule -->
                            <td>
                                <div class="small fw-bold text-dark">
                                    <?php echo date('d M', strtotime($b['check_in_date'])); ?> &rarr; <?php echo date('d M Y', strtotime($b['check_out_date'])); ?>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bx bx-moon text-danger"></i> <?php echo $b['nights']; ?> Night(s) Stay
                                </small>
                            </td>

                            <!-- Financials -->
                            <td>
                                <div class="fw-bold text-dark"><?php echo format_naira($totalDue); ?></div>
                                <small class="text-muted d-block" style="font-size:11px;">
                                    Caution: <?php echo format_naira($b['caution_deposit']); ?>
                                </small>
                                <?php if ($pStatus === 'paid'): 
                                    $methodLabel = 'PAID';
                                    if ($pMethod === 'pos') $methodLabel = 'PAID (POS)';
                                    elseif ($pMethod === 'cash') $methodLabel = 'PAID (CASH)';
                                    elseif ($pMethod === 'bank_transfer') $methodLabel = 'PAID (TRANSFER)';
                                    elseif (!empty($pMethod) && $pMethod !== 'pay_on_arrival') $methodLabel = 'PAID (' . strtoupper($pMethod) . ')';
                                ?>
                                    <span class="badge bg-success" style="font-size:10px;"><?php echo $methodLabel; ?></span>
                                    <small class="d-block text-success fw-semibold mt-1" style="font-size:11px;" title="Payment approved by staff">
                                        <i class="bx bx-check-shield"></i> Approved by: <strong><?php echo htmlspecialchars($approverName); ?></strong>
                                    </small>
                                <?php elseif ($pStatus === 'refunded'): ?>
                                    <span class="badge bg-secondary" style="font-size:10px;">REFUNDED</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">UNPAID (PAY ON ARRIVAL)</span>
                                    <small class="d-block text-muted mt-1" style="font-size:11px;">
                                        <i class="bx bx-time"></i> Awaiting Settlement
                                    </small>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="status-badge <?php echo $bBadgeCls; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo htmlspecialchars($bStatusLabel); ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="text-end align-middle">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions &amp; Options">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; min-width:205px;">
                                        <li>
                                            <button type="button" class="dropdown-item text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#manageModal<?php echo $b['id']; ?>">
                                                <i class="bx bx-check-square text-danger me-2"></i> Check-In &amp; Manage
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="../voucher.php?ref=<?php echo urlencode($b['booking_reference']); ?>" target="_blank">
                                                <i class="bx bx-receipt text-primary me-2"></i> Boarding Pass / Voucher
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-success" href="<?php echo $waUrl; ?>" target="_blank" rel="noopener">
                                                <i class="bx bxl-whatsapp me-2"></i> Message on WhatsApp
                                            </a>
                                        </li>
                                        <?php if (!empty($approverName)): ?>
                                            <li>
                                                <span class="dropdown-item-text text-muted small border-top pt-2 mt-1">
                                                    <i class="bx bx-check-shield text-success me-1"></i> Paid approved: <strong><?php echo htmlspecialchars($approverName); ?></strong>
                                                </span>
                                            </li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $b['id']; ?>">
                                                <i class="bx bx-trash me-2"></i> Delete Reservation
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Check-In & Edit Booking Modal for This Reservation -->
                        <div class="modal fade" id="manageModal<?php echo $b['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content admin-modal-content">
                                    <div class="modal-header admin-modal-header">
                                        <div>
                                            <h5 class="modal-title fw-bold text-white mb-0">Check-In &amp; Details: <?php echo htmlspecialchars($b['booking_reference']); ?></h5>
                                            <small class="text-white-50">Guest: <?php echo htmlspecialchars($b['guest_name']); ?> &bull; <?php echo htmlspecialchars($b['category_name']); ?></small>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    
                                    <form method="POST" action="bookings.php">
                                        <input type="hidden" name="action" value="update_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">

                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                
                                                <?php if (!empty($b['special_requests'])): ?>
                                                    <!-- Prominent Guest Special Requests Alert -->
                                                    <div class="col-12">
                                                        <div class="p-3 rounded-3 border" style="background:#fff5f5; border-color:#fecaca !important;">
                                                            <div class="d-flex align-items-start gap-2">
                                                                <i class="bx bx-bell text-danger fs-5 mt-1 flex-shrink-0"></i>
                                                                <div>
                                                                    <div class="fw-bold text-danger text-uppercase small" style="letter-spacing:0.5px;">Guest Special Requests &amp; Arrival Notes</div>
                                                                    <p class="mb-0 text-dark small fst-italic mt-1">&ldquo;<?php echo htmlspecialchars($b['special_requests']); ?>&rdquo;</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Status -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Reservation Status</label>
                                                    <select class="form-select" name="booking_status" required>
                                                        <option value="confirmed" <?php echo ($bStatus === 'confirmed') ? 'selected' : ''; ?>>Confirmed (Awaiting Arrival)</option>
                                                        <option value="checked_in" <?php echo ($bStatus === 'checked_in') ? 'selected' : ''; ?>>Checked-In (In-House Guest)</option>
                                                        <option value="completed" <?php echo ($bStatus === 'completed' || $bStatus === 'checked_out') ? 'selected' : ''; ?>>Completed / Checked-Out</option>
                                                        <option value="cancelled" <?php echo ($bStatus === 'cancelled' || $bStatus === 'no_show') ? 'selected' : ''; ?>>Cancelled / No-Show</option>
                                                    </select>
                                                </div>

                                                <!-- Assign Physical Room (Strict Filter: Only Clean & Vacant rooms or currently assigned) -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between align-items-center">
                                                        <span>Assign Physical Room</span>
                                                        <span class="text-danger fw-normal" style="font-size:10px;">(Clean &amp; Vacant Only)</span>
                                                    </label>
                                                    <select class="form-select" name="assigned_room_id">
                                                        <option value="">-- No Room Assigned Yet --</option>
                                                        <?php 
                                                        $eligibleRoomsCount = 0;
                                                        
                                                        // Sort rooms for this booking:
                                                        // 1. Currently allocated room at top
                                                        // 2. Clean & vacant rooms matching the booked category
                                                        // 3. All other available clean & vacant rooms
                                                        $bookingRooms = $allRooms;
                                                        usort($bookingRooms, function ($a, $bItem) use ($b) {
                                                            $aAssigned = (!empty($b['assigned_room_id']) && $b['assigned_room_id'] == $a['id']) ? 1 : 0;
                                                            $bAssigned = (!empty($b['assigned_room_id']) && $b['assigned_room_id'] == $bItem['id']) ? 1 : 0;
                                                            if ($aAssigned !== $bAssigned) return $bAssigned <=> $aAssigned;

                                                            $aMatch = (!empty($b['category_id']) && $a['category_id'] == $b['category_id']) ? 1 : 0;
                                                            $bMatch = (!empty($b['category_id']) && $bItem['category_id'] == $b['category_id']) ? 1 : 0;
                                                            if ($aMatch !== $bMatch) return $bMatch <=> $aMatch;

                                                            return ((int)$a['room_number']) <=> ((int)$bItem['room_number']);
                                                        });

                                                        foreach ($bookingRooms as $rm): 
                                                            $isAssignedToThis = (!empty($b['assigned_room_id']) && $b['assigned_room_id'] == $rm['id']);
                                                            $isClean = in_array(strtolower($rm['cleanliness_status']), ['clean', 'inspected']);
                                                            $isVacant = (strtolower($rm['occupancy_status']) === 'vacant');
                                                            $isMatch = (!empty($b['category_id']) && $rm['category_id'] == $b['category_id']);
                                                            
                                                            // STRICT OPERATIONAL RULE: Exclude occupied, dirty, and maintenance rooms
                                                            if (($isClean && $isVacant) || $isAssignedToThis):
                                                                $eligibleRoomsCount++;
                                                                $catName = !empty($rm['category_name']) ? htmlspecialchars($rm['category_name']) : 'Standard';

                                                                if ($isAssignedToThis) {
                                                                    $labelSuffix = '(Currently Allocated)';
                                                                } elseif ($isMatch) {
                                                                    $labelSuffix = '(Booked Type, ' . ucfirst($rm['cleanliness_status']) . ', Vacant)';
                                                                } else {
                                                                    $labelSuffix = '(' . ucfirst($rm['cleanliness_status']) . ', Vacant)';
                                                                }
                                                        ?>
                                                            <option value="<?php echo $rm['id']; ?>" <?php echo $isAssignedToThis ? 'selected' : ''; ?>>
                                                                Room <?php echo htmlspecialchars($rm['room_number']); ?> - <?php echo $catName; ?> <?php echo $labelSuffix; ?>
                                                            </option>
                                                        <?php 
                                                            endif;
                                                        endforeach; 
                                                        if ($eligibleRoomsCount === 0 && empty($b['assigned_room_id'])):
                                                        ?>
                                                            <option value="" disabled>-- No Clean &amp; Vacant Rooms in Inventory --</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-1" style="font-size:11px;">
                                                        Occupied, dirty, and maintenance rooms are excluded.
                                                    </small>
                                                </div>

                                                <!-- Guest Identification -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Guest ID Document Type</label>
                                                    <select class="form-select" name="id_type" id="idType_<?php echo $b['id']; ?>" onchange="handleIdTypeChange(<?php echo $b['id']; ?>)">
                                                        <option value="None" <?php echo ($b['id_type'] === 'None' || empty($b['id_type'])) ? 'selected' : ''; ?>>None (No Document Provided)</option>
                                                        <option value="National ID (NIN)" <?php echo $b['id_type'] === 'National ID (NIN)' ? 'selected' : ''; ?>>National ID (NIN)</option>
                                                        <option value="Driver's License" <?php echo $b['id_type'] === "Driver's License" ? 'selected' : ''; ?>>Driver's License</option>
                                                        <option value="International Passport" <?php echo $b['id_type'] === 'International Passport' ? 'selected' : ''; ?>>International Passport</option>
                                                        <option value="Voter's Card" <?php echo $b['id_type'] === "Voter's Card" ? 'selected' : ''; ?>>Voter's Card</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">ID Number / Serial</label>
                                                    <input type="text" class="form-control" name="id_number" id="idNumber_<?php echo $b['id']; ?>" 
                                                        placeholder="<?php echo ($b['id_type'] === 'None' || empty($b['id_type'])) ? 'No ID document required' : 'e.g. 98214-AA or NIN string'; ?>" 
                                                        value="<?php echo htmlspecialchars(($b['id_type'] === 'None' || empty($b['id_type'])) ? '' : ($b['id_number'] ?? '')); ?>" 
                                                        <?php echo ($b['id_type'] === 'None' || empty($b['id_type'])) ? 'disabled' : ''; ?>>
                                                </div>

                                                <!-- Payment Settlement -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Status</label>
                                                    <select class="form-select" name="payment_status" required>
                                                        <option value="unpaid" <?php echo ($pStatus === 'unpaid' || empty($pStatus)) ? 'selected' : ''; ?>>Unpaid (Pending Arrival)</option>
                                                        <option value="paid" <?php echo ($pStatus === 'paid') ? 'selected' : ''; ?>>Paid (Settled at Front Desk)</option>
                                                        <option value="refunded" <?php echo ($pStatus === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Payment Method Settled</label>
                                                    <select class="form-select" name="payment_method">
                                                        <option value="pay_on_arrival" <?php echo ($pMethod === 'pay_on_arrival' || empty($pMethod)) ? 'selected' : ''; ?>>Pay on Arrival (Pending)</option>
                                                        <option value="pos" <?php echo ($pMethod === 'pos') ? 'selected' : ''; ?>>POS Card Terminal</option>
                                                        <option value="cash" <?php echo ($pMethod === 'cash') ? 'selected' : ''; ?>>Physical Cash</option>
                                                        <option value="bank_transfer" <?php echo ($pMethod === 'bank_transfer') ? 'selected' : ''; ?>>Instant Bank Transfer</option>
                                                    </select>
                                                </div>

                                                <!-- Admin Notes -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Front Desk Staff Notes</label>
                                                    <textarea class="form-control" name="admin_notes" rows="2" placeholder="e.g. Early check-in granted, caution deposit receipt issued..."><?php echo htmlspecialchars($b['admin_notes'] ?? ''); ?></textarea>
                                                </div>

                                                <!-- Special Requests Display -->
                                                <?php if (!empty($b['special_requests'])): ?>
                                                    <div class="col-12">
                                                        <div class="p-2 rounded bg-light border small">
                                                            <strong>Guest's Special Requests:</strong>
                                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($b['special_requests']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                            </div>
                                        </div>

                                        <div class="modal-footer bg-light p-3">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">
                                                <i class="bx bx-save me-1"></i> Save Changes &amp; Update Booking
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        <!-- Delete Booking Confirmation Modal -->
                        <div class="modal fade" id="deleteModal<?php echo $b['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $b['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                                    <div class="p-3 text-end pb-0">
                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="bookings.php">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        <div class="modal-body px-4 pt-1 pb-3 text-center">
                                            <!-- Circular Warning Badge -->
                                            <div class="mx-auto mb-3" style="width: 58px; height: 58px; min-width: 58px; border-radius: 50% !important; aspect-ratio: 1/1; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 0 0 6px #fef2f2;">
                                                <i class="bx bx-trash"></i>
                                            </div>

                                            <h5 class="fw-bold text-dark mb-1" id="deleteModalLabel<?php echo $b['id']; ?>">Delete Reservation?</h5>
                                            <p class="text-muted small mb-3">Are you sure you want to permanently delete this reservation from the registry?</p>

                                            <!-- Structured Reservation Summary Card -->
                                            <div class="p-3 bg-light rounded-3 border text-start mb-3" style="font-size: 12px;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Reference</span>
                                                    <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2 py-1">
                                                        #<?php echo htmlspecialchars($b['booking_reference']); ?>
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Guest</span>
                                                    <span class="fw-bold text-dark text-truncate" style="max-width: 220px;"><?php echo htmlspecialchars($b['guest_name']); ?></span>
                                                </div>
                                                <?php if (!empty($b['room_number'])): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Allocated Room</span>
                                                        <span class="badge bg-success-subtle text-success fw-bold rounded-pill px-2 py-1">
                                                            Room <?php echo htmlspecialchars($b['room_number']); ?> (Will be released)
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Total Amount</span>
                                                    <span class="fw-bold text-danger">&#8358;<?php echo number_format($b['total_amount']); ?></span>
                                                </div>
                                            </div>

                                            <div class="alert alert-danger py-2 px-3 rounded-3 text-start d-flex align-items-center gap-2 mb-0" style="font-size: 11px; background: #fff5f5; border: 1px dashed #fca5a5; color: #991b1b;">
                                                <i class="bx bx-error-circle fs-6 flex-shrink-0"></i>
                                                <span>This will permanently purge this record and free up hotel room inventory.</span>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 px-4 pb-4 pt-1 d-flex justify-content-end gap-2 bg-white">
                                            <button type="button" class="btn btn-light fw-bold text-secondary rounded-pill px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold rounded-pill px-3 py-2" style="background:#E73539;">
                                                <i class="bx bx-trash me-1"></i> Delete Booking
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </form>
    <!-- Bookings Table Pagination -->
    <?php echo render_admin_pagination($page, $totalPages, $totalRecords, $perPage, ['status' => $statusFilter, 'search' => $search, 'sort_by' => $sortBy]); ?>
</div>

<script>
function toggleAllBookings(master) {
    const checkboxes = document.querySelectorAll('.booking-row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateBookingsSelection();
}

function updateBookingsSelection() {
    const checkboxes = document.querySelectorAll('.booking-row-checkbox:checked');
    const totalCheckboxes = document.querySelectorAll('.booking-row-checkbox');
    const count = checkboxes.length;
    const bar = document.getElementById('bookingsBatchActionBar');
    const countSpan = document.getElementById('bookingsSelectedCount');
    const master = document.getElementById('masterBookingsCheckbox');

    if (countSpan) countSpan.textContent = count;
    if (bar) {
        if (count > 0) {
            bar.classList.remove('d-none');
        } else {
            bar.classList.add('d-none');
        }
    }
    if (master) {
        master.checked = (totalCheckboxes.length > 0 && count === totalCheckboxes.length);
    }
}

function submitBatchDeleteBookings() {
    const count = document.querySelectorAll('.booking-row-checkbox:checked').length;
    if (count === 0) return;
    tianoConfirm(
        'Delete Reservations',
        `Are you sure you want to permanently delete the ${count} selected reservation(s)? Any assigned rooms will be returned to vacant status.`,
        'Delete Selected',
        function() {
            document.getElementById('batchBookingsForm').submit();
        }
    );
}

function handleIdTypeChange(bookingId) {
    var typeSelect = document.getElementById('idType_' + bookingId);
    var numInput = document.getElementById('idNumber_' + bookingId);
    if (!typeSelect || !numInput) return;

    var selectedVal = typeSelect.value;
    if (selectedVal === 'None' || !selectedVal) {
        numInput.disabled = true;
        numInput.value = '';
        numInput.placeholder = 'No ID document required';
    } else {
        numInput.disabled = false;
        numInput.placeholder = 'e.g. 98214-AA or NIN string';
        setTimeout(function() { numInput.focus(); }, 50);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

