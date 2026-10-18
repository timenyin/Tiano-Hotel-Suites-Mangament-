<?php
/**
 * Tiano Hotel & Suites - Administrative Activity & Security Audit Logs
 * Monitors staff logins, booking changes, and CMS updates with luxury pagination.
 */
$pageTitle = 'Activity Audit Logs';
$activePage = 'activity_logs';
require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['search'] ?? '');
$actionFilter = trim($_GET['action_filter'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Clean up any previously hardcoded strings in audit_logs so they resolve dynamically via the Geolocation API
try {
    $pdo->exec("UPDATE audit_logs SET location = NULL WHERE location LIKE '%Localhost / Hotel Server%' OR location LIKE '%Hotel Intranet%'");
} catch (Exception $e) {}

// Count Query
$countSql = "SELECT COUNT(*) FROM audit_logs WHERE 1=1";
$params = [];

if (!empty($search)) {
    $countSql .= " AND (admin_name LIKE ? OR details LIKE ? OR action LIKE ? OR ip_address LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

if (!empty($actionFilter)) {
    $countSql .= " AND action = ?";
    $params[] = $actionFilter;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Data Query
$sql = "SELECT * FROM audit_logs WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (admin_name LIKE ? OR details LIKE ? OR action LIKE ? OR ip_address LIKE ?)";
}
if (!empty($actionFilter)) {
    $sql .= " AND action = ?";
}
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
$bindIdx = 1;
if (!empty($search)) {
    $like = "%{$search}%";
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
}
if (!empty($actionFilter)) {
    $stmt->bindValue($bindIdx++, $actionFilter, PDO::PARAM_STR);
}
$stmt->bindValue($bindIdx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIdx++, $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// Distinct actions for dropdown
$distinctActions = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Activity &amp; Security Audit Logs</h4>
        <p class="text-muted small mb-0">Immutable tracking of administrative operations, reservation modifications, and system events.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border px-3 py-2 shadow-sm rounded-pill fw-bold">
            <i class="bx bx-shield-check text-success me-1"></i> Recorded Events: <?php echo number_format($totalRecords); ?>
        </span>
        <button type="button" class="btn btn-outline-dark px-3 py-2 fw-semibold" style="border-radius:10px;" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Print Log Sheet
        </button>
    </div>
</div>

<!-- Premium Filter Bar -->
<div class="admin-card mb-4 p-3">
    <form method="GET" action="activity-logs.php" class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px; height:46px;"><i class="bx bx-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by staff operator, action event, details, or IP..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius:0 10px 10px 0; height:46px;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="position-relative">
                <i class="bx bx-filter-alt position-absolute text-muted" style="left:14px; top:50%; transform:translateY(-50%); pointer-events:none; font-size:16px; z-index:2;"></i>
                <select name="action_filter" class="form-select no-custom-select" data-no-custom-select="true" onchange="this.form.submit()" style="border-radius:10px; height:46px; font-weight:600; font-size:13.5px; padding-left:38px; cursor:pointer; background-color:#ffffff;">
                    <option value="">-- All Action Events --</option>
                    <?php foreach ($distinctActions as $act): ?>
                        <option value="<?php echo htmlspecialchars($act); ?>" <?php echo $actionFilter === $act ? 'selected' : ''; ?>>
                            <?php echo ucwords(str_replace('_', ' ', $act)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark fw-bold px-3 flex-grow-1" style="border-radius:10px; height:46px;">
                <i class="bx bx-filter-alt me-1"></i> Apply Filter
            </button>
            <?php if (!empty($search) || !empty($actionFilter)): ?>
                <a href="activity-logs.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius:10px; height:46px; width:46px; flex-shrink:0;" title="Reset Filters">
                    <i class="bx bx-x fs-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Logs Data Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 170px;">Timestamp</th>
                    <th>Staff Operator</th>
                    <th>Action Event</th>
                    <th>Target Entity</th>
                    <th>Audit Details</th>
                    <th class="text-end">IP &amp; Location</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-shield-check fs-1 d-block mb-2 text-success"></i>
                            No activity records matched the selected query.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $rawAction = $log['action'];
                        $actionName = ucwords(str_replace('_', ' ', $rawAction));
                        
                        $badgeCls = 'status-badge-secondary';
                        if (strpos($rawAction, 'delete') !== false || strpos($rawAction, 'cancel') !== false) {
                            $badgeCls = 'status-badge-danger';
                        } elseif (strpos($rawAction, 'create') !== false || strpos($rawAction, 'add') !== false || strpos($rawAction, 'publish') !== false) {
                            $badgeCls = 'status-badge-success';
                        } elseif (strpos($rawAction, 'update') !== false || strpos($rawAction, 'edit') !== false) {
                            $badgeCls = 'status-badge-primary';
                        } elseif (strpos($rawAction, 'login') !== false || strpos($rawAction, 'auth') !== false) {
                            $badgeCls = 'status-badge-warning';
                        }

                        $rawIp = $log['ip_address'] ?: '127.0.0.1';
                        $rawLoc = !empty($log['location']) ? $log['location'] : resolve_ip_location($rawIp);
                    ?>
                        <tr>
                            <td>
                                <div class="text-dark fw-bold" style="font-size:12.5px;"><?php echo date('d M Y', strtotime($log['created_at'])); ?></div>
                                <small class="text-muted"><i class="bx bx-time-five me-1"></i><?php echo date('h:i:s A', strtotime($log['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark" style="font-size: 13px;">
                                    <?php echo htmlspecialchars($log['admin_name'] ?: 'Staff Operator'); ?>
                                </div>
                                <small class="text-muted">Staff ID: #<?php echo (int)$log['admin_id']; ?></small>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $badgeCls; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo htmlspecialchars($actionName); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1" style="font-size: 11px;">
                                    <?php echo htmlspecialchars($log['entity_type']); ?><?php echo $log['entity_id'] ? ' #' . $log['entity_id'] : ''; ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-dark" style="max-width: 420px; line-height: 1.5;">
                                    <?php echo htmlspecialchars($log['details']); ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-column align-items-end">
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1 mb-1" style="font-size: 11px;">
                                        <i class="bx bx-network-chart text-primary me-1"></i><?php echo htmlspecialchars($rawIp); ?>
                                    </span>
                                    <small class="text-muted fw-semibold d-flex align-items-center gap-1" style="font-size: 11px; max-width: 200px; text-align: right; line-height: 1.3;">
                                        <i class="bx bx-map-pin text-danger"></i> <?php echo htmlspecialchars($rawLoc); ?>
                                    </small>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Audit Logs Pagination -->
    <?php echo render_admin_pagination($page, $totalPages, $totalRecords, $perPage, ['search' => $search, 'action_filter' => $actionFilter]); ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
