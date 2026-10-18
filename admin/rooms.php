<?php
/**
 * Tiano Hotel & Suites - Rooms, Categories & Housekeeping CMS
 * Full dynamic room inventory, pricing CMS, drag-and-drop photo upload, amenities builder.
 */
$pageTitle = 'Rooms Inventory & Pricing CMS';
$activePage = 'rooms';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Gallery Sliders Map matching room-details.php and public rooms showcase
$room_sliders_map = [
    'coral-room' => [
        'assets/img/room/room-img30.png',
        'assets/img/room/room-img10.jpeg',
        'assets/img/room/room-img36.jpeg',
    ],
    'gold-room' => [
        'assets/img/room/room-img13.jpeg',
        'assets/img/room/room-img12.jpeg',
        'assets/img/room/room-img34.png',
    ],
    'diamond-room' => [
        'assets/img/room/room-img15.jpeg',
        'assets/img/room/room-img37.jpeg',
        'assets/img/room/room-img14.jpeg',
    ],
    'premium-room' => [
        'assets/img/room/room-img18.jpeg',
        'assets/img/room/room-img17.jpeg',
        'assets/img/room/room-img16.jpeg',
    ],
    'presidential-suite' => [
        'assets/img/room/room-img21.jpeg',
        'assets/img/room/room-img20.jpeg',
        'assets/img/room/room-img19.jpeg',
    ],
    'presidentalsuite' => [
        'assets/img/room/room-img21.jpeg',
        'assets/img/room/room-img20.jpeg',
        'assets/img/room/room-img19.jpeg',
    ],
];

// Handle Image Upload Helper
function handleRoomImageUpload($fileInputName) {
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

    $uploadDir = dirname(__DIR__) . '/assets/img/room/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'room_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'assets/img/room/' . $fileName;
    }
    return null;
}

