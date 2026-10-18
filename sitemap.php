<?php
/**
 * Dynamic XML Sitemap Generator
 * Tiano Hotel & Suites
 * Dynamically resolves canonical site_url from settings
 */
require_once __DIR__ . '/include/db.php';
require_once __DIR__ . '/include/helpers.php';

header("Content-Type: application/xml; charset=utf-8");

$siteUrl = rtrim(get_setting($pdo, 'site_url', 'https://www.tianohotel.com'), '/');

$pages = [
    ['loc' => '', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/booking.php', 'priority' => '0.95', 'changefreq' => 'daily'],
    ['loc' => '/room.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/coral-room.php', 'priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/silver-room.php', 'priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/gold-room.php', 'priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/diamond-room.php', 'priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/premium-room.php', 'priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/presidentalsuite.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => '/about.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/services.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/gallery.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => '/testimonials.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/contact.php', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/complimentary_gourmet_breakfast.php', 'priority' => '0.75', 'changefreq' => 'monthly'],
    ['loc' => '/luxurious_comfortable_accommodation.php', 'priority' => '0.75', 'changefreq' => 'monthly'],
    ['loc' => '/modern_wellness_recreation_facilities.php', 'priority' => '0.75', 'changefreq' => 'monthly'],
    ['loc' => '/secured_environment.php', 'priority' => '0.75', 'changefreq' => 'monthly'],
    ['loc' => '/blog.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => '/privacy-policy.php', 'priority' => '0.5', 'changefreq' => 'yearly'],
    ['loc' => '/terms-condition.php', 'priority' => '0.5', 'changefreq' => 'yearly'],
];

$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach ($pages as $p): ?>
    <url>
        <loc><?php echo htmlspecialchars($siteUrl . $p['loc']); ?></loc>
        <lastmod><?php echo $today; ?></lastmod>
        <changefreq><?php echo $p['changefreq']; ?></changefreq>
        <priority><?php echo $p['priority']; ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
