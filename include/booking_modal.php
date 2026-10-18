<?php
/**
 * Tiano Hotel & Suites - Global Reservation Modal
 * Full reservation form styled to match .contact-form template design.
 * Posts to controllers/process_booking.php → redirects to voucher.php
 */
$hotel_phone    = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_whatsapp = get_setting($pdo, 'hotel_whatsapp', '2348144734612');
$clean_phone    = preg_replace('/[^0-9+]/', '', $hotel_phone);
$clean_whatsapp = preg_replace('/[^0-9]/', '', $hotel_whatsapp);
if (empty($clean_whatsapp)) {
    $clean_whatsapp = '2348144734612';
}

$caution_deposit_enabled = (get_setting($pdo, 'caution_deposit_enabled', '0') === '1');
$caution_deposit_raw     = (float)get_setting($pdo, 'caution_deposit_default', 0.00);
$caution_deposit_val     = ($caution_deposit_enabled && $caution_deposit_raw > 0) ? $caution_deposit_raw : 0.00;

// Fetch room categories for dropdown
$room_categories = get_room_categories($pdo);

// Today and tomorrow for date defaults
$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<!-- =====================================================
     BOOKING RESERVATION MODAL
     ===================================================== -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:0;">

            <!-- ── Modal Header ── -->
            <div class="modal-header border-0 p-0">
                <div class="w-100 d-flex align-items-center justify-content-between px-4 py-3"
                     style="background:#212529; border-bottom:3px solid #E73539;">
                    <div class="d-flex align-items-center gap-3">
                        <img src="assets/img/compnay_logo.JPG"
                             alt="Tiano Hotel Logo"
                             style="max-height:46px;">
                        <div>
                            <h5 class="text-white mb-0 fw-bold" id="bookingModalLabel">Make a Reservation</h5>
                            <small class="text-white-50">Zero Online Prepayment &mdash; Pay on Arrival</small>
                        </div>
                    </div>
                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                    </button>
                </div>
            </div>

            <!-- ── Modal Body ── -->
            <div class="modal-body p-0">

                <!-- Quick-contact strip (always visible) -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 px-4 py-2"
                     style="background:#fdf2f2; border-bottom:1px solid #f3c4c5;">
                    <small class="text-muted">Prefer to talk?</small>
                    <a href="tel:<?php echo htmlspecialchars($clean_phone); ?>"
                       class="text-danger fw-semibold text-decoration-none small">
                        <i class="bx bx-phone-call me-1"></i><?php echo htmlspecialchars($hotel_phone); ?>
                    </a>
                    <a href="https://wa.me/<?php echo htmlspecialchars($clean_whatsapp); ?>"
                       target="_blank"
                       class="fw-semibold text-decoration-none small"
                       style="color:#25D366;">
                        <i class="bx bxl-whatsapp me-1"></i>WhatsApp Concierge
                    </a>
                </div>

                <!-- ── Reservation Form ── -->
                <div style="padding:36px 36px 24px; background:#F5F5F5;">
                    <form action="controllers/process_booking.php"
                          method="POST"
                          id="reservationForm"
                          novalidate>

                        <!-- ─── Section 1: Stay Details ─── -->
                        <div class="mb-3">
                            <p class="text-uppercase fw-bold mb-3"
                               style="font-size:11px; letter-spacing:1.5px; color:#E73539;">
                                01 &mdash; Stay Details
                            </p>
                        </div>

                        <!-- Room Tier Dropdown -->
                        <div class="form-group mb-3">
                            <select name="category_id"
                                    id="categorySelect"
                                    class="form-control nice-select"
                                    required
                                    style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500; appearance:none; -webkit-appearance:none;">
                                <option value="" disabled selected>Select Room Tier</option>
                                <?php foreach ($room_categories as $cat): ?>
                                    <option value="<?php echo (int)$cat['id']; ?>"
                                            data-price="<?php echo (float)$cat['price_per_night']; ?>"
                                            data-adults="<?php echo (int)$cat['capacity_adults']; ?>"
                                            data-children="<?php echo (int)$cat['capacity_children']; ?>"
                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                        &mdash; ₦<?php echo number_format((float)$cat['price_per_night'], 0); ?>/night
                                        (<?php echo htmlspecialchars($cat['bed_type']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Room Info Badge (shown after selection) -->
                        <div id="roomInfoBadge" class="mb-3" style="display:none;">
                            <div class="d-flex flex-wrap gap-2 align-items-center px-3 py-2"
                                 style="background:#fff; border:1px solid #ebebeb; font-size:13px;">
                                <span class="text-muted">Nightly Rate:</span>
                                <strong id="badgeRate" class="text-danger"></strong>
                                <span class="text-muted ms-3">Est. Total:</span>
                                <strong id="badgeTotal" class="text-dark"></strong>
                                <span class="badge bg-secondary ms-3" id="badgeNights">1 night</span>
                            </div>
                        </div>

                        <!-- Dates Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"
                                           style="font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:600; color:#555; margin-bottom:6px;">
                                        Check-In Date
                                    </label>
                                    <input type="date"
                                           name="check_in_date"
                                           id="checkInDate"
                                           class="form-control"
                                           min="<?php echo $today; ?>"
                                           value="<?php echo $tomorrow; ?>"
                                           required
                                           style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"
                                           style="font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:600; color:#555; margin-bottom:6px;">
                                        Check-Out Date
                                    </label>
                                    <input type="date"
                                           name="check_out_date"
                                           id="checkOutDate"
                                           class="form-control"
                                           min="<?php echo date('Y-m-d', strtotime('+2 day')); ?>"
                                           value="<?php echo date('Y-m-d', strtotime('+2 day')); ?>"
                                           required
                                           style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500;">
                                </div>
                            </div>
                        </div>

                        <!-- Adults & Children Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"
                                           style="font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:600; color:#555; margin-bottom:6px;">
                                        Adults
                                    </label>
                                    <select name="adults"
                                            id="adultsSelect"
                                            class="form-control"
                                            required
                                            style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 16px center;">
                                        <option value="1">1 Adult</option>
                                        <option value="2" selected>2 Adults</option>
                                        <option value="3">3 Adults</option>
                                        <option value="4">4 Adults</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"
                                           style="font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:600; color:#555; margin-bottom:6px;">
                                        Children
                                    </label>
                                    <select name="children"
                                            id="childrenSelect"
                                            class="form-control"
                                            style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23555' d='M6 8L1 3h10z'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 16px center;">
                                        <option value="0" selected>0 Children</option>
                                        <option value="1">1 Child</option>
                                        <option value="2">2 Children</option>
                                        <option value="3">3 Children</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="mb-3">
                            <p class="text-uppercase fw-bold mb-3"
                               style="font-size:11px; letter-spacing:1.5px; color:#E73539; border-top:1px solid #e0e0e0; padding-top:20px;">
                                02 &mdash; Guest Information
                            </p>
                        </div>

                        <!-- Guest Name & Phone -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text"
                                           name="guest_name"
                                           id="guestName"
                                           class="form-control"
                                           placeholder="Full Name *"
                                           required
                                           maxlength="150"
                                           style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="tel"
                                           name="guest_phone"
                                           id="guestPhone"
                                           class="form-control"
                                           placeholder="Phone Number *"
                                           required
                                           maxlength="50"
                                           style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500;">
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <input type="email"
                                   name="guest_email"
                                   id="guestEmail"
                                   class="form-control"
                                   placeholder="Email Address *"
                                   required
                                   maxlength="150"
                                   style="height:50px; color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500;">
                        </div>

                        <!-- Special Requests -->
                        <div class="form-group mb-4">
                            <textarea name="special_requests"
                                      id="specialRequests"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Special requests or notes (optional)"
                                      maxlength="1000"
                                      style="color:#292323; border:1px solid #ebebeb; background:#fff; font-size:14px; padding:10px 20px; font-weight:500; resize:vertical; min-height:90px;"></textarea>
                        </div>

                        <!-- Pay-on-Arrival Policy Notice -->
                        <div class="mb-4 px-3 py-3 d-flex gap-3 align-items-start"
                             style="background:#fff; border-left:3px solid #E73539; font-size:13px;">
                            <i class="bx bx-shield-quarter text-danger mt-1" style="font-size:20px; flex-shrink:0;"></i>
                            <div>
                                <strong class="d-block mb-1" style="color:#292323;">Pay on Arrival &mdash; Zero Online Prepayment</strong>
                                <span class="text-muted">
                                    No card details required. All payments (<?php echo ($caution_deposit_val > 0) ? 'Nightly Rate + Refundable Caution Deposit' : 'Nightly Accommodation Rate'; ?>)
                                    are settled physically at our front desk via <strong>cash, POS, or bank transfer</strong> upon arrival.
                                    A confirmed digital voucher with your booking reference will be issued instantly.
                                </span>
                            </div>
                        </div>

                        <!-- Error Message Area -->
                        <div id="bookingError" class="alert alert-danger d-none mb-3 py-2" role="alert" style="font-size:13px;"></div>

                        <!-- Submit Button -->
                        <div class="form-group mb-0">
                            <button type="submit"
                                    id="submitBookingBtn"
                                    class="default-btn btn-bg-three"
                                    style="width:100%; border:0; outline:none; padding:15px 60px; font-size:15px; letter-spacing:0.5px;">
                                <span id="submitBtnText">
                                    <i class="bx bx-calendar-check me-2"></i>Confirm Reservation
                                </span>
                                <span id="submitBtnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Processing&hellip;
                                </span>
                            </button>
                        </div>

                    </form><!-- /reservationForm -->
                </div>
            </div><!-- /modal-body -->

        </div>
    </div>
</div>

<!-- =====================================================
     BOOKING MODAL INLINE JS
     ===================================================== -->
<script>
(function () {
    'use strict';

    // ── Element refs ──────────────────────────────────────
    var catSelect   = document.getElementById('categorySelect');
    var checkIn     = document.getElementById('checkInDate');
    var checkOut    = document.getElementById('checkOutDate');
    var badge       = document.getElementById('roomInfoBadge');
    var badgeRate   = document.getElementById('badgeRate');
    var badgeTotal  = document.getElementById('badgeTotal');
    var badgeNights = document.getElementById('badgeNights');
    var form        = document.getElementById('reservationForm');
    var errBox      = document.getElementById('bookingError');
    var submitBtn   = document.getElementById('submitBookingBtn');
    var btnText     = document.getElementById('submitBtnText');
    var btnLoad     = document.getElementById('submitBtnLoading');

    // ── Format naira ─────────────────────────────────────
    function naira(n) {
        return '₦' + Number(n).toLocaleString('en-NG', { maximumFractionDigits: 0 });
    }

    // ── Night calculator ──────────────────────────────────
    function calcNights() {
        var d1 = new Date(checkIn.value);
        var d2 = new Date(checkOut.value);
        if (isNaN(d1) || isNaN(d2) || d2 <= d1) return 1;
        return Math.round((d2 - d1) / 86400000);
    }

    // ── Update price badge ────────────────────────────────
    function updateBadge() {
        var opt = catSelect.options[catSelect.selectedIndex];
        if (!opt || !opt.dataset.price) { badge.style.display = 'none'; return; }
        var price  = parseFloat(opt.dataset.price);
        var nights = calcNights();
        badgeRate.textContent   = naira(price) + '/night';
        badgeTotal.textContent  = naira(price * nights);
        badgeNights.textContent = nights + ' night' + (nights !== 1 ? 's' : '');
        badge.style.display = 'block';
    }

    // ── Enforce check-out > check-in ─────────────────────
    function enforceCheckOut() {
        if (!checkIn.value) return;
        var minOut = new Date(checkIn.value);
        minOut.setDate(minOut.getDate() + 1);
        var minStr = minOut.toISOString().split('T')[0];
        checkOut.min = minStr;
        if (checkOut.value <= checkIn.value) {
            checkOut.value = minStr;
        }
        updateBadge();
    }

    // ── Event listeners ───────────────────────────────────
    catSelect.addEventListener('change', updateBadge);
    checkIn.addEventListener('change', enforceCheckOut);
    checkOut.addEventListener('change', updateBadge);

    // ── Form validation & loading state ──────────────────
    form.addEventListener('submit', function (e) {
        errBox.classList.add('d-none');
        errBox.textContent = '';

        // Basic validation
        var errors = [];
        if (!catSelect.value)                   errors.push('Please select a Room Tier.');
        if (!checkIn.value)                     errors.push('Please select a Check-In date.');
        if (!checkOut.value)                    errors.push('Please select a Check-Out date.');
        if (new Date(checkOut.value) <= new Date(checkIn.value))
                                                errors.push('Check-Out date must be after Check-In.');
        if (!document.getElementById('guestName').value.trim())
                                                errors.push('Please enter your full name.');
        if (!document.getElementById('guestPhone').value.trim())
                                                errors.push('Please enter your phone number.');
        var emailVal = document.getElementById('guestEmail').value.trim();
        if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal))
                                                errors.push('Please enter a valid email address.');

        if (errors.length) {
            e.preventDefault();
            errBox.innerHTML = errors.join('<br>');
            errBox.classList.remove('d-none');
            errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Show loading state
        btnText.classList.add('d-none');
        btnLoad.classList.remove('d-none');
        submitBtn.disabled = true;
    });

    // ── Pre-fill room tier if triggered from a room page ─
    // Usage: <a href="#" data-bs-toggle="modal" data-bs-target="#bookingModal" data-category-id="3">
    var bookingModal = document.getElementById('bookingModal');
    if (bookingModal) {
        bookingModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            if (trigger && trigger.dataset.categoryId) {
                catSelect.value = trigger.dataset.categoryId;
                updateBadge();
            }
            // Reset loading state on reopen
            btnText.classList.remove('d-none');
            btnLoad.classList.add('d-none');
            submitBtn.disabled = false;
            errBox.classList.add('d-none');
        });
    }

    // Init badge on page load if a value is pre-selected
    updateBadge();

})();
</script>
