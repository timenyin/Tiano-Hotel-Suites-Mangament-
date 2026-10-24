<?php
/**
 * Unit & Integration Test: Complete SEO & Search Console Audit
 * Verifies XML sitemap, robots.txt, .htaccess headers, canonical tags, and Open Graph previews.
 */

class SeoAuditTest {
    private string $baseDir;

    public function __construct() {
        $this->baseDir = dirname(__DIR__, 2);
    }

    public function testSitemapXmlValid(): void {
        $path = $this->baseDir . '/sitemap.xml';
        assert(file_exists($path), "sitemap.xml does not exist at {$path}");
        
        $content = file_get_contents($path);
        assert(!empty($content), "sitemap.xml is empty");
        
        $xml = simplexml_load_string($content);
        assert($xml !== false, "sitemap.xml is not valid XML");
        assert(isset($xml->url) && count($xml->url) >= 15, "sitemap.xml must have at least 15 URLs");
    }

    public function testRobotsTxtBlocksAdmin(): void {
        $path = $this->baseDir . '/robots.txt';
        assert(file_exists($path), "robots.txt does not exist at {$path}");
        
        $content = file_get_contents($path);
        assert(strpos($content, 'Disallow: /admin/') !== false, "robots.txt must disallow /admin/");
        assert(strpos($content, 'Sitemap: https://tianohotelandsuites.com/sitemap.xml') !== false, "robots.txt must declare sitemap");
    }

    public function testAdminHtaccessSecurity(): void {
        $path = $this->baseDir . '/admin/.htaccess';
        assert(file_exists($path), "admin/.htaccess does not exist");
        
        $content = file_get_contents($path);
        assert(strpos($content, 'X-Robots-Tag') !== false, "admin/.htaccess must specify X-Robots-Tag");
        assert(strpos($content, 'noindex') !== false, "admin/.htaccess must enforce noindex");
    }

    public function testHeadSeoTemplateCompleteness(): void {
        $path = $this->baseDir . '/include/head-seo.php';
        assert(file_exists($path), "include/head-seo.php does not exist");
        
        $content = file_get_contents($path);
        assert(strpos($content, 'rel="canonical"') !== false, "head-seo.php must have canonical link");
        assert(strpos($content, 'property="og:image"') !== false, "head-seo.php must have og:image");
        assert(strpos($content, 'property="og:title"') !== false, "head-seo.php must have og:title");
        assert(strpos($content, 'property="og:description"') !== false, "head-seo.php must have og:description");
        assert(strpos($content, 'name="twitter:card"') !== false, "head-seo.php must have twitter:card");
        assert(strpos($content, 'application/ld+json') !== false, "head-seo.php must output Schema.org JSON-LD");
        assert(strpos($content, 'google-site-verification') !== false, "head-seo.php must include Google site verification");
    }

    public function testOgPreviewImageUnder300KB(): void {
        $path = $this->baseDir . '/assets/img/og-preview.jpg';
        assert(file_exists($path), "og-preview.jpg does not exist");
        
        $size = filesize($path);
        // WhatsApp requires preview image under 300KB (307,200 bytes)
        assert($size < 307200, "og-preview.jpg size ({$size} bytes) exceeds 300KB WhatsApp maximum limit");
    }

    public function runAll(): array {
        $results = [];
        $methods = ['testSitemapXmlValid', 'testRobotsTxtBlocksAdmin', 'testAdminHtaccessSecurity', 'testHeadSeoTemplateCompleteness', 'testOgPreviewImageUnder300KB'];
        foreach ($methods as $m) {
            try {
                $this->$m();
                $results[$m] = ['status' => 'PASS'];
            } catch (Throwable $e) {
                $results[$m] = ['status' => 'FAIL', 'error' => $e->getMessage()];
            }
        }
        return $results;
    }
}
