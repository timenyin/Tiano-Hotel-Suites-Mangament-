# Tiano Hotel & Suites — Dynamic CMS Architecture & Automated Image Pipeline

> **Document Type:** Dynamic CMS Specifications, Automated Image Compression Pipeline & Proactive Feature Analysis  
> **Target Project:** `Tiano-Hotel` (Tiano Hotel and Suites, Eket)  
> **Status:** Specification Phase (Review & Approval Required Before Code Execution)  
> **Companion Documents:** [`doc/HOTEL_MANAGEMENT_SYSTEM_SPEC.md`](file:///c:/xampp/htdocs/Tiano-Hotel/doc/HOTEL_MANAGEMENT_SYSTEM_SPEC.md), [`doc/DESIGN_SYSTEM_AND_COMPONENTS.md`](file:///c:/xampp/htdocs/Tiano-Hotel/doc/DESIGN_SYSTEM_AND_COMPONENTS.md)  

---

## Table of Contents
1. [Executive Summary](#1-executive-summary)
2. [Automated Image Compression & Lightbox Pipeline (Gallery Solution)](#2-automated-image-compression--lightbox-pipeline-gallery-solution)
3. [Comprehensive Dynamic Pages Architecture](#3-comprehensive-dynamic-pages-architecture)
   - [3.1 About Page (`about.php`)](#31-about-page-aboutphp)
   - [3.2 Services & Service Details (`services.php`)](#32-services--service-details-servicesphp)
   - [3.3 Rooms & Room Details (`rooms.php`)](#33-rooms--room-details-roomsphp)
   - [3.4 Contact Form & Dual-Notification Engine (`contact.php`)](#34-contact-form--dual-notification-engine-contactphp)
   - [3.5 Newsletter Management (`/admin/newsletter.php`)](#35-newsletter-management-adminnewsletterphp)
   - [3.6 Homepage / Index Dynamic Blocks (`index.php`)](#36-homepage--index-dynamic-blocks-indexphp)
4. [Proactive Strategic Analysis: Essential Unsaid Features](#4-proactive-strategic-analysis-essential-unsaid-features)
5. [Database Schema Additions (CMS & Media)](#5-database-schema-additions-cms--media)

---

## 1. Executive Summary

This specification outlines the full transformation of Tiano Hotel's static pages into a **100% manageable Dynamic Content Management System (CMS)** driven by PHP and MySQL. It directly answers the architectural questions regarding automated image optimization, multi-page content editing, dual email dispatch, and identifies vital real-world hotel operations not yet discussed.

---

## 2. Automated Image Compression & Lightbox Pipeline (Gallery Solution)

### Exact Alignment with Existing `gallery.html` Structure:
The gallery will **NOT** use any new or foreign popup/zoom plugin. It strictly retains the existing proven HTML/JS implementation already working on [`gallery.html`](file:///c:/xampp/htdocs/Tiano-Hotel/gallery.html):

```html
<div class="col-lg-4 col-sm-6">
    <div class="single-gallery">
        <!-- Optimized Grid Thumbnail (~80KB - 100KB) -->
        <img src="assets/img/gallery/thumbs/room-img35_thumb.webp" alt="Tiano Luxury Suite">
        <!-- Broad-View Lightbox Trigger via Magnific Popup with '+' Icon -->
        <a href="assets/img/gallery/full/room-img35_full.webp" class="gallery-icon">
            <i class="bx bx-plus"></i>
        </a>
    </div>
</div>
```
* **Hover Behavior:** Hovering over the image reveals the signature plus icon (`<i class="bx bx-plus"></i>`).
* **Zoom Behavior:** Clicking the plus icon opens the image in a broader view via the pre-initialized **Magnific Popup** (`assets/js/custom.js` line 209: `$('.gallery-view').magnificPopup(...)`).

---

### Cloud & Hosting Storage Space Protection (Zero-Waste Architecture):
A major reason for this pipeline is **avoiding hosting/cloud storage exhaustion**:
* If an admin uploads a raw 3MB to 10MB photo from an iPhone or professional DSLR, **the raw 3MB file is NEVER saved to permanent hosting storage**.
* Instead, the upload handler streams the file directly in memory from PHP's temporary buffer (`$_FILES['image']['tmp_name']`):
  1. **Memory Resampling:** Strips all EXIF metadata and resamples the image pixels in memory.
  2. **Broad-View Lightbox Master:** Compressed to a sharp high-definition master (max width: `1920px`, quality: `82%`, size: **~150 KB – 250 KB**).
  3. **Grid Thumbnail:** Resized for the gallery grid cards (`800x600px`, quality: `80%`, size: **~80 KB – 100 KB**).
  4. **Immediate Raw Cleanup:** The original 3MB+ raw file is immediately purged from memory/temp storage.
* **Storage & Bandwidth Impact:**
  * **95% Disk Space Reduction:** Instead of consuming 500 MB for 100 photos, the entire gallery consumes less than 35 MB of server disk space.
  * **Blazing Fast Mobile Speeds:** Nigerian mobile networks load 100 KB thumbnails in milliseconds, eliminating slow-loading complaints.
* **Category Association:** The admin simply selects a category dropdown (`Rooms & Suites`, `Restaurant & Dining`, `Outdoor Recreation`, or `Bush Bar & Pool`), and the front-end tabs dynamically filter the image.

---

## 3. Comprehensive Dynamic Pages Architecture

Every public page will be managed through the `/admin` portal:

### 3.1 About Page (`about.php`)
* **Editable Sections:**
  * Main Headline & Welcome Text.
  * Our Vision & Our Mission statement cards.
  * Core Hospitality Pillars & 24/7 Service commitments.
  * Facility stats counters (Number of luxury rooms, 24/7 security guarantee, continuous generator power).

### 3.2 Services & Service Details (`services.php`)
* **Master Service Grid (`services.php`):**
  * Displays all active hotel services from the database with icon, summary, and "Read More" button.
* **Individual Service Detail Pages:**
  * Dynamic template: `service-details.php?slug=luxurious_comfortable_accommodation` (or rewrite `/service/luxurious_comfortable_accommodation`).
  * Admin fields: Service Title, Flaticon/Boxicons icon selector, Hero Banner image, Full HTML formatted description, Bulleted feature list, and Sidebar cross-links.

### 3.3 Rooms & Room Details (`rooms.php`)
* **Master Room Directory:** Live rates, room availability tags, quick-book triggers.
* **Individual Room Detail Pages:**
  * Slugs: `coral-room`, `gold-room`, `diamond-room`, `premium-room`, `presidentalsuite`.
  * Dynamic fields: Nightly rate in NGN, bed dimensions (e.g. 7ft × 7ft auto-pedic), guest capacity, multi-photo slider, in-room amenities checklist (Smart TV, AC, safe, balcony), and direct WhatsApp booking link with pre-filled room name.

### 3.4 Contact Form & Dual-Notification Engine (`contact.php`)
* **Form Submission Flow:**
  1. Guest submits Name, Email, Phone, Subject, Message, and accepts Privacy Terms.
  2. Record saved to database table `contact_messages` with status `Unread`.
  3. **Auto-Reply Email to Guest:** Professional HTML confirmation acknowledging receipt and providing hotel reception numbers for urgent inquiries.
  4. **Instant Alert Email to Admin:** Sent immediately to `tianohotel@gmail.com` containing guest details and message body.
  5. **Admin Dashboard Messages Inbox (`/admin/messages.php`):** Shows unread badge count, message preview, and 1-click WhatsApp / Email reply buttons.

### 3.5 Newsletter Management (`/admin/newsletter.php`)
* Public footer newsletter form inserts subscriber emails into database table `newsletter_subscribers`.
* **Admin Dashboard Page:** Dedicated placeholder page at `/admin/newsletter.php` featuring subscriber count, table of registered emails with subscribe dates, and a CSV export button (ready for future campaign broadcasts).

### 3.6 Homepage / Index Dynamic Blocks (`index.php`)
* **Hero Banner:** Customizable main headline, supporting text, and video loop source path.
* **Featured Rooms Carousel:** Admin can toggle which 4 rooms are highlighted on the homepage.
* **FAQ Accordion Block:** Fully dynamic CRUD (Create, Read, Update, Delete) allowing front-desk staff to manage frequently asked questions without editing HTML code.
* **Photo Carousel / Bush Bar Highlights:** Dynamically pulls verified images from the gallery database.

---

## 4. Proactive Strategic Analysis: Essential Unsaid Features

Beyond what was directly mentioned, a high-end hotel in Eket requires the following real-world operational capabilities to run smoothly:

```text
┌────────────────────────────────────────────────────────────────────────┐
│                   UNSAID BUT CRITICAL HOTEL CAPABILITIES                │
├────────────────────────────┬───────────────────────────────────────────┤
│ 1. Walk-In Quick Booking   │ Allows front desk to log walk-in guests   │
│    Form in Dashboard       │ on the fly so online inventory stays sync │
├────────────────────────────┼───────────────────────────────────────────┤
│ 2. Dynamic FAQ Manager     │ Manage FAQs (pool hours, airport shuttle) │
│    (CRUD in Admin)         │ directly without calling a web developer  │
├────────────────────────────┼───────────────────────────────────────────┤
│ 3. Central Media Library   │ Reuse existing room/event photos across   │
│    (Asset Manager)         │ different pages without re-uploading      │
├────────────────────────────┼───────────────────────────────────────────┤
│ 4. Staff Activity Logs     │ Audit trail: records which staff confirmed│
│    (Audit Trail)           │ or cancelled a booking or edited a price  │
├────────────────────────────┼───────────────────────────────────────────┤
│ 5. 1-Click Database Backup │ Emergency SQL/CSV backup button in admin  │
│    & Export Tool           │ for local offline safety at Marina Road   │
├────────────────────────────┼───────────────────────────────────────────┤
│ 6. Top Announcement Banner │ Emergency or promo top-bar toggle         │
│    (Festive / Maintenance) │ (e.g. "Special Easter rates available!") │
└────────────────────────────┴───────────────────────────────────────────┘
```

1. **Walk-In Quick Booking Form in Admin:**
   * In Nigerian hotels, many guests walk in physically without booking online first. If reception cannot log walk-ins into the same dashboard, room availability on the website will be inaccurate, causing double bookings.
2. **Central Media Library (Asset Manager):**
   * Instead of uploading photos repeatedly, the admin can access a centralized media library showing all uploaded images with copyable URLs and thumbnail previews.
3. **Dynamic FAQ Management:**
   * Allows reception to address local inquiries dynamically (e.g., generator power backup schedule, ExxonMobil shuttle proximity, pool opening times).
4. **Staff Activity & Audit Log:**
   * Tracks user logins and operational actions (e.g., *"Receptionist Glory marked Booking #THS-849201 as Paid & Checked-In on Oct 12 at 2:15 PM"*).
5. **Emergency Database Backup & CSV Export:**
   * 1-click export of the entire bookings registry to Excel/CSV for offline reconciliation during local network outages.
6. **Top Announcement Banner Bar:**
   * An optional, dismissible top bar above the navbar to announce corporate discounts, holiday dining menus, or maintenance notices.

---

## 5. Database Schema Additions (CMS & Media)

```sql
-- 1. Gallery Items & Automated Images
CREATE TABLE `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` ENUM('rooms', 'dining', 'outdoor', 'events') NOT NULL,
  `thumb_url` VARCHAR(255) NOT NULL,
  `full_url` VARCHAR(255) NOT NULL,
  `file_size_kb` INT NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Contact Messages & Dual Notifications
CREATE TABLE `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `reply_status` ENUM('pending', 'replied_email', 'replied_whatsapp') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Newsletter Subscribers
CREATE TABLE `newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `status` ENUM('active', 'unsubscribed') DEFAULT 'active',
  `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Dynamic FAQs
CREATE TABLE `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Site Content Key-Value Settings (For About, Hero, Contact Info)
CREATE TABLE `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---
*End of Dynamic CMS & Image Pipeline Specification — Tiano Hotel & Suites.*
