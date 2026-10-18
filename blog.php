<?php
/**
 * Tiano Hotel & Suites - Official Hotel & Travel Blog
 * High-ranking SEO, rich article layout, fully responsive.
 */
$page_title     = 'Hotel Blog & Travel Guides | Tiano Hotel & Suites, Eket';
$page_desc      = 'Discover insider travel guides, hospitality updates, culinary stories, and luxury living in Eket, Akwa Ibom State from the Tiano Hotel editorial team.';
$page_canonical = 'https://www.tianohotel.com/blog.php';
$page_og_img    = 'assets/img/blog/blog-item-img1.jpg';
$active_page    = 'blog';

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Fetch all published blog posts
try {
    $stmt = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY id DESC");
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    $posts = [];
}
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg1" style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.65)), url('assets/img/hotel_build_three.jpg'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="inner-title text-center">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Hotel Blog</li>
            </ul>
            <h3 style="font-weight: 800; letter-spacing: -0.5px;">Tiano Journal &amp; Guides</h3>
        </div>
    </div>
</div>

<!-- Blog Area -->
<section class="blog-area pt-100 pb-70" style="background-color: #fbfbfc;">
    <div class="container">
        
        <!-- Section Header -->
        <div class="section-title text-center mb-5">
            <span class="sp-color" style="color: #E73539; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-size: 13px;">INSIGHTS &amp; STORIES</span>
            <h2 style="font-weight: 800; color: #212529; font-size: 32px; margin-top: 8px;">Latest News, Travel Guides &amp; Experiences</h2>
            <p style="max-width: 680px; margin: 12px auto 0; color: #64748b; font-size: 15px; line-height: 1.7;">
                Explore insider stories, local Akwa Ibom cultural highlights, corporate executive travel advice, and what makes Tiano Hotel &amp; Suites the foremost destination in Eket.
            </p>
        </div>

        <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <div class="p-4 rounded-3 d-inline-block bg-white shadow-sm border" style="max-width: 500px;">
                    <i class="bx bx-news fs-1 text-danger mb-2"></i>
                    <h4 class="fw-bold text-dark">Articles Coming Soon</h4>
                    <p class="text-muted small mb-0">Our editorial team is preparing fresh stories. Check back shortly!</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): 
                    $postSlug = $post['slug'];
                    $postUrl  = 'blog-details.php?slug=' . urlencode($postSlug);
                    $postImg  = !empty($post['featured_image']) ? $post['featured_image'] : 'assets/img/blog/blog-item-img1.jpg';
                    $postDate = date('M d, Y', strtotime($post['created_at']));
                    
                    // Estimate reading time (~200 words per minute)
                    $wordCount = str_word_count(strip_tags($post['content']));
                    $readMinutes = max(1, ceil($wordCount / 200));
                ?>
                    <div class="col-lg-4 col-md-6 col-12 d-flex">
                        <article class="card border-0 rounded-4 shadow-sm w-100 overflow-hidden d-flex flex-column" style="background:#ffffff; transition: transform 0.25s ease, box-shadow 0.25s ease;">
                            
                            <!-- Featured Image & Category Badge -->
                            <div class="position-relative overflow-hidden" style="height: 230px;">
                                <a href="<?php echo htmlspecialchars($postUrl); ?>" class="d-block w-100 h-100">
                                    <img src="<?php echo htmlspecialchars($postImg); ?>" 
                                         alt="<?php echo htmlspecialchars($post['title']); ?>" 
                                         class="w-100 h-100" 
                                         style="object-fit: cover; transition: transform 0.4s ease;"
                                         onmouseover="this.style.transform='scale(1.05)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <span class="position-absolute top-0 start-0 m-3 badge px-3 py-2 rounded-pill fw-bold text-uppercase" 
                                      style="background: #E73539; font-size: 11px; letter-spacing: 0.5px;">
                                    <?php echo htmlspecialchars($post['category']); ?>
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column">
                                <!-- Meta line -->
                                <div class="d-flex align-items-center gap-3 text-muted small mb-2" style="font-size: 12px;">
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bx bx-calendar text-danger"></i>
                                        <?php echo $postDate; ?>
                                    </span>
                                    <span>&bull;</span>
                                    <span class="d-flex align-items-center gap-1">
                                        <i class="bx bx-time text-danger"></i>
                                        <?php echo $readMinutes; ?> min read
                                    </span>
                                </div>

                                <!-- Title -->
                                <h4 class="card-title fw-bold mb-3" style="font-size: 19px; line-height: 1.4;">
                                    <a href="<?php echo htmlspecialchars($postUrl); ?>" class="text-dark text-decoration-none" style="transition: color 0.2s;" onmouseover="this.style.color='#E73539'" onmouseout="this.style.color='#212529'">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h4>

                                <!-- Excerpt -->
                                <p class="card-text text-muted small mb-4 flex-grow-1" style="line-height: 1.7; font-size: 14px;">
                                    <?php echo htmlspecialchars($post['excerpt']); ?>
                                </p>

                                <!-- Footer -->
                                <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; background: #212529; font-size: 12px;">
                                            T
                                        </div>
                                        <small class="text-secondary fw-semibold" style="font-size: 12px;"><?php echo htmlspecialchars($post['author_name']); ?></small>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($postUrl); ?>" class="fw-bold text-danger text-decoration-none small d-flex align-items-center gap-1">
                                        Read More <i class="bx bx-right-arrow-alt fs-5"></i>
                                    </a>
                                </div>

                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Newsletter / CTA Strip -->
<section class="py-5" style="background: #212529; color: #ffffff;">
    <div class="container text-center">
        <h3 class="fw-bold mb-2">Experience Tiano Hotel Firsthand</h3>
        <p class="text-white-50 mb-4" style="max-width: 580px; margin: 0 auto;">Reserve your suite online today with zero prepayment and instant verified digital boarding pass.</p>
        <a href="booking.php" class="btn btn-danger fw-bold px-4 py-3 rounded-pill" style="background: #E73539; border: none; font-size: 15px;">
            <i class="bx bx-calendar-check me-1"></i> Book Your Stay in Eket
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/include/footer.php'; ?>
