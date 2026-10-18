<?php
/**
 * Tiano Hotel & Suites - Global HTML Head & SEO Header
 * Strictly adheres to .agents/rules/seo.md for Google, WhatsApp & Social link previews.
 */
require_once __DIR__ . '/functions.php';

// Detect current absolute domain and request path
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$currentProtocol = $isHttps ? 'https://' : 'http://';
$currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentUri = $_SERVER['REQUEST_URI'] ?? '/Tiano-Hotel/';
$localBaseUrl = $currentProtocol . $currentHost . '/Tiano-Hotel/';
$prodBaseUrl = 'https://www.tianohotel.com/';

// Default Meta Properties
$page_title = isset($page_title) ? $page_title : 'Tiano Hotel and Suites | Luxury Accommodation in Eket, Akwa Ibom';
$page_desc  = isset($page_desc) ? $page_desc : 'Experience world-class luxury at Tiano Hotel and Suites, located at 44 Marina Road, Eket. 24/7 power, swimming pool, executive suites, and secured hospitality.';
$page_rel_img = isset($page_og_img) ? $page_og_img : 'assets/img/og-preview.jpg';

// Absolute Canonical URL (Guarantees independent page indexing per seo.md)
if (!isset($page_canonical)) {
    // Generate canonical pointing to production domain while maintaining current script name
    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $page_canonical = $prodBaseUrl . ($scriptName === 'index.php' ? '' : $scriptName);
}

// Absolute Image URL for WhatsApp & Social bots (WhatsApp requires absolute URL under 300KB)
if (strpos($page_rel_img, 'http://') === 0 || strpos($page_rel_img, 'https://') === 0) {
    $page_og_img_abs = $page_rel_img;
} else {
    // Ensure clean relative path without leading slash
    $cleanRel = ltrim($page_rel_img, '/');
    // If running in development, construct reachable absolute URL, but maintain production fallback
    $page_og_img_abs = $prodBaseUrl . $cleanRel;
}

$page_og_type = isset($page_og_type) ? $page_og_type : 'website';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Multi-Resolution Favicons & Apple Touch Icon for WhatsApp/iOS preview -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/logos/logo-1.png">
    
    <!-- Explicit Canonical Tag (Prevents Google search console collapsing) -->
    <link rel="canonical" href="<?php echo htmlspecialchars($page_canonical); ?>">

    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="keywords" content="Tiano Hotel and Suites, Hotel in Eket, Luxury Hotel Marina Road Eket, Accommodation in Akwa Ibom, Presidential Suite Eket, Hotel with Pool Eket, Secure Hotel Akwa Ibom, Tiano Suites, Best Hotel in Eket, Hotel in eket price, Luxury hotel in eket">
    <meta name="author" content="Tiano Hotel and Suites">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook / WhatsApp Preview Compatibility (per seo.md) -->
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="Tiano Hotel and Suites">
    <meta property="og:type" content="<?php echo htmlspecialchars($page_og_type); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($page_canonical); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($page_og_img_abs); ?>">
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($page_og_img_abs); ?>">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Tiano Hotel and Suites Exterior and Luxury Suites in Eket">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@tianohotel">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($page_og_img_abs); ?>">

    <!-- Schema.org JSON-LD Structured Data for Google Rich Snippets -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Hotel",
      "name": "Tiano Hotel and Suites",
      "description": "Premier boutique hotel and luxury suites in Eket, Akwa Ibom State, Nigeria.",
      "url": "https://www.tianohotel.com",
      "telephone": "+2348144734612",
      "priceRange": "₦35,000 - ₦150,000",
      "image": "https://www.tianohotel.com/assets/img/og-preview.jpg",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "44 Marina Road",
        "addressLocality": "Eket",
        "addressRegion": "Akwa Ibom State",
        "addressCountry": "NG"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 4.6439,
        "longitude": 7.9304
      },
      "amenityFeature": [
        {"@type": "LocationFeatureSpecification", "name": "Outdoor Swimming Pool", "value": true},
        {"@type": "LocationFeatureSpecification", "name": "24/7 Dual Generator Power", "value": true},
        {"@type": "LocationFeatureSpecification", "name": "Free High-Speed Wi-Fi", "value": true},
        {"@type": "LocationFeatureSpecification", "name": "Complimentary Gourmet Breakfast", "value": true},
        {"@type": "LocationFeatureSpecification", "name": "24/7 Armed Security & CCTV", "value": true}
      ]
    }
    </script>
    <?php if (!empty($schema_json)): ?>
        <script type="application/ld+json">
        <?php echo $schema_json; ?>
        </script>
    <?php endif; ?>

    <!-- Core & Vendor CSS Stylesheets -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/fonts/flaticon.css">
    <link rel="stylesheet" href="assets/css/boxicons.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/theme-dark.css">
    <!-- Flatpickr Date Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="5-EKPDU79tZaKmn1btvlHzB_y37AlAtf5JlkfotV5PQ" />
    
    <!-- Google Analytics (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17974817149"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17974817149');
    </script>
    <style>
        /* CSS Fail-Safe: Preloader auto-fades even if JavaScript is blocked or errors occur */
        .preloader {
            animation: preloaderFadeOut 0.4s ease 1.2s forwards;
        }
        @keyframes preloaderFadeOut {
            to {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }
        }
    </style>
</head>
<body>

    <!-- Preloader -->
    <div class="preloader">
        <div class="d-table">
            <div class="d-table-cell">
                <div class="sk-cube-area">
                    <div class="sk-cube1 sk-cube"></div>
                    <div class="sk-cube2 sk-cube"></div>
                    <div class="sk-cube4 sk-cube"></div>
                    <div class="sk-cube3 sk-cube"></div>
                </div>
            </div>
        </div>
    </div>
