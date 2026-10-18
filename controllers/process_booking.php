<?php
/**
 * Tiano Hotel and Suites - Booking Reservation Processor
 * Enforces strictly "Pay on Arrival" physical check-in policy.
 */
require_once __DIR__ . '/../include/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$category_id      = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
$check_in_date    = isset($_POST['check_in_date']) ? trim($_POST['check_in_date']) : '';
$check_out_date   = isset($_POST['check_out_date']) ? trim($_POST['check_out_date']) : '';
$guest_name       = isset($_POST['guest_name']) ? trim($_POST['guest_name']) : '';
$guest_email      = isset($_POST['guest_email']) ? trim($_POST['guest_email']) : '';
$guest_phone      = isset($_POST['guest_phone']) ? trim($_POST['guest_phone']) : '';
$adults           = isset($_POST['adults']) ? max(1, (int)$_POST['adults']) : 1;
$children         = isset($_POST['children']) ? max(0, (int)$_POST['children']) : 0;
$special_requests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!$category_id || empty($check_in_date) || empty($check_out_date) || empty($guest_name) || empty($guest_email) || empty($guest_phone)) {
    $_SESSION['booking_toast_error'] = "Please fill in all required booking information (Guest Name, Email, Phone, and Dates).";
    $redir = "../booking.php?error=" . urlencode("Please fill in all required booking information.");
    if ($category_id > 0) {
        $redir .= "&category_id=" . $category_id;
    }
    header("Location: " . $redir);
    exit;
}

// Fetch room category details
$stmt = $pdo->prepare("SELECT * FROM room_categories WHERE id = ? AND status = 'active' LIMIT 1");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['booking_toast_error'] = "Selected room category was not found or is currently unavailable.";
    header("Location: ../booking.php?error=" . urlencode("Selected room category is unavailable."));
    exit;
}

// Calculate nights
$ts_in  = strtotime($check_in_date);
$ts_out = strtotime($check_out_date);

if ($ts_out <= $ts_in) {
    $ts_out = strtotime('+1 day', $ts_in);
    $check_out_date = date('Y-m-d', $ts_out);
}

$nights = max(1, (int)round(($ts_out - $ts_in) / (60 * 60 * 24)));
$nightly_rate = (float)$category['price_per_night'];
$total_amount = $nightly_rate * $nights;
$caution_deposit = (float)get_setting($pdo, 'caution_deposit_default', 10000.00);

// Generate unique booking reference
$booking_reference = generate_booking_reference();

try {
    $insert = $pdo->prepare("
        INSERT INTO bookings (
            booking_reference,
            category_id,
            guest_name,
            guest_email,
            guest_phone,
            check_in_date,
            check_out_date,
            adults,
            children,
            nights,
            nightly_rate,
            total_amount,
            caution_deposit,
            payment_method,
            payment_status,
            booking_status,
            special_requests,
            created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pay_on_arrival', 'unpaid', 'confirmed', ?, NOW()
        )
    ");

    $insert->execute([
        $booking_reference,
        $category_id,
        $guest_name,
        $guest_email,
        $guest_phone,
        $check_in_date,
        $check_out_date,
        $adults,
        $children,
        $nights,
        $nightly_rate,
        $total_amount,
        $caution_deposit,
        $special_requests
    ]);

    // Automatically enroll booking guest email into Newsletter Subscribers
    if (!empty($guest_email) && filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmtSub = $pdo->prepare("
                INSERT INTO newsletter_subscribers (email, status, subscribed_at)
                VALUES (?, 'active', NOW())
                ON DUPLICATE KEY UPDATE status = 'active'
            ");
            $stmtSub->execute([$guest_email]);
        } catch (Exception $e) {
            error_log("[Newsletter Auto-Sync] Failed for {$guest_email}: " . $e->getMessage());
        }
    }

    // Trigger Dual Notifications (Guest Pass Email & Hotel Reception Alert)
    require_once __DIR__ . '/../include/mailer.php';
    $bookingData = [
        'booking_reference' => $booking_reference,
        'guest_name'        => $guest_name,
        'guest_email'       => $guest_email,
        'guest_phone'       => $guest_phone,
        'category_name'     => $category['name'],
        'check_in_date'     => $check_in_date,
        'check_out_date'    => $check_out_date,
        'nights'            => $nights,
        'nightly_rate'      => $nightly_rate,
        'total_amount'      => $total_amount,
        'caution_deposit'   => $caution_deposit,
        'special_requests'  => $special_requests,
    ];
    
    // Fast dual notifications dispatch in single persistent SMTP connection (Guest Pass + Admin Alert)
    $emailRes = sendDualBookingNotifications($bookingData);
    if (!empty($emailRes) && !$emailRes['success']) {
        error_log("[Booking Mailer] Dual dispatch warning for {$booking_reference}: " . ($emailRes['message'] ?? 'Unknown'));
    }

    // Redirect to digital printable voucher
    header("Location: ../voucher.php?ref=" . urlencode($booking_reference));
    exit;

} catch (Exception $e) {
    error_log("Reservation booking error: " . $e->getMessage());
    $_SESSION['booking_toast_error'] = "A temporary database error occurred while processing your booking. Please try again or call our reception desk.";
    header("Location: ../booking.php?error=" . urlencode("A temporary error occurred. Please try again."));
    exit;
}
