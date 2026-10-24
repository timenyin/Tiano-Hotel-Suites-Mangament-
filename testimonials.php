<?php
/**
 * Tiano Hotel and Suites - Guest Testimonials & Verified Reviews
 * Displays authentic guest reviews dynamically from the database.
 */
require_once __DIR__ . '/include/functions.php';

$page_title = 'Guest Reviews & Testimonials | Tiano Hotel and Suites Eket';
$page_desc  = 'Read authentic reviews from corporate executives, business travelers, and families who experienced the royal luxury of Tiano Hotel and Suites in Eket, Akwa Ibom.';
$active_page = 'about';

// Fetch approved guest reviews from database
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY id ASC");
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}

// Build Schema.org AggregateRating structured data for Google Rich Snippets
$reviewCount = count($reviews);
$ratingSum = 0;
$reviewListSchema = [];

foreach ($reviews as $rev) {
    $ratingSum += (int)$rev['rating'];
    $reviewListSchema[] = [
        "@type" => "Review",
        "author" => [
            "@type" => "Person",
            "name" => $rev['guest_name']
        ],
        "reviewRating" => [
            "@type" => "Rating",
            "ratingValue" => (int)$rev['rating'],
            "bestRating" => 5
        ],
        "reviewBody" => $rev['review_text']
    ];
}

$avgRating = $reviewCount > 0 ? round($ratingSum / $reviewCount, 1) : 5.0;

$schema_json = json_encode([
    "@context" => "https://schema.org",
    "@type" => "Hotel",
    "name" => "Tiano Hotel and Suites",
    "url" => "https://tianohotelandsuites.com",
    "image" => "https://tianohotelandsuites.com/assets/img/og-preview.jpg",
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => $avgRating,
        "reviewCount" => max(1, $reviewCount),
        "bestRating" => 5,
        "worstRating" => 1
    ],
    "review" => $reviewListSchema
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg4">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Testimonials</li>
            </ul>
            <h3>Guest Testimonials</h3>
        </div>
    </div>
</div>

<!-- Testimonials Main Showcase Area -->
<div class="testimonials-area-three pt-100 pb-70">
    <div class="container">
        <div class="section-title text-center mb-5">
            <span class="sp-color">VERIFIED GUEST EXPERIENCES</span>
            <h2>What Our Valued Guests Say About Tiano Hotel</h2>
            <p class="mx-auto" style="max-width: 680px; color:#64748b;">
                From top corporate executives to international consultants and families, discover why Tiano Hotel and Suites remains the premier sanctuary in Eket.
            </p>
        </div>

        <div class="row align-items-center pt-2">
            <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
                <div class="testimonials-img-two shadow-sm rounded-4 overflow-hidden">
                    <img src="assets/img/testimonials/testimonials-img5.jpg" alt="Tiano Hotel Hospitality" class="img-fluid w-100" style="object-fit:cover; min-height:360px;">
                </div>
            </div>
            <div class="col-lg-7 col-md-12">
                <div class="testimonials-slider-area owl-carousel owl-theme">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="testimonials-slider-content p-4 rounded-4" style="background:#fff; box-shadow:0 10px 30px rgba(0,0,0,0.05); border-left:4px solid #E73539;">
                                <i class="flaticon-left-quote text-danger" style="font-size:36px;"></i>
                                <p class="fs-6 fst-italic text-dark mb-4" style="line-height:1.7;">
                                    &ldquo;<?php echo htmlspecialchars($rev['review_text']); ?>&rdquo;
                                </p>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?php echo htmlspecialchars($rev['guest_photo']); ?>" alt="<?php echo htmlspecialchars($rev['guest_name']); ?>" class="rounded-circle" style="width:56px; height:56px; object-fit:cover; border:2px solid #E73539;">
                                    <div>
                                        <h5 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($rev['guest_name']); ?></h5>
                                        <small class="text-muted"><i class="bx bx-map-pin me-1 text-danger"></i><?php echo htmlspecialchars($rev['guest_location']); ?></small>
                                        <div class="text-warning small mt-1">
                                            <?php for ($s=0; $s < (int)$rev['rating']; $s++): ?>
                                                <i class="bx bxs-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Grid Section -->
<div class="testimonials-inner-area pb-100">
    <div class="container">
        <div class="row g-4">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonials-item p-4 rounded-4 h-100 d-flex flex-column justify-content-between" style="background:#fff; box-shadow:0 8px 24px rgba(0,0,0,0.04); border-top:3px solid #E73539;">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <i class="flaticon-left-quote text-danger" style="font-size:28px;"></i>
                                    <div class="text-warning small">
                                        <?php for ($s=0; $s < (int)$rev['rating']; $s++): ?>
                                            <i class="bx bxs-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="text-secondary small mb-4" style="line-height:1.6;">
                                    &ldquo;<?php echo htmlspecialchars($rev['review_text']); ?>&rdquo;
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-3 pt-3 border-top">
                                <img src="<?php echo htmlspecialchars($rev['guest_photo']); ?>" alt="<?php echo htmlspecialchars($rev['guest_name']); ?>" class="rounded-circle" style="width:48px; height:48px; object-fit:cover;">
                                <div>
                                    <strong class="d-block text-dark small"><?php echo htmlspecialchars($rev['guest_name']); ?></strong>
                                    <small class="text-muted" style="font-size:11px;"><?php echo htmlspecialchars($rev['guest_location']); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/include/footer.php'; ?>
