<?php
/**
 * Tiano Hotel & Suites - Newsletter Subscribers Desk & Promotional Campaign Broadcaster
 */
$pageTitle = 'Newsletter Subscribers';
$activePage = 'newsletter';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../include/mailer.php';

$success = '';
$error = '';

// Self-healing migration: Ensure newsletter_campaigns table exists and gallery has media_type
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(200) NOT NULL,
            headline VARCHAR(255) DEFAULT NULL,
            image_url VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            cta_text VARCHAR(100) DEFAULT NULL,
            cta_url VARCHAR(255) DEFAULT NULL,
            recipients_count INT DEFAULT 0,
            sent_by VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN media_type ENUM('image', 'video') DEFAULT 'image' AFTER category_filter");
} catch (Exception $e) {}

// Auto-Sync guest emails from bookings table into newsletter_subscribers
try {
    $pdo->exec("
        INSERT IGNORE INTO newsletter_subscribers (email, status, subscribed_at)
        SELECT DISTINCT guest_email, 'active', MIN(created_at)
        FROM bookings
        WHERE guest_email IS NOT NULL AND guest_email != '' AND guest_email LIKE '%@%'
        GROUP BY guest_email
    ");
} catch (Exception $e) {}

// Handle Subscriber Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_subscriber') {
    $subId = (int)($_POST['subscriber_id'] ?? 0);
    if ($subId > 0) {
        $del = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $del->execute([$subId]);
        log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_subscriber', 'newsletter', $subId, 'Deleted newsletter subscriber');
        $success = "Subscriber removed successfully.";
    }
}

// Handle Promotional Campaign Broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_campaign') {
    $subject  = trim($_POST['subject'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $message  = trim($_POST['message'] ?? '');
    $ctaText  = trim($_POST['cta_text'] ?? '');
    $ctaUrl   = trim($_POST['cta_url'] ?? '');
    $promoImg = trim($_POST['gallery_image'] ?? '');

    if (empty($subject) || empty($message)) {
        $error = "Please provide both a campaign subject and promotional message body.";
    } else {
        // Handle optional promo image file upload
        if (isset($_FILES['promo_image']) && $_FILES['promo_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/img/uploads/newsletter';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['promo_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'promo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $target = $uploadDir . '/' . $filename;
                if (move_uploaded_file($_FILES['promo_image']['tmp_name'], $target)) {
                    $promoImg = 'assets/img/uploads/newsletter/' . $filename;
                }
            }
        }

        // Fetch all active subscribers
        $stmtSubs = $pdo->query("SELECT email FROM newsletter_subscribers WHERE status = 'active'");
        $subscriberEmails = $stmtSubs->fetchAll(PDO::FETCH_COLUMN);

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

        if (empty($subscriberEmails)) {
            $error = "No active subscribers found to broadcast to.";
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'sent_count' => 0, 'message' => $error]);
                exit;
            }
        } else {
            $headline = !empty($headline) ? $headline : 'Exclusive Announcement from Tiano Hotel & Suites';
            $broadcastRes = sendPromotionalNewsletterBroadcast($subscriberEmails, $subject, $headline, $message, $promoImg, $ctaText, $ctaUrl);

            $sentCount = $broadcastRes['sent_count'] ?? 0;

            if ($sentCount > 0) {
                // Record campaign in database
                try {
                    $stmtC = $pdo->prepare("
                        INSERT INTO newsletter_campaigns (subject, headline, image_url, message, cta_text, cta_url, recipients_count, sent_by, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmtC->execute([$subject, $headline, $promoImg, $message, $ctaText, $ctaUrl, $sentCount, $admin['fullname']]);
                    $campId = $pdo->lastInsertId();

                    log_activity($pdo, $admin['id'], $admin['fullname'], 'send_newsletter_campaign', 'newsletter', $campId, "Broadcast campaign '{$subject}' sent to {$sentCount} subscriber(s)");
                } catch (Exception $e) {}

                $success = "Promotional campaign \"{$subject}\" successfully dispatched to {$sentCount} active subscriber(s)!";

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'sent_count' => $sentCount, 'message' => $success]);
                    exit;
                }
            } else {
                $errDetails = !empty($broadcastRes['errors']) ? implode('; ', array_slice($broadcastRes['errors'], 0, 2)) : 'Connection timeout / server rejected delivery';
                $error = "Campaign dispatch failed: " . htmlspecialchars($errDetails);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'sent_count' => 0, 'message' => $error]);
                    exit;
                }
            }
        }
    }
}

