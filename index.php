<?php
/**
 * Tiano Hotel and Suites - Dynamic Homepage
 */
$page_title = 'Tiano Hotel and Suites | Luxury Accommodation in Eket, Akwa Ibom';
$page_desc  = 'Experience world-class luxury at Tiano Hotel and Suites, located at 44 Marina Road, Eket. Presidential Suites, sparkling pool, 24/7 power, and guarded security.';
$active_page = 'home';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Fetch dynamic data from database
$categories = get_room_categories($pdo);
$services   = get_services($pdo);
$faqs       = get_faqs($pdo);
$reviews    = get_approved_reviews($pdo, 6);
$hero_title = get_setting($pdo, 'hero_title', 'Tiano Hotel & Suites: Your Home Away from Home');
$hero_desc  = get_setting($pdo, 'hero_subtitle', 'Experience the perfect fusion of Akwa Ibom warmth and world-class hospitality. From our Presidential Suites to our 24/7 secured environment, Tiano Hotel is your uncompromising sanctuary in the heart of Eket.');
?>

<!-- Banner Area Three -->
<div class="banner-area-three">
    <div class="container">
        <div class="banner-content text-center banner-max">
            <h1 class="text-white mb-3" style="font-size: 48px; font-weight: 800; letter-spacing: -0.5px;">
                <span class="home_font_col">Tiano Hotel & Suites:</span> <span class="text-white">Your Home Away from Home</span>
            </h1>
            <p class="text-white mb-4" style="max-width: 860px; margin: 0 auto 30px; font-size: 18px; line-height: 1.6; text-shadow: 0 2px 4px rgba(0,0,0,0.6); font-weight: 400;">
                Experience the perfect fusion of Akwa Ibom warmth and world-class hospitality. From our Presidential Suites to our 24/7 secured environment, Tiano Hotel is your uncompromising sanctuary in the heart of Eket.
            </p>

            <!-- Modern Horizontal Booking Search Bar (Mockup Style) -->
            <div class="hero-search-wrapper text-start mt-4">
                <form action="booking.php" method="GET" class="hero-search-bar shadow-lg">
                    
                    <!-- Room Tier Selection (Custom Luxury Dropdown) -->
                    <div class="search-col room-col">
                        <div class="search-icon"><i class="bx bx-map-pin"></i></div>
                        <div class="search-content">
                            <label class="search-label">Room Selection</label>
                            <input type="hidden" name="category_id" id="hero_room_input" value="">
                            <div class="custom-select-wrap" id="roomDropdownWrap">
                                <button type="button" class="custom-select-trigger" id="roomDropdownTrigger">
                                    <span class="custom-select-text" id="roomDropdownLabel">Select Room Tier</span>
                                    <i class="bx bx-chevron-down custom-select-arrow"></i>
                                </button>
                                <div class="custom-dropdown-panel shadow-lg" id="roomDropdownMenu">
                                    <div class="custom-dropdown-item active" data-value="" data-label="Select Room Tier">
                                        <span>Select Room Tier</span>
                                        <small class="text-muted">All Available</small>
                                    </div>
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="custom-dropdown-item" 
                                             data-value="<?php echo (int)$cat['id']; ?>" 
                                             data-label="<?php echo htmlspecialchars($cat['name']); ?> &mdash; &#x20A6;<?php echo number_format($cat['price_per_night']); ?>">
                                            <div>
                                                <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                                <small class="d-block text-muted" style="font-size:11px;"><?php echo htmlspecialchars($cat['bed_type']); ?></small>
                                            </div>
                                            <span class="item-price">&#x20A6;<?php echo number_format($cat['price_per_night']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="search-divider"></div>

                    <!-- Check-in & Check-out Dates (Flatpickr Range Input) -->
                    <div class="search-col dates-col">
                        <div class="search-icon"><i class="bx bx-calendar"></i></div>
                        <div class="search-content">
                            <label for="hero_date_range" class="search-label">Check in &mdash; out</label>
                            <input type="text" 
                                   id="hero_date_range" 
                                   class="hero-flatpickr-input" 
                                   placeholder="Select Dates" 
                                   readonly>
                            <input type="hidden" name="check_in_date" id="hero_check_in" value="<?php echo date('Y-m-d'); ?>">
                            <input type="hidden" name="check_out_date" id="hero_check_out" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>

                    <div class="search-divider"></div>

                    <!-- Guests (Custom Luxury Dropdown) -->
                    <div class="search-col guests-col">
                        <div class="search-icon"><i class="bx bx-user"></i></div>
                        <div class="search-content">
                            <label class="search-label">Guests</label>
                            <input type="hidden" name="adults" id="hero_guests_input" value="2">
                            <div class="custom-select-wrap" id="guestsDropdownWrap">
                                <button type="button" class="custom-select-trigger" id="guestsDropdownTrigger">
                                    <span class="custom-select-text" id="guestsDropdownLabel">2 Guests</span>
                                    <i class="bx bx-chevron-down custom-select-arrow"></i>
                                </button>
                                <div class="custom-dropdown-panel shadow-lg" id="guestsDropdownMenu" style="min-width: 190px;">
                                    <div class="custom-dropdown-item" data-value="1" data-label="1 Guest">
                                        <span>1 Guest</span>
                                        <small class="text-muted">Solo</small>
                                    </div>
                                    <div class="custom-dropdown-item active" data-value="2" data-label="2 Guests">
                                        <span>2 Guests</span>
                                        <small class="text-muted">Couple / Pair</small>
                                    </div>
                                    <div class="custom-dropdown-item" data-value="3" data-label="3 Guests">
                                        <span>3 Guests</span>
                                        <small class="text-muted">Small Group</small>
                                    </div>
                                    <div class="custom-dropdown-item" data-value="4" data-label="4+ Guests">
                                        <span>4+ Guests</span>
                                        <small class="text-muted">Family</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit / Search Button -->
                    <div class="search-action-col">
                        <button type="submit" class="hero-search-btn" title="Search Available Rooms">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<!-- About Us Section -->
