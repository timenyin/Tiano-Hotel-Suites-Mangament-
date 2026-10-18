<?php
/**
 * Tiano Hotel and Suites - Zero-Waste Image Pipeline Live Test Console
 */
require_once __DIR__ . '/../include/functions.php';

$page_title = 'Zero-Waste Image Upload Pipeline Test | Tiano Hotel & Suites';
$page_desc  = 'Test in-memory image compression pipeline and preview WebP assets.';
$active_page = 'gallery';

require_once __DIR__ . '/../include/head-seo.php';
require_once __DIR__ . '/../include/topbar.php';
require_once __DIR__ . '/../include/navbar.php';

$recent_items = get_gallery_items($pdo, 'all', 6);
?>

<div class="inner-banner inner-bg1">
    <div class="container">
        <div class="inner-title">
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li><a href="../gallery.php">Gallery</a></li>
                <li><i class="bx bx-chevron-right"></i></li>
                <li>Pipeline Tester</li>
            </ul>
            <h3>Zero-Waste Image Pipeline Test Console</h3>
        </div>
    </div>
</div>

<div class="container my-5 py-3">
    
    <!-- Header Notice -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm p-4 rounded-4" style="background:#f8f9fa; border-left: 5px solid #E73539 !important;">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-shield text-danger fs-1 me-3"></i>
                    <div>
                        <h4 class="mb-1 text-dark">Phase 5: Automated Zero-Waste In-Memory Compression</h4>
                        <p class="text-muted mb-0 small">
                            Raw 3MB–10MB high-res photos are streamed directly into memory via PHP GD, instantly compressed into <strong>~80–100 KB WebP thumbnails</strong> and <strong>~150–250 KB broad-view lightbox masters</strong>, and raw files are deleted immediately with zero server disk waste.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        
        <!-- Left: Upload Test Form -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white h-100">
                <h4 class="mb-3 text-dark fw-bold"><i class="bx bx-cloud-upload text-danger me-2"></i> Upload & Compress Image</h4>
                
                <div id="uploadFeedback" class="alert d-none mb-3" role="alert"></div>

                <form id="imagePipelineForm" action="../controllers/upload_gallery_image.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Image Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Royal Presidential Balcony" required value="Luxury Penthouse Suite Preview">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Gallery Category Filter *</label>
                        <select name="category_filter" class="form-select form-control" required>
                            <option value="rooms">Rooms & Suites</option>
                            <option value="facilities">Pool & Wellness</option>
                            <option value="dining">Dining & Bush Bar</option>
                            <option value="exterior">Exterior & Grounds</option>
                            <option value="events">Events & Banqueting</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Caption (Optional)</label>
                        <input type="text" name="caption" class="form-control" placeholder="e.g. Panoramic sunset view over Marina Road">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark">Select Raw Image (JPEG, PNG, or WebP) *</label>
                        <input type="file" name="image" id="fileInput" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                        <small class="text-muted d-block mt-1">Upload any photo (even 5MB–10MB) to witness real-time zero-waste compression.</small>
                    </div>

                    <button type="submit" id="uploadSubmitBtn" class="default-btn btn-bg-three w-100 py-3 rounded-3 fs-6">
                        <i class="bx bx-bolt-circle me-1"></i> Compress & Save to Live Gallery
                    </button>
                </form>

                <!-- Live Result Statistics Card -->
                <div id="statsCard" class="card mt-4 border-0 p-3 rounded-3 d-none" style="background:#eafaf1; border:1px solid #c3e6cb !important;">
                    <h6 class="text-success fw-bold mb-2"><i class="bx bx-check-circle me-1"></i> Pipeline Execution Results:</h6>
                    <ul class="list-unstyled mb-0 small text-dark">
                        <li class="mb-1">&bull; Original Raw Upload: <strong id="statOrig"></strong></li>
                        <li class="mb-1">&bull; Compressed Grid Thumb (800x600): <strong id="statThumb" class="text-primary"></strong></li>
                        <li class="mb-1">&bull; Broad-View Lightbox Master: <strong id="statFull" class="text-primary"></strong></li>
                        <li class="mb-1">&bull; Net Storage Saved: <strong id="statSaved" class="text-success fs-6"></strong></li>
                    </ul>
                    <div class="mt-3 text-center">
                        <a href="../gallery.php" class="btn btn-sm btn-outline-dark fw-bold">
                            <i class="bx bx-images me-1"></i> View in Live Public Gallery
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right: Live Magnific Popup Preview Box -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-lg rounded-4 p-4 bg-white h-100">
                <h4 class="mb-3 text-dark fw-bold"><i class="bx bx-show text-danger me-2"></i> Live Lightbox Preview</h4>
                <p class="text-muted small">
                    When you upload an image, it will appear below in the exact Magnific Popup format used on <code>gallery.php</code>:
                </p>

                <div class="gallery-view mt-3 text-center" id="livePreviewContainer">
                    <div class="single-gallery shadow-sm rounded-3 overflow-hidden">
                        <img id="previewImg" src="../assets/img/gallery/room-img35.jpg" alt="Preview" style="width:100%; height:280px; object-fit:cover;">
                        <a id="previewLink" href="../assets/img/gallery/room-img35.jpg" class="gallery-icon" title="Magnific Popup Preview">
                            <i class="bx bx-plus"></i>
                        </a>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted" id="previewCaption">Current: Default showcase preview (Click the plus icon to test lightbox)</small>
                </div>

                <hr class="my-4">

                <h6 class="text-muted small text-uppercase fw-bold mb-3">Recently Added Photos</h6>
                <div class="row g-2 gallery-view">
                    <?php foreach ($recent_items as $rec): ?>
                        <div class="col-4">
                            <div class="single-gallery rounded overflow-hidden" style="height:75px;">
                                <img src="../<?php echo htmlspecialchars($rec['thumb_url']); ?>" alt="<?php echo htmlspecialchars($rec['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <a href="../<?php echo htmlspecialchars($rec['full_url']); ?>" class="gallery-icon" title="<?php echo htmlspecialchars($rec['title']); ?>">
                                    <i class="bx bx-plus" style="font-size:14px;"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("imagePipelineForm");
    const submitBtn = document.getElementById("uploadSubmitBtn");
    const alertBox = document.getElementById("uploadFeedback");
    const statsCard = document.getElementById("statsCard");

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Compressing In-Memory...';
        alertBox.className = "alert d-none";
        statsCard.classList.add("d-none");

        const formData = new FormData(form);

        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bx bx-bolt-circle me-1"></i> Compress & Save to Live Gallery';
            alertBox.classList.remove("d-none");

            if (data.status === "success") {
                alertBox.className = "alert alert-success";
                alertBox.innerText = data.message;

                // Update stats card
                document.getElementById("statOrig").innerText = data.stats.original_size_kb + " KB";
                document.getElementById("statThumb").innerText = data.stats.thumb_size_kb + " KB";
                document.getElementById("statFull").innerText = data.stats.full_size_kb + " KB";
                document.getElementById("statSaved").innerText = data.stats.storage_savings_pct + " reduction";
                statsCard.classList.remove("d-none");

                // Update live preview
                document.getElementById("previewImg").src = "../" + data.thumb_url;
                document.getElementById("previewLink").href = "../" + data.full_url;
                document.getElementById("previewLink").title = data.title;
                document.getElementById("previewCaption").innerText = "Newly compressed: " + data.title + " (" + data.thumb_url + ")";

                // Re-initialize magnific popup
                $('.gallery-view').magnificPopup({
                    delegate: 'a',
                    type: 'image',
                    gallery: { enabled: true }
                });

                form.reset();
            } else {
                alertBox.className = "alert alert-danger";
                alertBox.innerText = data.message;
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bx bx-bolt-circle me-1"></i> Compress & Save to Live Gallery';
            alertBox.classList.remove("d-none");
            alertBox.className = "alert alert-danger";
            alertBox.innerText = "Error executing pipeline: " + err;
        });
    });
});
</script>

<?php require_once __DIR__ . '/../include/footer.php'; ?>
