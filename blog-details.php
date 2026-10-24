<?php
/**
 * Tiano Hotel & Suites - Single Blog Post Details
 * Includes BlogPosting Schema.org JSON-LD, Open Graph tags, and WhatsApp share buttons.
 */
require_once __DIR__ . '/include/functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$post = null;
if (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? AND status = 'published' LIMIT 1");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
}

if (!$post) {
    header("Location: blog.php");
    exit;
}

// Increment views count non-destructively
try {
    $pdo->prepare("UPDATE blog_posts SET views_count = views_count + 1 WHERE id = ?")->execute([$post['id']]);
} catch (Exception $e) {}

// SEO & Meta Configuration per .agents/rules/seo.md
$page_title     = !empty($post['seo_title']) ? $post['seo_title'] : ($post['title'] . ' | Tiano Hotel & Suites');
$page_desc      = !empty($post['seo_description']) ? $post['seo_description'] : ($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 155));
$page_canonical = 'https://tianohotelandsuites.com/blog-details.php?slug=' . urlencode($post['slug']);
$page_og_img    = !empty($post['featured_image']) ? $post['featured_image'] : 'assets/img/blog/blog-item-img1.jpg';
$page_og_type   = 'article';
$active_page    = 'blog';

// Prepare BlogPosting JSON-LD Schema
$publishedIso = date('c', strtotime($post['created_at']));
$modifiedIso  = date('c', strtotime($post['updated_at'] ?? $post['created_at']));
$schema_json = json_encode([
    "@context"         => "https://schema.org",
    "@type"            => "BlogPosting",
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id"   => $page_canonical
    ],
    "headline"         => $post['title'],
    "description"      => $page_desc,
    "image"            => "https://tianohotelandsuites.com/" . ltrim($page_og_img, '/'),
    "author"           => [
        "@type" => "Person",
        "name"  => $post['author_name'] ?: "Tiano Editorial Desk"
    ],
    "publisher"        => [
        "@type" => "Organization",
        "name"  => "Tiano Hotel and Suites",
        "logo"  => [
            "@type" => "ImageObject",
            "url"   => "https://tianohotelandsuites.com/assets/img/logos/logo-1.png"
        ]
    ],
    "datePublished"    => $publishedIso,
    "dateModified"     => $modifiedIso
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

require_once __DIR__ . '/include/head-seo.php';
require_once __DIR__ . '/include/topbar.php';
require_once __DIR__ . '/include/navbar.php';

// Fetch Related Articles (excluding current)
try {
    $stmtRelated = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 'published' AND id != ? ORDER BY id DESC LIMIT 3");
    $stmtRelated->execute([$post['id']]);
    $relatedPosts = $stmtRelated->fetchAll();
} catch (Exception $e) {
    $relatedPosts = [];
}

$currentAbsoluteUrl = "https://tianohotelandsuites.com/blog-details.php?slug=" . urlencode($post['slug']);
$whatsappShareUrl   = "https://api.whatsapp.com/send?text=" . urlencode($post['title'] . " - Read more at: " . $currentAbsoluteUrl);
$facebookShareUrl   = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($currentAbsoluteUrl);
$twitterShareUrl    = "https://twitter.com/intent/tweet?text=" . urlencode($post['title']) . "&url=" . urlencode($currentAbsoluteUrl);
?>

<!-- Inner Banner -->
<div class="inner-banner inner-bg1" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.7)), url('<?php echo htmlspecialchars($page_og_img); ?>'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="inner-title text-center">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a href="blog.php">Blog</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li class="text-truncate" style="max-width: 280px;"><?php echo htmlspecialchars($post['category']); ?></li>
            </ul>
            <h3 style="font-weight: 800; max-width: 820px; margin: 0 auto; line-height: 1.3;"><?php echo htmlspecialchars($post['title']); ?></h3>
        </div>
    </div>
</div>

<!-- Blog Details Area -->
<section class="blog-details-area pt-100 pb-70" style="background-color: #ffffff;">
    <div class="container">
        <div class="row">

            <!-- Main Article Column -->
            <div class="col-lg-8">
                <article class="blog-article-content pe-lg-3">

                    <!-- Featured Image -->
                    <div class="mb-4 rounded-4 overflow-hidden shadow-sm" style="max-height: 480px;">
                        <img src="<?php echo htmlspecialchars($page_og_img); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="w-100" 
                             style="object-fit: cover; max-height: 480px;">
                    </div>

                    <!-- Article Meta Header -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-4 border-bottom text-muted small">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge px-3 py-2 rounded-pill fw-bold" style="background: #E73539;">
                                <?php echo htmlspecialchars($post['category']); ?>
                            </span>
                            <span><i class="bx bx-calendar text-danger me-1"></i> <?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
                            <span><i class="bx bx-user text-danger me-1"></i> <?php echo htmlspecialchars($post['author_name']); ?></span>
                        </div>
                        <div>
                            <span><i class="bx bx-show text-muted me-1"></i> <?php echo (int)$post['views_count']; ?> views</span>
                        </div>
                    </div>

                    <!-- Excerpt Lead Paragraph -->
                    <?php if (!empty($post['excerpt'])): ?>
                        <div class="p-3 mb-4 rounded-3" style="background:#f8fafc; border-left: 4px solid #E73539; font-size: 17px; line-height: 1.8; color: #334155; font-weight: 500;">
                            <?php echo htmlspecialchars($post['excerpt']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Body Content -->
                    <div class="article-rich-body" style="font-size: 16px; line-height: 1.9; color: #334155;">
                        <?php echo $post['content']; ?>
                    </div>

                    <!-- Social Sharing Section (WhatsApp Friendly per user request) -->
                    <div class="p-4 rounded-4 my-5 d-flex flex-wrap align-items-center justify-content-between gap-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div>
                            <span class="fw-bold text-dark d-block mb-1" style="font-size: 15px;">Share this article with friends &amp; colleagues:</span>
                            <small class="text-muted">Copies cleanly with photo preview on WhatsApp &amp; socials</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <!-- WhatsApp Share -->
                            <a href="<?php echo htmlspecialchars($whatsappShareUrl); ?>" target="_blank" class="btn btn-sm btn-success fw-bold px-3 py-2 d-flex align-items-center gap-1" style="background: #25D366; border: none; border-radius: 8px;">
                                <i class="bx bxl-whatsapp fs-5"></i> Share on WhatsApp
                            </a>
                            <!-- Facebook Share -->
                            <a href="<?php echo htmlspecialchars($facebookShareUrl); ?>" target="_blank" class="btn btn-sm btn-primary fw-bold px-3 py-2 d-flex align-items-center gap-1" style="background: #1877F2; border: none; border-radius: 8px;">
                                <i class="bx bxl-facebook fs-5"></i> Facebook
                            </a>
                            <!-- Twitter / X Share -->
                            <a href="<?php echo htmlspecialchars($twitterShareUrl); ?>" target="_blank" class="btn btn-sm btn-dark fw-bold px-3 py-2 d-flex align-items-center gap-1" style="border-radius: 8px;">
                                <i class="bx bxl-twitter fs-5"></i> X
                            </a>
                            <!-- Copy Link -->
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold px-3 py-2 d-flex align-items-center gap-1" style="border-radius: 8px;" onclick="navigator.clipboard.writeText('<?php echo $currentAbsoluteUrl; ?>'); alert('Article link copied to clipboard!');">
                                <i class="bx bx-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Author Signature Card -->
                    <div class="p-4 rounded-4 mb-5 d-flex align-items-center gap-3 border" style="background: #ffffff;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold fs-4 flex-shrink-0" style="width: 58px; height: 58px; background: #212529;">
                            T
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($post['author_name']); ?></h5>
                            <p class="mb-0 text-muted small">Official editorial desk of Tiano Hotel &amp; Suites. Dedicated to curating unparalleled hospitality insights, local Akwa Ibom travel intelligence, and guest luxury guides.</p>
                        </div>
                    </div>

                </article>
            </div>

            <!-- Sidebar Column -->
            <div class="col-lg-4">
                <aside class="blog-sidebar">

                    <!-- Quick Booking Card -->
                    <div class="p-4 rounded-4 mb-4 text-center shadow-sm" style="background: #212529; color: #ffffff;">
                        <span class="badge px-3 py-2 rounded-pill fw-bold mb-3" style="background: #E73539;">RESERVE TODAY</span>
                        <h4 class="fw-bold text-white mb-2">Staying in Eket?</h4>
                        <p class="text-white-50 small mb-4">Book directly online. Zero prepayment required. Verified electronic voucher pass issued immediately.</p>
                        <a href="booking.php" class="btn btn-danger w-100 fw-bold py-2 rounded-3" style="background: #E73539; border: none;">
                            <i class="bx bx-calendar-check me-1"></i> Instant Online Booking
                        </a>
                    </div>

                    <!-- Concierge WhatsApp Widget -->
                    <div class="p-4 rounded-4 mb-4 border bg-white shadow-sm text-center">
                        <i class="bx bxl-whatsapp text-success mb-2" style="font-size: 42px;"></i>
                        <h5 class="fw-bold text-dark mb-1">Direct Front Desk WhatsApp</h5>
                        <p class="text-muted small mb-3">Speak directly with our concierge team for group bookings, airport transit, or bespoke suites.</p>
                        <a href="https://wa.me/2348144734612?text=Hello%20Tiano%20Hotel,%20I%20read%20your%20blog%20and%20would%20like%20to%20make%20an%20inquiry." target="_blank" class="btn btn-success w-100 fw-bold py-2 rounded-3" style="background: #25D366; border: none;">
                            <i class="bx bxl-whatsapp me-1"></i> +234 814 473 4612
                        </a>
                    </div>

                    <!-- Related Recent Posts -->
                    <?php if (!empty($relatedPosts)): ?>
                        <div class="p-4 rounded-4 border bg-white shadow-sm">
                            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">More From Our Journal</h5>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($relatedPosts as $rel): 
                                    $relUrl = 'blog-details.php?slug=' . urlencode($rel['slug']);
                                    $relImg = !empty($rel['featured_image']) ? $rel['featured_image'] : 'assets/img/blog/blog-item-img1.jpg';
                                ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="<?php echo htmlspecialchars($relUrl); ?>" class="flex-shrink-0 rounded-3 overflow-hidden" style="width: 72px; height: 60px;">
                                            <img src="<?php echo htmlspecialchars($relImg); ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 11px;"><?php echo date('M d, Y', strtotime($rel['created_at'])); ?></small>
                                            <h6 class="mb-0 fw-bold" style="font-size: 13px; line-height: 1.4;">
                                                <a href="<?php echo htmlspecialchars($relUrl); ?>" class="text-dark text-decoration-none">
                                                    <?php echo htmlspecialchars($rel['title']); ?>
                                                </a>
                                            </h6>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </aside>
            </div>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/include/footer.php'; ?>
