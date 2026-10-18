<?php
/**
 * Tiano Hotel and Suites - Dedicated Reservation & Booking Page
 * Full-page, modern booking interface replacing the modal.
 * Posts to controllers/process_booking.php -> voucher.php
 */
require_once __DIR__ . '/include/functions.php';

$page_title = 'Reserve Your Stay | Tiano Hotel and Suites Eket';
$page_desc  = 'Reserve luxury rooms and suites at Tiano Hotel & Suites, Eket. Best rates guaranteed, zero online prepayment, pay physically at check-in.';
$active_page = 'booking';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Fetch active room categories
$room_categories = get_room_categories($pdo);

// Fallback images map
$room_image_map = [
    'coral-room'         => 'assets/img/room/room-img30.png',
    'gold-room'          => 'assets/img/room/room-img34.png',
    'diamond-room'       => 'assets/img/room/room-img31.png',
    'premium-room'       => 'assets/img/room/room-img32.png',
    'presidential-suite' => 'assets/img/room/room-img33.png',
    'presidentalsuite'   => 'assets/img/room/room-img33.png',
];

// Pre-fill parameters from GET request
$prefill_cat_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : (isset($_GET['tier']) ? (int)$_GET['tier'] : 0);
if ($prefill_cat_id === 0 && isset($_GET['slug'])) {
    $cat_by_slug = get_room_category_by_slug($pdo, trim($_GET['slug']));
    if ($cat_by_slug) {
        $prefill_cat_id = (int)$cat_by_slug['id'];
    }
}
if ($prefill_cat_id === 0 && !empty($room_categories)) {
    $prefill_cat_id = (int)$room_categories[0]['id'];
}

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$check_in_val  = !empty($_GET['check_in_date']) ? trim($_GET['check_in_date']) : (!empty($_GET['checkin']) ? trim($_GET['checkin']) : $today);
$check_out_val = !empty($_GET['check_out_date']) ? trim($_GET['check_out_date']) : (!empty($_GET['checkout']) ? trim($_GET['checkout']) : $tomorrow);

// Ensure valid dates
if (strtotime($check_in_val) < strtotime($today)) {
    $check_in_val = $today;
}
if (strtotime($check_out_val) <= strtotime($check_in_val)) {
    $check_out_val = date('Y-m-d', strtotime('+1 day', strtotime($check_in_val)));
}

$adults_val   = isset($_GET['adults']) ? max(1, (int)$_GET['adults']) : 1;
$children_val = isset($_GET['children']) ? max(0, (int)$_GET['children']) : 0;

$hotel_phone    = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_whatsapp = get_setting($pdo, 'hotel_whatsapp', '2348144734612');
$clean_phone    = preg_replace('/[^0-9+]/', '', $hotel_phone);
$clean_whatsapp = preg_replace('/[^0-9]/', '', $hotel_whatsapp);
$caution_deposit_enabled = (get_setting($pdo, 'caution_deposit_enabled', '0') === '1');
$caution_deposit_raw     = (float)get_setting($pdo, 'caution_deposit_default', 0.00);
$caution_deposit_val     = ($caution_deposit_enabled && $caution_deposit_raw > 0) ? $caution_deposit_raw : 0.00;

// Exact room slideshow images map matching room-details.php for each tier
$room_gallery_map = [
    'coral-room' => [
        'assets/img/room/room-img30.png',
        'assets/img/room/room-img10.jpeg',
        'assets/img/room/room-img36.jpeg',
    ],
    'gold-room' => [
        'assets/img/room/room-img13.jpeg',
        'assets/img/room/room-img12.jpeg',
        'assets/img/room/room-img34.png',
    ],
    'diamond-room' => [
        'assets/img/room/room-img15.jpeg',
        'assets/img/room/room-img37.jpeg',
        'assets/img/room/room-img14.jpeg',
    ],
    'premium-room' => [
        'assets/img/room/room-img18.jpeg',
        'assets/img/room/room-img17.jpeg',
        'assets/img/room/room-img16.jpeg',
    ],
    'presidential-suite' => [
        'assets/img/room/room-img21.jpeg',
        'assets/img/room/room-img20.jpeg',
        'assets/img/room/room-img19.jpeg',
    ],
    'presidentalsuite' => [
        'assets/img/room/room-img21.jpeg',
        'assets/img/room/room-img20.jpeg',
        'assets/img/room/room-img19.jpeg',
    ],
];

