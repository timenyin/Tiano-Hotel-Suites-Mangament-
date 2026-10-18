# Tiano Hotel & Suites - Official Staff & Admin Access Directory

This document contains official portal URLs, staff access tiers, login credentials, and email configuration details for Tiano Hotel & Suites.

---

## 1. Quick Access Portals

* **Public Website**: [https://tianohotelandsuites.com](https://tianohotelandsuites.com)
* **Staff & Admin Management Portal**: [https://tianohotelandsuites.com/admin/login.php](https://tianohotelandsuites.com/admin/login.php)
* **Webmail Portal (cPanel)**: [https://tianohotelandsuites.com:2096](https://tianohotelandsuites.com:2096)

---

## 2. Staff Roles & Login Credentials

All staff can log into the management portal using either their **Username** or their **Official Email Address**.

### 1. Front Desk / Reception
* **Designation**: Front Desk Receptionist
* **Username**: `reception`
* **Email**: `frontdesk@tianohotelandsuites.com`
* **Password**: `FrontDesk@Tiano2026`
* **Role / Tier**: `receptionist`
* **Responsibilities**:
  * Guest check-in and check-out processing
  * Room availability lookup and reservation confirmation
  * On-site guest requests and daily room status updates

---

### 2. Hotel Management
* **Designation**: General Manager
* **Username**: `manager`
* **Email**: `management@tianohotelandsuites.com`
* **Password**: `Manager@Tiano2026`
* **Role / Tier**: `manager`
* **Responsibilities**:
  * Comprehensive booking and reservation oversight
  * Room category rate adjustments and availability schedules
  * Booking analytics, revenue reports, and guest feedback management

---

### 3. Executive Superadmin (Hotel Support)
* **Designation**: Tiano Hotel Support / Executive Admin
* **Username**: `admin`
* **Email**: `support@tianohotelandsuites.com`
* **Password**: `Admin@Tiano2026`
* **Role / Tier**: `super_admin`
* **Responsibilities**:
  * Full unrestricted access to the entire management portal
  * Staff account creation, permission changes, and password management
  * Hotel profile, billing settings, room master catalog, and site configurations

---

### 4. Technical Operations / DevOps
* **Designation**: Technical Admin & DevOps
* **Username**: `dev`
* **Email**: `dev@tianohotelandsuites.com`
* **Password**: `DevOps@Tiano2026!`
* **Role / Tier**: `super_admin`
* **Responsibilities**:
  * System diagnostics, database maintenance, and error logs
  * Mail relay infrastructure and server health monitoring
  * Integration and codebase updates

---

## 3. Official Mailer & Webmail Configuration

The hotel uses an authenticated SSL SMTP relay on port 465 for transactional guest receipts, contact messages, and reservation notifications:

* **Email Account**: `support@tianohotelandsuites.com`
* **Email Password**: `Tiano2k_@`
* **Incoming / Outgoing Server (SMTP/IMAP)**: `s9621.fra1.stableserver.net`
* **Outgoing SMTP Port**: `465` (SSL/TLS required)
* **Incoming IMAP Port**: `993` (SSL) / POP3: `995`
* **Webmail Direct Access**: [https://s9621.fra1.stableserver.net:2096](https://s9621.fra1.stableserver.net:2096)