// Fetch Gallery Photos for optional promotional flyer dropdown
$galleryPhotos = [];
try {
    $galleryPhotos = $pdo->query("SELECT id, title, thumb_url, full_url FROM gallery WHERE full_url NOT LIKE '%.mp4' AND full_url NOT LIKE '%.webm' ORDER BY id DESC LIMIT 15")->fetchAll();
} catch (Exception $e) {
    $galleryPhotos = [];
}

// Search & Pagination
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$countSql = "SELECT COUNT(*) FROM newsletter_subscribers WHERE 1=1";
$params = [];
if (!empty($search)) {
    $countSql .= " AND email LIKE ?";
    $params[] = "%{$search}%";
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

$sql = "SELECT * FROM newsletter_subscribers WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND email LIKE ?";
}
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
$bindIdx = 1;
if (!empty($search)) {
    $stmt->bindValue($bindIdx++, "%{$search}%", PDO::PARAM_STR);
}
$stmt->bindValue($bindIdx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIdx++, $offset, PDO::PARAM_INT);
$stmt->execute();
$subscribers = $stmt->fetchAll();

// Recent campaigns
$recentCampaigns = $pdo->query("SELECT * FROM newsletter_campaigns ORDER BY id DESC LIMIT 5")->fetchAll();
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

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Newsletter Subscriber Desk</h4>
        <p class="text-muted small mb-0">Registered guest emails automatically synced from online bookings, walk-ins, and newsletter forms.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-white text-dark border px-3 py-2 shadow-sm rounded-pill fw-bold">
            <i class="bx bx-envelope text-danger me-1"></i> Total Subscribers: <?php echo number_format($totalRecords); ?>
        </span>
        <button type="button" class="btn btn-danger fw-bold rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" style="background:#E73539;" data-bs-toggle="modal" data-bs-target="#composeNewsletterModal">
            <i class="bx bx-paper-plane fs-5"></i>
            <span>Send Promotional Campaign</span>
        </button>
    </div>
</div>