// Handle POST submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Update Physical Room Status
    if ($action === 'update_room') {
        $roomId      = (int)($_POST['room_id'] ?? 0);
        $cleanliness = trim($_POST['cleanliness_status'] ?? 'clean');
        $occupancy   = trim($_POST['occupancy_status'] ?? 'vacant');
        $notes       = trim($_POST['notes'] ?? '');

        try {
            $stmt = $pdo->prepare("UPDATE rooms SET cleanliness_status = ?, occupancy_status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$cleanliness, $occupancy, $notes, $roomId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'update_room_status', 'room', $roomId, "Updated Room status: {$cleanliness}/{$occupancy}");
            $success = "Room status updated successfully.";
        } catch (Exception $e) {
            $error = "Error updating room: " . $e->getMessage();
        }
    }

    // Batch Update Physical Rooms Cleanliness
    if ($action === 'batch_update_rooms') {
        $selectedRoomIds = $_POST['selected_room_ids'] ?? [];
        $batchStatus = trim($_POST['batch_cleanliness_status'] ?? '');
        if (!empty($selectedRoomIds) && !empty($batchStatus) && is_array($selectedRoomIds)) {
            $updated = 0;
            $stmtUp = $pdo->prepare("UPDATE rooms SET cleanliness_status = ?, updated_at = NOW() WHERE id = ?");
            foreach ($selectedRoomIds as $rId) {
                $rId = (int)$rId;
                if ($rId > 0) {
                    $stmtUp->execute([$batchStatus, $rId]);
                    $updated++;
                }
            }
            log_activity($pdo, $admin['id'], $admin['fullname'], 'batch_update_rooms', 'room', null, "Batch updated {$updated} room(s) cleanliness to {$batchStatus}");
            $success = "Successfully updated {$updated} room(s) housekeeping status to " . ucfirst($batchStatus) . ".";
        } else {
            $error = "No rooms or valid status selected.";
        }
    }

    // Batch Delete Physical Rooms
    if ($action === 'batch_delete_rooms') {
        $selectedRoomIds = $_POST['selected_room_ids'] ?? [];
        if (!empty($selectedRoomIds) && is_array($selectedRoomIds)) {
            $deleted = 0;
            $stmtDel = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            foreach ($selectedRoomIds as $rId) {
                $rId = (int)$rId;
                if ($rId > 0) {
                    $stmtDel->execute([$rId]);
                    $deleted++;
                }
            }
            log_activity($pdo, $admin['id'], $admin['fullname'], 'batch_delete_rooms', 'room', null, "Batch deleted {$deleted} physical room(s)");
            $success = "Successfully deleted {$deleted} physical room(s) from inventory.";
        } else {
            $error = "No rooms selected for deletion.";
        }
    }

    // 2. Add New Physical Room
    elseif ($action === 'add_physical_room') {
        $roomNumber = trim($_POST['room_number'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $floor      = (int)($_POST['floor_number'] ?? 1);
        $cleanliness= trim($_POST['cleanliness_status'] ?? 'clean');
        $occupancy  = trim($_POST['occupancy_status'] ?? 'vacant');
        $notes      = trim($_POST['notes'] ?? '');

        if (empty($roomNumber) || $categoryId <= 0) {
            $error = "Room number and category are required.";
        } else {
            try {
                $check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ?");
                $check->execute([$roomNumber]);
                if ($check->fetchColumn() > 0) {
                    $error = "Room number {$roomNumber} already exists.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, category_id, floor_number, cleanliness_status, occupancy_status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$roomNumber, $categoryId, $floor, $cleanliness, $occupancy, $notes]);
                    log_activity($pdo, $admin['id'], $admin['fullname'], 'add_physical_room', 'room', $pdo->lastInsertId(), "Created physical room {$roomNumber}");
                    $success = "Physical room {$roomNumber} added to inventory.";
                }
            } catch (Exception $e) {
                $error = "Error adding room: " . $e->getMessage();
            }
        }
    }

    // 3. Edit Room Category (Price, description, photos, amenities)
    elseif ($action === 'edit_category') {
        $catId       = (int)($_POST['category_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $tagline     = trim($_POST['tagline'] ?? '');
        $price       = (float)($_POST['price_per_night'] ?? 0);
        $adults      = (int)($_POST['capacity_adults'] ?? 2);
        $children    = (int)($_POST['capacity_children'] ?? 1);
        $bedType     = trim($_POST['bed_type'] ?? 'King Bed');
        $roomSize    = trim($_POST['room_size'] ?? '35 sqm');
        $description = trim($_POST['description'] ?? '');
        $amenities   = trim($_POST['amenities'] ?? '');
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        $uploadedImg = handleRoomImageUpload('featured_image');
        $selectedExistingPhoto = trim($_POST['selected_existing_photo'] ?? '');

        if (empty($name) || $price <= 0) {
            $error = "Category name and valid nightly price are required.";
        } else {
            try {
                $newFeaturedImg = $uploadedImg ?: (!empty($selectedExistingPhoto) ? $selectedExistingPhoto : null);

                if ($newFeaturedImg) {
                    $stmt = $pdo->prepare("
                        UPDATE room_categories SET
                            name = ?, tagline = ?, price_per_night = ?, capacity_adults = ?,
                            capacity_children = ?, bed_type = ?, room_size = ?, description = ?,
                            amenities = ?, featured_image = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $tagline, $price, $adults, $children, $bedType,
                        $roomSize, $description, $amenities, $newFeaturedImg, $status, $catId
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE room_categories SET
                            name = ?, tagline = ?, price_per_night = ?, capacity_adults = ?,
                            capacity_children = ?, bed_type = ?, room_size = ?, description = ?,
                            amenities = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name, $tagline, $price, $adults, $children, $bedType,
                        $roomSize, $description, $amenities, $status, $catId
                    ]);
                }

                log_activity($pdo, $admin['id'], $admin['fullname'], 'update_room_category', 'room_category', $catId, "Updated room category: {$name}");
                $success = "Room category '{$name}' updated successfully.";
            } catch (Exception $e) {
                $error = "Error updating category: " . $e->getMessage();
            }
        }
    }

    // 4. Delete Photo from Room Category
    elseif ($action === 'delete_category_photo') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $photoPath = trim($_POST['photo_path'] ?? '');
        if ($catId > 0 && !empty($photoPath)) {
            try {
                $cleanRel = ltrim($photoPath, '/');
                $fullDiskPath = __DIR__ . '/../' . $cleanRel;
                if (strpos($cleanRel, 'uploads/') === 0 && file_exists($fullDiskPath)) {
                    @unlink($fullDiskPath);
                }

                $stmtCat = $pdo->prepare("SELECT id, slug, featured_image, name FROM room_categories WHERE id = ?");
                $stmtCat->execute([$catId]);
                $cRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
                if ($cRow && trim($cRow['featured_image'], '/') === trim($photoPath, '/')) {
                    $fallback = $room_sliders_map[$cRow['slug']][0] ?? 'assets/img/room/room-img30.png';
                    $pdo->prepare("UPDATE room_categories SET featured_image = ? WHERE id = ?")->execute([$fallback, $catId]);
                }

                log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_room_photo', 'room_category', $catId, "Deleted photo '{$photoPath}' from category '{$cRow['name']}'");
                $success = "Photo deleted successfully from room category.";
            } catch (Exception $e) {
                $error = "Error deleting photo: " . $e->getMessage();
            }
        }
    }
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM room_categories ORDER BY price_per_night ASC")->fetchAll();

// Active tab detection
$activeTab = ($_GET['tab'] ?? '') === 'inventory' ? 'inventory' : 'categories';

// Fetch all physical rooms with pagination and sorting
$roomPage = max(1, (int)($_GET['page'] ?? 1));
$roomPerPage = 10;
$roomOffset = ($roomPage - 1) * $roomPerPage;
$totalRooms = (int)$pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$totalRoomPages = ceil($totalRooms / $roomPerPage);

$roomSort = trim($_GET['room_sort'] ?? 'room_asc');
$roomSortMap = [
    'room_asc'    => 'r.room_number ASC',
    'room_desc'   => 'r.room_number DESC',
    'floor_asc'   => 'r.floor_number ASC, r.room_number ASC',
    'floor_desc'  => 'r.floor_number DESC, r.room_number ASC',
    'clean_first' => 'r.cleanliness_status = "clean" DESC, r.room_number ASC',
    'dirty_first' => 'r.cleanliness_status = "dirty" DESC, r.room_number ASC',
    'vacant_first'=> 'r.occupancy_status = "vacant" DESC, r.room_number ASC',
    'rate_desc'   => 'c.price_per_night DESC, r.room_number ASC',
];
$roomOrderSql = $roomSortMap[$roomSort] ?? 'r.floor_number ASC, r.room_number ASC';

$stmtRooms = $pdo->prepare("
    SELECT r.*, c.name AS category_name, c.price_per_night, c.bed_type
    FROM rooms r
    JOIN room_categories c ON r.category_id = c.id
    ORDER BY {$roomOrderSql}
    LIMIT ? OFFSET ?
");
$stmtRooms->bindValue(1, $roomPerPage, PDO::PARAM_INT);
$stmtRooms->bindValue(2, $roomOffset, PDO::PARAM_INT);
$stmtRooms->execute();
$rooms = $stmtRooms->fetchAll();
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

<!-- Header Title -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Rooms &amp; Inventory Operations</h4>
        <p class="text-muted small mb-0">Manage room categories, nightly rates, photos, amenities checklist, and physical room occupancy.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger fw-semibold px-3 py-2" style="background:#E73539; border-radius:10px;" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bx bx-plus-circle me-1"></i> Add Physical Room
        </button>
        <a href="../rooms.php" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2" style="border-radius:10px;">
            <i class="bx bx-globe me-1"></i> View Public Rooms Page
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2 flex-nowrap overflow-auto pb-1" id="roomTabs" role="tablist">
    <li class="nav-item flex-shrink-0" role="presentation">
        <button class="nav-link fw-bold px-4 py-2 rounded-3 <?php echo $activeTab === 'categories' ? 'active' : ''; ?>" id="categories-tab" data-bs-toggle="pill" data-bs-target="#categories-pane" type="button" role="tab">
            <i class="bx bx-bed me-1"></i> Room Categories &amp; Rates CMS
        </button>
    </li>
    <li class="nav-item flex-shrink-0" role="presentation">
        <button class="nav-link fw-bold px-4 py-2 rounded-3 <?php echo $activeTab === 'inventory' ? 'active' : ''; ?>" id="inventory-tab" data-bs-toggle="pill" data-bs-target="#inventory-pane" type="button" role="tab">
            <i class="bx bx-door-open me-1"></i> Physical Room Inventory &amp; Housekeeping (<?php echo $totalRooms; ?> Rooms)
        </button>
    </li>
</ul>

<div class="tab-content" id="roomTabContent">

    <!-- ══════════════════════════════════════════
         TAB 1: ROOM CATEGORIES & RATES CMS
         ══════════════════════════════════════════ -->
    <div class="tab-pane fade <?php echo $activeTab === 'categories' ? 'show active' : ''; ?>" id="categories-pane" role="tabpanel">
        <div class="row g-4">
            <?php foreach ($categories as $cat): 
                $catImg = !empty($cat['featured_image']) ? '../' . ltrim($cat['featured_image'], '/') : '../assets/img/room/room-img1.jpg';
            ?>
                <div class="col-lg-6 col-12">
                    <div class="admin-card h-100 p-4 d-flex flex-column">
                        <div class="d-flex flex-wrap gap-3 align-items-start mb-3">
                            <img src="<?php echo htmlspecialchars($catImg); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="rounded-3 shadow-sm flex-shrink-0" style="width: 130px; height: 95px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($cat['name']); ?></h5>
                                    <span class="badge <?php echo $cat['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?> text-uppercase" style="font-size: 10px;">
                                        <?php echo $cat['status']; ?>
                                    </span>
                                </div>
                                <div class="fs-5 fw-bold text-danger mb-1">
                                    &#8358;<?php echo number_format($cat['price_per_night']); ?> <small class="text-muted fw-normal" style="font-size: 12px;">/ night</small>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="bx bx-bed me-1 text-danger"></i> <?php echo htmlspecialchars($cat['bed_type']); ?> &bull; 
                                    <i class="bx bx-expand me-1 text-danger"></i> <?php echo htmlspecialchars($cat['room_size']); ?> &bull; 
                                    <i class="bx bx-group me-1 text-danger"></i> <?php echo $cat['capacity_adults']; ?> Adults, <?php echo $cat['capacity_children']; ?> Child
                                </small>
                            </div>
                        </div>

                        <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                            <?php echo htmlspecialchars($cat['description']); ?>
                        </p>

                        <!-- Amenities Tags -->
                        <div class="mb-3">
                            <small class="fw-bold text-dark d-block mb-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Included Amenities:</small>
                            <div class="d-flex flex-wrap gap-1">
                                <?php 
                                $amenList = array_filter(array_map('trim', explode(',', $cat['amenities'] ?? '')));
                                foreach ($amenList as $am): ?>
                                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 11px;">
                                        <i class="bx bx-check text-success me-1"></i><?php echo htmlspecialchars($am); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">Slug: <code><?php echo htmlspecialchars($cat['slug']); ?></code></small>
                            <button type="button" class="btn btn-sm btn-danger fw-semibold px-3" style="background:#E73539; border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#editCatModal<?php echo $cat['id']; ?>">
                                <i class="bx bx-edit me-1"></i> Edit Category &amp; Rates
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Edit Category Modal -->
                <div class="modal fade" id="editCatModal<?php echo $cat['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content admin-modal-content">
                            <div class="modal-header admin-modal-header">
                                <h5 class="modal-title fw-bold text-white mb-0">Edit Category: <?php echo htmlspecialchars($cat['name']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="rooms.php" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="edit_category">
                                <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Tier Name *</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Nightly Rate (&#8358;) *</label>
                                            <input type="number" step="0.01" name="price_per_night" class="form-control fw-bold text-danger" value="<?php echo $cat['price_per_night']; ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Tagline / Headline</label>
                                            <input type="text" name="tagline" class="form-control" value="<?php echo htmlspecialchars($cat['tagline'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Bed Type</label>
                                            <input type="text" name="bed_type" class="form-control" value="<?php echo htmlspecialchars($cat['bed_type']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Room Size</label>
                                            <input type="text" name="room_size" class="form-control" value="<?php echo htmlspecialchars($cat['room_size']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Adults Capacity</label>
                                            <input type="number" name="capacity_adults" class="form-control" value="<?php echo (int)$cat['capacity_adults']; ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Children Capacity</label>
                                            <input type="number" name="capacity_children" class="form-control" value="<?php echo (int)$cat['capacity_children']; ?>">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Room Overview &amp; Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($cat['description']); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Amenities (Comma separated)</label>
                                            <textarea name="amenities" class="form-control" rows="3" placeholder="Free High-Speed Wi-Fi, 4K Smart TV with DSTV, Individual Climate Control, Gourmet Breakfast, Rain Shower"><?php echo htmlspecialchars($cat['amenities']); ?></textarea>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="active" <?php echo $cat['status'] === 'active' ? 'selected' : ''; ?>>Active (Available for booking)</option>
                                                <option value="inactive" <?php echo $cat['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                                            </select>
                                        </div>

                                        <!-- Photo Selection & Former Slideshow Gallery -->
                                        <?php
                                        $catSlug = $cat['slug'];
                                        $formerImages = [];
                                        if (!empty($cat['featured_image'])) {
                                            $formerImages[] = $cat['featured_image'];
                                        }
                                        if (isset($room_sliders_map[$catSlug])) {
                                            foreach ($room_sliders_map[$catSlug] as $slideImg) {
                                                if (!in_array($slideImg, $formerImages)) {
                                                    $formerImages[] = $slideImg;
                                                }
                                            }
                                        }
                                        ?>
                                        <div class="col-12">
                                            <div class="p-3 rounded-3 border" style="background:#f8fafc;">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <label class="form-label fw-bold text-dark mb-0">
                                                        <i class="bx bx-images text-danger me-1"></i> Former Room Images &amp; Slideshow Photos
                                                    </label>
                                                    <span class="badge bg-white text-muted border fw-normal" style="font-size:11px;">
                                                        Click any photo to select
                                                    </span>
                                                </div>
                                                <p class="text-muted small mb-3" style="font-size:12px; line-height:1.5;">
                                                    Select any former photo below to update this room category without re-uploading from scratch, or upload a new photo from your device.
                                                </p>

                                                <div class="row g-2 mb-3">
                                                    <?php foreach ($formerImages as $fIdx => $fImg): 
                                                        $isActive = (trim($cat['featured_image'], '/') === trim($fImg, '/'));
                                                        $displayPath = '../' . ltrim($fImg, '/');
                                                    ?>
                                                        <div class="col-md-4 col-sm-6 col-12">
                                                            <div class="former-img-card position-relative rounded-3 overflow-hidden border p-1 text-center transition-all <?php echo $isActive ? 'border-danger shadow-sm bg-white' : 'bg-white border-light-subtle'; ?>" style="cursor: pointer;" onclick="selectFormerPhoto(this, <?php echo $cat['id']; ?>)">
                                                                <input type="radio" name="selected_existing_photo" value="<?php echo htmlspecialchars($fImg); ?>" class="position-absolute opacity-0 former-photo-radio" <?php echo $isActive ? 'checked' : ''; ?>>
                                                                <div class="position-relative">
                                                                    <img src="<?php echo htmlspecialchars($displayPath); ?>" alt="Former Image" class="w-100 rounded-2" style="height: 100px; object-fit: cover;">
                                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 start-0 m-1 rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width:24px; height:24px; z-index:15; border: 1.5px solid #fff;" onclick="event.stopPropagation(); deleteCategoryPhoto('<?php echo htmlspecialchars(addslashes($fImg)); ?>', <?php echo $cat['id']; ?>, '<?php echo htmlspecialchars(addslashes($cat['name'])); ?>')" title="Delete this photo">
                                                                        <i class="bx bx-trash" style="font-size:12px;"></i>
                                                                    </button>
                                                                    <span class="photo-check-badge position-absolute top-0 end-0 m-1 badge rounded-pill <?php echo $isActive ? 'bg-danger text-white' : 'bg-dark bg-opacity-75 text-white'; ?>" style="font-size: 10px;">
                                                                        <i class="bx <?php echo $isActive ? 'bx-check-circle' : 'bx-circle'; ?>"></i> <?php echo $isActive ? 'Current Active' : 'Select Photo'; ?>
                                                                    </span>
                                                                </div>
                                                                <small class="d-block text-truncate text-muted mt-1 px-1" style="font-size: 11px;">
                                                                    <?php echo htmlspecialchars(basename($fImg)); ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                                <!-- Upload New Photo Alternative -->
                                                <div class="pt-2 border-top">
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-md-7">
                                                            <label class="form-label small fw-bold text-muted text-uppercase mb-1">Or Upload A New Photo</label>
                                                            <input type="file" name="featured_image" id="catUpload_<?php echo $cat['id']; ?>" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp" onchange="previewNewCatPhoto(this, <?php echo $cat['id']; ?>)">
                                                            <small class="text-muted d-block mt-1" style="font-size: 11px;">Max 5MB. Formats: JPG, PNG, WEBP.</small>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div id="newPhotoPreview_<?php echo $cat['id']; ?>" class="d-none text-center p-2 rounded-2 border bg-white">
                                                                <small class="text-success fw-bold d-block mb-1"><i class="bx bx-check-circle"></i> New File Selected:</small>
                                                                <img src="" class="rounded-2 shadow-sm" style="max-height: 70px; max-width: 100%; object-fit: cover;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         TAB 2: PHYSICAL INVENTORY & HOUSEKEEPING
         ══════════════════════════════════════════ -->
    <div class="tab-pane fade <?php echo $activeTab === 'inventory' ? 'show active' : ''; ?>" id="inventory-pane" role="tabpanel">
        <div class="admin-card">
            <!-- Toolbar with Count & Sorting -->
            <div class="admin-card-header d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Physical Room Inventory &amp; Housekeeping</h5>
                    <small class="text-muted">Displaying <?php echo count($rooms); ?> of <?php echo $totalRooms; ?> total rooms</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted fw-bold mb-0 text-nowrap"><i class="bx bx-sort"></i> Sort By:</label>
                    <select class="form-select form-select-sm" style="width:180px; border-radius:8px;" onchange="location.href='rooms.php?tab=inventory&room_sort=' + this.value">
                        <option value="room_asc" <?php echo $roomSort === 'room_asc' ? 'selected' : ''; ?>>Room # (Ascending)</option>
                        <option value="room_desc" <?php echo $roomSort === 'room_desc' ? 'selected' : ''; ?>>Room # (Descending)</option>
                        <option value="floor_asc" <?php echo $roomSort === 'floor_asc' ? 'selected' : ''; ?>>Floor (1 &rarr; 10)</option>
                        <option value="clean_first" <?php echo $roomSort === 'clean_first' ? 'selected' : ''; ?>>Clean Rooms First</option>
                        <option value="dirty_first" <?php echo $roomSort === 'dirty_first' ? 'selected' : ''; ?>>Dirty Turnover First</option>
                        <option value="vacant_first" <?php echo $roomSort === 'vacant_first' ? 'selected' : ''; ?>>Vacant Rooms First</option>
                        <option value="rate_desc" <?php echo $roomSort === 'rate_desc' ? 'selected' : ''; ?>>Nightly Rate (High-Low)</option>
                    </select>
                </div>
            </div>

            <!-- Batch Action Bar -->
            <div id="roomsBatchActionBar" class="d-none bg-danger bg-opacity-10 border-bottom border-danger px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 text-danger fw-bold small">
                    <i class="bx bx-check-square fs-5"></i>
                    <span id="roomsSelectedCount">0</span> room(s) selected
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold px-2 py-1" onclick="submitBatchRoomsCleanliness('clean')">
                        <i class="bx bx-check-shield me-1"></i> Mark Clean
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning fw-bold px-2 py-1 text-dark" onclick="submitBatchRoomsCleanliness('dirty')">
                        <i class="bx bx-brush me-1"></i> Mark Dirty
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-2 py-1" onclick="submitBatchRoomsCleanliness('inspected')">
                        <i class="bx bx-search-alt me-1"></i> Mark Inspected
                    </button>
                    <button type="button" class="btn btn-sm btn-danger fw-bold px-2 py-1" onclick="submitBatchDeleteRooms()">
                        <i class="bx bx-trash me-1"></i> Delete Selected
                    </button>
                </div>
            </div>

            <form method="POST" action="rooms.php?tab=inventory&room_sort=<?php echo urlencode($roomSort); ?>&page=<?php echo $roomPage; ?>" id="batchRoomsForm">
                <input type="hidden" name="action" id="batchRoomsAction" value="batch_update_rooms">
                <input type="hidden" name="batch_cleanliness_status" id="batchCleanlinessStatus" value="">

                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px;" class="text-center">
                                    <input type="checkbox" id="masterRoomsCheckbox" class="form-check-input" onclick="toggleAllRooms(this)" title="Select All">
                                </th>
                                <th style="width:50px;">S/N</th>
                                <th>Room #</th>
                                <th>Wing / Category</th>
                                <th>Floor</th>
                                <th>Nightly Rate</th>
                                <th>Housekeeping Status</th>
                                <th>Occupancy</th>
                                <th>Staff Notes</th>
                                <th class="text-end">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rooms)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">No physical rooms found in inventory.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rooms as $rIdx => $r): 
                                    $cleanCls = 'status-badge-success';
                                    if ($r['cleanliness_status'] === 'dirty') $cleanCls = 'status-badge-warning';
                                    elseif ($r['cleanliness_status'] === 'maintenance') $cleanCls = 'status-badge-danger';
                                    elseif ($r['cleanliness_status'] === 'inspected') $cleanCls = 'status-badge-secondary';

                                    $occCls = 'status-badge-success';
                                    if ($r['occupancy_status'] === 'occupied') $occCls = 'status-badge-primary';
                                    elseif ($r['occupancy_status'] === 'reserved') $occCls = 'status-badge-warning';
                                ?>
                                    <tr>
                                        <!-- Checkbox -->
                                        <td class="text-center">
                                            <input type="checkbox" name="selected_room_ids[]" value="<?php echo $r['id']; ?>" class="form-check-input room-row-checkbox" onchange="updateRoomsSelection()">
                                        </td>

                                        <!-- S/N -->
                                        <td>
                                            <span class="badge bg-light text-secondary border fw-bold">#<?php echo ($roomOffset + $rIdx + 1); ?></span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="fs-5 text-dark fw-bold">Room <?php echo htmlspecialchars($r['room_number']); ?></strong>
                                            </div>
                                            <?php 
                                            $isRoomClean = in_array(strtolower($r['cleanliness_status']), ['clean', 'inspected']);
                                            $isRoomVacant = (strtolower($r['occupancy_status']) === 'vacant');
                                            if ($isRoomClean && $isRoomVacant):
                                            ?>
                                                <span class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 rounded-2 border" style="background:#ecfdf5; border-color:#a7f3d0 !important; color:#065f46 !important; font-size:11px; font-weight:600; line-height:1.2;">
                                                    <i class="bx bx-check-shield text-success"></i> Ready for Check-In
                                                </span>
                                            <?php elseif ($r['occupancy_status'] === 'occupied'): ?>
                                                <span class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 rounded-2 border" style="background:#dbeafe; border-color:#bfdbfe !important; color:#1e40af !important; font-size:11px; font-weight:600; line-height:1.2;">
                                                    <i class="bx bx-user-check text-primary"></i> Guest In-House
                                                </span>
                                            <?php elseif ($r['cleanliness_status'] === 'dirty'): ?>
                                                <span class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 rounded-2 border" style="background:#fef3c7; border-color:#fde68a !important; color:#78350f !important; font-size:11px; font-weight:700; line-height:1.2;">
                                                    <i class="bx bx-brush" style="color:#d97706 !important;"></i> Cleaning Required
                                                </span>
                                            <?php elseif ($r['cleanliness_status'] === 'maintenance'): ?>
                                                <span class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 rounded-2 border" style="background:#fee2e2; border-color:#fecaca !important; color:#991b1b !important; font-size:11px; font-weight:700; line-height:1.2;">
                                                    <i class="bx bx-wrench text-danger"></i> Out of Order
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($r['category_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($r['bed_type']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">Floor <?php echo $r['floor_number']; ?></span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            &#8358;<?php echo number_format($r['price_per_night']); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $cleanCls; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo htmlspecialchars(ucfirst($r['cleanliness_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $occCls; ?>">
                                                <span class="status-dot"></span>
                                                <?php echo htmlspecialchars(ucfirst($r['occupancy_status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($r['notes'] ?: 'No notes'); ?></small>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-semibold px-2" style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#updateRoomModal<?php echo $r['id']; ?>">
                                                <i class="bx bx-check-square me-1"></i> Update
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Update Modal -->
                                    <div class="modal fade" id="updateRoomModal<?php echo $r['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content admin-modal-content">
                                                <div class="modal-header admin-modal-header">
                                                    <h5 class="modal-title fw-bold text-white mb-0">Update Room <?php echo htmlspecialchars($r['room_number']); ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="rooms.php?tab=inventory&room_sort=<?php echo urlencode($roomSort); ?>&page=<?php echo $roomPage; ?>">
                                                    <input type="hidden" name="action" value="update_room">
                                                    <input type="hidden" name="room_id" value="<?php echo $r['id']; ?>">
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Cleanliness / Housekeeping Status</label>
                                                            <select class="form-select" name="cleanliness_status" required>
                                                                <option value="clean" <?php echo $r['cleanliness_status'] === 'clean' ? 'selected' : ''; ?>>Clean (Ready for Guest)</option>
                                                                <option value="dirty" <?php echo $r['cleanliness_status'] === 'dirty' ? 'selected' : ''; ?>>Dirty (Turnover Needed)</option>
                                                                <option value="inspected" <?php echo $r['cleanliness_status'] === 'inspected' ? 'selected' : ''; ?>>Inspected &amp; Sanitized</option>
                                                                <option value="maintenance" <?php echo $r['cleanliness_status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance / Out of Order</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Occupancy Status</label>
                                                            <select class="form-select" name="occupancy_status" required>
                                                                <option value="vacant" <?php echo $r['occupancy_status'] === 'vacant' ? 'selected' : ''; ?>>Vacant (Available)</option>
                                                                <option value="occupied" <?php echo $r['occupancy_status'] === 'occupied' ? 'selected' : ''; ?>>Occupied (In-House Guest)</option>
                                                                <option value="reserved" <?php echo $r['occupancy_status'] === 'reserved' ? 'selected' : ''; ?>>Reserved (Awaiting Arrival)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Housekeeping &amp; Staff Notes</label>
                                                            <textarea class="form-control" name="notes" rows="3" placeholder="e.g. Extra pillows provided, bathroom light serviced..."><?php echo htmlspecialchars($r['notes'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light p-3">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">Save Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Inventory Table Pagination -->
            <?php echo render_admin_pagination($roomPage, $totalRoomPages, $totalRooms, $roomPerPage, ['tab' => 'inventory', 'room_sort' => $roomSort]); ?>
        </div>
    </div>

</div>

<!-- Modal: Add Physical Room -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Add Physical Room to Inventory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="rooms.php?tab=inventory">
                <input type="hidden" name="action" value="add_physical_room">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Room Number *</label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. 106, 204" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Floor Level</label>
                            <input type="number" name="floor_number" class="form-control" value="1" min="1" max="10">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Room Category / Wing *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?> (&#8358;<?php echo number_format($c['price_per_night']); ?>/night)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cleanliness</label>
                            <select name="cleanliness_status" class="form-select">
                                <option value="clean">Clean (Ready)</option>
                                <option value="dirty">Dirty (Cleaning Needed)</option>
                                <option value="inspected">Inspected</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Occupancy</label>
                            <select name="occupancy_status" class="form-select">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Initial Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Balcony overlooking pool..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">Add Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAllRooms(master) {
    const checkboxes = document.querySelectorAll('.room-row-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateRoomsSelection();
}

function updateRoomsSelection() {
    const checkboxes = document.querySelectorAll('.room-row-checkbox:checked');
    const totalCheckboxes = document.querySelectorAll('.room-row-checkbox');
    const count = checkboxes.length;
    const bar = document.getElementById('roomsBatchActionBar');
    const countSpan = document.getElementById('roomsSelectedCount');
    const master = document.getElementById('masterRoomsCheckbox');

    if (countSpan) countSpan.textContent = count;
    if (bar) {
        if (count > 0) {
            bar.classList.remove('d-none');
        } else {
            bar.classList.add('d-none');
        }
    }
    if (master) {
        master.checked = (totalCheckboxes.length > 0 && count === totalCheckboxes.length);
    }
}

function submitBatchRoomsCleanliness(status) {
    const count = document.querySelectorAll('.room-row-checkbox:checked').length;
    if (count === 0) return;
    tianoConfirm(
        'Update Housekeeping Status',
        `Change housekeeping status of ${count} selected room(s) to "${status.toUpperCase()}"?`,
        'Update Status',
        function() {
            document.getElementById('batchRoomsAction').value = 'batch_update_rooms';
            document.getElementById('batchCleanlinessStatus').value = status;
            document.getElementById('batchRoomsForm').submit();
        }
    );
}

function submitBatchDeleteRooms() {
    const count = document.querySelectorAll('.room-row-checkbox:checked').length;
    if (count === 0) return;
    tianoConfirm(
        'Delete Physical Rooms',
        `Are you sure you want to permanently delete the ${count} selected physical room(s) from inventory?`,
        'Delete Rooms',
        function() {
            document.getElementById('batchRoomsAction').value = 'batch_delete_rooms';
            document.getElementById('batchRoomsForm').submit();
        }
    );
}

function selectFormerPhoto(cardElem, catId) {
    const modal = document.getElementById('editCatModal' + catId);
    if (!modal) return;
    
    // Clear selections in this modal
    modal.querySelectorAll('.former-img-card').forEach(card => {
        card.classList.remove('border-danger', 'shadow-sm');
        card.classList.add('border-light-subtle');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = false;
        const badge = card.querySelector('.photo-check-badge');
        if (badge) {
            badge.className = 'photo-check-badge position-absolute top-0 end-0 m-1 badge rounded-pill bg-dark bg-opacity-75 text-white';
            badge.innerHTML = '<i class="bx bx-circle"></i> Select Photo';
        }
    });

    // Mark clicked card as selected
    cardElem.classList.remove('border-light-subtle');
    cardElem.classList.add('border-danger', 'shadow-sm');
    const radio = cardElem.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
    const badge = cardElem.querySelector('.photo-check-badge');
    if (badge) {
        badge.className = 'photo-check-badge position-absolute top-0 end-0 m-1 badge rounded-pill bg-danger text-white';
        badge.innerHTML = '<i class="bx bx-check-circle"></i> Selected';
    }

    // Reset file input if user picks an existing photo
    const fileInput = document.getElementById('catUpload_' + catId);
    if (fileInput) fileInput.value = '';
    const previewBox = document.getElementById('newPhotoPreview_' + catId);
    if (previewBox) previewBox.classList.add('d-none');
}

function previewNewCatPhoto(inputElem, catId) {
    const previewBox = document.getElementById('newPhotoPreview_' + catId);
    if (!previewBox) return;
    if (inputElem.files && inputElem.files[0]) {
        const file = inputElem.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = previewBox.querySelector('img');
            if (img) img.src = e.target.result;
            previewBox.classList.remove('d-none');

            // Uncheck former photos in modal since file was selected
            const modal = document.getElementById('editCatModal' + catId);
            if (modal) {
                modal.querySelectorAll('.former-img-card').forEach(card => {
                    card.classList.remove('border-danger', 'shadow-sm');
                    card.classList.add('border-light-subtle');
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) radio.checked = false;
                    const badge = card.querySelector('.photo-check-badge');
                    if (badge) {
                        badge.className = 'photo-check-badge position-absolute top-0 end-0 m-1 badge rounded-pill bg-dark bg-opacity-75 text-white';
                        badge.innerHTML = '<i class="bx bx-circle"></i> Select Photo';
                    }
                });
            }
        };
        reader.readAsDataURL(file);
    } else {
        previewBox.classList.add('d-none');
    }
}

function deleteCategoryPhoto(photoPath, catId, catName) {
    tianoConfirm(
        'Delete Room Photo',
        `Are you sure you want to permanently delete this photo from "${catName}"? If it is currently active, a default photo will be assigned.`,
        'Delete Photo',
        function() {
            document.getElementById('delPhotoCatId').value = catId;
            document.getElementById('delPhotoPath').value = photoPath;
            document.getElementById('delPhotoForm').submit();
        }
    );
}
</script>

<!-- Hidden form for deleting room category photos -->
<form method="POST" action="rooms.php?tab=categories" id="delPhotoForm" class="d-none">
    <input type="hidden" name="action" value="delete_category_photo">
    <input type="hidden" name="category_id" id="delPhotoCatId" value="">
    <input type="hidden" name="photo_path" id="delPhotoPath" value="">
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