// Prepare JSON metadata for room categories for client-side reactivity
$categories_json = [];
$initial_slides = [];
foreach ($room_categories as $cat) {
    $slug = $cat['slug'];
    $img = !empty($cat['featured_image']) ? $cat['featured_image'] : ($room_image_map[$slug] ?? 'assets/img/room/room-img30.png');
    $gallery = $room_gallery_map[$slug] ?? [$img, $img, $img];
    if (!empty($cat['featured_image'])) {
        $gallery[0] = $cat['featured_image'];
    }
    $categories_json[$cat['id']] = [
        'id'        => (int)$cat['id'],
        'name'      => $cat['name'],
        'price'     => (float)$cat['price_per_night'],
        'formatted_price' => number_format((float)$cat['price_per_night'], 0),
        'bed'       => $cat['bed_type'],
        'size'      => $cat['room_size'],
        'adults'    => (int)$cat['capacity_adults'],
        'children'  => (int)$cat['capacity_children'],
        'image'     => $img,
        'images'    => $gallery,
        'tagline'   => $cat['tagline'] ?? ''
    ];
    if ((int)$cat['id'] === $prefill_cat_id) {
        $initial_slides = $gallery;
    }
}
if (empty($initial_slides) && !empty($categories_json)) {
    $firstKey = array_key_first($categories_json);
    $initial_slides = $categories_json[$firstKey]['images'];
}
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg10">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Reservation</li>
            </ul>
            <h3>Reserve Your Stay</h3>
        </div>
    </div>
</div>

