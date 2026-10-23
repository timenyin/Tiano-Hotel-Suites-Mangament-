<?php
/**
 * Tiano Hotel and Suites - Global Helper Functions & Data Access
 */

require_once __DIR__ . '/db.php';

/**
 * Fetch all site settings as a key => value array
 */
function get_site_settings($pdo) {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $settings;
    } catch (Exception $e) {
        error_log("Error fetching site settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a specific site setting with fallback
 */
function get_setting($pdo, $key, $default = '') {
    $settings = get_site_settings($pdo);
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Format currency in Nigerian Naira (₦)
 */
function format_naira($amount) {
    return '₦' . number_format((float)$amount, 0);
}

/**
 * Generate a unique booking reference (e.g., THS-782410)
 */
function generate_booking_reference() {
    return 'THS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

/**
 * Fetch active room categories
 */
function get_room_categories($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM room_categories WHERE status = 'active' ORDER BY price_per_night ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching room categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch a single room category by slug
 */
function get_room_category_by_slug($pdo, $slug) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM room_categories WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching room by slug: " . $e->getMessage());
        return null;
    }
}

/**
 * Fetch a single room category by ID
 */
function get_room_category_by_id($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM room_categories WHERE id = ? AND status = 'active' LIMIT 1");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching room by id: " . $e->getMessage());
        return null;
    }
}

/**
 * Fetch gallery items with optional category filter
 */
function get_gallery_items($pdo, $category = null, $limit = 50) {
    try {
        if ($category && $category !== 'all') {
            $stmt = $pdo->prepare("SELECT * FROM gallery WHERE status = 'active' AND category_filter = ? ORDER BY sort_order ASC, id DESC LIMIT ?");
            $stmt->bindValue(1, $category, PDO::PARAM_STR);
            $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("SELECT * FROM gallery WHERE status = 'active' ORDER BY sort_order ASC, id DESC LIMIT ?");
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching gallery items: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch active services
 */
function get_services($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching services: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch active FAQs
 */
function get_faqs($pdo, $category = null) {
    try {
        if ($category) {
            $stmt = $pdo->prepare("SELECT * FROM faqs WHERE status = 'active' AND category = ? ORDER BY sort_order ASC");
            $stmt->execute([$category]);
        } else {
            $stmt = $pdo->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC");
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching FAQs: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch approved reviews
 */
function get_approved_reviews($pdo, $limit = 10) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY id DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Resolve client geolocation (City, Region, Country) dynamically via Geolocation API.
 * Never hardcodes default cities. Dynamically queries public geolocation from the API.
 */
function resolve_ip_location(?string $ip): string
{
    $isLoopback = empty($ip) || $ip === '::1' || $ip === '127.0.0.1' || $ip === 'localhost';

    static $memoryCache = [];
    $cacheKey = $isLoopback ? 'localhost_caller_geo' : $ip;

    if (isset($memoryCache[$cacheKey])) {
        return $memoryCache[$cacheKey];
    }

    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['ip_geo_' . md5($cacheKey)])) {
        $memoryCache[$cacheKey] = $_SESSION['ip_geo_' . md5($cacheKey)];
        return $memoryCache[$cacheKey];
    }

    $location = $isLoopback ? 'Localhost (Local Session)' : 'Unknown Location';

    // Query Geolocation API: If loopback, omit IP to query caller's public location dynamically
    $apiUrl = $isLoopback 
        ? "http://ip-api.com/json/?fields=status,country,city,regionName" 
        : "http://ip-api.com/json/{$ip}?fields=status,country,city,regionName";

    try {
        $ctx = stream_context_create([
            'http' => ['timeout' => 1.5, 'ignore_errors' => true]
        ]);
        $raw = @file_get_contents($apiUrl, false, $ctx);
        if ($raw) {
            $data = json_decode($raw, true);
            if (!empty($data) && ($data['status'] ?? '') === 'success') {
                $city = $data['city'] ?? '';
                $region = $data['regionName'] ?? '';
                $country = $data['country'] ?? '';
                $parts = array_filter([$city, $region, $country]);
                if (!empty($parts)) {
                    $location = implode(', ', $parts);
                    if ($isLoopback) {
                        $location .= ' (Host)';
                    }
                }
            }
        }
    } catch (Exception $e) {}

    $memoryCache[$cacheKey] = $location;
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['ip_geo_' . md5($cacheKey)] = $location;
    }
    return $location;
}

/**
 * Log Administrative Action to Audit Trail with Geolocation
 */
function log_activity($pdo, $adminId, $adminName, $action, $entityType, $entityId = null, $details = '') {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $location = resolve_ip_location($ip);

        // Self-healing schema: auto-create location column if missing
        static $colChecked = false;
        if (!$colChecked) {
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'location'")->fetchAll();
                if (empty($cols)) {
                    $pdo->exec("ALTER TABLE audit_logs ADD COLUMN location VARCHAR(150) NULL AFTER ip_address");
                }
            } catch (Exception $e) {}
            $colChecked = true;
        }

        $adminIdVal = (!empty($adminId) && (int)$adminId > 0) ? (int)$adminId : null;
        $entityIdVal = (!empty($entityId) && (int)$entityId > 0) ? (int)$entityId : null;

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (admin_id, admin_name, action, entity_type, entity_id, details, ip_address, location, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$adminIdVal, $adminName, $action, $entityType, $entityIdVal, $details, $ip, $location]);
    } catch (\Throwable $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}


/**
 * Render luxury pagination links for admin data tables
 */
function render_admin_pagination($currentPage, $totalPages, $totalRecords, $perPage, $queryParams = []) {
    if ($totalPages <= 1 && $totalRecords <= $perPage) {
        return '';
    }
    
    $startRecord = $totalRecords > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
    $endRecord = min($currentPage * $perPage, $totalRecords);
    
    $buildUrl = function($pageNum) use ($queryParams) {
        $params = array_merge($queryParams, ['page' => $pageNum]);
        return '?' . http_build_query($params);
    };
    
    $html = '<div class="admin-pagination-container d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 border-top bg-light-subtle">';
    $html .= '<div class="pagination-info text-muted small">';
    $html .= 'Showing <strong class="text-dark">' . $startRecord . '</strong> to <strong class="text-dark">' . $endRecord . '</strong> of <strong class="text-dark">' . number_format($totalRecords) . '</strong> entries';
    $html .= '</div>';
    
    $html .= '<nav aria-label="Page navigation"><ul class="pagination pagination-sm mb-0 admin-pagination gap-1">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($buildUrl($currentPage - 1)) . '"><i class="bx bx-chevron-left"></i> Prev</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link"><i class="bx bx-chevron-left"></i> Prev</span></li>';
    }
    
    // Page links with sliding window
    $window = 2;
    $startPage = max(1, $currentPage - $window);
    $endPage = min($totalPages, $currentPage + $window);
    
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($buildUrl(1)) . '">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($buildUrl($i)) . '">' . $i . '</a></li>';
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($buildUrl($totalPages)) . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . htmlspecialchars($buildUrl($currentPage + 1)) . '">Next <i class="bx bx-chevron-right"></i></a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next <i class="bx bx-chevron-right"></i></span></li>';
    }
    
    $html .= '</ul></nav>';
    $html .= '</div>';
    
    return $html;
}

// ─── Self-Healing Schema Migrations ──────────────────────────────────────────
try {
    $cols = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_approved_by'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_approved_by VARCHAR(150) NULL");
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_approved_at DATETIME NULL");
    }
} catch (Exception $e) {}

