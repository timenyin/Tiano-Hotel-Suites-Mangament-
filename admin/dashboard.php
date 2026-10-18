<?php
/**
 * Tiano Hotel & Suites - Executive Command & Financial Earnings Dashboard
 * Comprehensive revenue metrics, interactive graphical tracking, and exportable earnings ledger.
 */
$pageTitle = 'Executive Dashboard & Earnings';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Handle Batch Deletion of Bookings from Financial Ledger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_delete_bookings') {
    $selectedIds = $_POST['selected_booking_ids'] ?? [];
    if (!empty($selectedIds) && is_array($selectedIds)) {
        $deletedCount = 0;
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
                        log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_booking', 'booking', $bId, "Deleted reservation #{$bRow['booking_reference']} (Guest: {$bRow['guest_name']})");
                        $deletedCount++;
                    }
                } catch (Exception $e) {}
            }
        }
        $success = "Successfully deleted {$deletedCount} reservation record(s) and released room inventory.";
    } else {
        $error = "No reservations were selected for deletion.";
    }
}

// Handle Single Deletion of Booking from Ledger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_single_booking') {
    $bId = (int)($_POST['booking_id'] ?? 0);
    try {
        $stmtB = $pdo->prepare("SELECT id, booking_reference, assigned_room_id, guest_name FROM bookings WHERE id = ?");
        $stmtB->execute([$bId]);
        $bRow = $stmtB->fetch(PDO::FETCH_ASSOC);
        if ($bRow) {
            if (!empty($bRow['assigned_room_id'])) {
                $pdo->prepare("UPDATE rooms SET occupancy_status = 'vacant' WHERE id = ?")->execute([$bRow['assigned_room_id']]);
            }
            $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_booking', 'booking', $bId, "Deleted reservation #{$bRow['booking_reference']} (Guest: {$bRow['guest_name']})");
            $success = "Reservation #{$bRow['booking_reference']} deleted successfully.";
        }
    } catch (Exception $e) {
        $error = "Error deleting reservation: " . $e->getMessage();
    }
}

$today = date('Y-m-d');

// 1. Core Operational Metrics (Nigeria Time CURDATE())
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE check_in_date = ? AND booking_status != 'cancelled'");
$stmt->execute([$today]);
$todaysArrivals = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE check_out_date = ? AND booking_status != 'cancelled'");
$stmt->execute([$today]);
$todaysDepartures = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'checked_in'");
$activeBookings = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalBookingsCount = (int)$stmt->fetchColumn();

// 2. Comprehensive Financial Calculations
// Total Gross Projected (excluding cancelled)
$stmt = $pdo->query("SELECT SUM(total_amount + caution_deposit) FROM bookings WHERE booking_status != 'cancelled'");
$totalGrossRevenue = (float)$stmt->fetchColumn();

// Settled / Paid at Desk
$stmt = $pdo->query("SELECT SUM(total_amount + caution_deposit) FROM bookings WHERE payment_status = 'paid'");
$totalPaidRevenue = (float)$stmt->fetchColumn();

// Pending Receivables (Pay-on-Arrival unpaid)
$stmt = $pdo->query("SELECT SUM(total_amount + caution_deposit) FROM bookings WHERE payment_status = 'unpaid' AND booking_status != 'cancelled'");
$totalPendingRevenue = (float)$stmt->fetchColumn();

// Total Caution Deposits Pool
$stmt = $pdo->query("SELECT SUM(caution_deposit) FROM bookings WHERE booking_status != 'cancelled'");
$totalCautionPool = (float)$stmt->fetchColumn();

// 3. Multi-Period Revenue Tracking Histogram (Default: Weekly Mon - Sun)
$periodFilter = trim($_GET['period'] ?? 'week');

$histogramLabels    = [];
$histogramSubLabels = [];
$histogramRevenue   = [];
$histogramCounts    = [];
$histogramIsToday   = [];
$periodTitle        = "Weekly Gross Earnings Volume";
$periodSubtitle     = "Monday to Sunday daily gross revenue & reservation flow";