<!-- Booking Experience Section -->
<div class="booking-section pt-100 pb-70" style="background:#f8f9fa;">
    <div class="container">
        
        <!-- Header Introduction -->
        <div class="section-title text-center mb-5">
            <span class="sp-color">RESERVATION DESK</span>
            <h2>Complete Your Booking in Minutes</h2>
            <p style="max-width: 680px; margin: 0 auto; color: #6c757d;">
                Zero upfront charges online. Select your preferred room tier, confirm your dates, and secure your reservation. Settle your payment physically upon check-in at our front desk.
            </p>
        </div>

        <form action="controllers/process_booking.php" method="POST" id="mainBookingForm" novalidate>
            <div class="row g-4">
                
                <!-- ══════════════════════════════════════
                     LEFT COLUMN: Booking Form Steps
                     ══════════════════════════════════════ -->
                <div class="col-lg-8">
                    
                    <!-- STEP 1: Accommodation Selection -->
                    <div class="booking-card mb-4">
                        <div class="booking-card-header">
                            <div class="step-num">01</div>
                            <div>
                                <h4 class="mb-0">Select Room Tier</h4>
                                <small class="text-muted">Choose your accommodation category</small>
                            </div>
                        </div>
                        <div class="booking-card-body">
                            <div class="row g-3" id="roomCardsSelector">
                                <?php foreach ($room_categories as $cat): 
                                    $isSelected = ($cat['id'] == $prefill_cat_id);
                                    $slug = $cat['slug'];
                                    $img = !empty($cat['featured_image']) ? $cat['featured_image'] : ($room_image_map[$slug] ?? 'assets/img/room/room-img30.png');
                                ?>
                                    <div class="col-md-6">
                                        <label class="room-tier-radio-card <?php echo $isSelected ? 'active' : ''; ?>" for="cat_radio_<?php echo $cat['id']; ?>">
                                            <input type="radio" 
                                                   name="category_id" 
                                                   id="cat_radio_<?php echo $cat['id']; ?>" 
                                                   value="<?php echo (int)$cat['id']; ?>" 
                                                   <?php echo $isSelected ? 'checked' : ''; ?>
                                                   class="room-radio-input"
                                                   required>
                                            <div class="room-radio-content">
                                                <div class="room-radio-img-wrap">
                                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                                                    <span class="room-radio-price">&#x20A6;<?php echo number_format($cat['price_per_night']); ?> <small>/night</small></span>
                                                </div>
                                                <div class="room-radio-details">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h5 class="room-radio-title mb-0"><?php echo htmlspecialchars($cat['name']); ?></h5>
                                                        <span class="check-indicator"><i class="bx bx-check"></i></span>
                                                    </div>
                                                    <p class="room-radio-sub mb-2 text-muted small"><?php echo htmlspecialchars($cat['tagline'] ? $cat['tagline'] : $cat['bed_type'] . ' · ' . $cat['room_size']); ?></p>
                                                    <div class="room-radio-meta">
                                                        <span><i class="bx bx-bed me-1"></i><?php echo htmlspecialchars($cat['bed_type']); ?></span>
                                                        <span><i class="bx bx-expand me-1"></i><?php echo htmlspecialchars($cat['room_size']); ?></span>
                                                        <span><i class="bx bx-user me-1"></i>Up to <?php echo $cat['capacity_adults']; ?> Adults</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Dates & Guests -->
                    <div class="booking-card mb-4">
                        <div class="booking-card-header">
                            <div class="step-num">02</div>
                            <div>
                                <h4 class="mb-0">Stay Dates & Guests</h4>
                                <small class="text-muted">Pick your arrival and departure schedule</small>
                            </div>
                        </div>
                        <div class="booking-card-body">
                            <div class="row g-3">
                                
                                <!-- Check-in Date -->
                                <div class="col-md-6">
                                    <div class="form-field-group">
                                        <label for="check_in_date" class="form-label fw-semibold">
                                            <i class="bx bx-calendar-check text-danger me-1"></i> Check-In Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="booking-date-wrapper" id="bookingCheckInWrap">
                                            <span class="booking-date-tag">ARRIVAL</span>
                                            <input type="text" 
                                                   id="check_in_date" 
                                                   name="check_in_date" 
                                                   class="booking-date-input" 
                                                   value="<?php echo htmlspecialchars($check_in_val); ?>" 
                                                   placeholder="Select Arrival Date"
                                                   required>
                                            <i class="bx bx-calendar text-danger ms-auto fs-5"></i>
                                        </div>
                                        <small class="text-muted d-block mt-1">Check-in starts from 1:00 PM</small>
                                    </div>
                                </div>

                                <!-- Check-out Date -->
                                <div class="col-md-6">
                                    <div class="form-field-group">
                                        <label for="check_out_date" class="form-label fw-semibold">
                                            <i class="bx bx-calendar-x text-danger me-1"></i> Check-Out Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="booking-date-wrapper" id="bookingCheckOutWrap">
                                            <span class="booking-date-tag">DEPARTURE</span>
                                            <input type="text" 
                                                   id="check_out_date" 
                                                   name="check_out_date" 
                                                   class="booking-date-input" 
                                                   value="<?php echo htmlspecialchars($check_out_val); ?>" 
                                                   placeholder="Select Departure Date"
                                                   required>
                                            <i class="bx bx-calendar text-danger ms-auto fs-5"></i>
                                        </div>
                                        <small class="text-muted d-block mt-1">Check-out before 12:00 PM</small>
                                    </div>
                                </div>

                                <!-- Adults -->
                                <div class="col-sm-6">
                                    <div class="form-field-group">
                                        <label for="adults" class="form-label fw-semibold">
                                            <i class="bx bx-user text-danger me-1"></i> Adults (12+ yrs) <span class="text-danger">*</span>
                                        </label>
                                        <select id="adults" name="adults" class="form-select custom-input" required>
                                            <option value="1" <?php echo ($adults_val == 1) ? 'selected' : ''; ?>>1 Adult</option>
                                            <option value="2" <?php echo ($adults_val == 2) ? 'selected' : ''; ?>>2 Adults</option>
                                            <option value="3" <?php echo ($adults_val == 3) ? 'selected' : ''; ?>>3 Adults</option>
                                            <option value="4" <?php echo ($adults_val == 4) ? 'selected' : ''; ?>>4 Adults</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Children -->
                                <div class="col-sm-6">
                                    <div class="form-field-group">
                                        <label for="children" class="form-label fw-semibold">
                                            <i class="bx bx-face text-danger me-1"></i> Children (0–11 yrs)
                                        </label>
                                        <select id="children" name="children" class="form-select custom-input">
                                            <option value="0" <?php echo ($children_val == 0) ? 'selected' : ''; ?>>0 Children</option>
                                            <option value="1" <?php echo ($children_val == 1) ? 'selected' : ''; ?>>1 Child</option>
                                            <option value="2" <?php echo ($children_val == 2) ? 'selected' : ''; ?>>2 Children</option>
                                            <option value="3" <?php echo ($children_val == 3) ? 'selected' : ''; ?>>3 Children</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- STEP 3: Guest Information -->
                    <div class="booking-card mb-4">
                        <div class="booking-card-header">
                            <div class="step-num">03</div>
                            <div>
                                <h4 class="mb-0">Primary Guest Details</h4>
                                <small class="text-muted">Voucher will be issued with these particulars</small>
                            </div>
                        </div>
                        <div class="booking-card-body">
                            <div class="row g-3">
                                
                                <div class="col-md-12">
                                    <div class="form-field-group">
                                        <label for="guest_name" class="form-label fw-semibold">
                                            Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               id="guest_name" 
                                               name="guest_name" 
                                               class="form-control custom-input" 
                                               placeholder="e.g. Chief Donald Bassey" 
                                               required>
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-field-group">
                                        <label for="guest_phone" class="form-label fw-semibold">
                                            Phone / WhatsApp Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" 
                                               id="guest_phone" 
                                               name="guest_phone" 
                                               class="form-control custom-input" 
                                               placeholder="e.g. 0801 234 5678" 
                                               required>
                                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-field-group">
                                        <label for="guest_email" class="form-label fw-semibold">
                                            Email Address <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" 
                                               id="guest_email" 
                                               name="guest_email" 
                                               class="form-control custom-input" 
                                               placeholder="e.g. guest@example.com" 
                                               required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-field-group">
                                        <label for="special_requests" class="form-label fw-semibold">
                                            Special Requests & Arrival Notes <span class="text-muted fw-normal">(Optional)</span>
                                        </label>
                                        <textarea id="special_requests" 
                                                  name="special_requests" 
                                                  class="form-control custom-input" 
                                                  rows="3" 
                                                  placeholder="e.g. Late flight arrival, quiet upper floor, airport transfer inquiry..."></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- STEP 4: Pay on Arrival Guarantee -->
                    <div class="payment-policy-box p-4 rounded-4 mb-4" style="background:#fff; border-left:5px solid #E73539; box-shadow:0 6px 20px rgba(0,0,0,0.04);">
                        <div class="d-flex align-items-start gap-3">
                            <div class="policy-icon-wrap" style="width:48px; height:48px; background:#fdf2f2; color:#E73539; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0;">
                                <i class="bx bx-shield-quarter"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1" style="color:#212529;">Pay on Arrival &mdash; Zero Online Prepayment</h5>
                                <p class="text-muted mb-2 small" style="line-height:1.6;">
                                    No credit card details required online. All payments (<?php echo ($caution_deposit_val > 0) ? 'Nightly Accommodation + Refundable Caution Deposit' : 'Nightly Room Accommodation'; ?>) are settled physically at our reception upon arrival via cash, POS card terminal, or instant bank transfer.
                                </p>
                                <div class="d-flex flex-wrap gap-3 small text-secondary">
                                    <span><i class="bx bx-check-circle text-success me-1"></i> Instant Digital Voucher</span>
                                    <span><i class="bx bx-check-circle text-success me-1"></i> Free Cancellation</span>
                                    <span><i class="bx bx-check-circle text-success me-1"></i> 24/7 Front Desk Guard</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ══════════════════════════════════════
                     RIGHT COLUMN: Live Sticky Booking Summary
                     ══════════════════════════════════════ -->
                <div class="col-lg-4">
                    <div class="booking-summary-sticky" style="position:sticky; top:100px;">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#fff;">
                            
                            <!-- Summary Header -->
                            <div class="card-header border-0 p-4 text-white" style="background:#212529; border-bottom:3px solid #E73539;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-white mb-0 fw-bold">Reservation Summary</h5>
                                    <span class="badge bg-danger px-2 py-1" style="font-size:11px;">LIVE QUOTE</span>
                                </div>
                            </div>

                            <!-- Room Preview Carousel (3 Dynamic Views with Dots) -->
                            <div class="summary-room-preview position-relative overflow-hidden" id="summaryCarouselWrap" style="height:220px; background:#111;">
                                <div class="summary-carousel-track d-flex" id="summaryCarouselTrack" style="width:300%; height:100%; transition:transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);">
                                    <img src="<?php echo htmlspecialchars($initial_slides[0] ?? 'assets/img/room/room-img30.png'); ?>" class="summary-carousel-slide" id="summarySlide0" alt="Room View 1" style="width:33.333%; height:220px; object-fit:cover; flex-shrink:0;">
                                    <img src="<?php echo htmlspecialchars($initial_slides[1] ?? 'assets/img/room/room-img10.jpeg'); ?>" class="summary-carousel-slide" id="summarySlide1" alt="Room View 2" style="width:33.333%; height:220px; object-fit:cover; flex-shrink:0;">
                                    <img src="<?php echo htmlspecialchars($initial_slides[2] ?? 'assets/img/room/room-img36.jpeg'); ?>" class="summary-carousel-slide" id="summarySlide2" alt="Room View 3" style="width:33.333%; height:220px; object-fit:cover; flex-shrink:0;">
                                </div>
                                
                                <!-- Room Name & Specs Header Overlay -->
                                <div class="summary-room-badge position-absolute top-0 start-0 w-100 p-3" style="background:linear-gradient(rgba(0,0,0,0.8), transparent); pointer-events:none; z-index:3;">
                                    <h4 class="text-white fw-bold mb-0" id="summaryRoomName" style="font-size:20px; text-shadow:0 1px 3px rgba(0,0,0,0.8);">Coral Room</h4>
                                    <small class="text-white-50" id="summaryRoomMeta" style="font-size:12px;">Queen Bed · 28 sqm</small>
                                </div>

                                <!-- 3 Dot Indicators (Interactive & Dynamic) -->
                                <div class="summary-dots-indicator position-absolute start-50 translate-middle-x d-flex gap-2" style="bottom: 12px; z-index: 5;">
                                    <span class="carousel-dot active" data-slide="0"></span>
                                    <span class="carousel-dot" data-slide="1"></span>
                                    <span class="carousel-dot" data-slide="2"></span>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                
                                <!-- Dates & Stay Recap (Upgraded Size & Hierarchy) -->
                                <div class="summary-stay-grid p-3 rounded-3 mb-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                    <div class="row g-2 text-center align-items-center">
                                        <div class="col-5 text-start">
                                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size:10px; letter-spacing:0.8px;">CHECK-IN</span>
                                            <strong class="d-block text-dark fw-bold" style="font-size:14px;" id="summaryCheckInText">--</strong>
                                            <small class="text-muted" style="font-size:11px;">From 1:00 PM</small>
                                        </div>
                                        <div class="col-2 align-self-center">
                                            <i class="bx bx-right-arrow-alt fs-2 text-danger"></i>
                                        </div>
                                        <div class="col-5 text-end">
                                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size:10px; letter-spacing:0.8px;">CHECK-OUT</span>
                                            <strong class="d-block text-dark fw-bold" style="font-size:14px;" id="summaryCheckOutText">--</strong>
                                            <small class="text-muted" style="font-size:11px;">By 12:00 PM</small>
                                        </div>
                                    </div>
                                    <hr class="my-3" style="opacity:0.12;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-semibold" style="font-size:14px;"><i class="bx bx-moon me-1 text-danger"></i>Duration:</span>
                                        <span class="badge bg-dark px-3 py-2 fw-bold text-white fs-6" id="summaryDurationBadge" style="border-radius:6px;">1 Night</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted fw-semibold" style="font-size:14px;"><i class="bx bx-user me-1 text-danger"></i>Guests:</span>
                                        <span class="fw-bold text-dark fs-6" id="summaryGuestsText">2 Adults, 0 Children</span>
                                    </div>
                                </div>

                                <!-- Financial Breakdown -->
                                <div class="summary-finance-breakdown mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:14px;">
                                        <span class="text-muted">Room Rate (<span id="calcNightsLabel">1 night</span>):</span>
                                        <span class="fw-semibold" id="calcRoomTotal">&#x20A6;35,000</span>
                                    </div>
                                    <?php if ($caution_deposit_val > 0): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2" id="calcCautionRow" style="font-size:14px;">
                                        <span class="text-muted" title="Reconciled or fully refunded upon checkout">
                                            Refundable Caution Deposit: <i class="bx bx-info-circle text-muted" style="font-size:13px;"></i>
                                        </span>
                                        <span class="fw-semibold" id="calcCautionDeposit">&#x20A6;<?php echo number_format($caution_deposit_val); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <div>
                                            <strong class="d-block fs-6" style="color:#212529;">Estimated Total</strong>
                                            <small class="text-muted" style="font-size:11px;">Payable upon arrival</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fs-4 fw-bold text-danger" id="calcGrandTotal">&#x20A6;45,000</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Action Button -->
                                <button type="submit" 
                                        id="submitBookingBtn" 
                                        class="default-btn btn-bg-three w-100 py-3 text-center fw-bold shadow-sm"
                                        style="font-size:16px; border-radius:10px; border:none; cursor:pointer;">
                                    <i class="bx bx-check-shield me-1"></i> Confirm Reservation
                                </button>

                                <p class="text-center text-muted small mt-2 mb-3" style="font-size:11px;">
                                    Instant digital voucher with official reference code generated upon submission.
                                </p>

                                <hr class="my-3">

                                <!-- Direct Concierge Assistance -->
                                <div class="concierge-help text-center p-3 rounded-3" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                    <span class="text-dark fw-bold small d-block mb-1">
                                        <i class="bx bx-support text-danger me-1"></i> Front-Desk &amp; Concierge Direct Hotline
                                    </span>
                                    <p class="text-muted small mb-2" style="font-size: 11px;">
                                        Need instant assistance or custom reservation arrangements?
                                    </p>
                                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                        <a href="tel:<?php echo htmlspecialchars($clean_phone); ?>" class="btn btn-sm btn-dark px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-1 shadow-sm" style="font-size:12.5px; border-radius:20px; text-decoration:none;" title="Click to call hotel reception">
                                            <i class="bx bxs-phone-call text-warning fs-6"></i> Call Desk Concierge (<?php echo htmlspecialchars($hotel_phone); ?>)
                                        </a>
                                        <a href="https://wa.me/<?php echo htmlspecialchars($clean_whatsapp); ?>" target="_blank" class="btn btn-sm btn-outline-success px-3 py-2 fw-bold d-inline-flex align-items-center justify-content-center gap-1" style="font-size:12.5px; border-radius:20px;" title="Chat on WhatsApp">
                                            <i class="bx bxl-whatsapp fs-6"></i> WhatsApp Reception
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</div>

