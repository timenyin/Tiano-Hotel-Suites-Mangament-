<?php
/**
 * Tiano Hotel and Suites - Digital Printable Reservation Voucher
 * Luxury Boarding-Pass / Ticket Design Format.
 * Strictly optimized for print/PDF (clean 1-page fit, all headers/footers suppressed).
 */
require_once __DIR__ . '/include/functions.php';

$ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

if (empty($ref)) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, c.name AS category_name, c.bed_type, c.room_size
    FROM bookings b
    JOIN room_categories c ON b.category_id = c.id
    WHERE b.booking_reference = ?
    LIMIT 1
");
$stmt->execute([$ref]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Error: Booking reference not found.");
}

$hotel_name     = get_setting($pdo, 'hotel_name', 'Tiano Hotel and Suites');
$hotel_address  = get_setting($pdo, 'hotel_address', '44 Marina Road, Eket, Akwa Ibom State, Nigeria');
$hotel_phone    = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_whatsapp = get_setting($pdo, 'hotel_whatsapp', '2348144734612');
$checkin_time   = get_setting($pdo, 'checkin_time', '1:00 PM');
$checkout_time  = get_setting($pdo, 'checkout_time', '12:00 PM');

$page_title = 'Reservation Voucher: ' . $booking['booking_reference'] . ' | ' . $hotel_name;
$active_page = 'rooms';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Prepare WhatsApp confirmation prefilled link
$wa_text = "Hello Tiano Hotel & Suites, I have reserved " . $booking['category_name'] . " with Voucher Ref: " . $booking['booking_reference'] . " for " . date('d M Y', strtotime($booking['check_in_date'])) . ". My name is " . $booking['guest_name'] . ". Please confirm my reservation.";
$wa_url = "https://wa.me/" . htmlspecialchars($hotel_whatsapp) . "?text=" . urlencode($wa_text);
?>

<!-- Inner Banner (Screen Only) -->
<div class="inner-banner inner-bg1 d-print-none">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Digital Voucher</li>
            </ul>
            <h3>Reservation Confirmation</h3>
        </div>
    </div>
</div>

