# Tiano Hotel & Suites — Complete cPanel, Git & Database Deployment Guide

**Document Reference:** `DOC-THS-DEP-2026-V1`  
**Target Environment:** cPanel Shared / VPS Hosting (Linux / Apache / MySQL / PHP 8.x)  
**Domain:** `https://www.tianohotelandsuites.com`  
**GitHub Repository:** `https://github.com/timenyin/Tiano-Hotel-Suites-Mangament-.git`

---

## Table of Contents
1. [Overview & Architecture](#1-overview--architecture)
2. [Phase 1: Preparing & Exporting the Local Database](#phase-1-preparing--exporting-the-local-database)
3. [Phase 2: Creating the Database & User in cPanel](#phase-2-creating-the-database--user-in-cpanel)
4. [Phase 3: Importing the Database into cPanel phpMyAdmin](#phase-3-importing-the-database-into-cpanel-phpmyadmin)
5. [Phase 4: Setting Up Git & Deploying via cPanel Terminal](#phase-4-setting-up-git--deploying-via-cpanel-terminal)
6. [Phase 5: Configuring Live Database Credentials (`include/pdoconfig.php`)](#phase-5-configuring-live-database-credentials-includepdoconfigphp)
7. [Phase 6: Updating Production Settings & Site URL](#phase-6-updating-production-settings--site-url)
8. [Phase 7: SSL Certificate & HTTPS Enforcement](#phase-7-ssl-certificate--https-enforcement)
9. [Phase 8: Post-Deployment Verification Checklist](#phase-8-post-deployment-verification-checklist)
10. [Ongoing Maintenance: How to Push & Pull Future Updates](#ongoing-maintenance-how-to-push--pull-future-updates)

---

## 1. Overview & Architecture

Deploying Tiano Hotel & Suites to live cPanel using the Git terminal method provides several major advantages over traditional FTP:
* **Zero Missing Files:** Atomic file transfers ensure no corrupted or partially uploaded PHP files.
* **Preserved File Permissions:** Executable and readable permissions remain intact.
* **Instant 1-Command Updates:** Future website updates take 3 seconds via `git pull origin main`.
* **Zero Downtime:** Seamless synchronisation between local development and production.

---

## Phase 1: Preparing & Exporting the Local Database

The project includes the master database SQL script located at:
`sql/tiano_hotel_master.sql`

> [!CAUTION]
> ### The #1 Common cPanel Import Error: `#1044 - Access Denied`
> On shared cPanel hosting, MySQL users **cannot** run `CREATE DATABASE` or `USE database_name;` because cPanel automatically prefixes database names with your cPanel account username (e.g. `tianohot_hotel_db`).
>
> If you import an SQL file containing `CREATE DATABASE tiano_hotel_db;`, cPanel will reject it with:
> `ERROR 1044 (42000): Access denied for user 'youruser'@'localhost' to database 'tiano_hotel_db'`

### How to Prepare Your SQL File:
1. Open `sql/tiano_hotel_master.sql` in any text editor.
2. Locate lines 8 through 12 at the top:
   ```sql
   -- REMOVE OR COMMENT OUT THESE LINES:
   CREATE DATABASE IF NOT EXISTS `tiano_hotel_db` 
   DEFAULT CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;

   USE `tiano_hotel_db`;
   ```
3. Remove or delete those 5 lines, then save the file as `tiano_production.sql`.

### Alternative: Exporting Freshly from Local phpMyAdmin
1. Open your browser and go to `http://localhost/phpmyadmin`.
2. Click **`tiano_hotel_db`** on the left menu.
3. Click the **Export** tab at the top.
4. Choose **Custom - display all possible options**.
5. Under **Database(s) to be created**, ensure **"Add CREATE DATABASE / USE statement" is UNCHECKED**.
6. Scroll to the bottom and click **Export**.

---

## Phase 2: Creating the Database & User in cPanel

1. Log in to your **cPanel Account**.
2. In the search box at the top, type `MySQL` and click **MySQL® Database Wizard**.
3. **Step 1: Create A Database**
   * New Database Name: `hotel_db`
   * *Full Database Name will be:* `yourcpaneluser_hotel_db` (Note this down!)
   * Click **Next Step**.
4. **Step 2: Create Database Users**
   * Username: `hotel_user`
   * *Full Username will be:* `yourcpaneluser_hotel_user` (Note this down!)
   * Password: Click **Password Generator** (e.g., `K9#m$P2!wX89@zQ`).
   * **Copy this password and save it in a secure note immediately.**
   * Check "I have copied this password in a safe place" and click **Use Password**.
   * Click **Create User**.
5. **Step 3: Add User to the Database**
   * Check the checkbox: **ALL PRIVILEGES**.
   * Click **Make Changes**.
   * Click **Return to MySQL Databases** or Return to Home.

---

## Phase 3: Importing the Database into cPanel phpMyAdmin

1. In cPanel Home, scroll to **Databases** and click **phpMyAdmin**.
2. In the left-hand column, click on your newly created database:
   `yourcpaneluser_hotel_db`
3. Click the **Import** tab on the top menu bar.
4. Click **Choose File** (or **Browse**) and select `tiano_production.sql`.
5. Ensure the character set is set to `utf-8`.
6. Scroll down and click the blue **Import** (or **Go**) button.
7. You should see a green success banner:
   *"Import has been successfully finished, XX queries executed."*
8. Verify that all 10 core tables are listed in the left sidebar:
   - `admins`
   - `bookings`
   - `booking_cancellations`
   - `gallery_albums`
   - `gallery_images`
   - `newsletter_subscribers`
   - `reviews`
   - `room_categories`
   - `rooms`
   - `settings`

---

## Phase 4: Setting Up Git & Deploying via cPanel Terminal

### Option A: Direct Terminal Deployment (Recommended & Fastest)

1. In cPanel, scroll down to the **Advanced** section and click **Terminal**.
   *(If prompted with a warning about SSH/Terminal access, click "I understand and want to proceed")*.

2. Navigate to your web directory (`public_html`):
   ```bash
   cd ~/public_html
   ```

3. Check if there are default placeholder files from the hosting provider:
   ```bash
   ls -la
   ```
   *If you see a default `index.html` or `default.html`, remove it:*
   ```bash
   rm -f index.html default.html
   ```

4. **Clone the GitHub Repository directly into `public_html`:**
   ```bash
   git clone https://github.com/timenyin/Tiano-Hotel-Suites-Mangament-.git .
   ```
   > [!IMPORTANT]
   > Notice the space and **dot (`.`)** at the end of the command! The dot clones the contents directly inside `public_html` rather than creating a subfolder named `Tiano-Hotel-Suites-Mangament-`.

5. If your GitHub repository is **Private**, Git will ask for your GitHub Username and Password:
   * **Username:** `timenyin`
   * **Password:** Use a **GitHub Personal Access Token (PAT)** *(standard passwords are not accepted by GitHub)*.
     * To generate a PAT on GitHub: Go to **GitHub Settings ➔ Developer Settings ➔ Personal access tokens (classic) ➔ Generate new token ➔ Select `repo` scope ➔ Generate & Copy token**.

---

### Option B: Using cPanel's Built-In "Git™ Version Control" Tool

If you prefer a graphical interface inside cPanel:
1. In cPanel Home, search for **Git™ Version Control** (under Files).
2. Click **Create** (blue button on top right).
3. Fill in the fields:
   * **Clone URL:** `https://github.com/timenyin/Tiano-Hotel-Suites-Mangament-.git`
   * **Repository Path:** `public_html`
   * **Repository Name:** `Tiano-Hotel`
4. Click **Create**.
5. cPanel will clone the repository and keep it tracked on the dashboard.

---

## Phase 5: Configuring Live Database Credentials (`include/pdoconfig.php`)

Now connect your live PHP scripts to the cPanel database created in Phase 2:

1. In the cPanel Terminal, edit `include/pdoconfig.php`:
   ```bash
   nano include/pdoconfig.php
   ```
   *(Or open `public_html/include/pdoconfig.php` using cPanel **File Manager** and click **Edit**)*.

2. Replace lines 8 through 12 with your live cPanel credentials:
   ```php
   // --- 1. ONLINE HOSTING SETTINGS (Active for Production) ---
   $host    = 'localhost';
   $db      = 'yourcpaneluser_hotel_db';    // Enter your full cPanel DB name
   $user    = 'yourcpaneluser_hotel_user';  // Enter your full cPanel DB user
   $pass    = 'YourGeneratedPasswordHere';  // Enter the exact password from Phase 2
   $charset = 'utf8mb4';
   ```

3. If using `nano`: Press `Ctrl + O`, then `Enter` to save, then `Ctrl + X` to exit.
4. If using File Manager: Click **Save Changes** at the top right.

---

## Phase 6: Updating Production Settings & Site URL

1. In cPanel **phpMyAdmin**, click on your database `yourcpaneluser_hotel_db`.
2. Click on the **`settings`** table.
3. Find the row where `setting_key` is **`site_url`**.
4. Click **Edit** and set:
   * `setting_value` = `https://www.tianohotelandsuites.com/`
5. Verify the following rows in the `settings` table are updated:
   * `hotel_phone` = `08144734612`
   * `hotel_email` = `tianohotel@gmail.com`
   * `hotel_whatsapp` = `2348144734612`
6. Click **Go** to save.

---

## Phase 7: SSL Certificate & HTTPS Enforcement

1. In cPanel, search for **SSL/TLS Status** (under Security).
2. Click **Run AutoSSL** to issue a free Let's Encrypt / cPanel SSL certificate for:
   - `tianohotelandsuites.com`
   - `www.tianohotelandsuites.com`
3. Once AutoSSL succeeds (green padlocks next to domains), visit **Domains** in cPanel.
4. Ensure the toggle **"Force HTTPS Redirect"** is turned **ON**.

---

## Phase 8: Post-Deployment Verification Checklist

Test the live website in your browser:

- [ ] **Homepage:** Visit `https://www.tianohotelandsuites.com` — verify CSS, images, and preloader load cleanly.
- [ ] **Rooms Page:** Visit `https://www.tianohotelandsuites.com/rooms.php` — verify all 6 room tiers (Coral, Silver, Gold, Diamond, Premium, Presidential) display with their new prices.
- [ ] **Room Details:** Click through to `silver-room.php`, `coral-room.php`, etc.
- [ ] **Booking Engine:** Visit `https://www.tianohotelandsuites.com/booking.php` — submit a test reservation and confirm it generates a voucher (`THS-XXXXXX`).
- [ ] **Admin Portal:** Visit `https://www.tianohotelandsuites.com/admin/` — log in with:
  * **Email:** `support@tianohotelandsuites.com`
  * **Password:** `Admin@Tiano2026`
- [ ] **Admin Room Matrix:** Navigate to `/admin/rooms.php` — verify all 33 physical rooms (Rooms 2 through 34) appear across Ground, 1st, and 2nd floors.
- [ ] **Google Search Console:** In Search Console, click **Verify** to confirm the DNS TXT record is active!

---

## Ongoing Maintenance: How to Push & Pull Future Updates

Whenever you make improvements or updates to the code on your computer in the future:

### Step 1: Push from your local computer
```bash
git add -A
git commit -m "feat: description of changes"
git push origin main
```

### Step 2: Pull on your live cPanel server
Open cPanel **Terminal** and run:
```bash
cd ~/public_html
git pull origin main
```
*Your live site is updated in seconds without touching FTP!*
