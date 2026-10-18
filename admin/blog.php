<?php
/**
 * Tiano Hotel & Suites - Journal & Blog Articles CMS
 * Manage, publish, edit, and optimize SEO articles.
 */
$pageTitle = 'Journal & Blog CMS';
$activePage = 'blog';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Handle Image Upload Helper
function handleBlogImageUpload($fileInputName) {
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

    $uploadDir = dirname(__DIR__) . '/assets/img/blog/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'blog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'assets/img/blog/' . $fileName;
    }
    return null;
}

// Slug Helper
function generateSlug($text) {
    $slug = preg_replace('~[^\pL\d]+~u', '-', $text);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);
    return empty($slug) ? 'post-' . time() : $slug;
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. Create Article
    if ($action === 'create_post') {
        $title       = trim($_POST['title'] ?? '');
        $customSlug  = trim($_POST['slug'] ?? '');
        $slug        = !empty($customSlug) ? generateSlug($customSlug) : generateSlug($title);
        $category    = trim($_POST['category'] ?? 'Hotel Experience');
        $author      = trim($_POST['author_name'] ?? $admin['fullname']);
        $excerpt     = trim($_POST['excerpt'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $seoTitle    = trim($_POST['seo_title'] ?? '');
        $seoDesc     = trim($_POST['seo_description'] ?? '');
        $seoKeywords = trim($_POST['seo_keywords'] ?? '');
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        $uploadedImg = handleBlogImageUpload('featured_image');
        $featuredImg = $uploadedImg ?: trim($_POST['existing_image'] ?? 'assets/img/blog/blog-item-img1.jpg');

        if (empty($title) || empty($content)) {
            $error = 'Article title and body content are required.';
        } else {
            try {
                // Ensure unique slug
                $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
                $checkSlug->execute([$slug]);
                if ($checkSlug->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts (
                        title, slug, excerpt, content, featured_image, category,
                        author_name, seo_title, seo_description, seo_keywords,
                        status, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $title, $slug, $excerpt, $content, $featuredImg, $category,
                    $author, $seoTitle, $seoDesc, $seoKeywords, $status
                ]);
                $newId = $pdo->lastInsertId();

                log_activity($pdo, $admin['id'], $admin['fullname'], 'create_blog_post', 'blog_post', $newId, "Published article: {$title}");
                $success = "Article '{$title}' created successfully.";
            } catch (Exception $e) {
                $error = "Error creating article: " . $e->getMessage();
            }
        }
    }

    // 2. Edit Article
    elseif ($action === 'edit_post') {
        $postId      = (int)($_POST['post_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $customSlug  = trim($_POST['slug'] ?? '');
        $slug        = !empty($customSlug) ? generateSlug($customSlug) : generateSlug($title);
        $category    = trim($_POST['category'] ?? 'Hotel Experience');
        $author      = trim($_POST['author_name'] ?? $admin['fullname']);
        $excerpt     = trim($_POST['excerpt'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $seoTitle    = trim($_POST['seo_title'] ?? '');
        $seoDesc     = trim($_POST['seo_description'] ?? '');
        $seoKeywords = trim($_POST['seo_keywords'] ?? '');
        $status      = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        $uploadedImg = handleBlogImageUpload('featured_image');

        if (empty($title) || empty($content)) {
            $error = 'Article title and content cannot be empty.';
        } else {
            try {
                // Ensure unique slug (excluding current post)
                $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
                $checkSlug->execute([$slug, $postId]);
                if ($checkSlug->fetchColumn() > 0) {
                    $slug .= '-' . time();
                }

                if ($uploadedImg) {
                    $stmt = $pdo->prepare("
                        UPDATE blog_posts SET
                            title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
                            category = ?, author_name = ?, seo_title = ?, seo_description = ?,
                            seo_keywords = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $slug, $excerpt, $content, $uploadedImg, $category,
                        $author, $seoTitle, $seoDesc, $seoKeywords, $status, $postId
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE blog_posts SET
                            title = ?, slug = ?, excerpt = ?, content = ?,
                            category = ?, author_name = ?, seo_title = ?, seo_description = ?,
                            seo_keywords = ?, status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $title, $slug, $excerpt, $content, $category,
                        $author, $seoTitle, $seoDesc, $seoKeywords, $status, $postId
                    ]);
                }

                log_activity($pdo, $admin['id'], $admin['fullname'], 'update_blog_post', 'blog_post', $postId, "Updated article: {$title}");
                $success = "Article updated successfully.";
            } catch (Exception $e) {
                $error = "Error updating article: " . $e->getMessage();
            }
        }
    }

    // 3. Delete Article
    elseif ($action === 'delete_post') {
        $postId = (int)($_POST['post_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'delete_blog_post', 'blog_post', $postId, "Deleted article ID {$postId}");
            $success = "Article deleted successfully.";
        } catch (Exception $e) {
            $error = "Error deleting article: " . $e->getMessage();
        }
    }

    // 4. Toggle Status
    elseif ($action === 'toggle_status') {
        $postId = (int)($_POST['post_id'] ?? 0);
        $newStatus = $_POST['new_status'] === 'draft' ? 'draft' : 'published';
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$newStatus, $postId]);
            log_activity($pdo, $admin['id'], $admin['fullname'], 'toggle_blog_status', 'blog_post', $postId, "Changed status to {$newStatus}");
            $success = "Article status set to {$newStatus}.";
        } catch (Exception $e) {
            $error = "Error changing status: " . $e->getMessage();
        }
    }
}

// Fetch all articles with pagination
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$countSql = "SELECT COUNT(*) FROM blog_posts WHERE 1=1";
$params = [];

if (!empty($search)) {
    $countSql .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

if (!empty($categoryFilter)) {
    $countSql .= " AND category = ?";
    $params[] = $categoryFilter;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

$sql = "SELECT * FROM blog_posts WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
}
if (!empty($categoryFilter)) {
    $sql .= " AND category = ?";
}
$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";

$stmt = $pdo->prepare($sql);
$bindIdx = 1;
if (!empty($search)) {
    $like = "%{$search}%";
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
    $stmt->bindValue($bindIdx++, $like, PDO::PARAM_STR);
}
if (!empty($categoryFilter)) {
    $stmt->bindValue($bindIdx++, $categoryFilter, PDO::PARAM_STR);
}
$stmt->bindValue($bindIdx++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($bindIdx++, $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Distinct categories for filter
$allCategories = $pdo->query("SELECT DISTINCT category FROM blog_posts WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);
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
        <h4 class="fw-bold mb-1 text-dark">Journal &amp; Blog CMS</h4>
        <p class="text-muted small mb-0">Publish SEO-rich travel guides, local experiences, and hotel news to boost Google ranking.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger fw-semibold px-3 py-2" style="background:#E73539; border-radius:8px;" data-bs-toggle="modal" data-bs-target="#newPostModal">
            <i class="bx bx-plus-circle me-1"></i> New Article
        </button>
        <a href="../blog.php" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2" style="border-radius:8px;">
            <i class="bx bx-globe me-1"></i> View Live Blog
        </a>
    </div>
</div>

<!-- Filter Bar with Matching 46px Height and Premium Styling -->
<div class="admin-card mb-4 p-3">
    <form method="GET" action="blog.php" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by title, author, or content..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select" onchange="this.form.submit()" style="height:46px; border-radius:10px;">
                <option value="">-- All Categories --</option>
                <?php foreach ($allCategories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark fw-bold px-4 w-100" style="border-radius:10px; height:46px;">
                <i class="bx bx-filter-alt me-1"></i> Filter
            </button>
            <?php if (!empty($search) || !empty($categoryFilter)): ?>
                <a href="blog.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="border-radius:10px; height:46px; width:46px;" title="Reset Filter">
                    <i class="bx bx-x fs-5"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Articles Table -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 70px;">Cover</th>
                    <th>Article Details</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bx bx-news fs-3 d-block mb-1"></i>
                            No articles found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($posts as $p): 
                        $isPub = ($p['status'] === 'published');
                        $cover = !empty($p['featured_image']) ? '../' . ltrim($p['featured_image'], '/') : '../assets/img/blog/blog-item-img1.jpg';
                    ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($cover); ?>" alt="Cover" class="rounded-3 shadow-sm" style="width: 58px; height: 44px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1" style="font-size: 14px; max-width: 320px;"><?php echo htmlspecialchars($p['title']); ?></div>
                                <small class="text-muted font-monospace" style="font-size: 11px;">/<?php echo htmlspecialchars($p['slug']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($p['category']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="fw-semibold text-secondary"><?php echo htmlspecialchars($p['author_name']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    <i class="bx bx-show me-1"></i><?php echo (int)$p['views_count']; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="blog.php" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="new_status" value="<?php echo $isPub ? 'draft' : 'published'; ?>">
                                    <button type="submit" class="status-badge <?php echo $isPub ? 'status-badge-success' : 'status-badge-warning'; ?> border-0" style="cursor: pointer;" title="Click to toggle status">
                                        <span class="status-dot"></span>
                                        <?php echo ucfirst($p['status']); ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('d M Y', strtotime($p['created_at'])); ?></small>
                            </td>
                            <td class="text-end align-middle">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions &amp; Options">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; min-width:180px;">
                                        <li>
                                            <a class="dropdown-item" href="../blog-details.php?slug=<?php echo urlencode($p['slug']); ?>" target="_blank">
                                                <i class="bx bx-link-external text-secondary me-2"></i> View Article
                                            </a>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#editPostModal<?php echo $p['id']; ?>">
                                                <i class="bx bx-edit me-2"></i> Edit Article
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal<?php echo $p['id']; ?>">
                                                <i class="bx bx-trash me-2"></i> Delete Article
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPostModal<?php echo $p['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content admin-modal-content">
                                    <div class="modal-header admin-modal-header">
                                        <h5 class="modal-title fw-bold text-white mb-0">Edit Article: <?php echo htmlspecialchars($p['title']); ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="blog.php" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="edit_post">
                                        <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Article Title *</label>
                                                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($p['title']); ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">URL Slug</label>
                                                    <input type="text" name="slug" class="form-control font-monospace" value="<?php echo htmlspecialchars($p['slug']); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                                                    <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($p['category']); ?>" list="catList">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Author Name</label>
                                                    <input type="text" name="author_name" class="form-control" value="<?php echo htmlspecialchars($p['author_name']); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="published" <?php echo $p['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                        <option value="draft" <?php echo $p['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Change Featured Image</label>
                                                    <input type="file" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                                    <small class="text-muted">Current: <?php echo htmlspecialchars($p['featured_image']); ?></small>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Short Excerpt (1-2 sentences)</label>
                                                    <textarea name="excerpt" class="form-control" rows="2"><?php echo htmlspecialchars($p['excerpt']); ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Article Content (HTML supported) *</label>
                                                    <textarea name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($p['content']); ?></textarea>
                                                </div>

                                                <!-- SEO Panel -->
                                                <div class="col-12 mt-3 pt-3 border-top">
                                                    <span class="fw-bold text-danger text-uppercase small d-block mb-2">
                                                        <i class="bx bx-search-alt me-1"></i> Google &amp; WhatsApp SEO Meta (Optional)
                                                    </span>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <input type="text" name="seo_title" class="form-control form-control-sm" placeholder="Custom SEO Title (Under 60 chars)" value="<?php echo htmlspecialchars($p['seo_title'] ?? ''); ?>">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="text" name="seo_keywords" class="form-control form-control-sm" placeholder="SEO Keywords (comma-separated)" value="<?php echo htmlspecialchars($p['seo_keywords'] ?? ''); ?>">
                                                        </div>
                                                        <div class="col-12">
                                                            <input type="text" name="seo_description" class="form-control form-control-sm" placeholder="Custom Meta Description (140-160 chars for Google rich snippet)" value="<?php echo htmlspecialchars($p['seo_description'] ?? ''); ?>">
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

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deletePostModal<?php echo $p['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-body p-4 text-center">
                                        <i class="bx bx-trash text-danger mb-2" style="font-size: 44px;"></i>
                                        <h5 class="fw-bold mb-2">Delete Article?</h5>
                                        <p class="small text-muted mb-4">Are you sure you want to delete &ldquo;<?php echo htmlspecialchars($p['title']); ?>&rdquo;? This action cannot be undone.</p>
                                        <form method="POST" action="blog.php">
                                            <input type="hidden" name="action" value="delete_post">
                                            <input type="hidden" name="post_id" value="<?php echo $p['id']; ?>">
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

    <!-- Blog Table Pagination -->
    <?php echo render_admin_pagination($page, $totalPages, $totalRecords, $perPage, ['search' => $search, 'category' => $categoryFilter]); ?>
</div>

<!-- Create New Article Modal -->
<div class="modal fade" id="newPostModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-modal-content">
            <div class="modal-header admin-modal-header">
                <h5 class="modal-title fw-bold text-white mb-0">Publish New Blog Article</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="blog.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_post">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Article Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Inside the Bush Bar: Fresh Grilled Delights at Tiano Hotel" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">URL Slug (Leave blank to auto-generate)</label>
                            <input type="text" name="slug" class="form-control font-monospace" placeholder="e.g. inside-the-bush-bar-grilled-delights">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Culinary Experience" list="catList">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Author Name</label>
                            <input type="text" name="author_name" class="form-control" value="<?php echo htmlspecialchars($admin['fullname']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Publish Status</label>
                            <select name="status" class="form-select">
                                <option value="published">Published (Visible immediately)</option>
                                <option value="draft">Draft (Saved privately)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Featured Cover Image</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Recommended dimension: 1200x630px for WhatsApp &amp; Facebook previews.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Short Excerpt (Lead summary for cards)</label>
                            <textarea name="excerpt" class="form-control" rows="2" placeholder="Brief summary of the article (approx. 1-2 sentences)..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Article Body (HTML formatting supported) *</label>
                            <textarea name="content" class="form-control" rows="8" placeholder="Write full article here. Use &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt; for rich styling..." required></textarea>
                        </div>

                        <!-- SEO Expansion -->
                        <div class="col-12 mt-3 pt-3 border-top">
                            <span class="fw-bold text-danger text-uppercase small d-block mb-2">
                                <i class="bx bx-search-alt me-1"></i> Google &amp; WhatsApp SEO Meta (Optional)
                            </span>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="seo_title" class="form-control form-control-sm" placeholder="Custom SEO Meta Title (Under 60 chars)">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="seo_keywords" class="form-control form-control-sm" placeholder="SEO Keywords (e.g. Eket hotel, dining, tourism)">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="seo_description" class="form-control form-control-sm" placeholder="Custom Meta Description (140-160 chars for Google rich snippet)">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold" style="background:#E73539;">Publish Article</button>
                </div>
            </form>
        </div>
    </div>
</div>

<datalist id="catList">
    <option value="Hotel Experience">
    <option value="Travel & Tourism">
    <option value="Business Travel">
    <option value="Culinary & Dining">
    <option value="Wellness & Leisure">
    <option value="News & Events">
</datalist>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