<!-- Main Voucher Container -->
<div class="voucher-page-wrap my-4 my-md-5 py-2">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">

                <!-- ══════════════════════════════════════
                     ANIMATED CONFIRMATION BANNER (SCREEN ONLY - TOP)
                     ══════════════════════════════════════ -->
                <div class="d-print-none mb-4">
                    <div class="confirmation-top-card text-center p-3 p-md-4 rounded-4 shadow-sm">
                        <div class="animated-check-wrap mb-2">
                            <span class="animated-check-circle">
                                <i class="bx bx-check"></i>
                            </span>
                        </div>
                        <h4 class="confirmation-title mb-1">RESERVATION CONFIRMED</h4>
                        <p class="confirmation-sub mb-0">Your reservation at Tiano Hotel &amp; Suites is confirmed. Present your pass upon arrival.</p>
                    </div>
                </div>

                <!-- ══════════════════════════════════════
                     LUXURY TICKET VOUCHER
                     ══════════════════════════════════════ -->
                <div class="hotel-ticket-pass" id="printableVoucher">
                    
                    <!-- Ticket Top Section -->
                    <div class="ticket-head">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="assets/img/compnay_logo.JPG" alt="Tiano Hotel Logo" class="ticket-logo">
                                <div>
                                    <h4 class="ticket-hotel-title mb-0"><?php echo htmlspecialchars($hotel_name); ?></h4>
                                    <span class="ticket-hotel-sub" style="color: rgba(255,255,255,0.75); font-size: 11px; letter-spacing: 1.2px; text-transform: uppercase;">Luxury Accommodation</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="text-white fw-semibold" style="font-size: 13px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($hotel_address); ?><br>
                                    <small class="text-white-50" style="font-size: 11px;">Eket, Akwa Ibom State</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Reference & Barcode Bar -->
                    <div class="ticket-ref-bar">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="ticket-ref-label">OFFICIAL BOOKING REFERENCE</span>
                                <h2 class="ticket-ref-code mb-0"><?php echo htmlspecialchars($booking['booking_reference']); ?></h2>
                            </div>
                            <!-- Barcode Visual Representation -->
                            <div class="ticket-barcode-wrap text-end">
                                <div class="ticket-barcode-bars">
                                    <span></span><span></span><span></span><span></span><span></span>
                                    <span></span><span></span><span></span><span></span><span></span>
                                    <span></span><span></span><span></span><span></span><span></span>
                                    <span></span><span></span><span></span><span></span><span></span>
                                </div>
                                <span class="ticket-barcode-text">*<?php echo htmlspecialchars($booking['booking_reference']); ?>*</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Main Information Grid -->
                    <div class="ticket-body">
                        
                        <div class="row g-4">
                            <!-- Guest Particulars -->
                            <div class="col-sm-6">
                                <div class="ticket-info-card">
                                    <span class="ticket-sec-title"><i class="bx bx-user me-1 text-danger"></i> PRIMARY GUEST</span>
                                    <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($booking['guest_name']); ?></h5>
                                    <div class="ticket-meta-line text-muted small"><i class="bx bx-phone me-1"></i> <?php echo htmlspecialchars($booking['guest_phone']); ?></div>
                                    <div class="ticket-meta-line text-muted small"><i class="bx bx-envelope me-1"></i> <?php echo htmlspecialchars($booking['guest_email']); ?></div>
                                </div>
                            </div>

                            <!-- Room Allocation -->
                            <div class="col-sm-6">
                                <div class="ticket-info-card">
                                    <span class="ticket-sec-title"><i class="bx bx-bed me-1 text-danger"></i> ACCOMMODATION</span>
                                    <h5 class="fw-bold mb-1 text-danger"><?php echo htmlspecialchars($booking['category_name']); ?></h5>
                                    <div class="ticket-meta-line text-muted small"><i class="bx bx-expand me-1"></i> <?php echo htmlspecialchars($booking['bed_type']); ?> (<?php echo htmlspecialchars($booking['room_size']); ?>)</div>
                                    <div class="ticket-meta-line text-muted small"><i class="bx bx-group me-1"></i> <?php echo $booking['adults']; ?> Adults, <?php echo $booking['children']; ?> Children</div>
                                </div>
                            </div>
                        </div>

                        <!-- Guest Special Requests (if provided) -->
                        <?php if (!empty($booking['special_requests'])): ?>
                            <div class="ticket-special-note-box my-3 p-3 rounded-3" style="background:#f8fafc; border:1px dashed #cbd5e1;">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bx bx-message-rounded-dots text-danger fs-5 mt-1 flex-shrink-0"></i>
                                    <div>
                                        <span class="ticket-sec-title mb-1 d-block text-danger">GUEST SPECIAL REQUESTS &amp; ARRIVAL NOTES</span>
                                        <p class="mb-0 text-dark small fst-italic">&ldquo;<?php echo htmlspecialchars($booking['special_requests']); ?>&rdquo;</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Redesigned High-Impact Schedule Timeline (Large, Bold, Ticket Style) -->
                        <div class="ticket-timeline-grid my-4 p-3 p-md-4 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="row g-3 text-center align-items-center">
                                
                                <!-- Check-In Block -->
                                <div class="col-md-4 col-12 text-md-start text-center">
                                    <div class="d-flex align-items-center justify-content-md-start justify-content-center gap-2 mb-1">
                                        <span class="badge bg-danger px-2 py-1" style="font-size:10px; letter-spacing:1px;">CHECK-IN</span>
                                        <small class="text-muted fw-bold" style="font-size:11px;">FROM <?php echo htmlspecialchars($checkin_time); ?></small>
                                    </div>
                                    <h4 class="ticket-date-large mb-0 fw-bold" style="font-size:20px; color:#0f172a; letter-spacing:-0.3px;">
                                        <?php echo date('D, d M Y', strtotime($booking['check_in_date'])); ?>
                                    </h4>
                                    <small class="text-secondary d-block mt-1" style="font-size:12px;">Official Arrival Date</small>
                                </div>
                                
                                <!-- Stay Duration & Pass Center -->
                                <div class="col-md-4 col-12 text-center py-2 py-md-0 border-md-start border-md-end">
                                    <span class="badge bg-dark fs-6 px-3 py-2 fw-bold text-white mb-2" style="letter-spacing:1px; border-radius:30px;">
                                        <i class="bx bx-moon text-danger me-1"></i> <?php echo $booking['nights']; ?> NIGHT<?php echo $booking['nights'] > 1 ? 'S' : ''; ?> STAY
                                    </span>
                                    <div class="d-flex align-items-center justify-content-center gap-2 my-1">
                                        <span style="height:2px; width:25px; background:#cbd5e1; display:inline-block;"></span>
                                        <span class="badge px-2 py-1 fw-bold" style="font-size:11px; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;">
                                            <i class="bx bx-check-double me-1 text-danger"></i> CONFIRMED PASS
                                        </span>
                                        <span style="height:2px; width:25px; background:#cbd5e1; display:inline-block;"></span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size:11px;">Zero Online Prepayment</small>
                                </div>

                                <!-- Check-Out Block -->
                                <div class="col-md-4 col-12 text-md-end text-center">
                                    <div class="d-flex align-items-center justify-content-md-end justify-content-center gap-2 mb-1">
                                        <small class="text-muted fw-bold" style="font-size:11px;">BY <?php echo htmlspecialchars($checkout_time); ?></small>
                                        <span class="badge bg-secondary px-2 py-1" style="font-size:10px; letter-spacing:1px;">CHECK-OUT</span>
                                    </div>
                                    <h4 class="ticket-date-large mb-0 fw-bold" style="font-size:20px; color:#0f172a; letter-spacing:-0.3px;">
                                        <?php echo date('D, d M Y', strtotime($booking['check_out_date'])); ?>
                                    </h4>
                                    <small class="text-secondary d-block mt-1" style="font-size:12px;">Scheduled Departure</small>
                                </div>

                            </div>
                        </div>

                        <!-- Perforated Tear-Line Divider -->
                        <div class="ticket-perforated-divider">
                            <div class="ticket-notch ticket-notch-left"></div>
                            <div class="ticket-dash-line"></div>
                            <div class="ticket-notch ticket-notch-right"></div>
                        </div>

                        <!-- Settlement & Billing Table -->
                        <div class="mt-4">
                            <span class="ticket-sec-title mb-3 d-block"><i class="bx bx-receipt me-1 text-danger"></i> BILLING &amp; SETTLEMENT SUMMARY</span>
                            <div class="table-responsive">
                                <table class="table ticket-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-center" style="white-space:nowrap;">Rate</th>
                                            <th class="text-center" style="white-space:nowrap;">Qty</th>
                                            <th class="text-end" style="white-space:nowrap;">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['category_name']); ?></strong>
                                                <small class="d-block text-muted">Nightly luxury accommodation</small>
                                            </td>
                                            <td class="text-center text-nowrap" style="white-space:nowrap;"><?php echo format_naira($booking['nightly_rate']); ?></td>
                                            <td class="text-center text-nowrap" style="white-space:nowrap;"><span style="display:inline-block;white-space:nowrap !important;"><?php echo $booking['nights']; ?> night<?php echo $booking['nights'] > 1 ? 's' : ''; ?></span></td>
                                            <td class="text-end fw-semibold text-nowrap" style="white-space:nowrap;"><?php echo format_naira($booking['total_amount']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Refundable Caution Deposit</strong>
                                                <small class="d-block text-muted">Reconciled or fully refunded upon checkout</small>
                                            </td>
                                            <td class="text-center text-nowrap" style="white-space:nowrap;">-</td>
                                            <td class="text-center text-nowrap" style="white-space:nowrap;">1</td>
                                            <td class="text-end fw-semibold text-nowrap" style="white-space:nowrap;"><?php echo format_naira($booking['caution_deposit']); ?></td>
                                        </tr>
                                        <tr class="ticket-total-row">
                                            <th colspan="3" class="text-end">Total Payable at Front Desk:</th>
                                            <th class="text-end text-danger fs-5 fw-bold"><?php echo format_naira($booking['total_amount'] + $booking['caution_deposit']); ?></th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Front Desk Protocol Alert -->
                        <div class="ticket-notice-box mt-4 p-3 rounded-3">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bx bx-info-circle text-danger fs-5 mt-1 flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Check-In Instructions:</strong>
                                    Please present this booking reference (<strong><?php echo htmlspecialchars($booking['booking_reference']); ?></strong>) along with a valid ID upon arrival. Payment is settled physically at reception using POS terminal, cash, or bank transfer. Reservations are held until 11:30 PM on arrival date unless reconfirmed with front desk.
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Footer Seal -->
                        <div class="ticket-footer-seal mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2 small text-muted">
                            <div>
                                <i class="bx bx-check-shield text-danger me-1"></i> Authenticated Electronic Reservation Pass
                            </div>
                            <div class="text-end">
                                Desk Concierge: <?php echo htmlspecialchars($hotel_phone); ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ══════════════════════════════════════
                     ACTION BUTTONS & HOME LINK (SCREEN ONLY - BOTTOM)
                     ══════════════════════════════════════ -->
                <div class="d-print-none mt-4 text-center">
                    <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center mb-3">
                        <button type="button" class="ticket-action-btn ticket-btn-print" onclick="window.print();">
                            <i class="bx bx-printer fs-5"></i> Print / Save as PDF
                        </button>
                        <a href="<?php echo $wa_url; ?>" target="_blank" rel="noopener" class="ticket-action-btn ticket-btn-wa">
                            <i class="bx bxl-whatsapp fs-5"></i> WhatsApp Concierge
                        </a>
                    </div>
                    <div>
                        <a href="index.php" class="voucher-home-link">
                            <i class="bx bx-arrow-back me-1"></i> Return to Homepage
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     COMPREHENSIVE STYLING & STRICT PRINT ISOLATION
     ══════════════════════════════════════════════════════ -->
<style>
/* ── SCREEN STYLES ── */
.hotel-ticket-pass {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    border: 1px solid #ebebeb;
    overflow: hidden;
    position: relative;
}
.ticket-head {
    background: #212529;
    padding: 24px 28px;
    border-bottom: 3px solid #E73539;
}
.ticket-logo {
    max-height: 52px;
    border-radius: 6px;
}
.ticket-hotel-title {
    color: #ffffff;
    font-weight: 700;
    font-size: 20px;
}
.ticket-hotel-sub {
    color: rgba(255, 255, 255, 0.65);
    font-size: 12px;
}
.ticket-badge-pay {
    background: #E73539;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    display: inline-block;
}
.ticket-ref-bar {
    background: #fdf2f2;
    padding: 20px 28px;
    border-bottom: 1px solid #f8d7da;
}
.ticket-ref-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #842029;
    text-transform: uppercase;
}
.ticket-ref-code {
    font-weight: 800;
    color: #E73539;
    letter-spacing: 2px;
    font-size: 28px;
}
.ticket-barcode-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.ticket-barcode-bars {
    display: flex;
    gap: 3px;
    height: 30px;
    align-items: flex-end;
}
.ticket-barcode-bars span {
    background: #212529;
    width: 2px;
    height: 100%;
}
.ticket-barcode-bars span:nth-child(even) {
    width: 4px;
}
.ticket-barcode-bars span:nth-child(3n) {
    width: 1px;
    height: 80%;
}
.ticket-barcode-text {
    font-family: monospace;
    font-size: 11px;
    letter-spacing: 1px;
    color: #64748b;
    margin-top: 2px;
}
.ticket-body {
    padding: 28px;
}
.ticket-sec-title {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #64748b;
    text-transform: uppercase;
    display: block;
    margin-bottom: 6px;
}
.ticket-info-card {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    height: 100%;
}
.ticket-timeline-grid {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 8px;
}
.ticket-perforated-divider {
    position: relative;
    margin: 28px -28px;
    height: 24px;
    display: flex;
    align-items: center;
}
.ticket-dash-line {
    width: 100%;
    border-top: 2px dashed #cbd5e1;
}
.ticket-notch {
    position: absolute;
    width: 24px;
    height: 24px;
    background: #f8f9fa;
    border-radius: 50%;
    z-index: 2;
}
.ticket-notch-left {
    left: -12px;
    box-shadow: inset -2px 0 3px rgba(0,0,0,0.06);
}
.ticket-notch-right {
    right: -12px;
    box-shadow: inset 2px 0 3px rgba(0,0,0,0.06);
}