<!-- Compose Campaign Modal -->
<div class="modal fade" id="composeNewsletterModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-paper-plane"></i> Compose Promotional Newsletter
                    </h5>
                    <small class="text-white-50">Broadcast offers, event hall promotions, or dining specials to all <?php echo number_format($totalRecords); ?> active subscribers.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="newsletter.php" enctype="multipart/form-data" id="composeNewsletterForm">
                <input type="hidden" name="action" value="send_campaign">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Subject Line *</label>
                            <input type="text" class="form-control" name="subject" required placeholder="e.g. Special Weekend Getaway & Event Hall Offers at Tiano Hotel">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted text-uppercase">Headline / Tagline</label>
                            <input type="text" class="form-control" name="headline" placeholder="e.g. Luxury Suites & Gourmet Banqueting">
                        </div>

                        <!-- Image Section -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Upload Promotional Flyer</label>
                            <input type="file" class="form-control" name="promo_image" accept="image/*">
                            <small class="text-muted" style="font-size:11px;">Upload custom flyer or banner (JPEG, PNG, WebP)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">OR Pick Gallery Image</label>
                            <select class="form-select" name="gallery_image">
                                <option value="">-- Do not attach gallery image --</option>
                                <?php foreach ($galleryPhotos as $gp): ?>
                                    <option value="<?php echo htmlspecialchars($gp['full_url']); ?>">
                                        <?php echo htmlspecialchars($gp['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted" style="font-size:11px;">Pick an existing hotel showcase image</small>
                        </div>

                        <!-- Write-Up -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Promotional Write-Up / Announcement *</label>
                            <textarea class="form-control" name="message" rows="5" required placeholder="Write your announcement here: discounts on executive suites, hosting corporate conferences at the banquet hall, weekend live grills at the bush bar..."></textarea>
                        </div>

                        <!-- Call To Action Button -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">CTA Button Text</label>
                            <input type="text" class="form-control" name="cta_text" placeholder="e.g. Reserve Event Hall & Book Suite" value="Book Room & Explore Offers">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">CTA Button Link URL</label>
                            <input type="url" class="form-control" name="cta_url" placeholder="e.g. https://tianohotel.com/booking.php" value="http://localhost/Tiano-Hotel/booking.php">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4" id="sendCampaignBtn" style="background:#E73539;">
                        <i class="bx bx-send me-1"></i> Send Campaign Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Luxury Broadcast Sending Animation Overlay -->
<div id="broadcastLoadingOverlay" class="broadcast-overlay" style="display: none;">
    <div class="broadcast-overlay-card">
        <!-- Floating Animated Radar / Plane -->
        <div class="broadcast-radar-wrap">
            <div class="broadcast-pulse-ring ring-1"></div>
            <div class="broadcast-pulse-ring ring-2"></div>
            <div class="broadcast-icon-core" id="broadcastIconCore">
                <i class="bx bx-paper-plane"></i>
            </div>
        </div>

        <h4 class="broadcast-title" id="broadcastTitle">Broadcasting Campaign</h4>
        <p class="broadcast-subtitle" id="broadcastSubtitle">Transmitting promotional newsletter to active subscribers</p>

        <!-- Shimmer Progress Bar -->
        <div class="broadcast-progress-track">
            <div class="broadcast-progress-bar" id="broadcastProgressBar"></div>
        </div>

        <!-- Live Step Status -->
        <div class="broadcast-status-badge" id="broadcastStatusBadge">
            <span class="broadcast-spinner-dot" id="broadcastSpinnerDot"></span>
            <span id="broadcastStatusText">Connecting to mail server & transmitting...</span>
        </div>

        <div class="broadcast-notice" id="broadcastNotice">
            <i class="bx bx-shield-check me-1"></i> Please keep this tab open. Your dispatch report will be generated automatically once sent.
        </div>

        <div id="broadcastActionWrap" style="display:none; margin-top: 18px;">
            <button type="button" class="btn btn-sm btn-outline-light px-3 py-2 rounded-pill" onclick="document.getElementById('broadcastLoadingOverlay').classList.remove('active');">
                <i class="bx bx-x me-1"></i> Dismiss &amp; Check Settings
            </button>
        </div>
    </div>
</div>

<style>
.broadcast-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 10999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.broadcast-overlay.active {
    display: flex !important;
    opacity: 1;
}
.broadcast-overlay-card {
    background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 24px;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.65), 0 0 40px rgba(231, 53, 57, 0.2);
    max-width: 480px;
    width: 100%;
    padding: 42px 32px 34px;
    text-align: center;
    color: #ffffff;
    animation: broadcastCardIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes broadcastCardIn {
    from { transform: scale(0.92); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.broadcast-radar-wrap {
    position: relative;
    width: 90px;
    height: 90px;
    margin: 0 auto 22px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.broadcast-pulse-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
    border: 2px solid rgba(231, 53, 57, 0.45);
    box-sizing: border-box;
}
.broadcast-pulse-ring.ring-1 {
    width: 100%;
    height: 100%;
    animation: ringPulse 2.2s infinite cubic-bezier(0.215, 0.61, 0.355, 1);
}
.broadcast-pulse-ring.ring-2 {
    width: 140%;
    height: 140%;
    animation: ringPulse 2.2s infinite 0.7s cubic-bezier(0.215, 0.61, 0.355, 1);
}
@keyframes ringPulse {
    0% { transform: translate(-50%, -50%) scale(0.6); opacity: 0.9; }
    100% { transform: translate(-50%, -50%) scale(1.35); opacity: 0; }
}
.broadcast-icon-core {
    position: relative;
    width: 66px;
    height: 66px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E73539 0%, #b91c1c 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 10px 25px rgba(231, 53, 57, 0.5);
    animation: floatIcon 2s ease-in-out infinite alternate;
}
@keyframes floatIcon {
    from { transform: translateY(0) rotate(0deg); }
    to { transform: translateY(-4px) rotate(-6deg); }
}
.broadcast-title {
    font-size: 21px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.3px;
    margin-bottom: 6px;
}
.broadcast-subtitle {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 24px;
}
.broadcast-progress-track {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 100px;
    height: 8px;
    overflow: hidden;
    position: relative;
    margin-bottom: 18px;
}
.broadcast-progress-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;
    background: linear-gradient(90deg, #E73539 0%, #ff7b72 50%, #E73539 100%);
    background-size: 200% 100%;
    animation: progressShimmer 1.6s infinite linear;
    border-radius: 100px;
}
@keyframes progressShimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.broadcast-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 12.5px;
    color: #f1f5f9;
    font-weight: 500;
    margin-bottom: 18px;
}
.broadcast-spinner-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 10px #10b981;
    animation: blinkDot 1s infinite alternate;
}
@keyframes blinkDot {
    from { opacity: 0.35; }
    to { opacity: 1; }
}
.broadcast-notice {
    font-size: 11.5px;
    color: rgba(255, 255, 255, 0.5);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 14px;
    line-height: 1.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const composeForm = document.getElementById('composeNewsletterForm');
    const sendBtn = document.getElementById('sendCampaignBtn');
    const overlay = document.getElementById('broadcastLoadingOverlay');
    const statusText = document.getElementById('broadcastStatusText');

    if (composeForm && sendBtn && overlay) {
        composeForm.addEventListener('submit', function(e) {
            if (!composeForm.checkValidity()) {
                return;
            }
            e.preventDefault(); // Stop page from navigating away and creating blank screen!

            // Hide the Compose Modal
            const composeModalEl = document.getElementById('composeNewsletterModal');
            if (composeModalEl) {
                const modalInstance = bootstrap.Modal.getInstance(composeModalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Disable submit button inside modal
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Broadcasting...';

            // Show Luxury Overlay
            overlay.classList.add('active');

            // Rotating status messages so user sees live animation and progress
            const statusSteps = [
                'Connecting to secure mail relay server...',
                'Formatting responsive luxury newsletter template...',
                'Embedding high-resolution promotional assets...',
                'Transmitting campaign to active subscribers in queue...',
                'Finalizing delivery reports and audit log...'
            ];
            let stepIdx = 0;
            const stepInterval = setInterval(function() {
                stepIdx = (stepIdx + 1) % statusSteps.length;
                if (statusText) {
                    statusText.textContent = statusSteps[stepIdx];
                }
            }, 1500);

            // Transmit via AJAX with FormData
            const formData = new FormData(composeForm);
            formData.append('ajax', '1');

            fetch('newsletter.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                clearInterval(stepInterval);
                const iconCore = document.getElementById('broadcastIconCore');
                const titleEl = document.getElementById('broadcastTitle');
                const subEl = document.getElementById('broadcastSubtitle');
                const progressBar = document.getElementById('broadcastProgressBar');
                const noticeEl = document.getElementById('broadcastNotice');
                const actionWrap = document.getElementById('broadcastActionWrap');

                if (data.success) {
                    if (iconCore) {
                        iconCore.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                        iconCore.style.boxShadow = '0 10px 25px rgba(16, 185, 129, 0.5)';
                        iconCore.innerHTML = '<i class="bx bx-check"></i>';
                    }
                    if (titleEl) titleEl.textContent = 'Campaign Dispatched!';
                    if (subEl) subEl.textContent = data.message || 'Newsletter broadcast delivered to active subscribers!';
                    if (progressBar) {
                        progressBar.style.background = '#10b981';
                        progressBar.style.animation = 'none';
                        progressBar.style.width = '100%';
                    }
                    if (statusText) {
                        statusText.textContent = 'Dispatched to ' + (data.sent_count || 'all') + ' subscriber(s)';
                    }
                    if (noticeEl) {
                        noticeEl.innerHTML = '<i class="bx bx-check-circle me-1 text-success"></i> Refreshing desk to update campaign history...';
                    }

                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (iconCore) {
                        iconCore.style.background = 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)';
                        iconCore.style.boxShadow = '0 10px 25px rgba(239, 68, 68, 0.5)';
                        iconCore.innerHTML = '<i class="bx bx-error-circle"></i>';
                    }
                    if (titleEl) titleEl.textContent = 'Dispatch Incomplete';
                    if (subEl) subEl.textContent = data.message || 'An error occurred during SMTP transmission.';
                    if (progressBar) {
                        progressBar.style.background = '#ef4444';
                        progressBar.style.animation = 'none';
                    }
                    if (statusText) {
                        statusText.textContent = 'Transmission halted';
                    }
                    if (actionWrap) {
                        actionWrap.style.display = 'block';
                    }
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="bx bx-send me-1"></i> Send Campaign Now';
                }
            })
            .catch(function(err) {
                clearInterval(stepInterval);
                const titleEl = document.getElementById('broadcastTitle');
                const subEl = document.getElementById('broadcastSubtitle');
                const actionWrap = document.getElementById('broadcastActionWrap');

                if (titleEl) titleEl.textContent = 'Transmission Notice';
                if (subEl) subEl.textContent = 'The campaign request completed. Check the broadcast log below.';
                if (actionWrap) {
                    actionWrap.style.display = 'block';
                }
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bx bx-send me-1"></i> Send Campaign Now';
                setTimeout(function() {
                    window.location.reload();
                }, 2500);
            });
        });
    }
});
</script>

<!-- Search Bar with Premium Form Control -->
<div class="admin-card mb-4 p-3">
    <form method="GET" action="newsletter.php" class="row g-2 align-items-center">
        <div class="col-md-9">
            <div class="input-group">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search subscriber by email address..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark fw-bold px-4 w-100" style="border-radius:10px; height:46px;">
                <i class="bx bx-filter-alt me-1"></i> Search
            </button>
            <?php if (!empty($search)): ?>
                <a href="newsletter.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius:10px; height:46px; width:46px;" title="Reset Search">
                    <i class="bx bx-x fs-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Subscribers Data Table -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <div>
            <h5 class="fw-bold mb-0 text-dark">Active Email Subscribers</h5>
            <small class="text-muted">Displaying <?php echo count($subscribers); ?> of <?php echo number_format($totalRecords); ?> subscriber(s)</small>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">#</th>
                    <th>Subscriber Email</th>
                    <th>Status</th>
                    <th>Subscribed On</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscribers)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bx bx-envelope-open fs-1 d-block mb-2 text-secondary"></i>
                            No newsletter subscribers found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $idx => $s): 
                        $rawDate = !empty($s['subscribed_at']) ? $s['subscribed_at'] : (!empty($s['created_at']) ? $s['created_at'] : null);
                        $formattedDate = $rawDate ? date('d M Y, h:i A', strtotime($rawDate)) : 'Active';
                        $statusVal = $s['status'] ?? 'active';
                        $isSubscribed = ($statusVal === 'active');
                    ?>
                        <tr>
                            <td><span class="text-muted fw-semibold"><?php echo $offset + $idx + 1; ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                        <i class="bx bx-envelope"></i>
                                    </div>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($s['email']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $isSubscribed ? 'status-badge-success' : 'status-badge-secondary'; ?>">
                                    <span class="status-dot"></span>
                                    <?php echo htmlspecialchars(ucfirst($statusVal)); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted fw-semibold">
                                    <i class="bx bx-calendar me-1"></i><?php echo $formattedDate; ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="newsletter.php" class="d-inline" id="deleteForm_<?php echo $s['id']; ?>">
                                    <input type="hidden" name="action" value="delete_subscriber">
                                    <input type="hidden" name="subscriber_id" value="<?php echo $s['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" style="border-radius:8px;" title="Delete Subscriber" onclick="tianoConfirm('Remove Subscriber', 'Remove <?php echo htmlspecialchars(addslashes($s['email'])); ?> from the newsletter subscribers list?', 'Remove Email', function() { document.getElementById('deleteForm_<?php echo $s['id']; ?>').submit(); });">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Pagination -->
    <?php echo render_admin_pagination($page, $totalPages, $totalRecords, $perPage, ['search' => $search]); ?>
</div>

<!-- Recent Campaigns Dispatched History -->
<?php if (!empty($recentCampaigns)): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Recent Broadcast Campaigns</h5>
                <small class="text-muted">Log of promotional email campaigns dispatched to subscribers</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Subject Line</th>
                        <th>Headline</th>
                        <th>Recipients</th>
                        <th>Dispatched At</th>
                        <th>Sent By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCampaigns as $c): ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?php echo htmlspecialchars($c['subject']); ?></strong>
                                <?php if (!empty($c['image_url'])): ?>
                                    <small class="text-success d-block"><i class="bx bx-image me-1"></i>Image flyer attached</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($c['headline'] ?: 'General Announcement'); ?></td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bx bx-check-circle me-1"></i> <?php echo number_format($c['recipients_count']); ?> sent
                                </span>
                            </td>
                            <td><small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($c['created_at'])); ?></small></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($c['sent_by'] ?: 'Admin'); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
