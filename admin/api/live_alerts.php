<?php
/**
 * Tiano Hotel & Suites - Live Alert Monitoring Endpoint
 * Provides 24/7 real-time polling data for new bookings, contact inquiries,
 * and pending counts to trigger front-desk audio alarms and live UI updates.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';

// Verify admin session
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // 1. Fetch latest booking ID and complete details (joined with room category and room)
    $stmtBooking = $pdo->query("
        SELECT b.id, b.booking_reference, b.guest_name, b.guest_phone, b.guest_email,
               b.check_in_date, b.check_out_date, b.nights, b.nightly_rate, b.total_amount,
               b.caution_deposit, b.payment_method, b.payment_status, b.booking_status,
               b.special_requests, r.room_number, b.created_at,
               COALESCE(rc.name, 'Luxury Accommodation') AS category_name
        FROM bookings b
        LEFT JOIN room_categories rc ON b.category_id = rc.id
        LEFT JOIN rooms r ON b.assigned_room_id = r.id
        ORDER BY b.id DESC 
        LIMIT 1
    ");
    $latestBooking = $stmtBooking->fetch(PDO::FETCH_ASSOC);

    // 2. Fetch latest contact message ID and details
    $stmtMsg = $pdo->query("
        SELECT id, name, email, msg_subject AS subject, is_read, created_at 
        FROM contact_messages 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $latestMessage = $stmtMsg->fetch(PDO::FETCH_ASSOC);

    // 3. Unread / Pending totals
    $unreadBookingsCount = (int)$pdo->query("
        SELECT COUNT(*) 
        FROM bookings 
        WHERE booking_status = 'confirmed' AND payment_status = 'unpaid'
    ")->fetchColumn();

    $unreadMessagesCount = (int)$pdo->query("
        SELECT COUNT(*) 
        FROM contact_messages 
        WHERE is_read = 0
    ")->fetchColumn();

    $totalNotifications = $unreadBookingsCount + $unreadMessagesCount;

    // 4. Operational Metrics (for dynamic real-time dashboard update without reloading)
    $todayStr = date('Y-m-d');
    $todaysArrivals = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings 
        WHERE check_in_date = '$todayStr' AND booking_status != 'cancelled'
    ")->fetchColumn();

    $todaysDepartures = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings 
        WHERE check_out_date = '$todayStr' AND booking_status != 'cancelled'
    ")->fetchColumn();

    $activeBookings = (int)$pdo->query("
        SELECT COUNT(*) FROM bookings 
        WHERE booking_status = 'checked_in'
    ")->fetchColumn();

    $totalPendingRevenue = (float)$pdo->query("
        SELECT COALESCE(SUM(total_amount), 0) FROM bookings 
        WHERE payment_status = 'unpaid' AND booking_status != 'cancelled'
    ")->fetchColumn();

    // 5. Front-Desk Audio Alarm & Chime Configuration
    $alarmSoundEnabled = 1;
    $alarmSoundTone = 'concierge_bell';
    try {
        $stmtAlarm = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('alarm_sound_enabled', 'alarm_sound_tone')");
        while ($sRow = $stmtAlarm->fetch(PDO::FETCH_ASSOC)) {
            if ($sRow['setting_key'] === 'alarm_sound_enabled') {
                $alarmSoundEnabled = ($sRow['setting_value'] === '0') ? 0 : 1;
            } elseif ($sRow['setting_key'] === 'alarm_sound_tone') {
                $alarmSoundTone = $sRow['setting_value'] ?: 'concierge_bell';
            }
        }
    } catch (Exception $e) {}

    echo json_encode([
        'success'              => true,
        'latest_booking_id'    => $latestBooking ? (int)$latestBooking['id'] : 0,
        'latest_message_id'    => $latestMessage ? (int)$latestMessage['id'] : 0,
        'unread_bookings'      => $unreadBookingsCount,
        'unread_messages'      => $unreadMessagesCount,
        'total_notifications'  => $totalNotifications,
        'latest_booking'       => $latestBooking ?: null,
        'latest_message'       => $latestMessage ?: null,
        'alarm_sound_enabled'  => $alarmSoundEnabled,
        'alarm_sound_tone'     => $alarmSoundTone,
        'stats'                => [
            'arrivals_today'         => $todaysArrivals,
            'departures_today'       => $todaysDepartures,
            'active_bookings'        => $activeBookings,
            'pending_revenue_raw'    => $totalPendingRevenue,
            'pending_revenue_fmt'    => '₦' . number_format($totalPendingRevenue),
            'unread_bookings_count'  => $unreadBookingsCount
        ],
        'server_time'          => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
