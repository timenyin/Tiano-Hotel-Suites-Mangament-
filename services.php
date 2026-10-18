<?php
/**
 * Tiano Hotel and Suites - Services Directory
 * Replicates the exact structure from _html_backup/services.html
 */
$page_title = 'Services | Tiano Hotel & Suites';
$page_desc  = 'Experience world-class service & amenities: Luxurious Accommodation, Complimentary Breakfast, Wellness & Recreation, and Secured Environment in Eket.';
$active_page = 'services';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

$services = get_services($pdo);
?>

<div class="inner-banner inner-bg2">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li> Services</li>
            </ul>
            <h3> Our Services </h3>
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="services-area-three pt-100 pb-70 section-bg">
    <div class="container">
        
        <!-- Section Title -->
        <div class="section-title text-center">
            <span class="sp-color">Our Services</span>
            <h2>
                Experience World-Class Service & Amenities
            </h2>
        </div>
        
        <!-- Services Grid -->
        <div class="row pt-45">
            <?php foreach ($services as $srv): 
                $slug = $srv['slug'];
                $link = file_exists(__DIR__ . '/' . $slug . '.php') ? ($slug . '.php') : ('service-details.php?slug=' . urlencode($slug));
            ?>
                <div class="col-lg-6 col-md-6">
                    <div class="service-item-two">
                        <i class="<?php echo htmlspecialchars($srv['icon_class']); ?>"></i>
                        <div class="content">
                            <h3>
                                <a href="<?php echo htmlspecialchars($link); ?>">
                                    <?php echo htmlspecialchars($srv['title']); ?>
                                </a>
                            </h3>
                            <p>
                                <?php echo htmlspecialchars($srv['short_description']); ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($link); ?>" class="read-btn">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Rooms & Rates Section (Dual Action Buttons & Featured Presidential Suite) -->
<?php require_once __DIR__ . '/include/rooms_section.php'; ?>

<?php require_once __DIR__ . '/include/footer.php'; ?>