try {
    $pdo->exec("UPDATE site_settings SET setting_value = 'support@tianohotelandsuites.com' WHERE setting_key = 'hotel_email' AND (setting_value LIKE '%@gmail.com' OR setting_value LIKE '%@weburea.com' OR setting_value = '' OR setting_value IS NULL)");
} catch (Exception $e) {}

// Self-healing: Provision and sync official 4-tier staff management accounts
try {
    $officialStaff = [
        [
            'username' => 'admin',
            'fullname' => 'Tiano Hotel Administrator',
            'email'    => 'support@tianohotelandsuites.com',
            'hash'     => '$2y$10$KYHjSGeEOvzKGoQ10bYAGOmpflZJnJEq8yB0MZTjeGpavjXgeY0IW', // Admin@Tiano2026
            'pwdCheck' => 'Admin@Tiano2026',
            'role'     => 'super_admin'
        ],
        [
            'username' => 'dev',
            'fullname' => 'Weburea Dev Ops',
            'email'    => 'dev@tianohotelandsuites.com',
            'hash'     => '$2y$10$F46UCRKxM7maLKqVsWUUAusCn/fIMhM80Vd8I9rQC49ZxBMW2GpUe', // DevOps@Tiano2026!
            'pwdCheck' => 'DevOps@Tiano2026!',
            'role'     => 'super_admin'
        ],
        [
            'username' => 'manager',
            'fullname' => 'Tiano General Manager',
            'email'    => 'management@tianohotelandsuites.com',
            'hash'     => '$2y$10$dyVECavZVevoPzkpnsSVO.r0H/NwE2oVYQHYezncNOn7DaOOBXw6u', // Manager@Tiano2026
            'pwdCheck' => 'Manager@Tiano2026',
            'role'     => 'manager'
        ],
        [
            'username' => 'reception',
            'fullname' => 'Tiano Front Desk Reception',
            'email'    => 'frontdesk@tianohotelandsuites.com',
            'hash'     => '$2y$10$wV8qrI3Zm/fQD/xrf5xp1.gcvtfjqMUShBxuobj.alPPrI6pFJMyi', // FrontDesk@Tiano2026
            'pwdCheck' => 'FrontDesk@Tiano2026',
            'role'     => 'receptionist'
        ],
    ];

    foreach ($officialStaff as $staff) {
        $stmtChk = $pdo->prepare("SELECT id, password_hash, is_verified, status, role FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmtChk->execute([$staff['username'], $staff['email']]);
        $existing = $stmtChk->fetch();

        if ($existing) {
            // Keep user-customized passwords intact! Only repair if password_hash is empty or if unverified/inactive
            $needsUpdate = false;
            $newHash = $existing['password_hash'];
            if (empty($existing['password_hash'])) {
                $newHash = $staff['hash'];
                $needsUpdate = true;
            }
            if ((int)$existing['is_verified'] === 0 || $existing['status'] !== 'active' || $existing['role'] !== $staff['role']) {
                $needsUpdate = true;
            }
            if ($needsUpdate) {
                $stmtUp = $pdo->prepare("UPDATE admin_users SET password_hash = ?, is_verified = 1, status = 'active', role = ? WHERE id = ?");
                $stmtUp->execute([$newHash, $staff['role'], $existing['id']]);
            }
        } else {
            // Insert staff account if not yet present
            $stmtIns = $pdo->prepare("
                INSERT INTO admin_users (username, fullname, email, password_hash, role, is_verified, status, created_at)
                VALUES (?, ?, ?, ?, ?, 1, 'active', NOW())
            ");
            $stmtIns->execute([$staff['username'], $staff['fullname'], $staff['email'], $staff['hash'], $staff['role']]);
        }
    }
} catch (Exception $e) {}

