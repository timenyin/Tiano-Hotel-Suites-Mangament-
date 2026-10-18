<?php
/**
 * Tiano Hotel and Suites - Shared Rooms & Rates Section
 * Displays standard rooms in 2 columns, and Presidential Suite as a full-width featured card.
 * Each room features dual action buttons: "View Details" and "Book Now".
 */
if (!isset($categories) || empty($categories)) {
    $categories = get_room_categories($pdo);
}
?>
<!-- Rooms & Rates Section -->
<div class="room-area pt-100 pb-70 section-bg" id="roomsAndRatesSection">
    <div class="container">
        
        <!-- Section Header -->
        <div class="section-title text-center mb-5">
            <span class="sp-color">TIANO SUITES ACCOMMODATION</span>
            <h2>Our Rooms & Rates</h2>
            <p style="max-width: 650px; margin: 10px auto 0; color: #6c757d;">
                Discover thoughtfully appointed rooms and aristocratic suites designed for world-class relaxation, uncompromising security, and bespoke hospitality in Eket.
            </p>
        </div>

        <div class="row pt-20 g-4">
            <?php foreach ($categories as $cat): 
                $slug = $cat['slug'];
                $is_presidential = ($slug === 'presidential-suite' || $slug === 'presidentalsuite');
                $room_link = file_exists(__DIR__ . '/../' . $slug . '.php') ? ($slug . '.php') : ('room-details.php?slug=' . urlencode($slug));
                if ($is_presidential && file_exists(__DIR__ . '/../presidentalsuite.php')) {
                    $room_link = 'presidentalsuite.php';
                }
            ?>
                <?php if ($is_presidential): ?>
                    <!-- ══════════════════════════════════════════════
                         PRESIDENTIAL SUITE: Full-Width Featured VIP Banner
                         ══════════════════════════════════════════════ -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm presidential-featured-card overflow-hidden" 
                             style="border-radius: 20px; background: #ffffff; border: 2px solid #E73539 !important;">
                            <div class="row g-0 align-items-center">
                                
                                <!-- Left Column: Suite Showcase Image -->
                                <div class="col-lg-6 col-md-5">
                                    <div class="position-relative h-100" style="min-height: 360px; overflow: hidden;">
                                        <a href="<?php echo htmlspecialchars($room_link); ?>" class="d-block h-100">
                                            <img src="<?php echo htmlspecialchars($cat['featured_image']); ?>" 
                                                 alt="Tiano <?php echo htmlspecialchars($cat['name']); ?>" 
                                                 style="width: 100%; height: 100%; object-fit: cover; min-height: 360px; transition: transform 0.4s ease;">
                                        </a>
                                        <span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 fw-bold" 
                                              style="background: #212529; color: #ffffff; font-size: 11px; letter-spacing: 1px; border-left: 3px solid #E73539; border-radius: 4px;">
                                            ★ VIP FLAGSHIP SUITE
                                        </span>
                                    </div>
                                </div>

                                <!-- Right Column: Suite Details & Specs -->
                                <div class="col-lg-6 col-md-7">
                                    <div class="p-4 p-lg-5">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                            <h3 class="mb-0 fw-bold" style="font-size: 28px;">
                                                <a href="<?php echo htmlspecialchars($room_link); ?>" style="color: #212529; text-decoration: none;">
                                                    <?php echo htmlspecialchars($cat['name']); ?>
                                                </a>
                                            </h3>
                                            <div class="text-end">
                                                <span class="fs-4 fw-bold text-danger">&#x20A6;<?php echo number_format($cat['price_per_night']); ?></span>
                                                <small class="text-muted d-block" style="font-size: 12px;">Per Night</small>
                                            </div>
                                        </div>

                                        <div class="rating mb-3">
                                            <i class="bx bxs-star text-warning"></i>
                                            <i class="bx bxs-star text-warning"></i>
                                            <i class="bx bxs-star text-warning"></i>
                                            <i class="bx bxs-star text-warning"></i>
                                            <i class="bx bxs-star text-warning"></i>
                                            <span class="badge bg-danger ms-2 px-2 py-1" style="font-size: 11px;">Royal Crown Tier</span>
                                        </div>

                                        <p class="text-muted mb-4" style="font-size: 15px; line-height: 1.7;">
                                            <?php echo htmlspecialchars($cat['description'] ? $cat['description'] : $cat['tagline']); ?>
                                        </p>

                                        <!-- Specifications Grid -->
                                        <div class="row g-2 mb-4 text-center">
                                            <div class="col-6 col-sm-3">
                                                <div class="p-2 rounded bg-light">
                                                    <i class="bx bx-user text-danger d-block fs-5"></i>
                                                    <small class="fw-semibold text-dark">1–<?php echo $cat['capacity_adults']; ?> Guests</small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="p-2 rounded bg-light">
                                                    <i class="bx bx-bed text-danger d-block fs-5"></i>
                                                    <small class="fw-semibold text-dark"><?php echo htmlspecialchars($cat['bed_type']); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="p-2 rounded bg-light">
                                                    <i class="bx bx-expand text-danger d-block fs-5"></i>
                                                    <small class="fw-semibold text-dark"><?php echo htmlspecialchars($cat['room_size']); ?></small>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-3">
                                                <div class="p-2 rounded bg-light">
                                                    <i class="bx bx-wifi text-danger d-block fs-5"></i>
                                                    <small class="fw-semibold text-dark">Fiber Wi-Fi</small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dual Action Buttons -->
                                        <div class="d-flex flex-wrap gap-3 align-items-center">
                                            <a href="<?php echo htmlspecialchars($room_link); ?>" 
                                               class="default-btn btn-bg-one px-4 py-2" 
                                               style="border-radius: 8px;">
                                                View Details
                                            </a>
                                            <a href="booking.php?category_id=<?php echo (int)$cat['id']; ?>" 
                                               class="default-btn btn-bg-three px-4 py-2" 
                                               style="border-radius: 8px;">
                                                Book Presidential Suite
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ══════════════════════════════════════════════
                         STANDARD ROOM CARDS (2 Columns)
                         ══════════════════════════════════════════════ -->
                    <div class="col-lg-6 mb-4">
                        <div class="room-card-two h-100 shadow-sm" style="border-radius: 16px; overflow: hidden; background: #fff; border: 1px solid #ebebeb;">
                            <div class="row g-0 align-items-center h-100">
                                
                                <div class="col-lg-5 col-md-5 p-0">
                                    <div class="room-card-img h-100" style="min-height: 250px;">
                                        <a href="<?php echo htmlspecialchars($room_link); ?>" class="d-block h-100">
                                            <img src="<?php echo htmlspecialchars($cat['featured_image']); ?>" 
                                                 alt="Tiano <?php echo htmlspecialchars($cat['name']); ?>"
                                                 style="width: 100%; height: 100%; object-fit: cover; min-height: 250px;">
                                        </a>
                                    </div>
                                </div>

                                <div class="col-lg-7 col-md-7 p-0">
                                    <div class="room-card-content p-4 d-flex flex-column justify-content-between h-100">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h3 class="mb-0 fs-5 fw-bold">
                                                    <a href="<?php echo htmlspecialchars($room_link); ?>">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </a>
                                                </h3>
                                            </div>
                                            <span class="text-danger fw-bold d-block mb-2 fs-6">&#x20A6;<?php echo number_format($cat['price_per_night']); ?> <small class="text-muted fw-normal">/ Per Night</small></span>
                                            
                                            <div class="rating mb-2">
                                                <i class="bx bxs-star text-warning"></i>
                                                <i class="bx bxs-star text-warning"></i>
                                                <i class="bx bxs-star text-warning"></i>
                                                <i class="bx bxs-star text-warning"></i>
                                                <i class="bx bxs-star text-warning"></i>
                                            </div>
                                            
                                            <p class="text-muted small mb-3">
                                                <?php echo htmlspecialchars($cat['tagline'] ? $cat['tagline'] : substr($cat['description'], 0, 120) . '...'); ?>
                                            </p>
                                            
                                            <ul class="list-unstyled mb-2 d-flex flex-wrap gap-2 text-muted" style="font-size: 12px;">
                                                <li class="bg-light px-2 py-1 rounded"><i class="bx bx-user me-1 text-danger"></i>1–<?php echo $cat['capacity_adults']; ?> Guests</li>
                                                <li class="bg-light px-2 py-1 rounded"><i class="bx bx-expand me-1 text-danger"></i><?php echo htmlspecialchars($cat['bed_type']); ?></li>
                                            </ul>
                                        </div>

                                        <!-- Dual Buttons: View Details & Book Now -->
                                        <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                                            <a href="<?php echo htmlspecialchars($room_link); ?>" 
                                               class="default-btn btn-bg-one px-3 py-2 flex-grow-1 text-center" 
                                               style="font-size: 13px; border-radius: 6px; padding: 8px 12px !important;">
                                                View Details
                                            </a>
                                            <a href="booking.php?category_id=<?php echo (int)$cat['id']; ?>" 
                                               class="default-btn btn-bg-three px-3 py-2 flex-grow-1 text-center" 
                                               style="font-size: 13px; border-radius: 6px; padding: 8px 12px !important;">
                                                Book Now
                                            </a>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