<!-- Extra CSS for Booking Interface -->
<style>
.booking-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid #ebebeb;
    overflow: visible;
}
.booking-card-header {
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}

/* Luxury Flatpickr Calendar Styling - Full Rounded Floating Popover */
.flatpickr-calendar {
    background: #ffffff !important;
    border-radius: 18px !important;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.22) !important;
    border: 1.5px solid #e2e8f0 !important;
    padding: 14px 14px 18px 14px !important;
    font-family: inherit !important;
    width: 336px !important;
    max-width: 95vw !important;
    overflow: visible !important;
    z-index: 999999 !important;
}
.flatpickr-calendar:before, .flatpickr-calendar:after {
    display: none !important;
}
.flatpickr-innerContainer {
    overflow: visible !important;
    width: 100% !important;
}
.flatpickr-rContainer {
    overflow: visible !important;
    width: 100% !important;
}
.flatpickr-days {
    width: 100% !important;
    overflow: visible !important;
}
.dayContainer {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    min-height: auto !important;
    max-height: none !important;
    height: auto !important;
    padding-bottom: 8px !important;
    overflow: visible !important;
}
.flatpickr-day {
    height: 36px !important;
    line-height: 36px !important;
    max-width: 36px !important;
    margin: 2px auto !important;
    border-radius: 50% !important;
}
.booking-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 28px;
    background: #fafbfc;
    border-bottom: 1px solid #ebebeb;
}
.step-num {
    width: 40px;
    height: 40px;
    background: #212529;
    color: #ffffff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    border-left: 3px solid #E73539;
}
.booking-card-body {
    padding: 28px;
}
.custom-input {
    height: 50px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    background: #fff;
    transition: all 0.2s ease;
}
.custom-input:focus {
    border-color: #E73539;
    box-shadow: 0 0 0 3px rgba(231, 53, 57, 0.12);
    outline: none;
}
textarea.custom-input {
    height: auto;
}

