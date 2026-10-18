<?php
/**
 * Tiano Hotel and Suites - Dynamic Service Details
 * Exact structure from _html_backup/luxurious_comfortable_accommodation.html
 */
require_once __DIR__ . '/include/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'luxurious_comfortable_accommodation';

// Service image mapping to guarantee exact original assets
$service_image_map = [
    'luxurious_comfortable_accommodation'   => 'assets/img/Comfortable Accommodation.jpg',
    'complimentary_gourmet_breakfast'        => 'assets/img/breakfast-img6.jpg',
    'modern_wellness_recreation_facilities' => 'assets/img/modren wellness.jpg',
    'secured_environment'                   => 'assets/img/Seamless.jpg',
];

$stmt = $pdo->prepare("SELECT * FROM services WHERE slug = ? LIMIT 1");
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    $all = get_services($pdo);
    $service = !empty($all) ? $all[0] : null;
}

if (!$service) {
    header("Location: services.php");
    exit;
}

$service_img = !empty($service['featured_image']) ? $service['featured_image'] : ($service_image_map[$service['slug']] ?? 'assets/img/Comfortable Accommodation.jpg');
$page_og_img = $service_img;

$page_title = htmlspecialchars($service['title']) . ' | Tiano Hotel & Suites';
$page_desc  = htmlspecialchars($service['short_description']);
$active_page = 'services';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

$all_services = get_services($pdo);

// Content & facilities by service
$facilities_map = [
    'luxurious_comfortable_accommodation' => [
        'title' => 'Room Features & Amenities',
        'desc' => 'Our rooms combine style, functionality, and privacy, offering guests everything needed for a stress-free and enjoyable experience throughout their stay.',
        'items' => [
            'Spacious & well-furnished rooms',
            'Comfortable beds with premium linens',
            'Air-conditioning & modern lighting',
            'High-speed Wi-Fi access',
            'Ensuite bathrooms with quality fittings',
            'Daily housekeeping & room service'
        ],
        'quote' => 'Experience refined comfort, seamless service, and a welcoming atmosphere designed to make every stay exceptional.'
    ],
    'complimentary_gourmet_breakfast' => [
        'title' => 'Breakfast Highlights & Dining Experience',
        'desc' => 'We prepare each morning selection with wholesome ingredients to energize your business day or leisure vacation in Eket.',
        'items' => [
            'Freshly prepared continental and traditional Nigerian options',
            'Fresh fruits, juices, tea, and brewed coffee',
            'Made-to-order eggs and hot buffet selections',
            'Served daily from 6:30 AM to 10:00 AM',
            'Hygienic and elegant dining atmosphere',
            'Dedicated breakfast staff & prompt service'
        ],
        'quote' => 'Start every morning energized with fresh, mouthwatering gourmet breakfast served with warmth.'
    ],
    'modern_wellness_recreation_facilities' => [
        'title' => 'Wellness & Leisure Amenities',
        'desc' => 'Enjoy an enriching blend of physical fitness, rejuvenating spa therapy, and refreshing open-air cabanas during your stay.',
        'items' => [
            'Sparkling outdoor swimming pool & poolside loungers',
            'Fully-equipped modern fitness center',
            'Therapeutic spa & massage treatment rooms',
            'Open-air bush bar with evening live entertainment',
            'Poolside refreshments and mocktail bar',
            'Professional wellness and massage attendants'
        ],
        'quote' => 'Rejuvenate your body, calm your mind, and experience uncompromised leisure at our poolside and wellness center.'
    ],
    'secured_environment' => [
        'title' => 'Security & Reliability Measures',
        'desc' => 'Your security and uninterrupted comfort are our highest operational priorities at 44 Marina Road, Eket.',
        'items' => [
            '24/7 armed security personnel & manned gate checkpoint',
            'Comprehensive CCTV perimeter monitoring',
            'Uninterrupted 24/7 dual generator power supply',
            'Dedicated and secure guest vehicle parking',
            'High-speed enterprise fiber Wi-Fi throughout grounds',
            'Emergency response protocols and fire safety systems'
        ],
        'quote' => 'Peace of mind is true luxury. Enjoy 24/7 power, fast Wi-Fi, and total security throughout your stay.'
    ]
];

$db_items = [];
if (!empty($service['facilities_items'])) {
    $db_items = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $service['facilities_items']))));
}

$default_map = $facilities_map[$service['slug']] ?? [
    'title' => 'Service Features & Highlights',
    'desc'  => 'Experience seamless quality and premium service tailored to your stay.',
    'items' => ['Dedicated staff service', 'Premium quality amenities', '24/7 front desk support'],
    'quote' => 'Delivering hospitality excellence at 44 Marina Road, Eket.'
];

