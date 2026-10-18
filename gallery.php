<?php
/**
 * Tiano Hotel and Suites - Photo Gallery
 * Exact 4-tab structure from _html_backup/gallery.html
 */
$page_title = 'Photo Gallery | Tiano Hotel and Suites Eket';
$page_desc  = 'Explore our hotel photo gallery: luxury rooms, exquisite dining, sparkling pool, and tranquil outdoor recreation at 44 Marina Road, Eket.';
$page_og_img = 'assets/img/gallery/gallery-img1.jpg';
$active_page = 'gallery';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Fetch gallery items from database
$all_gallery_items = get_gallery_items($pdo, 'all', 100);

// Filter by categories
$rooms_items   = array_filter($all_gallery_items, fn($i) => $i['category_filter'] === 'rooms');
$dining_items  = array_filter($all_gallery_items, fn($i) => in_array($i['category_filter'], ['dining', 'restaurant']));
$outdoor_items = array_filter($all_gallery_items, fn($i) => in_array($i['category_filter'], ['outdoor', 'facilities', 'pool', 'spa']));
?>

<div class="inner-banner inner-bg6">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Gallery</li>
            </ul>
            <h3>Gallery</h3>
        </div>
    </div>
</div>

<div class="gallery-area pt-100 pb-70">
    <div class="container">
        
        <!-- Gallery Tabs -->
        <div class="tab gallery-tab">
            
            <!-- Tab Navigation -->
            <ul class="tabs text-center">
                <li>
                    <a href="#">All Gallery</a>
                </li>
                <li>
                    <a href="#">Rooms & Suites</a>
                </li>
                <li>
                    <a href="#">Restaurant & Dining</a>
                </li>
                <li>
                    <a href="#">Outdoor Recreation </a>
                </li>
            </ul>
            
<?php
// Reusable media renderer for photos and soundless endless loop videos
if (!function_exists('render_gallery_media_card')) {
    function render_gallery_media_card($item) {
        $isVideo = (($item['media_type'] ?? 'image') === 'video' || preg_match('/\.(mp4|webm|ogv|mov)$/i', $item['full_url']));
        ?>
        <div class="col-lg-4 col-sm-6 mb-4">
            <div class="single-gallery position-relative" style="border-radius: 12px; overflow: hidden; height: 280px; background: #0f172a; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
                <?php if ($isVideo): ?>
                    <!-- Endless, soundless looping video -->
                    <video autoplay muted loop playsinline class="w-100 h-100" style="object-fit: cover; display: block;" preload="auto">
                        <source src="<?php echo htmlspecialchars($item['full_url']); ?>" type="video/mp4">
                        Your browser does not support HTML5 video.
                    </video>
                    <span class="badge position-absolute top-0 end-0 m-3 text-white px-2 py-1 shadow-sm d-flex align-items-center gap-1" style="background: rgba(231, 53, 57, 0.95); font-size: 11px; z-index: 2;">
                        <i class="bx bx-play-circle"></i> VIDEO
                    </span>
                    <span class="badge position-absolute bottom-0 start-0 m-3 text-white px-2 py-1 shadow-sm" style="background: rgba(15, 23, 42, 0.8); font-size: 11px; z-index: 2; max-width: 85%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </span>
                <?php else: ?>
                    <img src="<?php echo htmlspecialchars($item['thumb_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <a href="<?php echo htmlspecialchars($item['full_url']); ?>" class="gallery-icon" title="<?php echo htmlspecialchars($item['title']); ?>">
                        <i class="bx bx-plus"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
?>

            <!-- Tab Content -->
            <div class="tab_content current active pt-45">

                <!-- Tab 1: All Gallery -->
                <div class="tabs_item current">
                    <div class="gallery-tab-item">
                        <div class="gallery-view">
                            <div class="row">
                                <?php foreach ($all_gallery_items as $item): ?>
                                    <?php render_gallery_media_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 2: Rooms & Suites -->
                <div class="tabs_item">
                    <div class="gallery-tab-item">
                        <div class="gallery-view">
                            <div class="row">
                                <?php foreach ($rooms_items as $item): ?>
                                    <?php render_gallery_media_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Restaurant & Dining -->
                <div class="tabs_item">
                    <div class="gallery-tab-item">
                        <div class="gallery-view">
                            <div class="row">
                                <?php foreach ($dining_items as $item): ?>
                                    <?php render_gallery_media_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Outdoor Recreation -->
                <div class="tabs_item">
                    <div class="gallery-tab-item">
                        <div class="gallery-view">
                            <div class="row">
                                <?php foreach ($outdoor_items as $item): ?>
                                    <?php render_gallery_media_card($item); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