/* Luxury Date Picker Inputs */
.booking-date-wrapper {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 14px;
    transition: all 0.2s ease;
    cursor: pointer;
}
.booking-date-wrapper:hover, .booking-date-wrapper:focus-within {
    border-color: #E73539;
    box-shadow: 0 0 0 3px rgba(231, 53, 57, 0.12);
}
.booking-date-tag {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #E73539;
    background: #fee2e2;
    padding: 3px 8px;
    border-radius: 6px;
    margin-right: 10px;
    flex-shrink: 0;
    line-height: 1;
}
.booking-date-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    font-size: 14.5px;
    font-weight: 600;
    color: #0f172a;
    outline: none;
    flex-grow: 1;
    width: 100%;
    cursor: pointer;
    padding: 4px 0;
}
.booking-date-input::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(28%) sepia(85%) saturate(3000%) hue-rotate(345deg) brightness(90%) contrast(95%);
    opacity: 0.85;
    transition: opacity 0.2s;
    font-size: 16px;
}
.booking-date-input::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

/* Room Tier Cards Selector */
.room-tier-radio-card {
    display: block;
    position: relative;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.25s ease;
    background: #ffffff;
    height: 100%;
}
.room-tier-radio-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.05);
}
.room-tier-radio-card.active {
    border-color: #E73539;
    box-shadow: 0 0 0 2px rgba(231, 53, 57, 0.2), 0 8px 24px rgba(231, 53, 57, 0.1);
}
.room-radio-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.room-radio-content {
    display: flex;
    flex-direction: column;
    height: 100%;
}
.room-radio-img-wrap {
    position: relative;
    width: 100%;
    height: 140px;
    overflow: hidden;
}
.room-radio-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.room-tier-radio-card:hover .room-radio-img-wrap img {
    transform: scale(1.05);
}
.room-radio-price {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(33, 37, 41, 0.88);
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 6px;
    backdrop-filter: blur(4px);
}
.room-radio-price small {
    font-weight: 400;
    opacity: 0.8;
}
.room-radio-details {
    padding: 14px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.room-radio-title {
    font-size: 16px;
    font-weight: 700;
    color: #212529;
}
.check-indicator {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}
.room-tier-radio-card.active .check-indicator {
    background: #E73539;
}
.room-radio-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11px;
    color: #64748b;
    margin-top: auto;
}
.room-radio-meta span {
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 4px;
}
.room-tier-radio-card.active .room-radio-meta span {
    background: #fdf2f2;
    color: #E73539;
}

