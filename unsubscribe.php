<?php
/**
 * Tiano Hotel & Suites - Guest Newsletter Unsubscribe Handler
 * Allows guests to opt out of promotional email campaigns with one click.
 */
require_once __DIR__ . '/include/functions.php';

$page_title = 'Unsubscribe from Newsletter | Tiano Hotel & Suites';
$page_desc  = 'Manage your email subscription preferences for Tiano Hotel & Suites.';
$active_page = 'unsubscribe';

$email = trim($_GET['email'] ?? ($_POST['email'] ?? ''));
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$action = $_POST['action'] ?? ($_GET['action'] ?? 'confirm');

$status = 'pending';
$message = '';
$isValid = false;

// Verify security HMAC token or fallback
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $expectedToken = hash_hmac('sha256', strtolower($email), 'tiano_secret_unsub_key');
    // Allow if token matches or direct confirmation
    if (hash_equals($expectedToken, $token) || $action === 'resubscribe' || $action === 'do_unsubscribe') {
        $isValid = true;
    }
}

// Process Unsubscribe Request
if ($isValid && ($action === 'confirm' || $action === 'do_unsubscribe')) {
    try {
        // Update subscriber status to 'unsubscribed'
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?");
        $stmt->execute([$email]);
        
        $status = 'unsubscribed';
        $message = "You have been successfully unsubscribed from Tiano Hotel & Suites marketing announcements.";
    } catch (Exception $e) {
        error_log("[Newsletter Unsubscribe Error] " . $e->getMessage());
        $status = 'error';
        $message = "An error occurred while updating your subscription. Please try again or contact front-desk.";
    }
} elseif ($isValid && $action === 'resubscribe') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO newsletter_subscribers (email, status, subscribed_at)
            VALUES (?, 'active', NOW())
            ON DUPLICATE KEY UPDATE status = 'active'
        ");
        $stmt->execute([$email]);
        
        $status = 'resubscribed';
        $message = "Welcome back! Your email has been re-activated to receive exclusive discounts and events.";
    } catch (Exception $e) {
        $status = 'error';
        $message = "Could not re-subscribe at this time. Please try again later.";
    }
}

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg1" style="padding-top: 130px; padding-bottom: 70px;">
    <div class="container">
        <div class="inner-title text-center">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class='bx bx-chevron-right'></i></li>
                <li>Subscription Preferences</li>
            </ul>
            <h3>Newsletter Subscription</h3>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<section class="unsubscribe-section pt-100 pb-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-center bg-white">
                    
                    <?php if ($status === 'unsubscribed'): ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 76px; height: 76px; background: rgba(231, 53, 57, 0.1); color: #E73539;">
                            <i class="bx bx-envelope-open" style="font-size: 42px;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Unsubscribed Successfully</h3>
                        <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.7;">
                            The email address <strong class="text-dark"><?php echo htmlspecialchars($email); ?></strong> has been removed from our promotional broadcast mailing list. You will no longer receive marketing campaigns, seasonal discounts, or hotel flyers.
                        </p>
                        <div class="alert alert-light border rounded-3 text-start mb-4 p-3" style="font-size: 13px; color: #64748b;">
                            <i class="bx bx-info-circle text-primary me-1"></i>
                            <strong>Please Note:</strong> Transactional emails regarding future room reservations, physical check-in passes, or direct contact inquiries will still be sent to you whenever you book a stay at Tiano Hotel &amp; Suites.
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="index.php" class="default-btn btn-bg-one border-radius-5">
                                Return to Homepage
                            </a>
                            <form method="POST" action="unsubscribe.php" class="d-inline">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                <input type="hidden" name="action" value="resubscribe">
                                <button type="submit" class="default-btn btn-bg-three border-radius-5" style="background:#212529; color:#fff;">
                                    Resubscribe Email
                                </button>
                            </form>
                        </div>

                    <?php elseif ($status === 'resubscribed'): ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 76px; height: 76px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="bx bx-check-double" style="font-size: 42px;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Subscription Re-activated!</h3>
                        <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.7;">
                            Thank you! <strong class="text-dark"><?php echo htmlspecialchars($email); ?></strong> is now subscribed to receive priority announcements and exclusive promotional offers from Tiano Hotel &amp; Suites.
                        </p>
                        <a href="index.php" class="default-btn btn-bg-one border-radius-5">
                            Explore Rooms &amp; Suites
                        </a>

                    <?php elseif (!$isValid): ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 76px; height: 76px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="bx bx-error-circle" style="font-size: 42px;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Invalid or Missing Link</h3>
                        <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.7;">
                            This unsubscribe link appears invalid, expired, or was requested without an email address. You may manually enter your email address below to unsubscribe.
                        </p>
                        <form method="POST" action="unsubscribe.php" class="mx-auto" style="max-width: 420px;">
                            <input type="hidden" name="action" value="do_unsubscribe">
                            <div class="input-group mb-3">
                                <input type="email" name="email" class="form-control py-2 px-3" required placeholder="Enter your email address...">
                                <button class="btn btn-danger fw-bold" type="submit" style="background:#E73539;">Unsubscribe</button>
                            </div>
                        </form>
                        <a href="index.php" class="text-muted small mt-2 d-inline-block">&larr; Return to Homepage</a>

                    <?php else: ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-4" style="width: 76px; height: 76px; background: rgba(231, 53, 57, 0.1); color: #E73539;">
                            <i class="bx bx-bell-off" style="font-size: 42px;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">Manage Subscription</h3>
                        <p class="text-muted mb-4">Confirm that you wish to unsubscribe <strong><?php echo htmlspecialchars($email); ?></strong> from all marketing communications.</p>
                        <form method="POST" action="unsubscribe.php">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="do_unsubscribe">
                            <button type="submit" class="default-btn btn-bg-one border-radius-5">
                                Confirm Unsubscribe
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/include/footer.php'; ?>
