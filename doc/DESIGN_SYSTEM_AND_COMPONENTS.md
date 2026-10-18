# Tiano Hotel & Suites — Design System & Admin UI Component Library

> **Document Type:** UI/UX Component Specifications, Design Tokens & Non-AI Dashboard Layout Guide  
> **Target Application:** Tiano Hotel & Suites — Admin Management Dashboard & Frontend Unification  
> **Purpose:** Ensure the upcoming Admin Management Dashboard perfectly inherits the front-end brand identity, typography, border-radii, animations, and dark/light modes without looking like a generic, disjointed AI-generated template.  
> **Source Stylesheets:** [`assets/css/style.css`](file:///c:/xampp/htdocs/Tiano-Hotel/assets/css/style.css), [`assets/css/theme-dark.css`](file:///c:/xampp/htdocs/Tiano-Hotel/assets/css/theme-dark.css), [`assets/css/bootstrap.min.css`](file:///c:/xampp/htdocs/Tiano-Hotel/assets/css/bootstrap.min.css)  

---

## Table of Contents
1. [Core Design Philosophy (Anti-Generic / Human-Crafted)](#1-core-design-philosophy-anti-generic--human-crafted)
2. [Global Design Tokens & Color Palette](#2-global-design-tokens--color-palette)
3. [Typography & Iconography Standards](#3-typography--iconography-standards)
4. [Shape Geometry & Elevation (Radii & Shadows)](#4-shape-geometry--elevation-radii--shadows)
5. [Core Component Library](#5-core-component-library)
   - [5.1 Buttons & Interactive Hover Wipes](#51-buttons--interactive-hover-wipes)
   - [5.2 Form Inputs, Floating Labels & Selects](#52-form-inputs-floating-labels--selects)
   - [5.3 Metric Stat Cards & KPI Widgets](#53-metric-stat-cards--kpi-widgets)
   - [5.4 Data Tables & Status Badges](#54-data-tables--status-badges)
   - [5.5 Navigation, Sidebar & Hamburger Drawer](#55-navigation-sidebar--hamburger-drawer)
   - [5.6 Modals & Action Drawers](#56-modals--action-drawers)
   - [5.7 Dark Mode & Light Mode Theme Toggle](#57-dark-mode--light-mode-theme-toggle)
6. [Complete Admin Dashboard Screen Wireframe](#6-complete-admin-dashboard-screen-wireframe)

---

## 1. Core Design Philosophy (Anti-Generic / Human-Crafted)

Most AI-generated dashboards suffer from noticeable tells: sterile purple gradients, cookie-cutter Bootstrap default cards, generic sans-serif fonts, and detached aesthetics that clash with the public website.

To ensure the Tiano Hotel Management Dashboard feels **cohesive, luxurious, and human-crafted**, every admin interface element directly inherits the established frontend styling:
* **The Signature Tiano Crimson/Red (`#E73539`):** Used consistently for primary calls-to-action, active indicators, and focus states.
* **The Dual-Wipe Animated Button:** Reusing the custom `.default-btn` slide-animation with its distinctive split pseudo-element hover effect.
* **Curved Geometry:** Utilizing the soft `15px` to `20px` border-radius identical to the hotel's room cards and booking modals.
* **Native Boxicons & Flaticons:** Strictly using the existing font glyphs (`bx bx-...` and `flaticon-...`) instead of importing foreign icon libraries.
* **Cohesive Dark/Light Modes:** Utilizing `#1d1d1d` and `#252525` dark backgrounds matching `theme-dark.css`.

---

## 2. Global Design Tokens & Color Palette

### A. Brand Colors
| Token Name | Hex Code | Visual Swatch | Role & Application |
| :--- | :--- | :--- | :--- |
| `--tiano-primary` | `#E73539` | 🟥 Crimson Red | Primary brand color, main CTAs (`.btn-bg-three`), active tabs, key accents. |
| `--tiano-primary-hover`| `#c92529` | 🟥 Deep Crimson | Hover state for primary buttons. |
| `--tiano-dark` | `#212529` | ⬛ Rich Charcoal | Secondary buttons (`.btn-bg-four`), text headings in light mode. |
| `--tiano-navy-wipe` | `#1B2132` | 🟦 Deep Navy | Background color for button hover animations (`::before` & `::after`). |
| `--tiano-gold-accent` | `#C5A880` | 🟨 Warm Sand / Gold | VIP room tags, star ratings, Presidential Suite accents. |
| `--tiano-whatsapp` | `#25D366` | 🟩 WhatsApp Green | Direct WhatsApp concierge messaging buttons. |

### B. Surface & Theme Tokens
| Surface Token | Light Mode Value | Dark Mode Value (`.theme-dark`) | Usage |
| :--- | :--- | :--- | :--- |
| `--bg-body` | `#FFFFFF` | `#1D1D1D` | Main page background |
| `--bg-card` | `#FFFFFF` | `#252525` | Dashboard cards, tables, panels |
| `--bg-subtle` | `#F5F5F5` (`.section-bg`) | `#202020` | Table headers, secondary panels |
| `--border-color` | `#EAEAEA` | `#333333` | Card dividers, input borders, table borders |
| `--text-heading` | `#212529` | `#FFFFFF` | Headings, card titles, table headers |
| `--text-muted` | `#6C757D` | `#AAAAAA` | Helper labels, timestamps, metadata |

---

## 3. Typography & Iconography Standards

### A. Font Families
* **Primary Sans-Serif:** Clean geometric sans matching the site bundle (Inter / Outfit / Roboto).
* **Base Sizes:**
  * Page Title / H1: `28px` (Weight: 700)
  * Section Headers / H2: `22px` (Weight: 600)
  * Card Titles / H3: `18px` (Weight: 600)
  * Body Text: `15px` (Weight: 400, Line-height: 1.6)
  * Data & Badges: `13px` (Weight: 600)

### B. Iconography System
Exclusively use the preloaded icon sets in `assets/fonts/`:
* **Boxicons (`bx bx-...`):**
  * Calendar: `<i class="bx bx-calendar"></i>`
  * Phone/WhatsApp: `<i class="bx bx-phone-call"></i>` / `<i class="bx bxl-whatsapp"></i>`
  * User / Guest: `<i class="bx bx-user"></i>`
  * Checkmark / Success: `<i class="bx bx-check-circle"></i>`
  * Bed / Rooms: `<i class="bx bx-hotel"></i>`
  * Search: `<i class="bx bx-search"></i>`
  * Currency / Folio: `<i class="bx bx-receipt"></i>`
* **Flaticon (`flaticon-...`):**
  * Flaticon hotel amenities (Wifi, restaurant, pool, spa).

---

## 4. Shape Geometry & Elevation (Radii & Shadows)

### A. Border Radii Standards
```css
/* Established Border Radii */
--radius-sm: 8px;     /* Form inputs, select dropdowns, mini-buttons */
--radius-md: 12px;    /* Dropdown menus, table wrappers, action alerts */
--radius-lg: 20px;    /* Main cards, metric containers, modals (matches modal rounded-4) */
--radius-pill: 50px;  /* Status badges, category pills, search bar wrappers */
```

### B. Elevation & Shadows
* **Default Card Shadow (Light):** `box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);`
* **Card Hover Shadow:** `box-shadow: 0 15px 35px rgba(231, 53, 57, 0.12);`
* **Dark Mode Shadow:** `box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);`

---

## 5. Core Component Library

### 5.1 Buttons & Interactive Hover Wipes

The admin dashboard reuses the exact `.default-btn` from `assets/css/style.css`, featuring the luxury dual-wipe slide animation:

```html
<!-- Primary Action Button (Crimson) -->
<button class="default-btn btn-bg-three border-radius-sm">
    <i class="bx bx-plus"></i> New Reservation
</button>

<!-- Secondary Action Button (Charcoal) -->
<button class="default-btn btn-bg-four border-radius-sm">
    <i class="bx bx-printer"></i> Print Folio
</button>

<!-- WhatsApp Direct Button -->
<a href="https://wa.me/2348144734612" target="_blank" class="default-btn border-radius-sm" style="background-color: #25D366;">
    <i class="bx bxl-whatsapp"></i> Message Guest
</a>
```

#### CSS Specification:
```css
.default-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 24px;
  color: #ffffff;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
  z-index: 1;
  border: none;
  font-weight: 500;
  transition: all 0.3s ease;
}
.default-btn::before {
  content: "";
  position: absolute;
  top: 0; left: 0;
  width: 0; height: 50%;
  background-color: #1B2132;
  z-index: -1;
  transition: all 0.5s ease;
}
.default-btn::after {
  content: "";
  position: absolute;
  bottom: 0; right: 0;
  width: 0; height: 50%;
  background-color: #1B2132;
  z-index: -1;
  transition: all 0.5s ease;
}
.default-btn:hover::before,
.default-btn:hover::after {
  width: 100%;
}
```

---

### 5.2 Form Inputs, Floating Labels & Selects

Inputs match the clean, tall design seen in `contact.html`:

```html
<div class="form-group mb-3">
    <label class="form-label text-muted small fw-bold">GUEST FULL NAME</label>
    <div class="input-group">
        <span class="input-group-text bg-transparent border-end-0">
            <i class="bx bx-user text-muted"></i>
        </span>
        <input type="text" class="form-control border-start-0" placeholder="e.g. Chief Emeka Okon" required>
    </div>
</div>
```

#### CSS Specification:
```css
.form-control, .form-select {
  height: 48px;
  border-radius: 8px;
  border: 1px solid #EAEAEA;
  font-size: 14px;
  padding: 10px 16px;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.form-control:focus, .form-select:focus {
  border-color: #E73539;
  box-shadow: 0 0 0 3px rgba(231, 53, 57, 0.15);
  outline: none;
}
.theme-dark .form-control, .theme-dark .form-select {
  background-color: #202020;
  border-color: #333333;
  color: #FFFFFF;
}
```

---

### 5.3 Metric Stat Cards & KPI Widgets

Metric cards echo the front-end room cards (`border-radius: 16px`, subtle gradient background, and icon avatar):

```html
<div class="col-lg-3 col-md-6 mb-4">
    <div class="tiano-kpi-card p-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted small fw-bold text-uppercase">Today's Arrivals</span>
                <h2 class="mb-0 mt-2 fw-bold text-dark">12 <span class="fs-6 text-muted fw-normal">Guests</span></h2>
            </div>
            <div class="kpi-icon-badge bg-primary-soft">
                <i class="bx bx-log-in-circle text-danger fs-3"></i>
            </div>
        </div>
        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
            <span class="text-success"><i class="bx bx-trending-up"></i> 8 Checked-In</span>
            <span class="text-warning">4 Expected</span>
        </div>
    </div>
</div>
```

#### CSS Specification:
```css
.tiano-kpi-card {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid #EAEAEA;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.03);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.tiano-kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(231, 53, 57, 0.08);
}
.theme-dark .tiano-kpi-card {
  background: #252525;
  border-color: #333333;
}
.kpi-icon-badge {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(231, 53, 57, 0.1);
}
```

---

### 5.4 Data Tables & Status Badges

Data tables feature generous padding, readable row striping, and status pills:

```html
<div class="table-responsive tiano-table-wrapper">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>REF ID</th>
                <th>GUEST NAME</th>
                <th>ROOM ALLOTTED</th>
                <th>CHECK-IN / OUT</th>
                <th>AMOUNT (NGN)</th>
                <th>STATUS</th>
                <th>ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="fw-bold text-danger">#THS-849201</span></td>
                <td>
                    <div class="fw-bold">Engr. Bassey Udom</div>
                    <div class="small text-muted">+234 802 345 6789</div>
                </td>
                <td><span class="badge bg-light text-dark border">Diamond (Room 204)</span></td>
                <td>
                    <div>Oct 12, 2026</div>
                    <small class="text-muted">to Oct 15 (3 N)</small>
                </td>
                <td class="fw-bold">₦150,000</td>
                <td><span class="tiano-badge badge-confirmed">Confirmed</span></td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-success" title="Chat on WhatsApp">
                            <i class="bx bxl-whatsapp"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="View Folio">
                            <i class="bx bx-receipt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Check-In">
                            <i class="bx bx-check-circle"></i> Check-In
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

#### Status Badge Classes:
```css
.tiano-badge {
  display: inline-block;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 30px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge-pending    { background-color: #FFF3CD; color: #856404; }
.badge-confirmed  { background-color: #CCE5FF; color: #004085; }
.badge-checkedin  { background-color: #D4EDDA; color: #155724; }
.badge-completed  { background-color: #E2E3E5; color: #383D41; }
.badge-cancelled  { background-color: #F8D7DA; color: #721C24; }
```

---

### 5.5 Navigation, Sidebar & Hamburger Drawer

The admin navigation reuses the top-header styling and MeanMenu-style responsive drawer:
* **Desktop Sidebar:** Left-docked, 260px wide, dark charcoal/black (`#1B2132` / `#12141D`), containing hotel brand SVG logo (`assets/img/logos/logo-1.svg`).
* **Active Navigation Link:** Highlighted with a crimson left border and soft highlight.
* **Mobile Hamburger Drawer:** Toggles smoothly with the exact 3-line hamburger animation seen on mobile across `index.html`.

---

### 5.6 Modals & Action Drawers

Modals inherit the exact styling of the front-end `#bookingModal`:
* **Radius:** `border-radius: 20px` (`rounded-4`).
* **Close Button:** Floating crimson close button in the top right corner (`#dc3545`).
* **Backdrop:** Smooth semi-transparent blur (`backdrop-filter: blur(4px)`).

---

### 5.7 Dark Mode & Light Mode Theme Toggle

The admin dashboard integrates the existing `.switch-box` component from `assets/css/theme-dark.css`:
* Uses the floating or header-mounted toggle switch (`#night.png` and `#sunny.png` icons).
* Automatically persists theme preference in `localStorage` or session cookie.

---

## 6. Complete Admin Dashboard Screen Wireframe

```text
+-----------------------------------------------------------------------------------+
|  [TIANO HOTEL & SUITES]        [Search Ref / Guest...]     (🔔 3) (🌓) [Admin User ▼] |
+------------------+----------------------------------------------------------------+
|                  |  COMMAND CENTER / DASHBOARD OVERVIEW                           |
|  📊 Dashboard    +----------------------------------------------------------------+
|  📅 Bookings     | [Today's Arrivals: 12] [Departures: 8] [Occupancy: 84%] [Rev: ₦1.8M]|
|  🔑 Physical Rms |----------------------------------------------------------------+
|  🧹 Housekeeping |  QUICK ACTIONS:                                                |
|  💬 WhatsApp Hub |  [+ New Walk-In] [🔍 Check Reference] [📋 Print Daily Manifest] |
|  ⭐ Reviews      |----------------------------------------------------------------+
|  📩 Inquiries    |  ACTIVE RESERVATIONS (PAY-ON-ARRIVAL)                          |
|  ⚙️ Settings     |  +----------------------------------------------------------+  |
|                  |  | Ref      Guest Name     Room      Dates     Amount Status|  |
|  [Sign Out]      |  | #849201  Engr. Bassey   Diamond   Oct 12-15 ₦150k  [Conf]|  |
|                  |  | #849202  Chief Inyang   Presiden  Oct 13-14 ₦250k  [Arrv]|  |
|                  |  +----------------------------------------------------------+  |
|                  |  ROOM CLEANLINESS MATRIX:                                       |
|                  |  [Room 101: 🟢] [Room 102: 🔵] [Room 103: 🟡] [Room 201: 🟢]   |
+------------------+----------------------------------------------------------------+
```

---
*End of Design System & Component Library — Tiano Hotel & Suites.*