/* 3-Dot Carousel Indicator Styles */
.carousel-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.55);
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
    border: none;
}
.carousel-dot:hover {
    background: rgba(255, 255, 255, 0.9);
}
.carousel-dot.active {
    background: #E73539;
    width: 26px;
    border-radius: 12px;
}
</style>

<!-- JavaScript Reactivity for Booking Page -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categories = <?php echo json_encode($categories_json); ?>;
    const cautionDeposit = <?php echo (float)$caution_deposit_val; ?>;
    const cautionEnabled = <?php echo ($caution_deposit_val > 0) ? 'true' : 'false'; ?>;

    const checkInInput  = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    const adultsSelect  = document.getElementById('adults');
    const childrenSelect= document.getElementById('children');

    const summaryCarouselTrack = document.getElementById('summaryCarouselTrack');
    const slide0 = document.getElementById('summarySlide0');
    const slide1 = document.getElementById('summarySlide1');
    const slide2 = document.getElementById('summarySlide2');
    const dots   = document.querySelectorAll('.carousel-dot');

    const summaryTitle     = document.getElementById('summaryRoomName');
    const summaryMeta      = document.getElementById('summaryRoomMeta');
    const summaryCheckIn   = document.getElementById('summaryCheckInText');
    const summaryCheckOut  = document.getElementById('summaryCheckOutText');
    const summaryDuration  = document.getElementById('summaryDurationBadge');
    const summaryGuests    = document.getElementById('summaryGuestsText');
    const calcNightsLabel  = document.getElementById('calcNightsLabel');
    const calcRoomTotal    = document.getElementById('calcRoomTotal');
    const calcGrandTotal   = document.getElementById('calcGrandTotal');

    const radioCards  = document.querySelectorAll('.room-tier-radio-card');
    const radioInputs = document.querySelectorAll('.room-radio-input');

    let currentSlide = 0;
    let carouselTimer = null;

    function formatNaira(num) {
        return '₦' + Math.round(num).toLocaleString('en-US');
    }

    function getSelectedCategoryId() {
        const checked = document.querySelector('.room-radio-input:checked');
        return checked ? parseInt(checked.value, 10) : 0;
    }

    function goToSlide(idx) {
        currentSlide = (idx + 3) % 3;
        if (summaryCarouselTrack) {
            summaryCarouselTrack.style.transform = 'translateX(-' + (currentSlide * 33.3333) + '%)';
        }
        dots.forEach((d, i) => {
            if (i === currentSlide) {
                d.classList.add('active');
            } else {
                d.classList.remove('active');
            }
        });
    }

    function startAutoCarousel() {
        if (carouselTimer) clearInterval(carouselTimer);
        carouselTimer = setInterval(() => {
            goToSlide(currentSlide + 1);
        }, 3500);
    }

    // Dot click listeners
    dots.forEach(dot => {
        dot.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const slideIdx = parseInt(this.getAttribute('data-slide'), 10);
            goToSlide(slideIdx);
            startAutoCarousel();
        });
    });

    function updateCalculations() {
        const catId = getSelectedCategoryId();
        const cat = categories[catId];

        if (!cat) return;

        // Update Room visual cards state
        radioCards.forEach(card => {
            const input = card.querySelector('.room-radio-input');
            if (input && input.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });

        // Update 3 Carousel Images
        if (cat.images && cat.images.length >= 3) {
            slide0.src = cat.images[0];
            slide1.src = cat.images[1];
            slide2.src = cat.images[2];
        } else {
            slide0.src = cat.image;
            slide1.src = cat.image;
            slide2.src = cat.image;
        }
        goToSlide(0);
        startAutoCarousel();

        // Update Titles
        summaryTitle.textContent = cat.name;
        summaryMeta.textContent = cat.bed + ' · ' + cat.size;

        // Date calculations
        const inVal  = checkInInput.value;
        const outVal = checkOutInput.value;

        let nights = 1;
        if (inVal && outVal) {
            const dIn  = new Date(inVal);
            const dOut = new Date(outVal);
            const diffTime = dOut - dIn;
            nights = Math.round(diffTime / (1000 * 60 * 60 * 24));
            if (nights < 1) {
                nights = 1;
            }
        }

        // Format dates for display
        if (inVal) {
            const d = new Date(inVal);
            summaryCheckIn.textContent = d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        }
        if (outVal) {
            const d = new Date(outVal);
            summaryCheckOut.textContent = d.toLocaleDateString('en-US', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
        }

        summaryDuration.textContent = nights + (nights === 1 ? ' Night' : ' Nights');
        calcNightsLabel.textContent = nights + (nights === 1 ? ' night' : ' nights');

        // Guests display
        const adults = adultsSelect.value;
        const children = childrenSelect.value;
        summaryGuests.textContent = adults + (adults == 1 ? ' Adult' : ' Adults') + ', ' + children + (children == 1 ? ' Child' : ' Children');

        // Finance calculations
        const roomTotal = cat.price * nights;
        const grandTotal = roomTotal + (cautionEnabled ? cautionDeposit : 0);

        calcRoomTotal.textContent = formatNaira(roomTotal);
        calcGrandTotal.textContent = formatNaira(grandTotal);

        const cautionRow = document.getElementById('calcCautionRow');
        if (cautionRow) {
            cautionRow.style.setProperty('display', (cautionEnabled && cautionDeposit > 0) ? 'flex' : 'none', 'important');
        }
    }

    // Radio change listeners
    radioInputs.forEach(input => {
        input.addEventListener('change', updateCalculations);
    });

    // Flatpickr instances for Check-In and Check-Out
    let fpCheckIn = null;
    let fpCheckOut = null;

    function formatYMD(d) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    if (typeof flatpickr !== 'undefined') {
        fpCheckIn = flatpickr(checkInInput, {
            minDate: "today",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "D, j M Y",
            altInputClass: "booking-date-input",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const nextDay = new Date(selectedDates[0]);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const nextDayStr = formatYMD(nextDay);

                    if (fpCheckOut) {
                        fpCheckOut.set('minDate', nextDayStr);
                        // Auto-adjust Check-Out date if empty or before/on check-in date
                        if (fpCheckOut.selectedDates.length === 0 || fpCheckOut.selectedDates[0] <= selectedDates[0]) {
                            fpCheckOut.setDate(nextDayStr, true);
                        }
                        fpCheckOut.jumpToDate(nextDayStr);
                    }
                }
                updateCalculations();
            }
        });

        // Determine initial check-in date
        let initialInDate = new Date();
        if (fpCheckIn && fpCheckIn.selectedDates.length > 0) {
            initialInDate = fpCheckIn.selectedDates[0];
        } else if (checkInInput.value) {
            const parsed = new Date(checkInInput.value);
            if (!isNaN(parsed.getTime())) initialInDate = parsed;
        }

        const initialNextDay = new Date(initialInDate);
        initialNextDay.setDate(initialNextDay.getDate() + 1);
        const initialMinOutStr = formatYMD(initialNextDay);

        fpCheckOut = flatpickr(checkOutInput, {
            minDate: initialMinOutStr,
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "D, j M Y",
            altInputClass: "booking-date-input",
            onOpen: function(selectedDates, dateStr, instance) {
                if (fpCheckIn && fpCheckIn.selectedDates.length > 0) {
                    const nextDay = new Date(fpCheckIn.selectedDates[0]);
                    nextDay.setDate(nextDay.getDate() + 1);
                    const nextDayStr = formatYMD(nextDay);
                    instance.set('minDate', nextDayStr);
                    if (instance.selectedDates.length === 0) {
                        instance.jumpToDate(nextDayStr);
                    }
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                updateCalculations();
            }
        });

        // If checkout is empty on initial load, auto-set to day after check-in
        if (checkInInput.value && !checkOutInput.value && fpCheckOut) {
            fpCheckOut.setDate(initialMinOutStr, true);
        }

        // Wrapper click helpers
        const wrapIn = document.getElementById('bookingCheckInWrap');
        const wrapOut = document.getElementById('bookingCheckOutWrap');
        if (wrapIn) {
            wrapIn.addEventListener('click', function(e) {
                if (fpCheckIn) fpCheckIn.open();
            });
        }
        if (wrapOut) {
            wrapOut.addEventListener('click', function(e) {
                if (fpCheckOut) fpCheckOut.open();
            });
        }
    } else {
        // Fallback for native inputs
        checkInInput.addEventListener('change', updateCalculations);
        checkOutInput.addEventListener('change', updateCalculations);
    }

    adultsSelect.addEventListener('change', updateCalculations);
    childrenSelect.addEventListener('change', updateCalculations);

    // Form submission processing state with circular spinner
    const bookingForm = document.getElementById('mainBookingForm');
    const submitBtn = document.getElementById('submitBookingBtn');

    if (bookingForm && submitBtn) {
        bookingForm.addEventListener('submit', function (e) {
            const guestName = document.getElementById('guest_name');
            const guestEmail = document.getElementById('guest_email');
            const guestPhone = document.getElementById('guest_phone');

            let hasEmpty = false;
            [guestName, guestEmail, guestPhone].forEach(function(field) {
                if (field && !field.value.trim()) {
                    field.style.borderColor = '#E73539';
                    hasEmpty = true;
                } else if (field) {
                    field.style.borderColor = '';
                }
            });

            if (hasEmpty) {
                if (window.showToast) {
                    window.showToast('Missing Details', 'Please enter your Name, Email, and Phone Number.', 'danger');
                }
                return;
            }

            // Display animated circular loading spinner
            submitBtn.disabled = true;
            submitBtn.style.pointerEvents = 'none';
            submitBtn.style.opacity = '0.92';
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="width: 1.25rem; height: 1.25rem; border-width: 2.5px; vertical-align: -0.2em;"></span>
                <span>Processing Reservation...</span>
            `;
        });
    }

    // Initial run
    updateCalculations();
});
</script>

<?php require_once __DIR__ . '/include/footer.php'; ?>