$matched_data = [
    'title' => !empty($service['facilities_title']) ? $service['facilities_title'] : $default_map['title'],
    'desc'  => !empty($service['facilities_desc']) ? $service['facilities_desc'] : $default_map['desc'],
    'items' => !empty($db_items) ? $db_items : $default_map['items'],
    'quote' => !empty($service['quote_text']) ? $service['quote_text'] : $default_map['quote']
];
?>

<div class="inner-banner inner-bg6">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="services.php">Services</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><?php echo htmlspecialchars($service['title']); ?></li>
            </ul>
            <h3><?php echo htmlspecialchars($service['title']); ?></h3>
        </div>
    </div>
</div>

<!-- Service Details Area -->
<div class="service-details-area pt-100 pb-70">
    <div class="container">
        <div class="row">

            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="service-article">

                    <!-- Image -->
                    <div class="service-article-img">
                        <img src="<?php echo htmlspecialchars($service_img); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" style="width:100%; border-radius:12px; max-height:450px; object-fit:cover;">
                    </div>

                    <!-- Title -->
                    <div class="service-article-title mt-4">
                        <h2 style="font-weight:800; color:#212529;"><?php echo htmlspecialchars($service['title']); ?></h2>
                    </div>

                    <!-- Content -->
                    <div class="service-article-content">
                        <p style="font-size:16px; line-height:1.8; color:#475569;">
                            <?php echo nl2br(htmlspecialchars($service['full_content'] ? $service['full_content'] : $service['short_description'])); ?>
                        </p>

                        <?php if (!empty($matched_data['quote'])): ?>
                            <blockquote class="blockquote" style="border-left:4px solid #E73539; padding:18px 24px; background:#fff5f5; border-radius:0 10px 10px 0;">
                                <p style="font-style:italic; font-weight:600; color:#1e293b; margin:0;">
                                    &ldquo;<?php echo htmlspecialchars($matched_data['quote']); ?>&rdquo;
                                </p>
                            </blockquote>
                        <?php endif; ?>
                    </div>

                    <!-- Facilities -->
                    <?php if (!empty($matched_data['items'])): ?>
                        <div class="service-facility-content mt-4 pt-2">
                            <h3 style="font-weight:700; color:#212529; font-size:22px;"><?php echo htmlspecialchars($matched_data['title']); ?></h3>
                            <?php if (!empty($matched_data['desc'])): ?>
                                <p style="color:#64748b; line-height:1.7;"><?php echo htmlspecialchars($matched_data['desc']); ?></p>
                            <?php endif; ?>

                            <ul class="row g-3 list-unstyled mt-2">
                                <?php foreach ($matched_data['items'] as $item): ?>
                                    <li class="col-md-6 col-12 d-flex align-items-center gap-2" style="font-size:15px; color:#334155;">
                                        <i class="bx bxs-check-circle text-danger fs-5 flex-shrink-0"></i>
                                        <span><?php echo htmlspecialchars($item); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="service-side-bar">

                    <!-- Concierge & Inquiry Card -->
                    <div class="services-bar-widget mb-4 p-4 rounded-3 text-center" style="background:#f8fafc; border:1px solid #e2e8f0;">
                        <div class="mb-3 d-inline-flex p-3 rounded-circle" style="background:#fee2e2; color:#E73539;">
                            <i class="bx bx-support fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-2 text-dark">Bespoke Inquiry?</h4>
                        <p class="small text-muted mb-4">Have special dietary, wellness, or executive accommodation requests? Our 24/7 concierge is standing by.</p>
                        <div class="d-grid gap-2">
                            <a href="https://wa.me/2348144734612?text=<?php echo urlencode('Hello Tiano Hotel, I would like to inquire about: ' . $service['title']); ?>" target="_blank" class="btn btn-success fw-bold py-2 d-flex align-items-center justify-content-center gap-2" style="background:#25D366; border:none; border-radius:8px;">
                                <i class="bx bxl-whatsapp fs-5"></i> Chat on WhatsApp
                            </a>
                            <a href="booking.php" class="btn btn-danger fw-bold py-2" style="background:#E73539; border-radius:8px;">
                                <i class="bx bx-calendar-check me-1"></i> Reserve Your Stay
                            </a>
                        </div>
                    </div>

                    <div class="services-bar-widget">
                        <h3 class="title">Other Services</h3>
                        <div class="side-bar-categories">
                            <ul>
                                <?php foreach ($all_services as $s): 
                                    if ($s['id'] == $service['id']) continue;
                                    $s_slug = $s['slug'];
                                    $s_link = file_exists(__DIR__ . '/' . $s_slug . '.php') ? ($s_slug . '.php') : ('service-details.php?slug=' . urlencode($s_slug));
                                ?>
                                    <li>
                                        <a href="<?php echo htmlspecialchars($s_link); ?>" class="d-flex align-items-center justify-content-between">
                                            <span><?php echo htmlspecialchars($s['title']); ?></span>
                                            <i class="bx bx-chevron-right text-muted"></i>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Service Details Area -->

<?php require_once __DIR__ . '/include/footer.php'; ?>
