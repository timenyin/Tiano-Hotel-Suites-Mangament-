<?php
/**
 * Tiano Hotel and Suites - Privacy Policy
 */
$page_title = 'Privacy Policy | Tiano Hotel and Suites Eket';
$page_desc  = 'Tiano Hotel and Suites privacy policy regarding personal data collection, guest reservations, and security practices.';
$active_page = 'privacy';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg1">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Privacy Policy</li>
            </ul>
            <h3>Privacy Policy</h3>
        </div>
    </div>
</div>

<div class="privacy-policy-area pt-100 pb-70">
    <div class="container">
        <div class="section-title text-center">
            <span class="sp-color">DATA TRANSPARENCY</span>
            <h2>Tiano Hotel & Suites Privacy Policy</h2>
            <p>Last updated: <?php echo date('F Y'); ?></p>
        </div>

        <div class="row pt-45">
            <div class="col-lg-12">
                <div class="single-area-content mb-4">
                    <h3>1. Information Collection</h3>
                    <p>
                        Tiano Hotel and Suites collects personal information only when you voluntarily provide it to us through our reservation engine, newsletter signup, or contact forms. This includes your name, email address, phone number, dates of stay, and special requests.
                    </p>
                    <p>
                        We do not store credit card or sensitive financial credentials online as our operations are strictly <strong>Pay on Arrival</strong> with physical settlement at our front desk in Eket.
                    </p>
                </div>

                <div class="single-area-content mb-4">
                    <h3>2. Use of Information</h3>
                    <p>
                        Your details are used solely to confirm and manage your room reservations, communicate check-in reminders, provide customer service, and comply with standard Nigerian hospitality regulations.
                    </p>
                </div>

                <div class="single-area-content mb-4">
                    <h3>3. Data Protection</h3>
                    <p>
                        We implement appropriate technical and administrative safeguards to prevent unauthorized access or disclosure of guest records. Your information is never sold, leased, or distributed to third parties.
                    </p>
                </div>

                <div class="single-area-content mb-4">
                    <h3>4. Contact Us</h3>
                    <p>
                        For any inquiries regarding your privacy or reservation data, please contact management:
                    </p>
                    <p>
                        <strong>Email:</strong> <?php echo htmlspecialchars(get_setting($pdo, 'hotel_email', 'webureabooking@weburea.com')); ?><br>
                        <strong>Phone:</strong> <?php echo htmlspecialchars(get_setting($pdo, 'hotel_phone', '+234 814 473 4612')); ?><br>
                        <strong>Address:</strong> <?php echo htmlspecialchars(get_setting($pdo, 'hotel_address', '44 Marina Road, Eket, Akwa Ibom State, Nigeria')); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
