# Tiano Hotel & Suites — Complete Project & Architecture Documentation

> **Document Type:** Project Scope Analysis, Business Profile & Complete File Structure Audit  
> **Target Project:** `Tiano-Hotel` (Website for Tiano Hotel and Suites)  
> **Physical Location:** 44 Marina Road, Eket, Akwa Ibom State, Nigeria  
> **Official Website:** [https://www.tianohotel.com](https://www.tianohotel.com) / [https://www.tianohotelandsuites.com](https://www.tianohotelandsuites.com)  
> **Author & Date:** System Audit & Documentation | October 2026  

---

## Table of Contents
1. [Executive Summary & Project Overview](#1-executive-summary--project-overview)
2. [Real-World Business Scope & Online Market Intelligence](#2-real-world-business-scope--online-market-intelligence)
3. [Core Hotel Features, Rooms & Pricing Matrix](#3-core-hotel-features-rooms--pricing-matrix)
4. [Services & Amenities Breakdown](#4-services--amenities-breakdown)
5. [Complete Project File Structure](#5-complete-project-file-structure)
6. [Asset Dependency & Connectivity Audit](#6-asset-dependency--connectivity-audit)
   - [Connected Assets](#61-connected-assets)
   - [Disconnected & Orphaned Assets](#62-disconnected--orphaned-assets)
   - [Case Sensitivity & Broken Link Vulnerabilities](#63-case-sensitivity--broken-link-vulnerabilities)
7. [Technical Architecture & Deployment Stack](#7-technical-architecture--deployment-stack)
8. [Actionable Recommendations & Roadmap](#8-actionable-recommendations--roadmap)

---

## 1. Executive Summary & Project Overview

**Tiano Hotel and Suites** is an upscale, luxury boutique hospitality establishment situated at **44 Marina Road, Eket, Akwa Ibom State, Nigeria**. Positioned in one of the primary commercial and oil/gas corridors of the Niger Delta region, the hotel delivers world-class accommodation, top-tier security, and recreational facilities catering to business executives, international expatriates, and discerning leisure travelers.

The web application is a multi-page static website built using **HTML5, CSS3, Vanilla JavaScript, jQuery, and Bootstrap 5**, styled with custom stylesheets derived from a commercial hotel template (*Atoli by HiBootstrap*) and customized with bespoke branding, localized photography, video integration, and direct reservation funnels (WhatsApp and direct telephone booking).

### Key Business Identity
* **Establishment Name:** Tiano Hotel and Suites (often styled as *Tiano Suites*)
* **Address:** 44 Marina Road, Eket, Akwa Ibom State, Nigeria
* **Contact Phone:** `+234 814 473 4612` / `08144734612`
* **Official Email:** `tianohotel@gmail.com`
* **Social Media Handles:**
  * Facebook: `@TianoSuites`
  * Instagram: `@TianoSuites`
  * X (Twitter): Linked in footer
* **Primary Booking Channels:** Direct phone call, WhatsApp reservation modal (`https://wa.me/2348144734612`), and Netlify-backed contact form.

---

## 2. Real-World Business Scope & Online Market Intelligence

An analysis of the property and its online presence across Nigerian travel directories (Hotels.ng, BookRight.ng, EaseMyTrip, and Google Search) reveals the following operational details:

1. **Strategic Regional Role:**
   * Eket is home to major oil exploration operations (notably ExxonMobil’s operational base at Qua Iboe Terminal in Ibeno). Tiano Hotel was conceived to fill the market need for high-security, premium-comfort lodging with international standards.
2. **Room Capacity & Property Scale:**
   * The hotel features approximately **50 guest rooms and luxury suites**.
3. **Core Operational Policies:**
   * **Check-In Time:** 1:00 PM – 2:00 PM.
   * **Check-Out Time:** 12:00 Noon.
   * **Late Check-Out:** Check-outs after 6:00 PM incur a 50% room rate charge.
   * **External F&B Policy:** Outside food and drinks are strictly prohibited on premises to uphold sanitation and security standards.
   * **Payment Methods:** Cash, Debit/Credit Card (POS), Direct Bank Transfer.
4. **Distinctive Value Propositions:**
   * **24/7 Uninterrupted Power Supply:** Redundant generators ensuring constant air conditioning and smart system uptime.
   * **High Security Infrastructure:** Armed/trained security personnel, 24/7 CCTV surveillance, perimeter defense, and bullet-resistant doors installed in executive and presidential suites.
   * **Smart Tech & Energy Efficiency:** Kinetic-energy powered smart showers, beauty lighting systems, access-control biometric/keycard doors.

---

## 3. Core Hotel Features, Rooms & Pricing Matrix

The website showcases 5 distinct room categories, each targeting a specific budget and luxury requirement.

| Room Category | Nightly Rate (NGN) | Bed Specifications | Guest Capacity | Key Highlights & In-Room Amenities | Dedicated Detail Page |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Coral Room** | **₦35,000** | Small Double Bed (4ft × 6ft) | 1–2 Guests | Air conditioning, high-speed Wi-Fi, Smart TV, reading desk & chair, en-suite bathroom, 24/7 room service, wardrobe, toiletries. | [`coral-room.html`](file:///c:/xampp/htdocs/Tiano-Hotel/coral-room.html) |
| **Gold Room** | **₦40,000** | Queen Size Bed (6ft × 7ft) | 1–2 Guests | City view, sitting area, mini-fridge, tea/coffee maker, premium bedding, work desk, electronic wardrobe safe, Smart TV. | [`gold-room.html`](file:///c:/xampp/htdocs/Tiano-Hotel/gold-room.html) |
| **Diamond Room** | **₦50,000** | King Size Bed (7ft × 7ft) Auto-pedic | 1–3 Guests | Auto-pedic luxury mattress, ergonomic workspace, access-control door lock, 55" Smart TV, executive toiletries, mini-bar, ironing set. | [`diamond-room.html`](file:///c:/xampp/htdocs/Tiano-Hotel/diamond-room.html) |
| **Premium Room** | **₦65,000** | King Size Bed (7ft × 7ft) Auto-pedic | 2–4 Guests | Reinforced security entrance door, stylish lounge/seating area, 24/7 butler service, mini-bar, in-room safe deposit box, 55" Smart TV. | [`premium-room.html`](file:///c:/xampp/htdocs/Tiano-Hotel/premium-room.html) |
| **Presidential Suite** | **₦250,000** | Master King Bed (7ft × 7ft) Auto-pedic | VIP / Executive | **Bullet-resistant reinforced entrance doors**, private 2-person parlor with private entertainment system, **kinetic-energy powered smart shower**, professional beauty lighting system (for grooming/photography), private kitchenette, Jacuzzi & rain shower, walk-in closet, private scenic balcony, dedicated butler/concierge. | [`presidentalsuite.html`](file:///c:/xampp/htdocs/Tiano-Hotel/presidentalsuite.html) |

---

## 4. Services & Amenities Breakdown

The website dedicates extensive sections and landing pages to the hotel's four foundational service pillars:

### 1. Luxurious & Comfortable Accommodation
* **Landing Page:** [`luxurious_comfortable_accommodation.html`](file:///c:/xampp/htdocs/Tiano-Hotel/luxurious_comfortable_accommodation.html)
* **Scope:** Details the interior aesthetics, ergonomic furniture, silent climate control, posture-supporting auto-pedic bedding, and privacy-oriented layout for each room.

### 2. Complimentary Gourmet Breakfast & Dining
* **Landing Page:** [`complimentary_gourmet_breakfast.html`](file:///c:/xampp/htdocs/Tiano-Hotel/complimentary_gourmet_breakfast.html)
* **Scope:** Free gourmet breakfast buffet provided to all in-house guests, prepared daily by in-house chefs featuring both indigenous Akwa Ibom delicacies (such as Afang, Edikang Ikong on request/restaurant menu) and continental breakfast classics, freshly brewed coffee, teas, juices, and pastries.

### 3. Modern Wellness, Spa & Recreation Facilities
* **Landing Page:** [`modern_wellness_recreation_facilities.html`](file:///c:/xampp/htdocs/Tiano-Hotel/modern_wellness_recreation_facilities.html)
* **Scope:** Covers on-premise wellness and leisure:
  * Full-size outdoor swimming pool for adults and children.
  * Fully equipped gymnasium with cardio machines and weights.
  * Outdoor Bush Bar for relaxed evening socializing and drinks.
  * Modern spa treatments and hygienic locker/changing facilities.
  * Professional laundry and dry-cleaning services.

### 4. Seamless Connectivity & Secured Environment
* **Landing Page:** [`secured_environment.html`](file:///c:/xampp/htdocs/Tiano-Hotel/secured_environment.html)
* **Scope:** 
  * High-speed fiber-optic Wi-Fi covering all rooms, lobbies, and poolside.
  * 24/7 uniformed and plainclothes security personnel with perimeter monitoring.
  * Comprehensive closed-circuit television (CCTV) coverage.
  * Dedicated, well-lit, and monitored secure on-site vehicle parking.
  * Advanced fire-safety detection, smoke sensors, and emergency response plans.

---

## 5. Complete Project File Structure

The following tree represents the entire file and directory architecture of `c:\xampp\htdocs\Tiano-Hotel\`:

```text
Tiano-Hotel/
├── .agents/                                  # Workspace agent configurations & rules
│   └── rules/
│       └── seo.md                            # SEO, Search Console & Social Preview rules
├── doc/                                      # Project documentation & system specifications
│   ├── about_tiano_hotel.md                  # Comprehensive business & code audit
│   ├── about_hotel.md                        # Quick reference pointer
│   ├── HOTEL_MANAGEMENT_SYSTEM_SPEC.md       # Full backend & reservation system spec
│   └── DESIGN_SYSTEM_AND_COMPONENTS.md       # Front-end design token & admin UI blueprint
├── netlify.toml                              # Netlify deployment configuration & redirect rules
├── tmp_results.txt                           # Temporary build / search cache file
│
├── index.html                                # Homepage (Hero video, rooms, services, FAQ, booking modal)
├── about.html                                # About Us (Vision, mission, company background)
├── room.html                                 # Rooms catalog & rate sheet
├── coral-room.html                           # Coral Room details page (₦35,000)
├── gold-room.html                            # Gold Room details page (₦40,000)
├── diamond-room.html                         # Diamond Room details page (₦50,000)
├── premium-room.html                         # Premium Room details page (₦65,000)
├── presidentalsuite.html                     # Presidential Suite details page (₦250,000)
├── services.html                             # Main services directory
├── luxurious_comfortable_accommodation.html  # Service detail: Accommodation
├── complimentary_gourmet_breakfast.html      # Service detail: Dining & Breakfast
├── modern_wellness_recreation_facilities.html# Service detail: Gym, Pool & Recreation
├── secured_environment.html                  # Service detail: Security & Wi-Fi
├── gallery.html                              # Photo gallery with filtering and lightboxes
├── contact.html                              # Contact page with Netlify contact form & Google Map
├── privacy-policy.html                       # Privacy policy documentation
├── terms-condition.html                      # Terms & conditions, booking and cancellation rules
├── testimonials.html                         # [Template Remnant] Uncustomized Atoli testimonial page
├── coming-soon.html                          # [Template Remnant] Unused template holding page
│
└── assets/                                   # Static assets directory
    ├── css/                                  # Stylesheets
    │   ├── animate.min.css                   # CSS3 animation keyframes
    │   ├── bootstrap.min.css                 # Bootstrap 5 grid and base styles
    │   ├── boxicons.min.css                  # Boxicons font stylesheet
    │   ├── jquery-ui.css                     # jQuery UI styling (datepicker, accordions)
    │   ├── magnific-popup.css                # Lightbox modal styling for gallery
    │   ├── meanmenu.css                      # Mobile navigation responsive stylesheet
    │   ├── nice-select.min.css               # Styled select dropdown elements
    │   ├── owl.carousel.min.css              # Owl Carousel core layout
    │   ├── owl.theme.default.min.css         # Owl Carousel default theme
    │   ├── responsive.css                    # Custom responsive media query breakpoints
    │   ├── style.css                         # Primary hotel theme stylesheet (127 KB)
    │   ├── theme-dark.css                    # Dark mode overrides
    │   └── owl.video.play.html               # Template video icon fragment
    │
    ├── fonts/                                # Font and icon definitions
    │   ├── flaticon.css                      # Flaticon stylesheet (hotel icons)
    │   ├── Flaticon.eot, .svg, .ttf, .woff, .woff2
    │   └── boxicons.eot, .svg, .ttf, .woff, .woff2
    │
    ├── js/                                   # Client-side scripts
    │   ├── bootstrap.bundle.min.js           # Bootstrap 5 JavaScript & Popper
    │   ├── contact-form-script.js            # Contact form AJAX submission logic
    │   ├── custom.js                         # Custom initialization (nav, sliders, sticky bar)
    │   ├── form-validator.min.js             # Form validation library
    │   ├── jquery.ajaxchimp.min.js           # MailChimp newsletter subscription bridge
    │   ├── jquery.magnific-popup.min.js      # Lightbox popup modal plugin
    │   ├── jquery.min.js                     # Core jQuery 3.x library
    │   ├── jquery.nice-select.min.js         # Dropdown select enhancement
    │   ├── jquery-ui.js                      # jQuery UI library
    │   ├── meanmenu.js                       # Mobile navigation menu generator
    │   ├── owl.carousel.min.js               # Responsive carousel slider plugin
    │   └── wow.min.js                        # Scroll animation trigger library
    │
    ├── video/                                # Media assets
    │   └── hotel_room.mp4                    # Hero banner autoplay video (6.7 MB)
    │
    └── img/                                  # Image assets (15 subdirectories + root images)
        ├── compnay_logo.JPG                  # Official Tiano Hotel & Suites primary logo
        ├── favicon.ico                       # Website browser favicon
        ├── thumbnail.jpg                     # Exterior/room preview thumbnail
        ├── home_screen.jpeg                  # Hero banner static image
        ├── pool.jpeg                         # Swimming pool real photograph
        ├── bush_bar_1, 2, 3.jpeg             # Outdoor bush bar real photographs
        ├── hotel_build, hotel_build_two, etc.# Exterior architectural photos
        ├── Comfortable Accommodation.jpg     # Accommodation service hero
        ├── modren wellness.jpg               # Wellness service hero
        ├── Seamless.jpg                      # Network/security service hero
        │
        ├── about/                            # About page images (about-img4.jpeg, etc.)
        ├── contact/                          # Contact page images (contact_services.jpg, etc.)
        ├── gallery/                          # 70 gallery images (rooms, pool, bar, events)
        ├── logos/                            # SVG & PNG brand logos (logo-1.svg, footer logos)
        ├── room/                             # 48 room photos (room-img10.png through 39)
        ├── inner-banner/                     # Banner background images for inner pages
        ├── faq/                              # FAQ section images
        ├── shape/                            # Graphic decorative curve shapes
        ├── specialty/                        # Specialty card backgrounds
        │
        ├── blog/                             # [ORPHANED] 14 unused template blog images
        ├── restaurant/                       # [ORPHANED] 24 unused template food & beverage images
        ├── team/                             # [ORPHANED] 6 unused template staff portrait images
        ├── testimonials/                     # [ORPHANED] 7 template testimonial profile photos
        ├── home-two/                         # [ORPHANED] 3 unused template alternative home images
        └── reservation/                      # [ORPHANED] 1 unused template reservation image
```

---

## 6. Asset Dependency & Connectivity Audit

This section answers the critical operational question: **What is connected to the website and what is disconnected (orphaned)?**

### 6.1. Connected Assets
* **Core Branding:**
  * `assets/img/compnay_logo.JPG` — Used in booking modals and mission/vision sliders.
  * `assets/img/logos/logo-1.svg` & `footer-logo2.svg` — Used in header navbar and footer across all pages.
  * `assets/img/favicon.ico` — Linked across all production pages `<link rel="icon">`.
* **Primary Media:**
  * `assets/video/hotel_room.mp4` — Integrated into `index.html` via HTML5 `<video autoplay loop muted playsinline>`.
* **Room Media:**
  * `assets/img/room/room-img10.png` through `room-img35.png` — Connected to `room.html`, `coral-room.html`, `gold-room.html`, `diamond-room.html`, `premium-room.html`, `presidentalsuite.html`, and `index.html`.
* **Gallery Collection:**
  * `assets/img/gallery/1.png` through `29.png`, plus authentic WhatsApp photos (`WhatsApp Image 2026-01-05...`, `pool2.jpg`, `bush_bar_3.jpeg`) — Connected to the filtered tabs in `gallery.html`.
* **Service Banners:**
  * `Comfortable Accommodation.jpg`, `modren wellness.jpg`, `Seamless.jpg`, `contact_services.jpg`, and `bush_bar_1.jpeg` are actively bound to service cards and headers.

### 6.2. Disconnected & Orphaned Assets
During the audit, multiple directories containing unused files from the original template were identified:

1. **`assets/img/blog/` (14 files — ~800 KB):**
   * Files: `blog-details-img1.jpg`, `blog-img1.jpg`–`blog-img4.jpg`, `blog-item-img1.jpg`–`blog-item-img6.jpg`, `blog-profile1.jpg`, `blog-profile2.jpg`.
   * **Status:** Completely unreferenced across all HTML files. No active blog exists on the site.
2. **`assets/img/restaurant/` (24 files — ~1.8 MB):**
   * Files: `breakfast-img1.jpg`–`6`, `dinner-img1.jpg`–`6`, `lunch-img1.jpg`–`6`, `drink-img1.jpg`–`6`.
   * **Status:** Completely unreferenced. While the hotel offers gourmet breakfast and dining, none of these 24 high-resolution images are referenced in `complimentary_gourmet_breakfast.html` or `services.html`.
3. **`assets/img/team/` (6 files — ~280 KB):**
   * Files: `team-img1.jpg` through `team-img6.jpg`.
   * **Status:** Completely unreferenced. No team/staff section is present in `about.html`.
4. **`assets/img/home-two/` & `reservation/` (4 files — ~300 KB):**
   * Files: `home-two-bg.jpg`, `home-two-img.jpg`, `reservation-img.jpg`.
   * **Status:** Leftover files from the multi-homepage Atoli theme.
5. **Template-Only References in `testimonials.html`:**
   * Files in `assets/img/testimonials/` are only loaded by `testimonials.html`, which itself is an unfinished template remnant containing fictitious placeholder addresses in Virginia, USA.

### 6.3. Case Sensitivity & Broken Link Vulnerabilities
A critical discovery in the HTML markup is inconsistent casing in internal navigation links:

* **In `index.html`, `about.html`, `services.html`, etc.:**
  * Rooms link: `<a href="Room.html">Rooms</a>` (Uppercase `R`)  
    *Target physical file:* `room.html` (Lowercase `r`).
  * Gallery link: `<a href="Gallery.html">Gallery</a>` (Uppercase `G`)  
    *Target physical file:* `gallery.html` (Lowercase `g`).
* **Impact:**
  * On local Windows systems (which are case-insensitive), these links resolve normally.
  * On **Netlify / Linux production servers** (which are case-sensitive), clicking "Rooms" or "Gallery" can cause a **404 Not Found** error or trigger the Netlify SPA fallback back to `index.html`, preventing users from reaching the page!

---

## 7. Technical Architecture & Deployment Stack

### Frontend Stack
* **Markup:** Semantic HTML5 with Open Graph and Twitter Card metadata for social sharing.
* **Layout & Styling:**
  * CSS framework: Bootstrap 5.x.
  * Custom stylesheet: `style.css` (primary styling), `responsive.css` (mobile responsiveness), `theme-dark.css`.
  * Typography & Icons: Flaticon (hotel-specific SVG glyphs), Boxicons v2.
* **Scripting & Interactivity:**
  * jQuery 3.x with plugins: MeanMenu (mobile drawer navigation), Owl Carousel (responsive multi-item sliders), Magnific Popup (lightbox image zoom), Nice Select (styled dropdowns), WOW.js (CSS animations on scroll).

### Conversion & Booking System
1. **Modal Quick Booking (`#bookingModal`):**
   * Integrated into every navigation bar via `Book Now` button.
   * Direct Telephone CTA: `tel:+2348144734612`.
   * Direct WhatsApp CTA: Pre-filled link to `https://wa.me/2348144734612`.
2. **Lead Contact Form (`contact.html`):**
   * Enabled with Netlify Forms integration: `data-netlify="true"`, `name="contact"`, and `netlify-honeypot="bot-field"`.
   * Protected with checkbox acceptance for Privacy Policy & Terms.

### Analytics & Tracking Integrations
* **Google Site Verification:** `<meta name="google-site-verification" content="5-EKPDU79tZaKmn1btvlHzB_y37AlAtf5JlkfotV5PQ" />`
* **Google Ads / Tag Manager:** Global site tag `AW-17974817149` configured in `<head>` across all pages for conversion attribution.

### Hosting & Server Configuration
* **Deployment Platform:** Netlify.
* **`netlify.toml` Configuration:**
  ```toml
  [[redirects]]
  from = "/*"
  to = "/index.html"
  status = 200
  ```
  *(Note: This rule acts as a Single Page Application rewrite rule. Any unmapped or mistyped route returns `index.html` with a HTTP 200 status).*

---

## 8. Actionable Recommendations & Roadmap

Based on this comprehensive analysis, here are the top 5 high-impact improvements for the Tiano Hotel website:

1. **Standardize Internal Link Casing (Immediate Priority):**
   * Change `href="Room.html"` to `href="room.html"` across all navigation bars.
   * Change `href="Gallery.html"` to `href="gallery.html"` across all navigation bars.
2. **Utilize or Prune Disconnected Assets:**
   * Integrate the rich food images from `assets/img/restaurant/` into `complimentary_gourmet_breakfast.html` and `services.html` to showcase culinary offerings.
   * Delete or archive the unused `assets/img/blog/`, `assets/img/team/`, and `assets/img/home-two/` folders to reduce repository size by ~3.5 MB.
3. **Finish or Remove Template Remnants:**
   * Update `testimonials.html` with authentic guest reviews from Google/Hotels.ng, or remove it from the project and footer links.
   * Remove `coming-soon.html` unless a dedicated feature announcement is planned.
4. **Image Compression & Next-Gen Formats:**
   * Images such as `contact_services.jpg` (6.68 MB), `about_3.png` (5.63 MB), and `outside_view.jpg` (2.88 MB) are extremely heavy for mobile networks. Converting them to optimized `.webp` or compressing them will dramatically improve page load speed in Akwa Ibom.
5. **SEO & Open Graph Unification:**
   * Ensure meta titles and descriptions are distinct across all pages (e.g., removing leftover template titles such as `Atoli - Hotel & Resorts HTML Template` found in template files).

---
*End of Documentation — Tiano Hotel & Suites.*