<div class="about-area pt-100 pb-70">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="about-img">
                    <img src="assets/img/thumbnail.jpg" alt="Tiano Hotel Grounds" style="border-radius:20px;">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-content">
                    <div class="section-title">
                        <span>About Tiano Hotel</span>
                        <h2>The Definition of Comfort on Marina Road</h2>
                        <p>
                            Tiano Hotel and Suites is more than just a hotel; it is a commitment to excellence. Strategically located in the heart of Eket, we offer a secure, luxury environment where international standards meet the warmth of Akwa Ibom hospitality. Whether you are visiting for oil & gas business, government affairs, or weekend leisure, we ensure your stay is flawless.
                        </p>
                    </div>
                    <ul>
                        <li>
                            <i class="flaticon-restaurant"></i>
                            <div class="content">
                                <h3>Complimentary Gourmet Breakfast</h3>
                                <p>Start your morning with a nutritious gourmet banquet featuring fresh continental and authentic Nigerian culinary creations.</p>
                            </div>
                        </li>
                        <li>
                            <i class="flaticon-wifi-signal-1"></i>
                            <div class="content">
                                <h3>High-Speed Fiber Wi-Fi</h3>
                                <p>Stay seamlessly connected with complimentary high-speed fiber internet throughout all rooms, suites, and public terraces.</p>
                            </div>
                        </li>
                    </ul>
                    <a href="about.php" class="default-btn btn-bg-one">Read More About Us</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Showcase Banner -->
<div class="banner-area-two">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="banner-another">
                    <h2>Experience Comfort, Luxury & Style at Tiano Hotel & Suites</h2>
                    <p>
                        Discover a refined stay designed for relaxation and elegance. From our luxurious rooms to premium amenities, Tiano Hotel & Suites offers a peaceful escape with modern comfort, exceptional service, and a welcoming atmosphere in the heart of Eket.
                    </p>
                    <div class="banner-btn">
                        <a href="booking.php" class="default-btn btn-bg-one">
                            Book a Room
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="banner-img_vd">
                    <video autoplay loop muted playsinline style="width:100%; height:auto; border-radius: 20px; object-fit: cover;">
                        <source src="assets/video/hotel_room.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services Section (Dynamic) -->
<div class="services-area-three pt-100 pb-70 section-bg">
    <div class="container">
        <div class="section-title text-center">
            <span class="sp-color">Our Services</span>
            <h2>Experience World-Class Service & Amenities</h2>
        </div>
        <div class="row pt-45">
            <?php foreach ($services as $srv): ?>
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="service-item-two h-100">
                        <i class="<?php echo htmlspecialchars($srv['icon_class']); ?>"></i>
                        <div class="content">
                            <h3>
                                <a href="service-details.php?slug=<?php echo urlencode($srv['slug']); ?>">
                                    <?php echo htmlspecialchars($srv['title']); ?>
                                </a>
                            </h3>
                            <p><?php echo htmlspecialchars($srv['short_description']); ?></p>
                            <a href="service-details.php?slug=<?php echo urlencode($srv['slug']); ?>" class="read-btn">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Rooms & Rates Section (Dynamic with Dual Buttons & Full-Width Presidential Suite) -->
<?php require_once __DIR__ . '/include/rooms_section.php'; ?>

