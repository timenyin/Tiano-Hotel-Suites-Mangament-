<?php
/**
 * Tiano Hotel and Suites - Room Details
 * Exact structure from _html_backup/coral-room.html with dynamic data and working Owl Carousel
 */
require_once __DIR__ . '/include/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'coral-room';
// Normalize presidential suite aliases
if ($slug === 'presidential-suite' || $slug === 'presidentalsuite') {
    $room = get_room_category_by_slug($pdo, 'presidential-suite');
    if (!$room) {
        $room = get_room_category_by_slug($pdo, 'presidentalsuite');
    }
} else {
    $room = get_room_category_by_slug($pdo, $slug);
}

if (!$room) {
    $all = get_room_categories($pdo);
    $room = !empty($all) ? $all[0] : null;
}

if (!$room) {
    header("Location: rooms.php");
    exit;
}

$page_title = htmlspecialchars($room['name']) . ' | Tiano Hotel & Suites';
$page_desc  = htmlspecialchars($room['tagline'] ? $room['tagline'] : substr($room['description'], 0, 150));
$active_page = 'rooms';

$slider_preview = [
    'coral-room'         => 'assets/img/room/room-img30.png',
    'gold-room'          => 'assets/img/room/room-img13.jpeg',
    'diamond-room'       => 'assets/img/room/room-img15.jpeg',
    'premium-room'       => 'assets/img/room/room-img18.jpeg',
    'presidential-suite' => 'assets/img/room/room-img21.jpeg',
    'presidentalsuite'   => 'assets/img/room/room-img21.jpeg',
];
$page_og_img = $slider_preview[$room['slug']] ?? 'assets/img/og-preview.jpg';

// Structured Data for HotelRoom Google Rich Snippet
$roomPrice = (float)($room['price_per_night'] ?? 35000);
$schema_json = json_encode([
    "@context" => "https://schema.org",
    "@type" => "HotelRoom",
    "name" => $room['name'] . " at Tiano Hotel & Suites",
    "description" => $room['tagline'] ?: $room['description'],
    "image" => "https://www.tianohotel.com/" . ltrim($page_og_img, '/'),
    "bed" => [
        "@type" => "BedDetails",
        "numberOfBeds" => 1,
        "typeOfBed" => $room['bed_type'] ?? "Queen Bed"
    ],
    "occupancy" => [
        "@type" => "QuantitativeValue",
        "maxValue" => (int)($room['max_adults'] ?? 2)
    ],
    "offers" => [
        "@type" => "Offer",
        "price" => $roomPrice,
        "priceCurrency" => "NGN",
        "availability" => "https://schema.org/InStock",
        "validFrom" => date('Y-01-01')
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Sliders map matching original template photos
$room_sliders_map = [
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
        'assets/img/room/room-img38.jpeg',
    ],
    'presidentalsuite' => [
        'assets/img/room/room-img21.jpeg',
        'assets/img/room/room-img20.jpeg',
        'assets/img/room/room-img19.jpeg',
        'assets/img/room/room-img38.jpeg',
    ],
];

$matched_slug = $room['slug'];
$slider_images = $room_sliders_map[$matched_slug] ?? [
    !empty($room['featured_image']) ? $room['featured_image'] : 'assets/img/room/room-img30.png'
];

// Amenities
$amenities_list = array_filter(array_map('trim', explode(',', (string)$room['amenities'])));
if (empty($amenities_list)) {
    $amenities_list = [
        'Comfortable Bedding',
        'Free High-Speed Wi-Fi',
        'Flat-screen TV with Cable',
        'Air Conditioning',
        '24/7 Room Service',
        'Reading Desk & Chair',
        'Wardrobe',
        'Complimentary Toiletries'
    ];
}
$half = ceil(count($amenities_list) / 2);
$col1 = array_slice($amenities_list, 0, $half);
$col2 = array_slice($amenities_list, $half);

$all_categories = get_room_categories($pdo);
?>

<div class="inner-banner inner-bg9">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Rooms</li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><?php echo htmlspecialchars($room['name']); ?></li>
            </ul>
            <h3><?php echo htmlspecialchars($room['name']); ?></h3>
        </div>
    </div>
</div>

<!-- Room Details Area -->
<div class="room-details-area pt-100 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="room-details-article">
                    <div class="room-details-slider owl-carousel owl-theme">
                        <?php foreach ($slider_images as $s_img): ?>
                            <div class="room-details-item">
                                <img src="<?php echo htmlspecialchars($s_img); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="room-details-title">
                        <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                    </div>

                    <div class="room-details-content">
                        <p>
                            <?php echo nl2br(htmlspecialchars($room['description'])); ?>
                        </p>
                    </div>

                    <div class="room-details-facilities">
                        <h3>Room Amenities</h3>
                        <div class="row">
                            <div class="col-lg-6 col-md-6">
                                <ul>
                                    <?php foreach ($col1 as $item): ?>
                                        <li><i class="bx bx-check"></i> <?php echo htmlspecialchars($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="col-lg-6 col-md-6">
                                <ul>
                                    <?php foreach ($col2 as $item): ?>
                                        <li><i class="bx bx-check"></i> <?php echo htmlspecialchars($item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="room-details-side">
                    <div class="side-bar-form">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Book This Room</h3>
                            <span class="text-color" style="font-weight: bold;">&#x20A6;<?php echo number_format($room['price_per_night']); ?> / Per Night</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <p>
                                        Ready to book your stay? Contact us directly via WhatsApp or Phone for the best rates and instant confirmation.
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <a href="booking.php?category_id=<?php echo (int)$room['id']; ?>"
                                   class="default-btn btn-bg-three w-100 text-center">
                                    Book Now &mdash; &#x20A6;<?php echo number_format($room['price_per_night']); ?>/night
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="side-bar-plan">
                        <h3>Room Facilities</h3>
                        <ul>
                            <li><a href="#">Complimentary Breakfast</a></li>
                            <li><a href="#">Air Conditioning</a></li>
                            <li><a href="#">High-Speed Wi-Fi</a></li>
                            <li><a href="#">Smart TV</a></li>
                            <li><a href="#">En-suite Bathroom</a></li>
                            <li><a href="#">Toiletries</a></li>
                            <li><a href="#">Work Desk</a></li>
                        </ul>
                    </div>

                    <div class="side-bar-plan">
                        <h3>Other Rooms</h3>
                        <ul>
                            <?php foreach ($all_categories as $other): 
                                if ($other['id'] == $room['id']) continue;
                                $other_slug = $other['slug'];
                                $other_link = file_exists(__DIR__ . '/' . $other_slug . '.php') ? ($other_slug . '.php') : ('room-details.php?slug=' . urlencode($other_slug));
                                if ($other_slug === 'presidential-suite' && file_exists(__DIR__ . '/presidentalsuite.php')) {
                                    $other_link = 'presidentalsuite.php';
                                }
                            ?>
                                <li>
                                    <a href="<?php echo htmlspecialchars($other_link); ?>"><?php echo htmlspecialchars($other['name']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Room Details Area -->

<?php require_once __DIR__ . '/include/footer.php'; ?>