/* Settlement table */
.ticket-table {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}
.ticket-table thead {
    background: #f1f5f9;
}
.ticket-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    padding: 10px 14px;
}
.ticket-table tbody td {
    padding: 12px 14px;
    font-size: 13px;
    vertical-align: middle;
}
.ticket-total-row {
    background: #fafafa;
}
.ticket-notice-box {
    background: #fff5f5;
    border: 1px solid #fecaca;
    color: #1e293b;
    border-radius: 10px;
}
.ticket-notice-box strong {
    color: #991b1b;
}

/* ── ANIMATED CONFIRMATION BANNER (TOP) ── */
.confirmation-top-card {
    background: #ffffff;
    border: 1.5px solid #fee2e2;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(231, 53, 57, 0.08);
}
.animated-check-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.animated-check-circle {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: #E73539;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 0 0 0 rgba(231, 53, 57, 0.4);
    animation: checkPulse 2s infinite cubic-bezier(0.66, 0, 0, 1);
}
@keyframes checkPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(231, 53, 57, 0.5);
        transform: scale(0.96);
    }
    70% {
        box-shadow: 0 0 0 14px rgba(231, 53, 57, 0);
        transform: scale(1);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(231, 53, 57, 0);
        transform: scale(0.96);
    }
}
.confirmation-title {
    color: #E73539;
    font-weight: 800;
    letter-spacing: 1px;
    font-size: 19px;
}
.confirmation-sub {
    color: #475569;
    font-size: 13.5px;
    font-weight: 500;
}

