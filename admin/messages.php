<?php
/**
 * Tiano Hotel & Suites - Inbound Contact Messages & Inquiries Inbox
 */
$pageTitle = 'Inbound Inquiries';
$activePage = 'messages';
require_once __DIR__ . '/includes/header.php';

$success = '';

// Handle Mark as Read or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $msgId = (int)($_POST['message_id'] ?? 0);
    if ($_POST['action'] === 'mark_read') {
        $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?")->execute([$msgId]);
        log_activity($pdo, $admin['id'], $admin['fullname'], 'mark_message_read', 'message', $msgId, 'Marked inquiry as read');
        $success = "Message marked as read.";
    } elseif ($_POST['action'] === 'delete') {
        $pdo->prepare("DELETE FROM contact_messages WHERE id = ?")->execute([$msgId]);
        log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_message', 'message', $msgId, 'Deleted contact message');
        $success = "Message deleted successfully.";
    }
}

// Pagination & Fetch
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$totalRecords = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

$stmt = $pdo->prepare("SELECT * FROM contact_messages ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bx bx-check-circle fs-5"></i>
        <div><?php echo htmlspecialchars($success); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Guest Inquiries &amp; Contact Inbox</h4>
        <p class="text-muted small mb-0">Messages submitted via the public contact and booking assistance forms.</p>
    </div>
    <div>
        <span class="badge bg-white text-dark border px-3 py-2 shadow-sm rounded-pill fw-bold">
            <i class="bx bx-envelope text-danger me-1"></i> Total Inquiries: <?php echo number_format($totalRecords); ?>
        </span>
    </div>
</div>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Guest Sender</th>
                    <th>Contact Info</th>
                    <th>Subject</th>
                    <th>Inquiry Message</th>
                    <th>Received</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bx bx-envelope-open fs-1 d-block mb-2 text-secondary"></i>
                            No contact inquiries recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $m): 
                        $isUnread = ($m['is_read'] == 0);
                    ?>
                        <tr class="<?php echo $isUnread ? 'fw-bold bg-light-subtle' : ''; ?>">
                            <td>
                                <span class="status-badge <?php echo $isUnread ? 'status-badge-danger' : 'status-badge-secondary'; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo $isUnread ? 'Unread' : 'Read'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($m['name']); ?></div>
                            </td>
                            <td>
                                <div><i class="bx bx-envelope text-danger me-1"></i> <?php echo htmlspecialchars($m['email']); ?></div>
                                <small class="text-muted"><i class="bx bx-phone me-1"></i> <?php echo htmlspecialchars($m['phone_number'] ?: '—'); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m['msg_subject'] ?: 'General Inquiry'); ?></span>
                            </td>
                            <td>
                                <div style="max-width:340px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($m['message']); ?>">
                                    <?php echo htmlspecialchars($m['message']); ?>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted fw-semibold"><?php echo date('d M Y, H:i', strtotime($m['created_at'])); ?></small>
                            </td>
                            <td class="text-end">
                                <?php if ($isUnread): ?>
                                    <form method="POST" action="messages.php?page=<?php echo $page; ?>" class="d-inline">
                                        <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <button type="submit" class="btn btn-sm btn-outline-success" style="border-radius:8px;" title="Mark as read">
                                            <i class="bx bx-check"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="messages.php?page=<?php echo $page; ?>" class="d-inline" id="delMsgForm_<?php echo $m['id']; ?>">
                                        <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="button" class="btn btn-sm btn-outline-danger" style="border-radius:8px;" title="Delete" onclick="tianoConfirm('Delete Inquiry', 'Are you sure you want to delete this guest inquiry permanently?', 'Delete Message', function() { document.getElementById('delMsgForm_<?php echo $m['id']; ?>').submit(); });">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Messages Pagination -->
    <?php echo render_admin_pagination($page, $totalPages, $totalRecords, $perPage); ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
