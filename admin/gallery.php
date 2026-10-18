<?php
/**
 * Tiano Hotel & Suites - Gallery CMS & Zero-Waste Image Uploader
 */
$pageTitle = 'Gallery CMS & Media';
$activePage = 'gallery';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Self-healing migration: Ensure gallery table has media_type column
try {
    $pdo->exec("ALTER TABLE gallery ADD COLUMN media_type ENUM('image', 'video') DEFAULT 'image' AFTER category_filter");
} catch (Exception $e) {}

// Handle Image Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_image') {
    $imgId = (int)($_POST['image_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$imgId]);
    $img = $stmt->fetch();

    if ($img) {
        // Remove files if custom uploads
        if (strpos($img['thumb_url'], 'uploads/') !== false && file_exists(__DIR__ . '/../' . $img['thumb_url'])) {
            @unlink(__DIR__ . '/../' . $img['thumb_url']);
        }
        if (strpos($img['full_url'], 'uploads/') !== false && file_exists(__DIR__ . '/../' . $img['full_url'])) {
            @unlink(__DIR__ . '/../' . $img['full_url']);
        }
        $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$imgId]);
        $success = "Gallery image removed.";
    }
}

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    $title    = trim($_POST['title'] ?? '');
    $category = trim($_POST['category_filter'] ?? 'rooms');
    $caption  = trim($_POST['caption'] ?? '');

    if (empty($title) || !isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please provide an image title and select a valid photo file.";
    } else {
        require_once __DIR__ . '/../controllers/upload_gallery_image.php';
        $result = processGalleryUpload($_FILES['photo'], $title, $category, $caption, $pdo);
        if ($result['success']) {
            $success = "Image uploaded and compressed in memory (Zero-Waste WebP)!";
        } else {
            $error = $result['message'];
        }
    }
}

$galleryItems = $pdo->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
?>

<!-- Alerts -->
<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bx bx-check-circle fs-5"></i>
        <div><?php echo htmlspecialchars($success); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bx bx-error-circle fs-5"></i>
        <div><?php echo htmlspecialchars($error); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Zero-Waste Gallery Media CMS</h4>
        <p class="text-muted small mb-0">High-performance media management: WebP image compression (~80KB) and soundless looping HD videos.</p>
    </div>
    <button type="button" class="btn btn-danger fw-bold rounded-pill px-3" style="background:#E73539;" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bx bx-cloud-upload me-1"></i> Upload Photo / Video
    </button>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Upload &amp; Optimize Gallery Media</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="gallery.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_image">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Media Title</label>
                        <input type="text" class="form-control" name="title" required placeholder="e.g. VIP Penthouse Living Area or Poolside Drone Tour">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Category Filter</label>
                        <select class="form-select" name="category_filter" required>
                            <option value="rooms">Rooms &amp; Suites</option>
                            <option value="facilities">Pool &amp; Recreation</option>
                            <option value="dining">Dining &amp; Bush Bar</option>
                            <option value="exterior">Premises &amp; Architecture</option>
                            <option value="events">Events &amp; Hall</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Select Photo or Video File</label>
                        <input type="file" class="form-control" name="photo" accept="image/*,video/mp4,video/webm,video/quicktime" required>
                        <small class="text-muted d-block mt-1">
                            <i class="bx bx-info-circle me-1"></i>Photos are automatically compressed into ~80KB WebP. Videos (MP4, WebM up to 50MB) play in continuous soundless loops on the front end.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Caption / Description</label>
                        <input type="text" class="form-control" name="caption" placeholder="Short description for guests">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">
                        <i class="bx bx-upload me-1"></i> Upload &amp; Publish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Gallery Grid -->
<div class="row g-3">
    <?php foreach ($galleryItems as $item): 
        $isVideo = (($item['media_type'] ?? 'image') === 'video' || preg_match('/\.(mp4|webm|ogv|mov)$/i', $item['full_url']));
    ?>
        <div class="col-sm-6 col-md-4 col-xl-3">
            <div class="admin-card h-100">
                <div style="height: 180px; overflow: hidden; position: relative; background: #0f172a;">
                    <?php if ($isVideo): ?>
                        <video autoplay muted loop playsinline style="width:100%; height:100%; object-fit:cover;">
                            <source src="../<?php echo htmlspecialchars($item['full_url']); ?>" type="video/mp4">
                        </video>
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2 text-uppercase d-inline-flex align-items-center gap-1 shadow-sm" style="font-size:10px; z-index:2;">
                            <i class="bx bx-video"></i> Video
                        </span>
                    <?php else: ?>
                        <img src="../<?php echo htmlspecialchars($item['thumb_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php endif; ?>
                    <span class="badge bg-dark position-absolute top-0 start-0 m-2 text-uppercase" style="font-size:10px; opacity:0.85;">
                        <?php echo htmlspecialchars($item['category_filter']); ?>
                    </span>
                </div>
                <div class="p-3">
                    <h6 class="fw-bold mb-1 text-dark text-truncate"><?php echo htmlspecialchars($item['title']); ?></h6>
                    <small class="text-muted d-block text-truncate mb-2"><?php echo htmlspecialchars($item['caption'] ?: 'No caption'); ?></small>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="../<?php echo htmlspecialchars($item['full_url']); ?>" target="_blank" class="btn btn-sm btn-light border py-0 px-2" style="font-size:11px;">
                            <i class="bx bx-expand-alt"></i> Preview
                        </a>
                        <form method="POST" action="gallery.php" class="d-inline" id="delGal_<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="image_id" value="<?php echo $item['id']; ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" title="Delete image" onclick="tianoConfirm('Remove Media', 'Remove <?php echo htmlspecialchars(addslashes($item['title'])); ?> from the gallery?', 'Delete Media', function() { document.getElementById('delGal_<?php echo $item['id']; ?>').submit(); });">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
