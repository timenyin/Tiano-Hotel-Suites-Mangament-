<?php
/**
 * Tiano Hotel and Suites - Contact Us
 * Replicates the exact layout from _html_backup/contact.html with dynamic PHP variables
 */
$page_title = 'Contact & Concierge Desk | Tiano Hotel and Suites Eket';
$page_desc  = 'Get in touch with Tiano Hotel and Suites for bookings, corporate inquiries, and premium hospitality services in Eket, Akwa Ibom State. Call +234 814 473 4612.';
$page_og_img = 'assets/img/og-preview.jpg';
$active_page = 'contact';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

$hotel_phone   = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_email   = get_setting($pdo, 'hotel_email', 'webureabooking@weburea.com');
$hotel_address = get_setting($pdo, 'hotel_address', '44 Marina Road, Eket, Akwa Ibom State, Nigeria');
$clean_phone   = preg_replace('/[^0-9+]/', '', $hotel_phone);
?>

<div class="inner-banner inner-bg2">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Contact</li>
            </ul>
            <h3>Contact Us</h3>
        </div>
    </div>
</div>

<!-- Contact Form -->
<div class="contact-area pt-100 pb-70">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- Content & Image Column -->
            <div class="col-lg-6">
                <div class="contact-content">
                    
                    <!-- Section Title -->
                    <div class="section-title">
                        <h2>
                            Let's Start to Give Us a Message and Contact With Us
                        </h2>
                    </div>
                    
                    <!-- Contact Image -->
                    <div class="contact-img">
                       <img src="assets/img/contact/contact_services.jpg" alt="Images" style="height: 700px">
                    </div>
                    
                </div>
            </div>
            
            <!-- Contact Form Column -->
            <div class="col-lg-6">
                <div class="contact-form">
                    <form id="contactForm" method="POST" action="controllers/contact_handler.php">
                        <div class="row">

                            <!-- Name -->
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="name" id="name" class="form-control" required placeholder="Name">
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <input type="email" name="email" id="email" class="form-control" required placeholder="Email">
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="Phone">
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <input type="text" name="msg_subject" id="msg_subject" class="form-control" required placeholder="Your Subject">
                                </div>
                            </div>

                            <!-- Message -->
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <textarea name="message" id="message" class="form-control" rows="8" required placeholder="Your Message"></textarea>
                                </div>
                            </div>

                            <!-- Terms -->
                            <div class="col-lg-12">
                                <div class="form-group checkbox-option">
                                    <label class="terms-label">
                                        <input type="checkbox" id="terms" required checked>
                                        <span>
                                            Accept <a href="terms-condition.php">Terms & Conditions</a> and
                                            <a href="privacy-policy.php">Privacy Policy</a>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="col-lg-12">
                                <button type="submit" class="default-btn btn-bg-three">
                                    Send Message
                                </button>
                                <div id="msgSubmit" class="h4 text-center mt-3"></div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Contact Another -->
<div class="contact-another pb-70">
    <div class="container">
        <div class="row">
            
            <!-- Contact Information Column -->
            <div class="col-lg-6">
                <div class="contact-another-content">
                    
                    <!-- Section Title -->
                    <div class="section-title">
                        <h2>Contact Information</h2>
                        <p>
                            Get in touch with Tiano Hotel and Suites for bookings, 
                            inquiries, and premium hospitality services. We are always happy to assist you.
                        </p>
                    </div>
                    
                    <!-- Contact Details -->
                    <div class="contact-item">
                        <ul>
                            
                            <!-- Address -->
                            <li>
                                <i class="bx bx-home-alt"></i>
                                <div class="content">
                                    <span><?php echo htmlspecialchars($hotel_address); ?></span>
                                </div>
                            </li>
                            
                            <!-- Phone Numbers -->
                            <li>
                                <i class="bx bx-phone-call"></i>
                                <div class="content">
                                    <span>
                                        <a href="tel:<?php echo htmlspecialchars($clean_phone); ?>">
                                            <?php echo htmlspecialchars($hotel_phone); ?>
                                        </a>
                                    </span>
                                    <span>
                                        <a href="tel:+2348037621990">
                                            +234 803 762 1990
                                        </a>
                                    </span>
                                </div>
                            </li>
                            
                            <!-- Email Addresses -->
                            <li>
                                <i class="bx bx-envelope"></i>
                                <div class="content">
                                    <span>
                                        <a href="mailto:<?php echo htmlspecialchars($hotel_email); ?>">
                                            <?php echo htmlspecialchars($hotel_email); ?>
                                        </a>
                                    </span>
                                </div>
                            </li>
                            
                        </ul>
                    </div>
                    
                </div>
            </div>
            
            <!-- Contact Image Column -->
            <div class="col-lg-6">
                <div class="contact-another-img">
                    <img src="assets/img/contact/contact-img1.jpg" alt="Tiano Hotel and Suites Exterior">
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Map -->
<div class="map-area">
    <div class="container-fluid m-0 p-0">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d248.5470269735043!2d7.918190578687979!3d4.63819644760424!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1067dd0062b161c7%3A0x7f750331af9ebc2a!2sTiano%20Hotel%20%26%20Suites!5e0!3m2!1sen!2sng!4v1767378823872!5m2!1sen!2sng" width="800" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
