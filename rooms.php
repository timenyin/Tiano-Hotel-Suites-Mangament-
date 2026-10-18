<?php
/**
 * Tiano Hotel and Suites - Rooms & Rates
 * Replicates the exact 3-column .room-card layout from _html_backup/room.html
 */
$page_title = 'Luxury Rooms & Suites in Eket | Tiano Hotel and Suites';
$page_desc  = 'Browse our luxury rooms & suites in Eket, Akwa Ibom: Coral Room, Gold Room, Diamond Room, Premium Room, and Presidential Suite with 24/7 power, pool, and gourmet dining.';
$page_og_img = 'assets/img/room/room-img30.png';
$active_page = 'rooms';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

$categories = get_room_categories($pdo);

// Fallback images map matching _html_backup/room.html exactly
$room_image_map = [
    'coral-room'        => 'assets/img/room/room-img30.png',
    'gold-room'         => 'assets/img/room/room-img34.png',
    'diamond-room'      => 'assets/img/room/room-img31.png',
    'premium-room'      => 'assets/img/room/room-img32.png',
    'presidential-suite'=> 'assets/img/room/room-img33.png',
    'presidentalsuite'  => 'assets/img/room/room-img33.png',
];
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg9">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Rooms</li>
            </ul>
            <h3>Rooms</h3>
        </div>
    </div>
</div>

<!-- Room Area -->
<div class="room-area pt-100 pb-70">
    <div class="container">
        
        <!-- Section Title -->
        <div class="section-title text-center">
            <span class="sp-color">ROOMS</span>
            <h2>Our Rooms & Rates</h2>
        </div>
        
        <!-- Rooms Grid -->
        <div class="row pt-45">
            <?php foreach ($categories as $cat): 
                $slug = $cat['slug'];
                // Target dedicated php page or room-details.php fallback
                $target_page = file_exists(__DIR__ . '/' . $slug . '.php') ? ($slug . '.php') : ('room-details.php?slug=' . urlencode($slug));
                if ($slug === 'presidential-suite' && file_exists(__DIR__ . '/presidentalsuite.php')) {
                    $target_page = 'presidentalsuite.php';
                }
                $img = !empty($cat['featured_image']) ? $cat['featured_image'] : ($room_image_map[$slug] ?? 'assets/img/room/room-img30.png');
            ?>
                <!-- <?php echo htmlspecialchars($cat['name']); ?> -->
                <div class="col-lg-4 col-md-6">
                    <div class="room-card">
                        <a href="<?php echo htmlspecialchars($target_page); ?>" style="display: block; width: 100%; height: 250px; overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="Tiano <?php echo htmlspecialchars($cat['name']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        </a>
                        <div class="content">
                            <h3>
                                <a href="<?php echo htmlspecialchars($target_page); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                            </h3>
                            <ul>
                                <li class="text-color">&#x20A6;<?php echo number_format($cat['price_per_night']); ?></li>
                                <li class="text-color">Per Night</li>
                            </ul>
                            <div class="rating text-color">
                                <i class="bx bxs-star"></i>
                                <i class="bx bxs-star"></i>
                                <i class="bx bxs-star"></i>
                                <i class="bx bxs-star"></i>
                                <i class="bx bxs-star"></i>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <a href="<?php echo htmlspecialchars($target_page); ?>" class="default-btn btn-bg-one btn-sm flex-grow-1 text-center" style="border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                                    View Details
                                </a>
                                <a href="booking.php?category_id=<?php echo (int)$cat['id']; ?>" class="default-btn btn-bg-three btn-sm flex-grow-1 text-center" style="border-radius: 6px; padding: 8px 10px; font-size: 13px;">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
