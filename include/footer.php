<?php
/**
 * Tiano Hotel & Suites - Global Footer
 * Exact structure from _html_backup/index.html with dynamic PHP variables
 */
$hotel_phone   = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_email   = get_setting($pdo, 'hotel_email', 'webureabooking@weburea.com');
$hotel_address = get_setting($pdo, 'hotel_address', '44 Marina road, Eket.');
$fb_link       = get_setting($pdo, 'facebook_url', 'https://www.facebook.com/TianoSuites');
$ig_link       = get_setting($pdo, 'instagram_url', 'https://www.instagram.com/TianoSuites');
$clean_phone   = preg_replace('/[^0-9+]/', '', $hotel_phone);
?>

<!-- Footer -->
<footer class="footer-area footer-bg">
    <div class="container">
        <div class="footer-top pt-100 pb-70">
            <div class="row align-items-center">
                
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a href="index.php">
                                <img src="assets/img/logos/footer-logo2.svg" alt="Tiano Hotel Logo">
                            </a>
                        </div>
                        <p>
                            Tiano Hotel and Suites sets the standard for trusted luxury. We provide modern accomodations and excellent services to create memorable experiences for every guest in a secured environment.
                        </p>
                        <ul class="footer-list-contact">
                            <li>
                                <i class="bx bx-home-alt"></i>
                                <a href="#"><?php echo htmlspecialchars($hotel_address); ?></a>
                            </li>
                            <li>
                                <i class="bx bx-phone-call"></i>
                                <a href="tel:<?php echo htmlspecialchars($clean_phone); ?>"><?php echo htmlspecialchars($hotel_phone); ?></a>
                            </li>
                            <li>
                                <i class="bx bx-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($hotel_email); ?>"><?php echo htmlspecialchars($hotel_email); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget pl-5">
                        <h3>Explore</h3>
                        <ul class="footer-list">
                            <li>
                                <a href="index.php">
                                    <i class="bx bx-caret-right"></i>
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="about.php">
                                    <i class="bx bx-caret-right"></i>
                                    About Us
                                </a>
                            </li>
                            <li>
                                <a href="rooms.php">
                                    <i class="bx bx-caret-right"></i>
                                    Rooms
                                </a>
                            </li>
                            <li>
                                <a href="services.php">
                                    <i class="bx bx-caret-right"></i>
                                    Services
                                </a>
                            </li>
                            <li>
                                <a href="blog.php">
                                    <i class="bx bx-caret-right"></i>
                                    Hotel Blog
                                </a>
                            </li>
                            <li>
                                <a href="gallery.php">
                                    <i class="bx bx-caret-right"></i>
                                    Gallery
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h3>Contact & Info</h3>
                        <ul class="footer-list">
                            <li>
                                <a href="contact.php">
                                    <i class="bx bx-caret-right"></i>
                                    Contact Us
                                </a>
                            </li>
                            <li>
                                <a href="terms-condition.php">
                                    <i class="bx bx-caret-right"></i>
                                    Terms & Conditions
                                </a>
                            </li>
                            <li>
                                <a href="privacy-policy.php">
                                    <i class="bx bx-caret-right"></i>
                                    Privacy Policy
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h3>Newsletter</h3>
                        <p>
                            Subscribe to our newsletter to receive exclusive offers, latest news, and updates from Tiano Hotel directly to your inbox.
                        </p>
                        <div class="footer-form">
                            <form class="newsletter-form" data-toggle="validator" method="POST" action="controllers/newsletter_subscribe.php">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <input type="email" class="form-control" placeholder="Your Email*" name="EMAIL" required autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <button type="submit" class="default-btn btn-bg-three">
                                            Subscribe Now
                                        </button>
                                        <div id="validator-newsletter" class="form-result"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copy-right-area">
            <div class="row">
                <div class="col-lg-8 col-md-8">
                    <div class="copy-right-text text-align1">
                        <p>
                            Copyright @<?php echo date('Y'); ?> Tiano Hotel & Suites. All Rights Reserved by
                            <a href="https://www.tianohotelandsuites.com" target="_blank">Tiano Hotel & Suites</a>
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="social-icon text-align2">
                        <ul class="social-link">
                            <li>
                                <a href="<?php echo htmlspecialchars($fb_link); ?>" target="_blank"><i class="bx bxl-facebook"></i></a>
                            </li>
                            <li>
                                <a href="<?php echo htmlspecialchars($ig_link); ?>" target="_blank"><i class="bx bxl-instagram"></i></a>
                            </li>
                            <li>
                                <a href="#" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="assets/js/jquery.min.js"></script>
<!-- Bootstrap Bundle -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<!-- Magnific Popup JS -->
<script src="assets/js/jquery.magnific-popup.min.js"></script>
<!-- Owl Carousel JS -->
<script src="assets/js/owl.carousel.min.js"></script>
<!-- Nice Select JS -->
<script src="assets/js/jquery.nice-select.min.js"></script>
<!-- Wow JS -->
<script src="assets/js/wow.min.js"></script>
<!-- Jquery Ui JS -->
<script src="assets/js/jquery-ui.js"></script>
<!-- Meanmenu JS -->
<script src="assets/js/meanmenu.js"></script>
<!-- Form Ajaxchimp JS -->
<script src="assets/js/jquery.ajaxchimp.min.js"></script>
<!-- Form Validator JS -->
<script src="assets/js/form-validator.min.js"></script>
<!-- Contact Form JS -->
<script src="assets/js/contact-form-script.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Custom JS -->
<script src="assets/js/custom.js"></script>

<?php
// Global Flash Toast Notification (Session or Query Parameter)
if (session_status() === PHP_SESSION_NONE) { @session_start(); }
$flashError = $_SESSION['booking_toast_error'] ?? ($_GET['error'] ?? '');
$flashSuccess = $_SESSION['booking_toast_success'] ?? ($_GET['success'] ?? '');
unset($_SESSION['booking_toast_error'], $_SESSION['booking_toast_success']);
?>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 999999;" id="publicToastContainer">
    <?php if (!empty($flashError)): ?>
        <div id="globalFlashToastError" class="toast align-items-center text-white border-0 show shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 14px; background-color: #E73539 !important; min-width: 320px;">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3 px-3">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-error fs-4 text-white"></i>
                    </div>
                    <div>
                        <strong class="d-block text-white" style="font-size: 14px; font-weight: 700;">Reservation Alert</strong>
                        <span class="text-white-50" style="font-size: 13px; color: #ffe4e6 !important;"><?php echo htmlspecialchars($flashError); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashSuccess)): ?>
        <div id="globalFlashToastSuccess" class="toast align-items-center text-white border-0 show shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 14px; background-color: #10b981 !important; min-width: 320px;">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3 px-3">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-check fs-4 text-white"></i>
                    </div>
                    <div>
                        <strong class="d-block text-white" style="font-size: 14px; font-weight: 700;">Success</strong>
                        <span class="text-white-50" style="font-size: 13px; color: #ecfdf5 !important;"><?php echo htmlspecialchars($flashSuccess); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Bulletproof preloader dismissal
    $(window).on('load', function() {
        $('.preloader').fadeOut(250);
    });
    setTimeout(function() {
        $('.preloader').fadeOut(250);
    }, 1200);

    // Auto-initialize toasts with Bootstrap 5
    document.addEventListener('DOMContentLoaded', function () {
        var toastEls = document.querySelectorAll('#publicToastContainer .toast');
        toastEls.forEach(function (el) {
            var bsToast = new bootstrap.Toast(el, { delay: 7000, autohide: true });
            bsToast.show();
        });
    });
</script>

</body>
</html>
