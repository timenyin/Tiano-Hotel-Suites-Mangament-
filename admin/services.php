<?php
/**
 * Tiano Hotel & Suites - Services Directory CMS
 * Manage, edit, add, or toggle public hotel services.
 */
$pageTitle = 'Services CMS';
$activePage = 'services';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Handle Image Upload Helper
function handleServiceImageUpload($fileInputName) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$fileInputName];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes)) {
        return null;
    }

    $uploadDir = dirname(__DIR__) . '/assets/img/services/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'srv_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'assets/img/services/' . $fileName;
    }
    return null;
}

// Slug generator
function serviceSlug($text) {
    $slug = preg_replace('~[^\pL\d]+~u', '_', $text);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '_');
    $slug = strtolower($slug);
    return empty($slug) ? 'service_' . time() : $slug;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Add Service
    if ($action === 'add_service') {
        $title       = trim($_POST['title'] ?? '');
        $customSlug  = trim($_POST['slug'] ?? '');
        $slug        = !empty($customSlug) ? serviceSlug($customSlug) : serviceSlug($title);
        $iconClass   = trim($_POST['icon_class'] ?? 'flaticon-hotel');
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $fullContent = trim($_POST['full_content'] ?? '');
        $facTitle    = trim($_POST['facilities_title'] ?? 'Room Features & Amenities');
        $facDesc     = trim($_POST['facilities_desc'] ?? '');
        $facItems    = trim($_POST['facilities_items'] ?? '');
        $quoteText   = trim($_POST['quote_text'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        $uploadedImg = handleServiceImageUpload('featured_image');
        $featuredImg = $uploadedImg ?: 'assets/img/Comfortable Accommodation.jpg';

        if (empty($title)) {
            $error = 'Service title is required.';
        } else {
            try {
                // Ensure unique slug
                $check = $pdo->prepare("SELECT COUNT(*) FROM services WHERE slug = ?");
                $check->execute([$slug]);
                if ($check->fetchColumn() > 0) {
                    $slug .= '_' . time();
                }

                $stmt = $pdo->prepare("
                    INSERT INTO services (
                        title, slug, icon_class, short_description, full_content,
                        featured_image, facilities_title, facilities_desc, facilities_items,
                        quote_text, sort_order, status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $title, $slug, $iconClass, $shortDesc, $fullContent,
                    $featuredImg, $facTitle, $facDesc, $facItems,
                    $quoteText, $sortOrder, $status
                ]);
                $newId = $pdo->lastInsertId();

                log_activity($pdo, $admin['id'], $admin['fullname'], 'create_service', 'service', $newId, "Added service: {$title}");
                $success = "Service '{$title}' created successfully.";
            } catch (Exception $e) {
                $error = "Error adding service: " . $e->getMessage();
            }
        }
    }

    // 2. Edit Service
    elseif ($action === 'edit_service') {
        $srvId       = (int)($_POST['service_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $customSlug  = trim($_POST['slug'] ?? '');
        $slug        = !empty($customSlug) ? serviceSlug($customSlug) : serviceSlug($title);
        $iconClass   = trim($_POST['icon_class'] ?? 'flaticon-hotel');
        $shortDesc   = trim($_POST['short_description'] ?? '');
        $fullContent = trim($_POST['full_content'] ?? '');
        $facTitle    = trim($_POST['facilities_title'] ?? 'Room Features & Amenities');
        $facDesc     = trim($_POST['facilities_desc'] ?? '');
        $facItems    = trim($_POST['facilities_items'] ?? '');
        $quoteText   = trim($_POST['quote_text'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        $uploadedImg = handleServiceImageUpload('featured_image');

        if (empty($title)) {
            $error = 'Service title cannot be empty.';
        } else {
            try {
                // Check slug uniqueness
                $check = $pdo->prepare("SELECT COUNT(*) FROM services WHERE slug = ? AND id != ?");
                $check->execute([$slug, $srvId]);
                if ($check->fetchColumn() > 0) {
                    $slug .= '_' . time();
                }

                if ($uploadedImg) {
                    $stmt = $pdo->prepare("
                        UPDATE services SET
                            title = ?, slug = ?, icon_class = ?, short_description = ?, full_content = ?,
                            featured_image = ?, facilities_title = ?, facilities_desc = ?, facilities_items = ?,
                            quote_text = ?, sort_order = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $slug, $iconClass, $shortDesc, $fullContent,
                        $uploadedImg, $facTitle, $facDesc, $facItems,
                        $quoteText, $sortOrder, $status, $srvId
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE services SET
                            title = ?, slug = ?, icon_class = ?, short_description = ?, full_content = ?,
                            facilities_title = ?, facilities_desc = ?, facilities_items = ?,
                            quote_text = ?, sort_order = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $slug, $iconClass, $shortDesc, $fullContent,
                        $facTitle, $facDesc, $facItems,
                        $quoteText, $sortOrder, $status, $srvId
                    ]);
                }

                log_activity($pdo, $admin['id'], $admin['fullname'], 'update_service', 'service', $srvId, "Updated service: {$title}");
                $success = "Service '{$title}' updated successfully.";
            } catch (Exception $e) {
                $error = "Error updating service: " . $e->getMessage();
            }
        }
    }

    // 3. Delete Service
    elseif ($action === 'delete_service') {
        $srvId = (int)($_POST['service_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$srvId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_service', 'service', $srvId, "Deleted service ID {$srvId}");
            $success = "Service deleted successfully.";
        } catch (Exception $e) {
            $error = "Error deleting service: " . $e->getMessage();
        }
    }

    // 4. Toggle Status
    elseif ($action === 'toggle_status') {
        $srvId = (int)($_POST['service_id'] ?? 0);
        $newStatus = $_POST['new_status'] === 'inactive' ? 'inactive' : 'active';
        try {
            $stmt = $pdo->prepare("UPDATE services SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $srvId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'toggle_service_status', 'service', $srvId, "Set status to {$newStatus}");
            $success = "Service visibility updated.";
        } catch (Exception $e) {
            $error = "Error changing status: " . $e->getMessage();
        }
    }
}

// Fetch all services
$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<!-- Alerts -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
        <i class="bx bx-check-circle fs-5"></i>
        <div><?php echo htmlspecialchars($success); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
        <i class="bx bx-error-circle fs-5"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Top Controls -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Hotel Services CMS</h4>
        <p class="text-muted small mb-0">Manage services, room features, quotes, and amenities displayed across public pages.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger fw-semibold px-3 py-2" style="background:#E73539; border-radius:8px;" data-bs-toggle="modal" data-bs-target="#newServiceModal">
            <i class="bx bx-plus-circle me-1"></i> Add Service
        </button>
        <a href="../services.php" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2" style="border-radius:8px;">
            <i class="bx bx-globe me-1"></i> View Services Page
        </a>
    </div>
</div>

<!-- Services Grid / Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Order</th>
                    <th>Service &amp; Slug</th>
                    <th>Icon Class</th>
                    <th>Short Description</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hotel services configured.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $s): 
                        $isActive = ($s['status'] === 'active');
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold">#<?php echo $s['sort_order']; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 rounded bg-light border text-danger flex-shrink-0" style="font-size: 20px;">
                                        <i class="<?php echo htmlspecialchars($s['icon_class']); ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 14px;"><?php echo htmlspecialchars($s['title']); ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 11px;">slug: <?php echo htmlspecialchars($s['slug']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="small text-danger bg-light px-2 py-1 rounded"><?php echo htmlspecialchars($s['icon_class']); ?></code>
                            </td>
                            <td>
                                <p class="mb-0 text-muted small text-truncate" style="max-width: 320px;" title="<?php echo htmlspecialchars($s['short_description']); ?>">
                                    <?php echo htmlspecialchars($s['short_description']); ?>
                                </p>
                            </td>
                            <td>
                                <form method="POST" action="services.php" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $isActive ? 'inactive' : 'active'; ?>">
                                    <button type="submit" class="status-badge <?php echo $isActive ? 'status-badge-success' : 'status-badge-secondary'; ?> border-0" style="cursor: pointer;" title="Toggle visibility">
                                        <span class="status-dot"></span>
                                        <?php echo ucfirst($s['status']); ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end align-middle">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions &amp; Options">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; min-width:180px;">
                                        <li>
                                            <a class="dropdown-item" href="../service-details.php?slug=<?php echo urlencode($s['slug']); ?>" target="_blank">
                                                <i class="bx bx-link-external text-secondary me-2"></i> View on Live Site
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#editServiceModal<?php echo $s['id']; ?>">
                                                <i class="bx bx-edit me-2"></i> Edit Service
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteServiceModal<?php echo $s['id']; ?>">
                                                <i class="bx bx-trash me-2"></i> Delete Service
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editServiceModal<?php echo $s['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content admin-modal-content">
                                    <div class="modal-header admin-modal-header">
                                        <h5 class="modal-title fw-bold text-white mb-0">Edit Service: <?php echo htmlspecialchars($s['title']); ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="services.php" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="edit_service">
                                        <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Service Title *</label>
                                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($s['title']); ?>" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Display Order</label>
                                                    <input type="number" name="sort_order" class="form-control" value="<?php echo (int)$s['sort_order']; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Slug identifier</label>
                                                    <input type="text" name="slug" class="form-control font-monospace" value="<?php echo htmlspecialchars($s['slug']); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Icon Class (Flaticon / Boxicons)</label>
                                                    <input type="text" name="icon_class" class="form-control" value="<?php echo htmlspecialchars($s['icon_class']); ?>" list="iconPresets">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Short Summary (Services Grid Card)</label>
                                                    <textarea name="short_description" class="form-control" rows="2"><?php echo htmlspecialchars($s['short_description']); ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Service Description</label>
                                                    <textarea name="full_content" class="form-control" rows="4"><?php echo htmlspecialchars($s['full_content']); ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Quotation / Highlight Callout</label>
                                                    <input type="text" name="quote_text" class="form-control" value="<?php echo htmlspecialchars($s['quote_text'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Amenities / Features Title</label>
                                                    <input type="text" name="facilities_title" class="form-control" value="<?php echo htmlspecialchars($s['facilities_title'] ?? 'Room Features & Amenities'); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" <?php echo $s['status'] === 'active' ? 'selected' : ''; ?>>Active (Visible on Website)</option>
                                                        <option value="inactive" <?php echo $s['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Included Amenities List (One per line)</label>
                                                    <textarea name="facilities_items" class="form-control" rows="5" placeholder="e.g.&#10;Spacious & well-furnished rooms&#10;High-speed Wi-Fi access&#10;Daily housekeeping"><?php echo htmlspecialchars($s['facilities_items'] ?? ''); ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Update Featured Image</label>
                                                    <input type="file" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                                    <small class="text-muted">Current: <?php echo htmlspecialchars($s['featured_image']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light p-3">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteServiceModal<?php echo $s['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-trash text-danger mb-2" style="font-size: 44px;"></i>
                                        <h5 class="fw-bold mb-2">Delete Service?</h5>
                                        <p class="small text-muted mb-4">Are you sure you want to delete &ldquo;<?php echo htmlspecialchars($s['title']); ?>&rdquo;? This will remove it from the public services grid.</p>
                                        <form method="POST" action="services.php">
                                            <input type="hidden" name="action" value="delete_service">
                                            <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger flex-grow-1">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Service Modal -->
<div class="modal fade" id="newServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Add New Hotel Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="services.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_service">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-muted text-uppercase">Service Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. VIP Chauffeur &amp; Airport Transit" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Slug identifier (Optional)</label>
                            <input type="text" name="slug" class="form-control font-monospace" placeholder="e.g. airport_transit">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Icon Class</label>
                            <input type="text" name="icon_class" class="form-control" value="flaticon-hotel" list="iconPresets">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Short Summary *</label>
                            <textarea name="short_description" class="form-control" rows="2" placeholder="Brief summary displayed on services grid card..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Full Detailed Description</label>
                            <textarea name="full_content" class="form-control" rows="4" placeholder="Detailed service description for service-details page..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Quotation / Highlight Callout</label>
                            <input type="text" name="quote_text" class="form-control" placeholder="e.g. Seamless, secure, and punctual transportation tailored to your itinerary.">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Amenities / Features Title</label>
                            <input type="text" name="facilities_title" class="form-control" value="Service Highlights &amp; Features">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active (Visible)</option>
                                <option value="inactive">Inactive (Hidden)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Included Amenities (One per line)</label>
                            <textarea name="facilities_items" class="form-control" rows="4" placeholder="Feature 1&#10;Feature 2&#10;Feature 3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">Create Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<datalist id="iconPresets">
    <option value="flaticon-hotel">
    <option value="flaticon-restaurant">
    <option value="flaticon-resort">
    <option value="flaticon-buildings">
    <option value="bx bx-bed">
    <option value="bx bx-shield-quarter">
    <option value="bx bx-car">
    <option value="bx bx-swim">
    <option value="bx bx-wifi">
</datalist>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