/* ── HARMONIOUS ACTION BUTTONS & UNDERLINED HOME LINK (BOTTOM) ── */
.ticket-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 44px;
    padding: 0 22px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none !important;
    outline: none !important;
    cursor: pointer;
    text-decoration: none !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.2s ease;
    line-height: 1;
}
.ticket-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.ticket-btn-print {
    background: #E73539 !important;
    color: #ffffff !important;
}
.ticket-btn-print:hover {
    background: #c5292c !important;
}
.ticket-btn-wa {
    background: #25D366 !important;
    color: #ffffff !important;
}
.ticket-btn-wa:hover {
    background: #1ebc57 !important;
}
.voucher-home-link {
    color: #64748b;
    font-size: 14px;
    font-weight: 600;
    text-decoration: underline !important;
    text-underline-offset: 4px;
    transition: color 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.voucher-home-link:hover {
    color: #E73539;
}

/* ══════════════════════════════════════════════════════
   STRICT 1-PAGE PRINT ISOLATION RULES
   Guarantees 1-page fit on standard A4
   ══════════════════════════════════════════════════════ */
@media print {
    /* Hide ALL body elements by default */
    body * {
        visibility: hidden !important;
    }

    /* Make ONLY #printableVoucher and its children visible */
    #printableVoucher, #printableVoucher * {
        visibility: visible !important;
    }

    /* Suppress ALL website scaffolding & actions */
    header, nav, footer,
    .topbar-area, .navbar-area, .main-nav, .mobile-nav, 
    .mean-container, .mean-bar, .mean-nav,
    .inner-banner, .footer-area, .copy-right-area,
    .back-to-top, .preloader, .d-print-none,
    #toTop, .top-btn, .dark-version, .switch-box, .theme-switcher,
    .theme-controller, .mode-toggle, label.switch, input[type="checkbox"],
    .slider, .round, button, a.default-btn, .ticket-action-btn, .voucher-home-link {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        height: 0 !important;
        width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        pointer-events: none !important;
    }

    @page {
        size: A4 portrait;
        margin: 6mm 8mm;
    }

    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .voucher-page-wrap,
    .voucher-page-wrap > .container,
    .voucher-page-wrap > .container > .row,
    .voucher-page-wrap > .container > .row > [class*="col-"] {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #printableVoucher {
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        box-shadow: none !important;
        border: 1.5px solid #212529 !important;
        border-radius: 6px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        margin: 0 auto !important;
    }

    /* Compact Ticket Head */
    .ticket-head {
        background: #212529 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        padding: 10px 18px !important;
    }
    .ticket-logo {
        max-height: 38px !important;
    }
    .ticket-hotel-title {
        font-size: 16px !important;
    }
    .ticket-hotel-sub {
        font-size: 11px !important;
    }
    .ticket-badge-pay {
        background: #E73539 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        padding: 4px 10px !important;
        font-size: 10px !important;
    }

    /* Compact Ticket Ref Bar */
    .ticket-ref-bar {
        background: #fdf2f2 !important;
        padding: 8px 18px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .ticket-ref-label {
        font-size: 9px !important;
    }
    .ticket-ref-code {
        font-size: 20px !important;
        letter-spacing: 1.5px !important;
    }
    .ticket-barcode-bars {
        height: 20px !important;
        gap: 2px !important;
    }
    .ticket-barcode-text {
        font-size: 9.5px !important;
    }

    /* Compact Ticket Body */
    .ticket-body {
        padding: 12px 18px !important;
    }
    
    /* Guest Particulars & Accommodation side-by-side in 2 columns */
    #printableVoucher .ticket-body > .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        margin: 0 -6px 10px -6px !important;
    }
    #printableVoucher .ticket-body > .row > .col-sm-6 {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
        padding: 0 6px !important;
    }
    .ticket-sec-title {
        font-size: 9.5px !important;
        margin-bottom: 3px !important;
    }
    .ticket-info-card {
        padding: 8px 12px !important;
    }
    .ticket-info-card h5 {
        font-size: 14px !important;
        margin-bottom: 2px !important;
    }
    .ticket-meta-line {
        font-size: 11px !important;
    }

    /* ── High-Impact Schedule Timeline (STRICT 3-COLUMN HORIZONTAL ROW) ── */
    .ticket-timeline-grid {
        margin: 10px 0 !important;
        padding: 10px 14px !important;
        background: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .ticket-timeline-grid .row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        justify-content: space-between !important;
        margin: 0 !important;
    }
    /* Col 1: Check-in Block (Left-Aligned) */
    .ticket-timeline-grid .row > div:nth-child(1) {
        width: 37% !important;
        max-width: 37% !important;
        flex: 0 0 37% !important;
        text-align: left !important;
        padding: 0 !important;
    }
    .ticket-timeline-grid .row > div:nth-child(1) .d-flex {
        justify-content: flex-start !important;
    }
    /* Col 2: Stay Duration & Pass Center */
    .ticket-timeline-grid .row > div:nth-child(2) {
        width: 26% !important;
        max-width: 26% !important;
        flex: 0 0 26% !important;
        text-align: center !important;
        border-left: 1px solid #cbd5e1 !important;
        border-right: 1px solid #cbd5e1 !important;
        padding: 0 6px !important;
    }
    /* Col 3: Check-out Block (Right-Aligned) */
    .ticket-timeline-grid .row > div:nth-child(3) {
        width: 37% !important;
        max-width: 37% !important;
        flex: 0 0 37% !important;
        text-align: right !important;
        padding: 0 !important;
    }
    .ticket-timeline-grid .row > div:nth-child(3) .d-flex {
        justify-content: flex-end !important;
    }

    /* Large, Bold, Highly Visible Timeline Typography */
    .ticket-date-large {
        font-size: 18px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        letter-spacing: -0.3px !important;
        line-height: 1.2 !important;
        margin-top: 4px !important;
        margin-bottom: 2px !important;
    }
    .ticket-timeline-grid small.text-muted {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #475569 !important;
    }
    .ticket-timeline-grid small.text-secondary {
        font-size: 11px !important;
        font-weight: 500 !important;
        color: #64748b !important;
    }
    .ticket-timeline-grid .badge.bg-danger {
        background: #E73539 !important;
        color: #fff !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        padding: 3px 7px !important;
        letter-spacing: 0.8px !important;
    }
    .ticket-timeline-grid .badge.bg-secondary {
        background: #64748b !important;
        color: #fff !important;
        font-size: 10px !important;
        font-weight: 800 !important;
        padding: 3px 7px !important;
        letter-spacing: 0.8px !important;
    }
    .ticket-timeline-grid .badge.bg-dark {
        background: #0f172a !important;
        color: #fff !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        padding: 4px 10px !important;
        display: inline-block !important;
    }
    .ticket-timeline-grid .badge.bg-success-subtle {
        background: #dcfce7 !important;
        color: #15803d !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        border: 1px solid #bbf7d0 !important;
        padding: 2px 7px !important;
    }

    /* Compact Perforated Line */
    .ticket-perforated-divider {
        margin: 8px 0 !important;
        height: 12px !important;
    }
    .ticket-notch {
        display: none !important;
    }

    /* Compact Billing Table */
    .ticket-table thead th {
        padding: 4px 8px !important;
        font-size: 10.5px !important;
    }
    .ticket-table tbody td {
        padding: 5px 8px !important;
        font-size: 11px !important;
    }
    .ticket-total-row th {
        padding: 6px 8px !important;
        font-size: 12px !important;
    }
    .ticket-total-row .fs-5 {
        font-size: 14px !important;
    }

    /* Compact Notice Box & Seal */
    .ticket-notice-box {
        margin-top: 8px !important;
        padding: 6px 10px !important;
        font-size: 10px !important;
        background: #fff5f5 !important;
        border: 1px solid #fecaca !important;
        color: #1e293b !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .ticket-notice-box strong {
        color: #991b1b !important;
    }
    .ticket-footer-seal {
        margin-top: 6px !important;
        padding-top: 6px !important;
        font-size: 10px !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    tr, td, th {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>

<?php require_once __DIR__ . '/include/footer.php'; ?>