<!-- Reservation Banner Carousel -->
<div class="book-area-two pt-100 pb-70">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="book-content-two">
                    <div class="section-title">
                        <span class="sp-color">MAKE A RESERVATION TODAY</span>
                        <h2>Experience World-Class Luxury – Secure Your Tiano Suite Today</h2>
                        <p>
                            Tiano Hotel and Suites sets the standard for trusted luxury and seamless service. We are dedicated to creating joyful, meaningful, and secured stays for every guest. Our team is ready to provide you with personalized support. No credit card required online — reserve your room and pay upon physical arrival in Eket.
                        </p>
                    </div>
                    <a href="booking.php" class="default-btn btn-bg-three">
                        Reserve Your Room Now
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div id="bookingCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#bookingCarousel" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#bookingCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#bookingCarousel" data-bs-slide-to="2"></button>
                    </div>
                    <div class="carousel-inner" style="border-radius: 20px; overflow: hidden;">
                        <div class="carousel-item active">
                            <img src="assets/img/bush_bar_1.jpeg" class="d-block w-100" style="height: 480px; object-fit: cover;" alt="Tiano Bush Bar">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/img/thumbnail.jpg" class="d-block w-100" style="height: 480px; object-fit: cover;" alt="Tiano Hotel Overview">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/img/pool.jpeg" class="d-block w-100" style="height: 480px; object-fit: cover;" alt="Tiano Hotel Pool">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section (Dynamic from DB) -->
<div class="faq-area pt-100 pb-70 section-bg">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="faq-content faq-content-bg2">
                    <div class="section-title">
                        <span class="sp-color">FREQUENTLY ASKED QUESTIONS</span>
                        <h2>Everything You Need to Know About Your Stay</h2>
                        <p>We are dedicated to providing a seamless experience. Here are answers to common questions about Tiano Hotel and Suites.</p>
                    </div>
                    <div class="faq-accordion">
                        <ul class="accordion">
                            <?php $f_index = 0; foreach ($faqs as $faq): $f_index++; ?>
                                <li class="accordion-item">
                                    <a class="accordion-title <?php echo ($f_index === 1) ? 'active' : ''; ?>" href="javascript:void(0)">
                                        <i class="bx bx-plus"></i>
                                        <?php echo htmlspecialchars($faq['question']); ?>
                                    </a>
                                    <div class="accordion-content <?php echo ($f_index === 1) ? 'show' : ''; ?>">
                                        <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="faq-img-3">
                    <img src="assets/img/faq/faq-img4.jpg" alt="Tiano FAQ" style="border-radius:20px;">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Setup Custom Dropdown Helper ──
    function setupCustomDropdown(wrapId, triggerId, menuId, inputId, labelId) {
        const wrap = document.getElementById(wrapId);
        const trigger = document.getElementById(triggerId);
        const menu = document.getElementById(menuId);
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);

        if (!wrap || !trigger || !menu || !input || !label) return;

        // Toggle dropdown open/closed
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = wrap.classList.contains('open');
            // Close any other open custom dropdowns
            document.querySelectorAll('.custom-select-wrap.open').forEach(el => {
                if (el !== wrap) el.classList.remove('open');
            });
            wrap.classList.toggle('open', !isOpen);
        });

        // Handle item clicks
        menu.querySelectorAll('.custom-dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const val = this.getAttribute('data-value');
                const lbl = this.getAttribute('data-label') || this.innerText.trim();

                input.value = val;
                label.innerHTML = lbl;

                menu.querySelectorAll('.custom-dropdown-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                wrap.classList.remove('open');
            });
        });
    }

    setupCustomDropdown('roomDropdownWrap', 'roomDropdownTrigger', 'roomDropdownMenu', 'hero_room_input', 'roomDropdownLabel');
    setupCustomDropdown('guestsDropdownWrap', 'guestsDropdownTrigger', 'guestsDropdownMenu', 'hero_guests_input', 'guestsDropdownLabel');

    // Close dropdowns on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select-wrap')) {
            document.querySelectorAll('.custom-select-wrap.open').forEach(el => el.classList.remove('open'));
        }
    });

    // ── Flatpickr Range Initialization ──
    if (typeof flatpickr !== 'undefined') {
        const today = new Date();
        const tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);

        const fpRange = flatpickr("#hero_date_range", {
            mode: "range",
            minDate: "today",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j M",
            rangeSeparator: " to ",
            altInputClass: "hero-flatpickr-input",
            defaultDate: [today, tomorrow],
            showMonths: 1,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length >= 1) {
                    document.getElementById('hero_check_in').value = instance.formatDate(selectedDates[0], "Y-m-d");
                }
                if (selectedDates.length >= 2) {
                    document.getElementById('hero_check_out').value = instance.formatDate(selectedDates[1], "Y-m-d");
                }
            }
        });

        const datesCol = document.querySelector('.dates-col');
        if (datesCol && fpRange) {
            datesCol.style.cursor = 'pointer';
            datesCol.addEventListener('click', function(e) {
                if (!e.target.classList.contains('hero-flatpickr-input')) {
                    fpRange.open();
                }
            });
        }
    }
});
</script>

<?php require_once __DIR__ . '/include/footer.php'; ?>
