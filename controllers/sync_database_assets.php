<?php
/**
 * Tiano Hotel - Database Asset & Image Sync
 * Updates services table with real template images and seeds all 34 gallery images from _html_backup/gallery.html
 */
require_once __DIR__ . '/../include/db.php';

header('Content-Type: text/plain');

try {
    echo "Starting DB sync...\n";

    // 1. Update Services Images
    $services_updates = [
        'luxurious_comfortable_accommodation'   => 'assets/img/Comfortable Accommodation.jpg',
        'complimentary_gourmet_breakfast'        => 'assets/img/breakfast-img6.jpg',
        'modern_wellness_recreation_facilities' => 'assets/img/modren wellness.jpg',
        'secured_environment'                   => 'assets/img/Seamless.jpg',
    ];

    $stmtService = $pdo->prepare("UPDATE services SET featured_image = ? WHERE slug = ?");
    foreach ($services_updates as $slug => $img) {
        $stmtService->execute([$img, $slug]);
        echo "Updated service $slug -> $img\n";
    }

    // 2. Ensure room categories have their correct featured images
    $room_updates = [
        'coral-room'        => 'assets/img/room/room-img30.png',
        'gold-room'         => 'assets/img/room/room-img34.png',
        'diamond-room'      => 'assets/img/room/room-img31.png',
        'premium-room'      => 'assets/img/room/room-img32.png',
        'presidential-suite'=> 'assets/img/room/room-img33.png',
        'presidentalsuite'  => 'assets/img/room/room-img33.png',
    ];
    $stmtRoom = $pdo->prepare("UPDATE room_categories SET featured_image = ? WHERE slug = ?");
    foreach ($room_updates as $slug => $img) {
        $stmtRoom->execute([$img, $slug]);
        echo "Updated room $slug -> $img\n";
    }

    // 3. Populate Gallery Table
    // We clear or upsert all 34 images from _html_backup/gallery.html
    $gallery_items = [
        // Rooms & Suites (19 images)
        ['title' => 'Executive Suite Bedroom', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/4-1.png'],
        ['title' => 'Deluxe Suite Interior', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/11.png'],
        ['title' => 'Presidential Bedchamber', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/10.png'],
        ['title' => 'Luxury King Bed', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/room-img35.jpg'],
        ['title' => 'Comfortable Bedroom Setting', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/room-img36.jpg'],
        ['title' => 'Hotel Premium Suite', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/7.png'],
        ['title' => 'Contemporary Suite Lounge', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/18.png'],
        ['title' => 'Executive Room Ambience', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/19.png'],
        ['title' => 'Suite Interior View', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/18-1.png'],
        ['title' => 'Modern Room Layout', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/27.png'],
        ['title' => 'Luxury Guest Bedroom', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/28.png'],
        ['title' => 'Superior Room Decor', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/13.png'],
        ['title' => 'Elegant Guest Space', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/1.png'],
        ['title' => 'Cozy Bed & Desk Area', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/9.png'],
        ['title' => 'Refined Suite Panorama', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/3.png'],
        ['title' => 'Executive Bedroom Lighting', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/23.png'],
        ['title' => 'Master Suite View', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/24.png'],
        ['title' => 'Chic Interior Finish', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/29.png'],
        ['title' => 'Serene Accommodation', 'category' => 'rooms', 'thumb' => 'assets/img/gallery/gallery-img4.jpg'],

        // Restaurant & Dining (10 images)
        ['title' => 'Bar & Beverage Lounge', 'category' => 'dining', 'thumb' => 'assets/img/gallery/4.png'],
        ['title' => 'VIP Cocktail Bar', 'category' => 'dining', 'thumb' => 'assets/img/gallery/5.png'],
        ['title' => 'Fine Dining Table Setting', 'category' => 'dining', 'thumb' => 'assets/img/gallery/12.png'],
        ['title' => 'Executive Lounge Bar', 'category' => 'dining', 'thumb' => 'assets/img/gallery/14.png'],
        ['title' => 'Gourmet Culinary Service', 'category' => 'dining', 'thumb' => 'assets/img/gallery/20.png'],
        ['title' => 'Chef Specialty Dish', 'category' => 'dining', 'thumb' => 'assets/img/gallery/21.png'],
        ['title' => 'Fresh Culinary Preparation', 'category' => 'dining', 'thumb' => 'assets/img/gallery/22.png'],
        ['title' => 'Continental Breakfast Selection', 'category' => 'dining', 'thumb' => 'assets/img/gallery/25.png'],
        ['title' => 'Traditional Nigerian Dishes', 'category' => 'dining', 'thumb' => 'assets/img/gallery/26.png'],
        ['title' => 'Banquet Dining Hall', 'category' => 'dining', 'thumb' => 'assets/img/gallery/2.png'],

        // Outdoor Recreation (5 images)
        ['title' => 'Wellness Spa & Massage', 'category' => 'outdoor', 'thumb' => 'assets/img/gallery/16.png'],
        ['title' => 'Spa Facility & Relaxation', 'category' => 'outdoor', 'thumb' => 'assets/img/gallery/gallery-img16.jpg'],
        ['title' => 'Aromatherapy Suite', 'category' => 'outdoor', 'thumb' => 'assets/img/gallery/17.png'],
        ['title' => 'Sparkling Swimming Pool', 'category' => 'outdoor', 'thumb' => 'assets/img/gallery/pool.jpeg'],
        ['title' => 'Outdoor Bush Bar Cabana', 'category' => 'outdoor', 'thumb' => 'assets/img/gallery/bush_bar_3.jpeg'],
    ];

    $pdo->exec("TRUNCATE TABLE gallery");
    $stmtGal = $pdo->prepare("INSERT INTO gallery (title, category_filter, thumb_url, full_url, caption, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $order = 1;
    foreach ($gallery_items as $item) {
        $stmtGal->execute([
            $item['title'],
            $item['category'],
            $item['thumb'],
            $item['thumb'],
            $item['title'],
            $order++
        ]);
    }
    echo "Inserted " . count($gallery_items) . " gallery items successfully.\n";
    echo "SYNC COMPLETED SUCCESSFULLY!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
