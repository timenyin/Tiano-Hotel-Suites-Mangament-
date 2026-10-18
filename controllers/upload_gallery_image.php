<?php
/**
 * Tiano Hotel and Suites - Zero-Waste In-Memory Media Pipeline (Image & Video)
 * 
 * Handles:
 * 1. Image Compression (JPEG, PNG, WebP -> Resampled WebP Thumb & Broad Master)
 * 2. Video Processing (MP4, WebM, MOV -> Validated & Streamed for Soundless Endless Loop Playback)
 */

require_once __DIR__ . '/../include/db.php';

function processGalleryUpload($file, $title, $category, $caption, $pdo) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errCode = $file['error'] ?? 'missing';
        $msg = 'Upload failed with error code: ' . $errCode;
        if ($errCode === UPLOAD_ERR_INI_SIZE || $errCode === UPLOAD_ERR_FORM_SIZE) {
            $msg = 'File exceeds server upload size limit.';
        }
        return ['success' => false, 'message' => $msg];
    }

    $file_tmp  = $file['tmp_name'];
    $file_size = (int)$file['size'];
    $file_name = $file['name'];

    // Ensure schema has media_type column
    try {
        $pdo->exec("ALTER TABLE gallery ADD COLUMN media_type ENUM('image', 'video') DEFAULT 'image' AFTER category_filter");
    } catch (Exception $e) {}

    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_tmp);
    finfo_close($finfo);

    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $image_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $video_mimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-matroska'];
    $video_exts  = ['mp4', 'webm', 'ogv', 'mov', 'mkv'];

    // -----------------------------------------------------------------
    // 1. VIDEO HANDLING
    // -----------------------------------------------------------------
    if (in_array($mime_type, $video_mimes) || in_array($ext, $video_exts)) {
        $videos_dir = __DIR__ . '/../assets/img/uploads/gallery/videos';
        if (!is_dir($videos_dir)) {
            mkdir($videos_dir, 0755, true);
        }

        // 50MB reasonable video upload cap
        $max_video_size = 50 * 1024 * 1024;
        if ($file_size > $max_video_size) {
            @unlink($file_tmp);
            return ['success' => false, 'message' => 'Video file exceeds maximum allowed size of 50MB.'];
        }

        $unique_id = 'tiano_vid_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
        $safe_ext = in_array($ext, ['mp4', 'webm', 'ogv']) ? $ext : 'mp4';
        $video_filename = $unique_id . '.' . $safe_ext;
        $target_filepath = $videos_dir . '/' . $video_filename;

        if (!move_uploaded_file($file_tmp, $target_filepath)) {
            return ['success' => false, 'message' => 'Failed to save video to storage directory.'];
        }

        $rel_video_url = 'assets/img/uploads/gallery/videos/' . $video_filename;

        // Record video in gallery table
        try {
            $stmt = $pdo->prepare("
                INSERT INTO gallery (title, category_filter, media_type, thumb_url, full_url, caption, sort_order, status, created_at)
                VALUES (?, ?, 'video', ?, ?, ?, 0, 'active', NOW())
            ");
            $stmt->execute([$title, $category, $rel_video_url, $rel_video_url, $caption]);
            
            return [
                'success' => true,
                'message' => 'Video uploaded successfully! It will play in a continuous soundless loop on the front end gallery.',
                'media_type' => 'video',
                'url' => $rel_video_url
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error saving video: ' . $e->getMessage()];
        }
    }

    // -----------------------------------------------------------------
    // 2. IMAGE COMPRESSION (Zero-Waste WebP)
    // -----------------------------------------------------------------
    if (in_array($mime_type, $image_mimes)) {
        $thumbs_dir = __DIR__ . '/../assets/img/uploads/gallery/thumbs';
        $full_dir   = __DIR__ . '/../assets/img/uploads/gallery/full';
        if (!is_dir($thumbs_dir)) mkdir($thumbs_dir, 0755, true);
        if (!is_dir($full_dir)) mkdir($full_dir, 0755, true);

        $image_data = file_get_contents($file_tmp);
        $src_img = @imagecreatefromstring($image_data);
        unset($image_data);

        if (!$src_img) {
            @unlink($file_tmp);
            return ['success' => false, 'message' => 'Failed to process raw image data in memory.'];
        }

        imagealphablending($src_img, true);
        imagesavealpha($src_img, true);

        $orig_w = imagesx($src_img);
        $orig_h = imagesy($src_img);

        $unique_id = 'tiano_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
        $thumb_filename = $unique_id . '_thumb.webp';
        $full_filename  = $unique_id . '_full.webp';
        $thumb_filepath = $thumbs_dir . '/' . $thumb_filename;
        $full_filepath  = $full_dir . '/' . $full_filename;

        // Broadband lightbox master (Max 1920)
        $max_full_dim = 1920;
        if ($orig_w > $max_full_dim || $orig_h > $max_full_dim) {
            if ($orig_w >= $orig_h) {
                $full_w = $max_full_dim;
                $full_h = (int)round(($orig_h / $orig_w) * $max_full_dim);
            } else {
                $full_h = $max_full_dim;
                $full_w = (int)round(($orig_w / $orig_h) * $max_full_dim);
            }
        } else {
            $full_w = $orig_w;
            $full_h = $orig_h;
        }

        $full_canvas = imagecreatetruecolor($full_w, $full_h);
        imagealphablending($full_canvas, false);
        imagesavealpha($full_canvas, true);
        imagecopyresampled($full_canvas, $src_img, 0, 0, 0, 0, $full_w, $full_h, $orig_w, $orig_h);
        imagewebp($full_canvas, $full_filepath, 82);
        imagedestroy($full_canvas);

        // Center-cropped Thumbnail (800x600)
        $target_tw = 800;
        $target_th = 600;
        $src_aspect = $orig_w / $orig_h;
        $tgt_aspect = $target_tw / $target_th;

        if ($src_aspect > $tgt_aspect) {
            $crop_h = $orig_h;
            $crop_w = (int)round($orig_h * $tgt_aspect);
            $crop_x = (int)round(($orig_w - $crop_w) / 2);
            $crop_y = 0;
        } else {
            $crop_w = $orig_w;
            $crop_h = (int)round($orig_w / $tgt_aspect);
            $crop_x = 0;
            $crop_y = (int)round(($orig_h - $crop_h) / 2);
        }

        $thumb_canvas = imagecreatetruecolor($target_tw, $target_th);
        imagealphablending($thumb_canvas, false);
        imagesavealpha($thumb_canvas, true);
        imagecopyresampled($thumb_canvas, $src_img, 0, 0, $crop_x, $crop_y, $target_tw, $target_th, $crop_w, $crop_h);
        imagewebp($thumb_canvas, $thumb_filepath, 80);
        imagedestroy($thumb_canvas);
        imagedestroy($src_img);

        if (file_exists($file_tmp)) {
            @unlink($file_tmp);
        }

        $rel_thumb_url = 'assets/img/uploads/gallery/thumbs/' . $thumb_filename;
        $rel_full_url  = 'assets/img/uploads/gallery/full/' . $full_filename;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO gallery (title, category_filter, media_type, thumb_url, full_url, caption, sort_order, status, created_at)
                VALUES (?, ?, 'image', ?, ?, ?, 0, 'active', NOW())
            ");
            $stmt->execute([$title, $category, $rel_thumb_url, $rel_full_url, $caption]);

            return [
                'success' => true,
                'message' => 'Image compressed into WebP & published to gallery!',
                'media_type' => 'image',
                'thumb_url' => $rel_thumb_url,
                'full_url' => $rel_full_url
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error saving image: ' . $e->getMessage()];
        }
    }

    return ['success' => false, 'message' => 'Unsupported format (' . $mime_type . '). Please upload JPG, PNG, WebP images or MP4, WebM videos.'];
}

// Standalone AJAX endpoint support
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && !isset($included_via_admin)) {
    header('Content-Type: application/json; charset=utf-8');
    $title    = !empty($_POST['title']) ? trim($_POST['title']) : 'Tiano Hotel Showcase';
    $category = !empty($_POST['category_filter']) ? trim($_POST['category_filter']) : 'rooms';
    $caption  = !empty($_POST['caption']) ? trim($_POST['caption']) : '';
    $res = processGalleryUpload($_FILES['image'], $title, $category, $caption, $pdo);
    echo json_encode($res);
    exit;
}