if ($periodFilter === 'today') {
    $periodTitle = "Today's Hourly Revenue Flow";
    $periodSubtitle = "24-hour financial volume for " . date('l, d M Y');
    
    // Group into 12 2-hour slots: 00:00, 02:00, 04:00, ... 22:00
    $hourlyData = array_fill(0, 24, ['total' => 0.0, 'count' => 0]);
    $stmtHr = $pdo->prepare("
        SELECT HOUR(created_at) as hr, COUNT(*) as cnt, SUM(total_amount + caution_deposit) as total
        FROM bookings
        WHERE DATE(created_at) = CURDATE() AND booking_status != 'cancelled'
        GROUP BY HOUR(created_at)
    ");
    $stmtHr->execute();
    while ($r = $stmtHr->fetch(PDO::FETCH_ASSOC)) {
        $h = (int)$r['hr'];
        if ($h >= 0 && $h < 24) {
            $hourlyData[$h]['total'] = (float)$r['total'];
            $hourlyData[$h]['count'] = (int)$r['cnt'];
        }
    }
    
    $curHr = (int)date('G');
    for ($h = 0; $h < 24; $h += 2) {
        $tot = $hourlyData[$h]['total'] + $hourlyData[$h+1]['total'];
        $cnt = $hourlyData[$h]['count'] + $hourlyData[$h+1]['count'];
        $histogramLabels[] = sprintf("%02d:00", $h);
        $histogramSubLabels[] = sprintf("%02d-%02dh", $h, $h+2);
        $histogramRevenue[] = $tot;
        $histogramCounts[] = $cnt;
        $histogramIsToday[] = ($curHr >= $h && $curHr < $h+2);
    }
} elseif ($periodFilter === 'month') {
    $periodTitle = "Current Month Daily Volume";
    $periodSubtitle = date('F Y') . " daily gross revenue breakdown";
    $daysInMonth = (int)date('t');
    $currentDay = (int)date('j');
    
    $dailyData = array_fill(1, $daysInMonth, ['total' => 0.0, 'count' => 0]);
    $stmtMo = $pdo->prepare("
        SELECT DAY(created_at) as dy, COUNT(*) as cnt, SUM(total_amount + caution_deposit) as total
        FROM bookings
        WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) AND booking_status != 'cancelled'
        GROUP BY DAY(created_at)
    ");
    $stmtMo->execute();
    while ($r = $stmtMo->fetch(PDO::FETCH_ASSOC)) {
        $d = (int)$r['dy'];
        if ($d >= 1 && $d <= $daysInMonth) {
            $dailyData[$d]['total'] = (float)$r['total'];
            $dailyData[$d]['count'] = (int)$r['cnt'];
        }
    }
    
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $histogramLabels[] = (string)$d;
        $histogramSubLabels[] = date('D', strtotime(date('Y-m-') . sprintf('%02d', $d)));
        $histogramRevenue[] = $dailyData[$d]['total'];
        $histogramCounts[] = $dailyData[$d]['count'];
        $histogramIsToday[] = ($d === $currentDay);
    }
} elseif (in_array($periodFilter, ['3m', '6m', '12m', 'all'])) {
    $numMonths = ($periodFilter === '3m') ? 3 : (($periodFilter === '12m') ? 12 : (($periodFilter === 'all') ? 24 : 6));
    $periodTitle = "Historical Revenue Volume ({$numMonths} Months)";
    $periodSubtitle = "Monthly gross volume over the last {$numMonths} recording periods";
    
    $monthsList = [];
    for ($i = $numMonths - 1; $i >= 0; $i--) {
        $mKey = date('Y-m', strtotime("-$i months"));
        $mLabel = date('M Y', strtotime("-$i months"));
        $monthsList[$mKey] = [
            'label' => $mLabel,
            'total' => 0.0,
            'count' => 0,
            'is_current' => ($i === 0)
        ];
    }
    
    $stmtH = $pdo->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as m_key, COUNT(*) as cnt, SUM(total_amount + caution_deposit) as total
        FROM bookings
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) AND booking_status != 'cancelled'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ");
    $stmtH->execute([$numMonths]);
    while ($r = $stmtH->fetch(PDO::FETCH_ASSOC)) {
        if (isset($monthsList[$r['m_key']])) {
            $monthsList[$r['m_key']]['total'] = (float)$r['total'];
            $monthsList[$r['m_key']]['count'] = (int)$r['cnt'];
        }
    }
    
    foreach ($monthsList as $mItem) {
        $histogramLabels[] = $mItem['label'];
        $histogramSubLabels[] = '';
        $histogramRevenue[] = $mItem['total'];
        $histogramCounts[] = $mItem['count'];
        $histogramIsToday[] = $mItem['is_current'];
    }
} else {
    // DEFAULT: 'week' (Monday to Sunday)
    $periodFilter = 'week';
    $periodTitle = "Weekly Gross Earnings Volume";
    $periodSubtitle = "Monday to Sunday daily gross revenue & reservation flow";
    
    $mondayTimestamp = strtotime('monday this week');
    $currentYmd = date('Y-m-d');
    
    $daysData = [];
    for ($i = 0; $i < 7; $i++) {
        $dayTs = strtotime("+$i days", $mondayTimestamp);
        $dayYmd = date('Y-m-d', $dayTs);
        $daysData[$dayYmd] = [
            'label' => date('D', $dayTs),      // Mon, Tue, Wed, Thu, Fri, Sat, Sun
            'sub'   => date('d M', $dayTs),     // 13 Oct
            'total' => 0.0,
            'count' => 0,
            'is_today' => ($dayYmd === $currentYmd)
        ];
    }
    
    $startOfWeek = date('Y-m-d 00:00:00', $mondayTimestamp);
    $endOfWeek = date('Y-m-d 23:59:59', strtotime('+6 days', $mondayTimestamp));
    
    $stmtW = $pdo->prepare("
        SELECT DATE(created_at) as b_date, COUNT(*) as cnt, SUM(total_amount + caution_deposit) as total
        FROM bookings
        WHERE created_at BETWEEN ? AND ? AND booking_status != 'cancelled'
        GROUP BY DATE(created_at)
    ");
    $stmtW->execute([$startOfWeek, $endOfWeek]);
    while ($r = $stmtW->fetch(PDO::FETCH_ASSOC)) {
        $d = $r['b_date'];
        if (isset($daysData[$d])) {
            $daysData[$d]['total'] = (float)$r['total'];
            $daysData[$d]['count'] = (int)$r['cnt'];
        }
    }
    
    foreach ($daysData as $dItem) {
        $histogramLabels[] = $dItem['label'];
        $histogramSubLabels[] = $dItem['sub'];
        $histogramRevenue[] = $dItem['total'];
        $histogramCounts[] = $dItem['count'];
        $histogramIsToday[] = $dItem['is_today'];
    }
}

$maxHistogramRevenue = max($histogramRevenue ?: [0]);
if ($maxHistogramRevenue <= 0) $maxHistogramRevenue = 100000;
$totalPeriodRevenue = array_sum($histogramRevenue);

// 4. Payment Status Breakdown
$stmtStatus = $pdo->query("
    SELECT payment_status, COUNT(*) as cnt, SUM(total_amount + caution_deposit) as val
    FROM bookings
    GROUP BY payment_status
");
$statusBreakdown = $stmtStatus->fetchAll();
$paidCount = 0; $unpaidCount = 0;
foreach ($statusBreakdown as $sb) {
    if ($sb['payment_status'] === 'paid') $paidCount = (int)$sb['cnt'];
    if ($sb['payment_status'] === 'unpaid') $unpaidCount = (int)$sb['cnt'];
}
$collectionRate = $totalGrossRevenue > 0 ? round(($totalPaidRevenue / $totalGrossRevenue) * 100) : 0;
$pendingRate = $totalGrossRevenue > 0 ? (100 - $collectionRate) : 0;

// 5. Paginated & Sorted Earnings Ledger Table (10 per page)
$ledgerPage = max(1, (int)($_GET['page'] ?? 1));
$ledgerPerPage = 10;
$ledgerOffset = ($ledgerPage - 1) * $ledgerPerPage;

$sortBy = trim($_GET['sort_by'] ?? 'newest');
$sortMap = [
    'newest'       => 'b.id DESC',
    'oldest'       => 'b.id ASC',
    'amount_desc'  => 'b.total_amount DESC',
    'amount_asc'   => 'b.total_amount ASC',
    'guest_asc'    => 'b.guest_name ASC',
    'paid_first'   => 'b.payment_status = "paid" DESC, b.id DESC',
    'unpaid_first' => 'b.payment_status = "unpaid" DESC, b.id DESC',
];
$orderSql = $sortMap[$sortBy] ?? 'b.id DESC';

$stmtTotalLedger = $pdo->query("SELECT COUNT(*) FROM bookings");
$totalLedgerRecords = (int)$stmtTotalLedger->fetchColumn();
$totalLedgerPages = ceil($totalLedgerRecords / $ledgerPerPage);

$stmtLedger = $pdo->prepare("
    SELECT b.*, c.name AS category_name, r.room_number
    FROM bookings b
    JOIN room_categories c ON b.category_id = c.id
    LEFT JOIN rooms r ON b.assigned_room_id = r.id
    ORDER BY {$orderSql}
    LIMIT ? OFFSET ?
");
$stmtLedger->bindValue(1, $ledgerPerPage, PDO::PARAM_INT);
$stmtLedger->bindValue(2, $ledgerOffset, PDO::PARAM_INT);
$stmtLedger->execute();
$ledgerRecords = $stmtLedger->fetchAll();
?>

<!-- Print Header for PDF Export -->
<div class="d-none d-print-block mb-4 text-center">
    <h2>TIANO HOTEL &amp; SUITES</h2>
    <p class="mb-1">44 Marina Road, Eket, Akwa Ibom State, Nigeria</p>
    <h4>OFFICIAL EXECUTIVE EARNINGS &amp; FINANCIAL REPORT</h4>
    <small>Generated on: <?php echo date('d F Y, H:i A'); ?> by <?php echo htmlspecialchars($admin['fullname']); ?></small>
    <hr>
</div>

<!-- Welcome Header (Screen only) -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Welcome back, <?php echo htmlspecialchars($admin['fullname']); ?> 👋</h3>
        <p class="text-muted small mb-0">Financial &amp; Operations Executive Cockpit for <strong><?php echo date('l, d F Y'); ?></strong>.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-dark px-3 py-2 fw-semibold flex-grow-1" style="border-radius:10px; font-size:13.5px;" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Print / Export Report (PDF)
        </button>
        <a href="bookings.php" class="btn btn-danger px-3 py-2 fw-semibold flex-grow-1" style="background:#E73539; border-radius:10px; font-size:13.5px;">
            <i class="bx bx-list-check me-1"></i> Manage Bookings
        </a>
    </div>
</div>

<!-- Customized Premium Financial Stat Cards (Zero sidebar left borders) -->
<div class="row g-3 mb-4">
    <!-- Gross Volume -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-gross">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-title text-danger">Gross Revenue Volume</span>
                    <div class="stat-value text-dark"><?php echo format_naira($totalGrossRevenue); ?></div>
                    <small class="text-muted"><?php echo $totalBookingsCount; ?> Total Reservations</small>
                </div>
                <div class="stat-icon-wrap stat-icon-red">
                    <i class="bx bx-wallet-alt"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Settled Revenue -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-paid">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-title text-success">Settled / Paid at Desk</span>
                    <div class="stat-value text-success"><?php echo format_naira($totalPaidRevenue); ?></div>
                    <small class="text-success fw-bold"><i class="bx bx-check-double"></i> <?php echo $paidCount; ?> Settled Payments</small>
                </div>
                <div class="stat-icon-wrap stat-icon-emerald">
                    <i class="bx bx-badge-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Receivables -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-pending">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-title text-warning-emphasis">Pending Receivables</span>
                    <div class="stat-value text-warning-emphasis" id="statPendingReceivables"><?php echo format_naira($totalPendingRevenue); ?></div>
                    <small class="text-muted" id="statPendingCount"><?php echo $unpaidCount; ?> Pay on Arrival at Desk</small>
                </div>
                <div class="stat-icon-wrap stat-icon-amber">
                    <i class="bx bx-time-five"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Caution Pool -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-card-caution">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="stat-title text-primary">Caution Deposits Held</span>
                    <div class="stat-value text-primary"><?php echo format_naira($totalCautionPool); ?></div>
                    <small class="text-muted">Reconciled at Checkout</small>
                </div>
                <div class="stat-icon-wrap stat-icon-blue">
                    <i class="bx bx-shield-quarter"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Operational Metrics (Arrivals, Departures, Active) -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
            <div>
                <span class="text-muted small d-block">Expected Arrivals Today</span>
                <h4 class="fw-bold mb-0 text-dark" id="statArrivalsToday"><?php echo $todaysArrivals; ?> Check-Ins</h4>
            </div>
            <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; aspect-ratio: 1/1; font-size: 24px;">
                <i class="bx bx-log-in-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
            <div>
                <span class="text-muted small d-block">Expected Departures Today</span>
                <h4 class="fw-bold mb-0 text-dark" id="statDeparturesToday"><?php echo $todaysDepartures; ?> Check-Outs</h4>
            </div>
            <div class="rounded-circle bg-info-subtle text-info d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; aspect-ratio: 1/1; font-size: 24px;">
                <i class="bx bx-log-out-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between shadow-sm">
            <div>
                <span class="text-muted small d-block">Active In-House Stays</span>
                <h4 class="fw-bold mb-0 text-dark" id="statActiveBookings"><?php echo $activeBookings; ?> Occupied</h4>
            </div>
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px; aspect-ratio: 1/1; font-size: 24px;">
                <i class="bx bx-home-heart"></i>
            </div>
        </div>
    </div>
</div>

<!-- Graphical Tracking Section: Reliable Visual Histogram & Settlement Ratio -->
<div class="row g-4 mb-4 d-print-none">
    <!-- Revenue Tracking Histogram -->
    <div class="col-lg-8">
        <div class="admin-card p-4 h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($periodTitle); ?></h5>
                    <small class="text-muted"><?php echo $periodSubtitle; ?></small>
                </div>
                <!-- Interactive Month / Period Filter Dropdown -->
                <form method="GET" action="dashboard.php" class="d-flex align-items-center gap-2 m-0">
                    <label class="small text-muted fw-bold text-uppercase me-1 mb-0" style="font-size:11px;">Period:</label>
                    <select name="period" class="form-select form-select-sm" style="width: auto; min-width: 190px; border-radius: 8px;" onchange="this.form.submit()">
                        <option value="week" <?php echo $periodFilter === 'week' ? 'selected' : ''; ?>>This Week (Mon - Sun)</option>
                        <option value="today" <?php echo $periodFilter === 'today' ? 'selected' : ''; ?>>Today (Hourly: 00:00 - 23:00)</option>
                        <option value="month" <?php echo $periodFilter === 'month' ? 'selected' : ''; ?>>This Month (Daily)</option>
                        <option value="3m" <?php echo $periodFilter === '3m' ? 'selected' : ''; ?>>Last 3 Months</option>
                        <option value="6m" <?php echo $periodFilter === '6m' ? 'selected' : ''; ?>>Last 6 Months</option>
                        <option value="12m" <?php echo $periodFilter === '12m' ? 'selected' : ''; ?>>Last 12 Months</option>
                    </select>
                </form>
            </div>

            <!-- Zero-Dependency Interactive Responsive Histogram -->
            <div class="histogram-visual-container">
                <?php foreach ($histogramLabels as $idx => $hLabel): 
                    $hAmount = $histogramRevenue[$idx] ?? 0;
                    $hCount = $histogramCounts[$idx] ?? 0;
                    $hSub = $histogramSubLabels[$idx] ?? '';
                    $isToday = !empty($histogramIsToday[$idx]);
                    $barHeightPercent = ($hAmount > 0) ? max(14, round(($hAmount / $maxHistogramRevenue) * 100)) : 4;
                ?>
                    <div class="histogram-col <?php echo $isToday ? 'is-today' : ''; ?> <?php echo ($hAmount == 0) ? 'has-zero' : ''; ?>">
                        <div class="histogram-tooltip">
                            <div><strong><?php echo htmlspecialchars($hLabel); ?><?php echo $hSub ? " (" . htmlspecialchars($hSub) . ")" : ''; ?></strong></div>
                            <div><?php echo format_naira($hAmount); ?> &bull; <?php echo $hCount; ?> Booking<?php echo $hCount === 1 ? '' : 's'; ?></div>
                            <?php if ($isToday): ?>
                                <span class="badge bg-danger text-white mt-1" style="font-size:9.5px;">Current</span>
                            <?php endif; ?>
                        </div>
                        <div class="histogram-bar-track">
                            <div class="histogram-bar-fill" style="height: <?php echo $barHeightPercent; ?>%;"></div>
                        </div>
                        <div class="histogram-month-label"><?php echo htmlspecialchars($hLabel); ?></div>
                        <?php if (!empty($hSub)): ?>
                            <div class="histogram-sub-label"><?php echo htmlspecialchars($hSub); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-3 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 text-muted small">
                <div>
                    <span class="d-inline-block rounded-circle bg-danger me-1" style="width:10px; height:10px;"></span>
                    Peak Period Volume: <strong><?php echo format_naira($maxHistogramRevenue); ?></strong>
                    <span class="mx-2">&bull;</span>
                    Total Period Volume: <strong class="text-dark"><?php echo format_naira($totalPeriodRevenue); ?></strong>
                </div>
                <div>
                    Showing <strong><?php echo count($histogramLabels); ?></strong> intervals <?php echo ($periodFilter === 'week') ? '(Monday - Sunday)' : ''; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Settlement Ratio & Collection Intelligence Widget -->
    <div class="col-lg-4">
        <div class="admin-card p-4 h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark">Settlement Ratio</h5>
                    <span class="status-badge <?php echo $collectionRate >= 70 ? 'status-badge-success' : 'status-badge-warning'; ?>">
                        <span class="status-dot"></span>
                        <?php echo $collectionRate; ?>% Settled
                    </span>
                </div>
                <small class="text-muted d-block mb-3">Real-time breakdown of collected revenue vs. pending desk arrivals.</small>

                <!-- Dual Segment Collection Split Bar -->
                <div class="mb-4">
                    <div class="progress" style="height: 12px; border-radius: 8px; background-color: #f1f5f9; overflow: hidden;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $collectionRate; ?>%;" title="Paid: <?php echo $collectionRate; ?>%"></div>
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $pendingRate; ?>%;" title="Pending: <?php echo $pendingRate; ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                        <span><strong class="text-success"><?php echo $collectionRate; ?>%</strong> Settled (Paid)</span>
                        <span><strong class="text-warning-emphasis"><?php echo $pendingRate; ?>%</strong> Pending at Desk</span>
                    </div>
                </div>

                <!-- Itemized Metric Cards -->
                <div class="d-flex flex-column gap-2 mb-3">
                    <div class="settlement-card-box bg-success-subtle border border-success-subtle">
                        <div>
                            <small class="text-success fw-bold text-uppercase" style="font-size:11px;">Paid at Desk</small>
                            <div class="fw-bold text-dark fs-6"><?php echo format_naira($totalPaidRevenue); ?></div>
                        </div>
                        <span class="badge bg-success text-white rounded-pill px-2 py-1"><?php echo $paidCount; ?> Guests</span>
                    </div>

                    <div class="settlement-card-box bg-warning-subtle border border-warning-subtle">
                        <div>
                            <small class="text-warning-emphasis fw-bold text-uppercase" style="font-size:11px;">Pending Pay-on-Arrival</small>
                            <div class="fw-bold text-dark fs-6"><?php echo format_naira($totalPendingRevenue); ?></div>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><?php echo $unpaidCount; ?> Guests</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top text-center text-muted small">
                <i class="bx bx-info-circle me-1 text-primary"></i> Front-desk balance reconciles upon physical check-in.
            </div>
        </div>
    </div>
</div>

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

<!-- Earnings Ledger Table with Checklist, S/N, Sorting & Approver Info -->
<div class="admin-card mb-4 position-relative">
    <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Earnings &amp; Financial Ledger</h5>
            <small class="text-muted">Itemized audit of guest reservations, caution deposits, payment statuses &amp; authorized approvers</small>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
            <!-- Sort Selector -->
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted fw-bold text-nowrap mb-0"><i class="bx bx-sort"></i> Sort By:</label>
                <div style="min-width: 220px;">
                    <select class="form-select form-select-sm" style="min-width: 220px; border-radius:8px;" onchange="location.href='dashboard.php?period=<?php echo urlencode($periodFilter); ?>&sort_by=' + this.value">
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="amount_desc" <?php echo $sortBy === 'amount_desc' ? 'selected' : ''; ?>>Total Amount: High-Low</option>
                        <option value="amount_asc" <?php echo $sortBy === 'amount_asc' ? 'selected' : ''; ?>>Total Amount: Low-High</option>
                        <option value="guest_asc" <?php echo $sortBy === 'guest_asc' ? 'selected' : ''; ?>>Guest Name (A-Z)</option>
                        <option value="paid_first" <?php echo $sortBy === 'paid_first' ? 'selected' : ''; ?>>Paid Bookings First</option>
                        <option value="unpaid_first" <?php echo $sortBy === 'unpaid_first' ? 'selected' : ''; ?>>Pay at Desk First</option>
                    </select>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" style="border-radius:8px;" onclick="window.print()">
                <i class="bx bx-download me-1"></i> Export PDF
            </button>
            <a href="bookings.php" class="btn btn-sm btn-danger fw-bold" style="background:#E73539; border-radius:8px;">
                All Bookings &rarr;
            </a>
        </div>
    </div>

    <!-- Batch Selection Action Bar -->
    <div id="ledgerBatchActionBar" class="d-none bg-danger bg-opacity-10 border-bottom border-danger px-3 py-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2 text-danger fw-bold small">
            <i class="bx bx-check-square fs-5"></i>
            <span id="ledgerSelectedCount">0</span> reservation(s) selected
        </div>
        <button type="button" class="btn btn-sm btn-danger fw-bold px-3 py-1" style="border-radius:6px;" onclick="submitBatchDeleteLedger()">
            <i class="bx bx-trash me-1"></i> Delete Selected
        </button>
    </div>

    <form method="POST" action="dashboard.php?period=<?php echo urlencode($periodFilter); ?>&sort_by=<?php echo urlencode($sortBy); ?>&page=<?php echo $ledgerPage; ?>" id="batchLedgerForm">
        <input type="hidden" name="action" value="batch_delete_bookings">

        <div class="table-responsive" style="min-height:330px; overflow-y:visible;">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:40px;" class="d-print-none text-center">
                            <input type="checkbox" id="masterLedgerCheckbox" class="form-check-input" onclick="toggleAllLedger(this)" title="Select All">
                        </th>
                        <th style="width:50px;">S/N</th>
                        <th>Ref #</th>
                        <th>Primary Guest</th>
                        <th>Room Tier</th>
                        <th>Stay Dates</th>
                        <th>Nights</th>
                        <th>Nightly Rate</th>
                        <th>Caution</th>
                        <th>Total Payable</th>
                        <th>Payment Status &amp; Approver</th>
                        <th class="text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody id="dashboardLedgerTbody">
                    <?php if (empty($ledgerRecords)): ?>
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">No reservations recorded in this ledger.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ledgerRecords as $idx => $row): 
                            $sn = $ledgerOffset + $idx + 1;
                            $totalDue = $row['total_amount'] + ($row['caution_deposit'] ?? 0);
                            $isPaid = ($row['payment_status'] === 'paid');
                            $approverName = !empty($row['payment_approved_by']) ? $row['payment_approved_by'] : ($isPaid ? 'Super Admin' : null);
                        ?>
                            <tr>
                                <td class="d-print-none text-center">
                                    <input type="checkbox" name="selected_booking_ids[]" value="<?php echo $row['id']; ?>" class="form-check-input ledger-row-checkbox" onchange="updateLedgerSelection()">
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border fw-bold">#<?php echo $sn; ?></span>
                                </td>
                                <td>
                                    <strong class="text-danger fw-bold"><?php echo htmlspecialchars($row['booking_reference']); ?></strong>
                                    <?php if (!empty($row['room_number'])): ?>
                                        <small class="d-block text-muted">Room <?php echo htmlspecialchars($row['room_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['guest_name']); ?></div>
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i><?php echo htmlspecialchars($row['guest_phone']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                </td>
                                <td>
                                    <small class="text-dark fw-semibold"><?php echo date('d M', strtotime($row['check_in_date'])); ?> &rarr; <?php echo date('d M Y', strtotime($row['check_out_date'])); ?></small>
                                </td>
                                <td><?php echo $row['nights']; ?></td>
                                <td>&#8358;<?php echo number_format($row['nightly_rate']); ?></td>
                                <td>&#8358;<?php echo number_format($row['caution_deposit']); ?></td>
                                <td class="fw-bold text-dark fs-6">
                                    &#8358;<?php echo number_format($totalDue); ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $isPaid ? 'status-badge-success' : 'status-badge-warning'; ?>">
                                        <span class="status-dot"></span>
                                        <?php echo $isPaid ? 'Paid' : 'Pay at Desk'; ?>
                                    </span>
                                    <?php if ($isPaid): ?>
                                        <small class="d-block text-success fw-semibold mt-1" style="font-size:11px;" title="Payment approved by authorized staff">
                                            <i class="bx bx-check-shield"></i> By: <strong><?php echo htmlspecialchars($approverName); ?></strong>
                                        </small>
                                    <?php else: ?>
                                        <small class="d-block text-muted mt-1" style="font-size:11px;">
                                            <i class="bx bx-time"></i> Pending Desk Settlement
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end d-print-none">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px;">
                                            <li>
                                                <a class="dropdown-item" href="../voucher.php?ref=<?php echo urlencode($row['booking_reference']); ?>" target="_blank">
                                                    <i class="bx bx-receipt text-danger me-2"></i> Boarding Pass
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="bookings.php?search=<?php echo urlencode($row['booking_reference']); ?>">
                                                    <i class="bx bx-edit text-primary me-2"></i> Manage / Check-In
                                                </a>
                                            </li>
                                            <?php if ($approverName): ?>
                                                <li>
                                                    <span class="dropdown-item-text text-muted small border-top pt-2 mt-1">
                                                        <i class="bx bx-user-check text-success me-1"></i> Approver: <strong><?php echo htmlspecialchars($approverName); ?></strong>
                                                    </span>
                                                </li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="deleteSingleLedgerBooking(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['booking_reference']); ?>')">
                                                    <i class="bx bx-trash me-2"></i> Delete Reservation
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- Ledger Table Pagination -->
    <?php echo render_admin_pagination($ledgerPage, $totalLedgerPages, $totalLedgerRecords, $ledgerPerPage, ['period' => $periodFilter, 'sort_by' => $sortBy]); ?>
</div>

<!-- Single Delete Form Helper -->
<form id="singleDeleteLedgerForm" method="POST" action="dashboard.php?period=<?php echo urlencode($periodFilter); ?>&sort_by=<?php echo urlencode($sortBy); ?>&page=<?php echo $ledgerPage; ?>" style="display:none;">
    <input type="hidden" name="action" value="delete_single_booking">
    <input type="hidden" name="booking_id" id="singleDeleteBookingId" value="">
</form>

<script>
function toggleAllLedger(master) {
    const checkboxes = document.querySelectorAll('.ledger-row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateLedgerSelection();
}

function updateLedgerSelection() {
    const checkboxes = document.querySelectorAll('.ledger-row-checkbox:checked');
    const totalCheckboxes = document.querySelectorAll('.ledger-row-checkbox');
    const count = checkboxes.length;
    const bar = document.getElementById('ledgerBatchActionBar');
    const countSpan = document.getElementById('ledgerSelectedCount');
    const master = document.getElementById('masterLedgerCheckbox');

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

function submitBatchDeleteLedger() {
    const count = document.querySelectorAll('.ledger-row-checkbox:checked').length;
    if (count === 0) return;
    tianoConfirm(
        'Delete Reservations',
        `Are you sure you want to permanently delete the ${count} selected reservation(s)? Any allocated rooms will be returned to vacant.`,
        'Delete Selected',
        function() {
            document.getElementById('batchLedgerForm').submit();
        }
    );
}

function deleteSingleLedgerBooking(id, ref) {
    tianoConfirm(
        'Delete Reservation',
        `Are you sure you want to delete reservation #${ref}? Allocated room inventory will be released.`,
        'Delete Reservation',
        function() {
            document.getElementById('singleDeleteBookingId').value = id;
            document.getElementById('singleDeleteLedgerForm').submit();
        }
    );
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
