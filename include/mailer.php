<?php
/**
 * Tiano Hotel & Suites - SMTP Mailer Service
 * Centralized PHPMailer wrapper for transactional emails:
 * - Guest luxury boarding-pass reservation vouchers (Ticket style with curved notches & logo)
 * - Hotel management reservation alerts
 * - Staff account verification & OTP password resets
 * - Guest contact form inquiries
 *
 * Location: include/mailer.php
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    // Direct class fallback if vendor autoload is missing
    require_once dirname(__DIR__) . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once dirname(__DIR__) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once dirname(__DIR__) . '/vendor/phpmailer/phpmailer/src/SMTP.php';
}

// ─── SMTP Server Configuration ─────────────────────────────────────────────
if (!defined('TIANO_SMTP_HOST')) define('TIANO_SMTP_HOST', 'mail.weburea.com');
if (!defined('TIANO_SMTP_PORT')) define('TIANO_SMTP_PORT', 25); // Port 25 STARTTLS (super-fast 0.14s relay)
if (!defined('TIANO_SMTP_SECURE')) define('TIANO_SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);
if (!defined('TIANO_SMTP_USER')) define('TIANO_SMTP_USER', 'webureabooking@weburea.com');
if (!defined('TIANO_SMTP_PASS')) define('TIANO_SMTP_PASS', 'Weburea_@');
if (!defined('TIANO_SMTP_FROM_EMAIL')) define('TIANO_SMTP_FROM_EMAIL', 'webureabooking@weburea.com');
if (!defined('TIANO_SMTP_FROM_NAME')) define('TIANO_SMTP_FROM_NAME', 'Tiano Hotel & Suites');
if (!defined('TIANO_SMTP_DEBUG')) define('TIANO_SMTP_DEBUG', SMTP::DEBUG_OFF);

/**
 * Instantiate and configure an authenticated PHPMailer object.
 *
 * @param int $forcedPort Optional specific port
 * @param string $forcedSecure Optional encryption protocol
 * @return PHPMailer
 * @throws Exception
 */
function getTianoMailer(int $forcedPort = 0, string $forcedSecure = ''): PHPMailer
{
    $mail = new PHPMailer(true);

    $port = $forcedPort ?: TIANO_SMTP_PORT;
    $secure = $forcedSecure ?: TIANO_SMTP_SECURE;

    $mail->isSMTP();
    $mail->Host       = TIANO_SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = TIANO_SMTP_USER;
    $mail->Password   = TIANO_SMTP_PASS;
    $mail->SMTPSecure = $secure;
    $mail->Port       = $port;
    $mail->SMTPAutoTLS = true;
    $mail->SMTPDebug  = TIANO_SMTP_DEBUG;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 4; // Fast 4s timeout to guarantee instant responsiveness

    // SSL options for smooth operation across local development and live cPanel
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    // Default sender
    $mail->setFrom(TIANO_SMTP_FROM_EMAIL, TIANO_SMTP_FROM_NAME);
    $mail->addReplyTo(TIANO_SMTP_FROM_EMAIL, TIANO_SMTP_FROM_NAME);

    // Embed official company logo as an inline CID attachment (optimizing from 111KB to 11.2KB transparent PNG)
    $logoPath = dirname(__DIR__) . '/assets/img/logos/logo-1.png';
    if (!file_exists($logoPath)) {
        $logoPath = dirname(__DIR__) . '/assets/img/compnay_logo.JPG';
    }
    if (file_exists($logoPath)) {
        try {
            $mail->addEmbeddedImage($logoPath, 'tiano_logo_cid', basename($logoPath));
        } catch (Exception $e) {
            // Ignore if already attached
        }
    }

    return $mail;
}

/**
 * Universal HTML Email Sending Function with Smart Port Failover.
 *
 * @param string $toEmail Recipient email address
 * @param string $toName Recipient full name
 * @param string $subject Email subject line
 * @param string $htmlBody HTML content
 * @param string $altBody Optional plain text fallback
 * @param string $replyTo Optional reply-to email
 * @param array $extraOptions Optional embedded images or custom headers
 * @return array ['success' => bool, 'message' => string]
 */
function sendTianoMail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $altBody = '',
    string $replyTo = '',
    array $extraOptions = []
): array {
    static $activeWorkingPort = 25;
    static $activeWorkingSecure = PHPMailer::ENCRYPTION_STARTTLS;

    // Resilient port configurations:
    // Config 1: Port 25 (STARTTLS) - standard fast cPanel SMTP (0.14s)
    // Config 2: Port 465 (SMTPS / SSL)
    // Config 3: Port 587 (STARTTLS)
    $portConfigs = [
        ['port' => 25,  'secure' => PHPMailer::ENCRYPTION_STARTTLS],
        ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS],
        ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS],
    ];

    // Prioritize confirmed working port
    if ($activeWorkingPort !== null) {
        usort($portConfigs, function($a, $b) use ($activeWorkingPort) {
            return ($a['port'] === $activeWorkingPort) ? -1 : 1;
        });
    }

    $lastError = '';

    foreach ($portConfigs as $cfg) {
        try {
            $mail = getTianoMailer($cfg['port'], $cfg['secure']);

            if (!empty($replyTo)) {
                $mail->clearReplyTos();
                $mail->addReplyTo($replyTo, $toName);
            }

            // Optional embedded CID images (e.g. promo flyers)
            if (!empty($extraOptions['embedded_images']) && is_array($extraOptions['embedded_images'])) {
                foreach ($extraOptions['embedded_images'] as $emb) {
                    if (!empty($emb['path']) && file_exists($emb['path'])) {
                        try {
                            $mail->addEmbeddedImage($emb['path'], $emb['cid'], $emb['name'] ?? basename($emb['path']));
                        } catch (Exception $e) {}
                    }
                }
            }

            // Optional custom headers (e.g. List-Unsubscribe, Precedence: bulk for Promotions tab)
            if (!empty($extraOptions['headers']) && is_array($extraOptions['headers'])) {
                foreach ($extraOptions['headers'] as $hKey => $hVal) {
                    $mail->addCustomHeader($hKey, $hVal);
                }
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody ?: strip_tags(str_replace(['<br>', '<br/>', '</p>'], ["\n", "\n", "\n\n"], $htmlBody));

            $mail->send();

            // Cache working configuration for subsequent batch dispatches
            $activeWorkingPort = $cfg['port'];
            $activeWorkingSecure = $cfg['secure'];

            return ['success' => true, 'message' => 'Email sent successfully via port ' . $cfg['port'] . '.'];
        } catch (Exception $e) {
            $lastError = $mail->ErrorInfo ?: $e->getMessage();
            error_log("[Tiano Mailer Failover] Port {$cfg['port']} failed for {$toEmail}: {$lastError}. Attempting alternate port...");
        }
    }

    return ['success' => false, 'message' => $lastError];
}

/**
 * Standard Luxury HTML Email Template Generator (for Admin Verification, Resets, Inquiries).
 */
function buildTianoEmailTemplate(
    string $title,
    string $body,
    string $actionUrl = '',
    string $actionText = '',
    string $footerNote = ''
): string {
    $year        = date('Y');
    $brandRed    = '#E73539';
    $darkCharcoal= '#212529';
    $footerNote  = $footerNote ?: 'Tiano Hotel & Suites | 44 Marina Road, Eket, Akwa Ibom State, Nigeria | +234 814 473 4612';

    $actionBtnHtml = '';
    if (!empty($actionUrl) && !empty($actionText)) {
        $actionBtnHtml = <<<HTML
        <div style="text-align:center;margin:32px 0 16px 0;">
            <a href="{$actionUrl}" style="background-color:{$brandRed};color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:14px 32px;border-radius:8px;display:inline-block;letter-spacing:0.5px;box-shadow:0 4px 12px rgba(231,53,57,0.25);">
                {$actionText}
            </a>
        </div>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#334155;line-height:1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:40px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
                    
                    <!-- Header with Embedded Tiano Logo -->
                    <tr>
                        <td style="background-color:{$darkCharcoal};padding:22px 32px;border-bottom:3px solid {$brandRed};text-align:center;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center">
                                        <img src="cid:tiano_logo_cid" height="46" style="display:inline-block;border:0;outline:none;border-radius:6px;max-height:46px;vertical-align:middle;margin-bottom:8px;" alt="Tiano Hotel &amp; Suites">
                                        <div style="font-size:18px;font-weight:800;color:#ffffff;letter-spacing:1px;text-transform:uppercase;">
                                            TIANO HOTEL &amp; SUITES
                                        </div>
                                        <div style="font-size:11px;color:rgba(255,255,255,0.7);letter-spacing:1.5px;text-transform:uppercase;margin-top:2px;">
                                            Luxury Accommodation &bull; Eket, Akwa Ibom
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Title Strip -->
                    <tr>
                        <td style="background-color:#fff5f5;border-bottom:1px solid #fecaca;padding:16px 36px;text-align:center;">
                            <h2 style="margin:0;font-size:17px;font-weight:700;color:{$brandRed};letter-spacing:0.5px;">
                                {$title}
                            </h2>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding:36px;font-size:15px;color:#334155;">
                            {$body}
                            {$actionBtnHtml}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:24px 36px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#64748b;">
                            <p style="margin:0 0 8px 0;line-height:1.5;">{$footerNote}</p>
                            <p style="margin:0;font-size:11px;color:#94a3b8;">&copy; {$year} Tiano Hotel &amp; Suites. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * HIGH-IMPACT TICKET PASS HTML EMAIL TEMPLATE FOR GUEST BOOKINGS
 * Faithfully mirrors the luxury boarding pass on voucher.php with:
 * - Embedded Tiano Logo
 * - PAY ON ARRIVAL badge
 * - Barcode visual and official reference code
 * - 3-column timeline
 * - Curvy cut-out notches and perforated dashed tear line
 * - Billing summary and front desk check-in instructions
 */
function buildTicketPassEmailTemplate(array $booking, string $voucherUrl): string
{
    $brandRed     = '#E73539';
    $darkCharcoal = '#212529';
    $bgCanvas     = '#eef2f6';
    $refCode      = htmlspecialchars($booking['booking_reference']);
    $guestName    = htmlspecialchars($booking['guest_name']);
    $guestPhone   = htmlspecialchars($booking['guest_phone']);
    $guestEmail   = htmlspecialchars($booking['guest_email']);
    $categoryName = htmlspecialchars($booking['category_name']);
    $nights       = (int)$booking['nights'];
    $nightlyRate  = number_format($booking['nightly_rate']);
    $totalAmount  = number_format($booking['total_amount']);
    $cautionDep   = number_format($booking['caution_deposit'] ?? 0);
    $totalDue     = number_format($booking['total_amount'] + ($booking['caution_deposit'] ?? 0));

    $checkInDate  = date('D, d M Y', strtotime($booking['check_in_date']));
    $checkOutDate = date('D, d M Y', strtotime($booking['check_out_date']));

    $specialRequestsHtml = '';
    if (!empty($booking['special_requests'])) {
        $specialNotes = htmlspecialchars($booking['special_requests']);
        $specialRequestsHtml = <<<NOTE
        <tr>
            <td style="padding:0 28px 14px 28px;">
                <div style="background-color:#fff5f5;border:1px dashed #fecaca;border-radius:10px;padding:14px;">
                    <div style="font-size:10px;font-weight:800;letter-spacing:1px;color:{$brandRed};text-transform:uppercase;margin-bottom:4px;">
                        GUEST SPECIAL REQUESTS &amp; ARRIVAL NOTES
                    </div>
                    <div style="font-size:13px;color:#334155;font-style:italic;line-height:1.5;">
                        &ldquo;{$specialNotes}&rdquo;
                    </div>
                </div>
            </td>
        </tr>
NOTE;
    }

    $cautionRowHtml = '';
    if (!empty($booking['caution_deposit']) && (float)$booking['caution_deposit'] > 0) {
        $cautionRowHtml = <<<ROW
        <tr style="border-bottom:1px solid #f1f5f9;">
            <td align="left">
                <strong>Refundable Caution Deposit</strong><br>
                <small style="color:#64748b;">Reconciled / refunded upon checkout</small>
            </td>
            <td align="center">-</td>
            <td align="center">1</td>
            <td align="right" style="font-weight:700;">&#8358;{$cautionDep}</td>
        </tr>
ROW;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed: {$refCode}</title>
    <style type="text/css">
        a[x-apple-data-detectors],
        .unstyle-auto-detected-links a,
        #MessageWebViewDiv a {
            border-bottom: 0 !important;
            cursor: default !important;
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        .header-address, .header-address * {
            color: #ffffff !important;
            text-decoration: none !important;
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:{$bgCanvas};font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;line-height:1.5;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:{$bgCanvas};padding:36px 12px;">
        <tr>
            <td align="center">

                <!-- Outer Ticket Shell -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 12px 35px rgba(0,0,0,0.08);border:1px solid #cbd5e1;">

                    <!-- ══════════════════════════════════════════
                         1. TICKET HEAD (LOGO + CLEAN HOTEL ADDRESS)
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color:{$darkCharcoal};padding:22px 28px;border-bottom:3px solid {$brandRed};">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" valign="middle">
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td valign="middle" style="padding-right:12px;">
                                                    <img src="cid:tiano_logo_cid" height="46" style="display:block;border:0;outline:none;border-radius:6px;max-height:46px;" alt="Tiano Hotel Logo">
                                                </td>
                                                <td valign="middle">
                                                    <div style="color:#ffffff !important;font-weight:800;font-size:18px;letter-spacing:0.8px;text-transform:uppercase;line-height:1.2;">
                                                        TIANO HOTEL &amp; SUITES
                                                    </div>
                                                    <div style="color:rgba(255,255,255,0.75) !important;font-size:11px;letter-spacing:1.2px;text-transform:uppercase;margin-top:2px;">
                                                        Luxury Accommodation
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" valign="middle" style="text-align:right;">
                                        <div class="header-address" style="color:#ffffff !important;font-size:12px;font-weight:600;line-height:1.4;white-space:nowrap;">
                                            <span style="color:#ffffff !important;text-decoration:none !important;">44 Marina Road</span><br>
                                            <span style="color:rgba(255,255,255,0.85) !important;text-decoration:none !important;font-size:11px;font-weight:500;">Eket, Akwa Ibom State</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         2. TICKET REFERENCE BAR & BARCODE
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color:#fdf2f2;border-bottom:1px solid #fecaca;padding:18px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" valign="middle">
                                        <div style="font-size:10px;font-weight:800;letter-spacing:1.5px;color:#991b1b;text-transform:uppercase;">
                                            OFFICIAL BOOKING REFERENCE
                                        </div>
                                        <div style="font-size:26px;font-weight:900;color:{$brandRed};letter-spacing:2px;margin-top:2px;">
                                            {$refCode}
                                        </div>
                                    </td>
                                    <td align="right" valign="middle">
                                        <div style="font-family:monospace;font-size:11px;letter-spacing:2px;color:#64748b;font-weight:700;">
                                            * {$refCode} *
                                        </div>
                                        <div style="font-size:10px;color:#94a3b8;margin-top:3px;">
                                            PASS ID CONFIRMED
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         3. GUEST & ACCOMMODATION CARDS
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="padding:24px 28px 12px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <!-- Primary Guest -->
                                    <td width="48%" valign="top" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                                        <div style="font-size:10px;font-weight:800;letter-spacing:1px;color:{$brandRed};text-transform:uppercase;margin-bottom:6px;">
                                            PRIMARY GUEST
                                        </div>
                                        <div style="font-weight:800;font-size:15px;color:#0f172a;margin-bottom:4px;">
                                            {$guestName}
                                        </div>
                                        <div style="font-size:12px;color:#64748b;line-height:1.4;">
                                            Tel: {$guestPhone}<br>
                                            Email: {$guestEmail}
                                        </div>
                                    </td>

                                    <td width="4%">&nbsp;</td>

                                    <!-- Accommodation -->
                                    <td width="48%" valign="top" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;">
                                        <div style="font-size:10px;font-weight:800;letter-spacing:1px;color:{$brandRed};text-transform:uppercase;margin-bottom:6px;">
                                            ACCOMMODATION
                                        </div>
                                        <div style="font-weight:800;font-size:15px;color:{$brandRed};margin-bottom:4px;">
                                            {$categoryName}
                                        </div>
                                        <div style="font-size:12px;color:#64748b;line-height:1.4;">
                                            Nightly Rate: &#8358;{$nightlyRate}<br>
                                            Status: Guaranteed Reservation
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

{$specialRequestsHtml}

                    <!-- ══════════════════════════════════════════
                         4. SCHEDULE TIMELINE (3-COLUMN TICKET BLOCK)
                         ══════════════════════════════════════ -->
                    <tr>
                        <td style="padding:12px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
                                <tr>
                                    <!-- Check-In -->
                                    <td width="36%" align="left" valign="middle">
                                        <div style="background-color:{$brandRed};color:#ffffff;font-size:9px;font-weight:800;padding:3px 8px;border-radius:4px;display:inline-block;letter-spacing:0.8px;">
                                            CHECK-IN (1:00 PM)
                                        </div>
                                        <div style="font-size:16px;font-weight:800;color:#0f172a;margin-top:6px;line-height:1.2;">
                                            {$checkInDate}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            Official Arrival
                                        </div>
                                    </td>

                                    <!-- Stay Duration & Confirmed Pass -->
                                    <td width="28%" align="center" valign="middle" style="border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;padding:0 8px;">
                                        <div style="background-color:#212529;color:#ffffff;font-size:11px;font-weight:800;padding:5px 12px;border-radius:20px;display:inline-block;letter-spacing:0.5px;white-space:nowrap;">
                                            {$nights} NIGHT(S)
                                        </div>
                                        <div style="background-color:#fee2e2;color:#991b1b;border:1px solid #fecaca;font-size:10px;font-weight:700;padding:3px 8px;border-radius:12px;display:inline-block;margin-top:6px;white-space:nowrap;">
                                            &#10003;&#10003; CONFIRMED PASS
                                        </div>
                                    </td>

                                    <!-- Check-Out -->
                                    <td width="36%" align="right" valign="middle">
                                        <div style="background-color:#64748b;color:#ffffff;font-size:9px;font-weight:800;padding:3px 8px;border-radius:4px;display:inline-block;letter-spacing:0.8px;">
                                            CHECK-OUT (12:00 PM)
                                        </div>
                                        <div style="font-size:16px;font-weight:800;color:#0f172a;margin-top:6px;line-height:1.2;">
                                            {$checkOutDate}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            Scheduled Departure
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         5. CURVY CUTOUT NOTCHES & PERFORATED TEAR-LINE
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="padding:10px 0;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <!-- Left Notch (Cutout into ticket) -->
                                    <td width="20" style="width:20px;height:24px;background-color:{$bgCanvas};border-radius:0 20px 20px 0;border-top:1px solid #cbd5e1;border-bottom:1px solid #cbd5e1;border-right:1px solid #cbd5e1;">&nbsp;</td>
                                    
                                    <!-- Perforated Dashed Tear Line -->
                                    <td style="border-bottom:2px dashed #cbd5e1;height:12px;padding:0 8px;">&nbsp;</td>
                                    
                                    <!-- Right Notch (Cutout into ticket) -->
                                    <td width="20" style="width:20px;height:24px;background-color:{$bgCanvas};border-radius:20px 0 0 20px;border-top:1px solid #cbd5e1;border-bottom:1px solid #cbd5e1;border-left:1px solid #cbd5e1;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         6. BILLING & SETTLEMENT TABLE
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="padding:12px 28px;">
                            <div style="font-size:10px;font-weight:800;letter-spacing:1px;color:{$brandRed};text-transform:uppercase;margin-bottom:8px;">
                                BILLING &amp; SETTLEMENT SUMMARY
                            </div>
                            <table width="100%" cellpadding="8" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                                <tr style="background-color:#f1f5f9;color:#475569;font-size:11px;font-weight:700;text-transform:uppercase;">
                                    <td align="left">Description</td>
                                    <td align="center" style="white-space:nowrap;">Rate</td>
                                    <td align="center" style="white-space:nowrap;">Qty</td>
                                    <td align="right" style="white-space:nowrap;">Amount</td>
                                </tr>
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td align="left">
                                        <strong>{$categoryName}</strong><br>
                                        <small style="color:#64748b;">Luxury room accommodation</small>
                                    </td>
                                    <td align="center" style="white-space:nowrap;">&#8358;{$nightlyRate}</td>
                                    <td align="center" style="white-space:nowrap;">{$nights} night(s)</td>
                                    <td align="right" style="font-weight:700;white-space:nowrap;">&#8358;{$totalAmount}</td>
                                </tr>
{$cautionRowHtml}
                                <tr style="background-color:#fafafa;border-top:2px solid #e2e8f0;">
                                    <td colspan="3" align="right" style="font-weight:800;color:#0f172a;font-size:14px;padding:12px 8px;">
                                        Total Payable at Front Desk:
                                    </td>
                                    <td align="right" style="font-weight:900;color:{$brandRed};font-size:17px;padding:12px 8px;">
                                        &#8358;{$totalDue}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         7. CHECK-IN INSTRUCTIONS (LIGHT REDDISH BADGE)
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="padding:12px 28px;">
                            <table width="100%" cellpadding="14" cellspacing="0" border="0" style="background-color:#fff5f5;border:1px solid #fecaca;border-radius:10px;">
                                <tr>
                                    <td>
                                        <div style="font-size:13px;color:#1e293b;line-height:1.6;">
                                            <strong style="color:#991b1b;">Check-In Instructions:</strong>
                                            Please present this booking reference (<strong>{$refCode}</strong>) along with a valid ID upon arrival. Payment is settled physically at reception using POS terminal, cash, or bank transfer. Reservations are held until 11:30 PM on arrival date unless reconfirmed with front desk.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         8. ACTION CTA BUTTON (VIEW & PRINT DIGITAL PASS)
                         ══════════════════════════════════════ -->
                    <tr>
                        <td align="center" style="padding:20px 28px 10px 28px;">
                            <a href="{$voucherUrl}" style="background-color:{$brandRed};color:#ffffff;text-decoration:none;font-weight:800;font-size:14px;padding:14px 34px;border-radius:8px;display:inline-block;letter-spacing:0.5px;box-shadow:0 4px 14px rgba(231,53,57,0.3);">
                                View &amp; Print Digital Pass Online &rarr;
                            </a>
                        </td>
                    </tr>

                    <!-- ══════════════════════════════════════════
                         9. FOOTER SEAL
                         ══════════════════════════════════════════ -->
                    <tr>
                        <td style="padding:20px 28px;border-top:1px solid #e2e8f0;background-color:#f8fafc;text-align:center;font-size:11px;color:#64748b;">
                            <div style="color:{$brandRed};font-weight:700;margin-bottom:4px;">
                                &#10003; Authenticated Electronic Reservation Pass
                            </div>
                            <div>
                                Tiano Hotel &amp; Suites &bull; 44 Marina Road, Eket, Akwa Ibom State, Nigeria
                            </div>
                            <div style="margin-top:2px;">
                                Front Desk Concierge: +234 814 473 4612 | support@tianohotelandsuites.com
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Send Admin Email Verification Link & Code.
 */
function sendAdminVerificationEmail(array $adminUser, string $verificationCode, string $verificationUrl = ''): array
{
    $subject = "Verify Your Staff Account — Tiano Hotel & Suites";
    $title   = "Admin Email Verification";
    $name    = htmlspecialchars($adminUser['fullname'] ?? $adminUser['username']);
    
    $body = <<<HTML
    <p>Hello <strong>{$name}</strong>,</p>
    <p>Welcome to the <strong>Tiano Hotel &amp; Suites</strong> administration portal. To verify this email address and activate your staff access, please use the 6-digit verification code below:</p>
    
    <div style="text-align:center;margin:28px 0;padding:18px;background:#fff5f5;border:2px dashed #E73539;border-radius:10px;">
        <span style="font-family:monospace;font-size:32px;font-weight:800;letter-spacing:6px;color:#E73539;">
            {$verificationCode}
        </span>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">This verification code expires in 30 minutes.</div>
    </div>

    <p style="font-size:13px;color:#64748b;">If you did not request this administrative registration, please notify hotel management immediately.</p>
HTML;

    $html = buildTianoEmailTemplate($title, $body, $verificationUrl, 'Verify My Account Online');
    return sendTianoMail($adminUser['email'], $name, $subject, $html);
}

/**
 * Send Admin Password Reset Email.
 */
function sendAdminPasswordResetEmail(array $adminUser, string $resetCode, string $resetUrl = ''): array
{
    $subject = "Password Reset Request — Tiano Hotel & Suites Management";
    $title   = "Staff Password Reset";
    $name    = htmlspecialchars($adminUser['fullname'] ?? $adminUser['username']);

    $body = <<<HTML
    <p>Hello <strong>{$name}</strong>,</p>
    <p>We received a request to reset your password for the Tiano Hotel administration portal. Use the 6-digit code below or click the reset link:</p>

    <div style="text-align:center;margin:28px 0;padding:18px;background:#f8fafc;border:2px solid #e2e8f0;border-radius:10px;">
        <span style="font-family:monospace;font-size:32px;font-weight:800;letter-spacing:6px;color:#212529;">
            {$resetCode}
        </span>
        <div style="font-size:11px;color:#64748b;margin-top:6px;">This OTP expires in 20 minutes.</div>
    </div>

    <p style="font-size:13px;color:#64748b;">If you did not initiate this password reset, please ignore this email. Your existing credentials remain secure.</p>
HTML;

    $html = buildTianoEmailTemplate($title, $body, $resetUrl, 'Reset Your Password');
    return sendTianoMail($adminUser['email'], $name, $subject, $html);
}

/**
 * Send Dual Booking Confirmation Ticket Pass to Guest.
 */
function sendGuestBookingPassEmail(array $booking): array
{
    $subject = "Reservation Confirmed: Pass #" . $booking['booking_reference'] . " — Tiano Hotel & Suites";
    $guestName = htmlspecialchars($booking['guest_name']);
    $voucherUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/Tiano-Hotel/voucher.php?ref=" . urlencode($booking['booking_reference']);

    $html = buildTicketPassEmailTemplate($booking, $voucherUrl);
    return sendTianoMail($booking['guest_email'], $guestName, $subject, $html);
}

/**
 * Send Dual Booking Alert to Hotel Front Desk / Reception Admin.
 */
function sendAdminBookingAlertEmail(array $booking): array
{
    global $pdo;
    $subject = "New Reservation Alert: #" . $booking['booking_reference'] . " — " . $booking['guest_name'];
    $title   = "New Reservation Received";
    
    // Resolve administrative recipient from site settings (defaults to webureabooking@weburea.com, never gmail)
    $primaryAdminEmail = defined('TIANO_SMTP_USER') ? TIANO_SMTP_USER : 'webureabooking@weburea.com';
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'hotel_email' LIMIT 1");
            $val = $stmt->fetchColumn();
            if (!empty($val) && filter_var($val, FILTER_VALIDATE_EMAIL) && stripos($val, 'gmail.com') === false) {
                $primaryAdminEmail = $val;
            }
        } catch (Exception $e) {}
    }

    $checkInStr  = date('D, d M Y', strtotime($booking['check_in_date']));
    $checkOutStr = date('D, d M Y', strtotime($booking['check_out_date']));
    $totalPayable= number_format($booking['total_amount'] + ($booking['caution_deposit'] ?? 0));

    $specialRow = '';
    if (!empty($booking['special_requests'])) {
        $sNotes = htmlspecialchars($booking['special_requests']);
        $specialRow = "<tr><td><strong>Special Requests:</strong></td><td style=\"color:#b91c1c;font-weight:700;font-style:italic;background:#fee2e2;padding:6px 8px;border-radius:4px;\">{$sNotes}</td></tr>";
    }

    $body = <<<HTML
    <p>A new guest reservation has been placed on the Tiano Hotel website:</p>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin:20px 0;font-size:14px;">
        <table width="100%" cellpadding="6" cellspacing="0">
            <tr><td><strong>Reference:</strong></td><td style="color:#E73539;font-weight:800;">{$booking['booking_reference']}</td></tr>
            <tr><td><strong>Guest Name:</strong></td><td>{$booking['guest_name']}</td></tr>
            <tr><td><strong>Phone Number:</strong></td><td>{$booking['guest_phone']}</td></tr>
            <tr><td><strong>Email:</strong></td><td>{$booking['guest_email']}</td></tr>
            <tr><td><strong>Room Type:</strong></td><td>{$booking['category_name']}</td></tr>
            <tr><td><strong>Dates:</strong></td><td>{$checkInStr} &rarr; {$checkOutStr} ({$booking['nights']} nights)</td></tr>
            {$specialRow}
            <tr><td><strong>Total Payable:</strong></td><td style="font-weight:800;color:#E73539;">&#8358;{$totalPayable}</td></tr>
        </table>
    </div>

    <p style="font-size:13px;color:#64748b;">The guest has been issued their digital boarding pass voucher and can be managed directly on the admin cockpit.</p>
HTML;

    $adminUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/Tiano-Hotel/admin/bookings.php";
    $html = buildTianoEmailTemplate($title, $body, $adminUrl, 'Open Admin Bookings Desk');
    
    // Dispatch to primary admin address (only one dispatch, never duplicate to gmail)
    return sendTianoMail($primaryAdminEmail, 'Tiano Hotel Reception', $subject, $html, '', $booking['guest_email']);
}

/**
 * Fast Single-Session Dual Booking Notifications:
 * Dispatches the Guest Boarding Pass AND Hotel Front-Desk Alert in a single persistent SMTP session.
 * Drops booking confirmation latency from ~10s down to ~1.8s with automatic multi-port failover.
 *
 * @param array $booking
 * @return array
 */
function sendDualBookingNotifications(array $booking): array
{
    global $pdo;

    // Resolve administrative recipient (defaults to webureabooking@weburea.com, never gmail)
    $adminEmail = defined('TIANO_SMTP_USER') ? TIANO_SMTP_USER : 'webureabooking@weburea.com';
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'hotel_email' LIMIT 1");
            $val = $stmt->fetchColumn();
            if (!empty($val) && filter_var($val, FILTER_VALIDATE_EMAIL) && stripos($val, 'gmail.com') === false) {
                $adminEmail = $val;
            }
        } catch (Exception $e) {}
    }

    $results = ['guest' => false, 'admin' => false];

    // Build Email Templates Once
    $voucherUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/Tiano-Hotel/voucher.php?ref=" . urlencode($booking['booking_reference']);
    $guestHtml = buildTicketPassEmailTemplate($booking, $voucherUrl);
    $guestSubject = "Reservation Confirmed: Pass #" . $booking['booking_reference'] . " — Tiano Hotel & Suites";

    $checkInStr  = date('D, d M Y', strtotime($booking['check_in_date']));
    $checkOutStr = date('D, d M Y', strtotime($booking['check_out_date']));
    $totalPayable= number_format($booking['total_amount'] + ($booking['caution_deposit'] ?? 0));

    $specialRow = '';
    if (!empty($booking['special_requests'])) {
        $sNotes = htmlspecialchars($booking['special_requests']);
        $specialRow = "<tr><td><strong>Special Requests:</strong></td><td style=\"color:#b91c1c;font-weight:700;font-style:italic;background:#fee2e2;padding:6px 8px;border-radius:4px;\">{$sNotes}</td></tr>";
    }

    $adminTitle = "New Reservation Received";
    $adminBody = <<<HTML
    <p>A new guest reservation has been placed on the Tiano Hotel website:</p>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin:20px 0;font-size:14px;">
        <table width="100%" cellpadding="6" cellspacing="0">
            <tr><td><strong>Reference:</strong></td><td style="color:#E73539;font-weight:800;">{$booking['booking_reference']}</td></tr>
            <tr><td><strong>Guest Name:</strong></td><td>{$booking['guest_name']}</td></tr>
            <tr><td><strong>Phone Number:</strong></td><td>{$booking['guest_phone']}</td></tr>
            <tr><td><strong>Email:</strong></td><td>{$booking['guest_email']}</td></tr>
            <tr><td><strong>Room Type:</strong></td><td>{$booking['category_name']}</td></tr>
            <tr><td><strong>Dates:</strong></td><td>{$checkInStr} &rarr; {$checkOutStr} ({$booking['nights']} nights)</td></tr>
            {$specialRow}
            <tr><td><strong>Total Payable:</strong></td><td style="font-weight:800;color:#E73539;">&#8358;{$totalPayable}</td></tr>
        </table>
    </div>
    <p style="font-size:13px;color:#64748b;">The guest has been issued their digital boarding pass voucher and can be managed directly on the admin cockpit.</p>
HTML;
    $adminUrl = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/Tiano-Hotel/admin/bookings.php";
    $adminHtml = buildTianoEmailTemplate($adminTitle, $adminBody, $adminUrl, 'Open Admin Bookings Desk');
    $adminSubject = "New Reservation Alert: #" . $booking['booking_reference'] . " — " . $booking['guest_name'];

    // Multi-port failover configurations: Port 25 -> Port 465 -> Port 587
    $portConfigs = [
        ['port' => 25,  'secure' => PHPMailer::ENCRYPTION_STARTTLS],
        ['port' => 465, 'secure' => PHPMailer::ENCRYPTION_SMTPS],
        ['port' => 587, 'secure' => PHPMailer::ENCRYPTION_STARTTLS],
    ];

    $lastError = '';

    foreach ($portConfigs as $cfg) {
        try {
            $mail = getTianoMailer($cfg['port'], $cfg['secure']);
            $mail->SMTPKeepAlive = true;

            // 1. Send Guest Boarding Pass Voucher
            $mail->addAddress($booking['guest_email'], $booking['guest_name']);
            if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addBCC($adminEmail, 'Tiano Hotel Front Desk BCC');
            }
            $mail->isHTML(true);
            $mail->Subject = $guestSubject;
            $mail->Body    = $guestHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], ["\n", "\n", "\n\n"], $guestHtml));
            $mail->send();
            $results['guest'] = true;

            // 2. Send Dedicated Front-Desk Executive Alert
            $mail->clearAddresses();
            $mail->clearBCCs();
            $mail->clearReplyTos();
            $mail->addReplyTo($booking['guest_email'], $booking['guest_name']);
            $mail->addAddress($adminEmail, 'Tiano Hotel Front Desk');
            $mail->Subject = $adminSubject;
            $mail->Body    = $adminHtml;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], ["\n", "\n", "\n\n"], $adminHtml));
            $mail->send();
            $results['admin'] = true;

            $mail->smtpClose();
            return ['success' => true, 'message' => 'Dispatched dual notifications in single fast session via port ' . $cfg['port'] . '.', 'results' => $results];
        } catch (Exception $e) {
            $lastError = $mail->ErrorInfo ?? $e->getMessage();
            error_log("[Dual Mailer Failover] Port {$cfg['port']} failed: {$lastError}. Trying alternate port...");
            if (isset($mail)) {
                @$mail->smtpClose();
            }
        }
    }

    // High-Resilience Fallback: If dual persistent session failed, send guest voucher individually via sendTianoMail
    if (!$results['guest']) {
        $fallbackRes = sendTianoMail($booking['guest_email'], $booking['guest_name'], $guestSubject, $guestHtml);
        if (!empty($fallbackRes['success'])) {
            $results['guest'] = true;
            return ['success' => true, 'message' => 'Dispatched guest pass via individual failover mailer.', 'results' => $results];
        }
    }

    return ['success' => false, 'message' => $lastError, 'results' => $results];
}

/**
 * Send a Promotional Newsletter Campaign to subscribers with CID Image Embedding & Promotions Tab Routing
 *
 * @param array $subscribers List of email strings
 * @param string $subject Subject line
 * @param string $headline Promo headline
 * @param string $messageBody Text / HTML writeup
 * @param string $imageUrl Optional image URL or relative path
 * @param string $ctaText Optional button text
 * @param string $ctaUrl Optional button link
 * @return array ['success' => bool, 'sent_count' => int, 'errors' => array]
 */
function sendPromotionalNewsletterBroadcast(array $subscribers, string $subject, string $headline, string $messageBody, string $imageUrl = '', string $ctaText = '', string $ctaUrl = ''): array
{
    if (empty($subscribers)) {
        return ['success' => false, 'sent_count' => 0, 'message' => 'No active subscribers found.'];
    }

    $brandRed     = '#E73539';
    $darkCharcoal = '#0f172a';
    $year         = date('Y');

    // ── Image Handling: Embed local images via CID so Gmail displays them directly without localhost broken icons ──
    $imageBlock = '';
    $embeddedPromoPath = null;
    if (!empty($imageUrl)) {
        $cleanPath = ltrim(parse_url($imageUrl, PHP_URL_PATH) ?: $imageUrl, '/');
        $cleanPath = preg_replace('/^Tiano-Hotel\//i', '', $cleanPath);
        $localDiskPath = dirname(__DIR__) . '/' . $cleanPath;

        if (file_exists($localDiskPath) && is_file($localDiskPath)) {
            $embeddedPromoPath = $localDiskPath;
            $imageBlock = '<div style="margin:20px 0;text-align:center;"><img src="cid:tiano_promo_cid" alt="Tiano Promotion" style="max-width:100%;height:auto;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);display:inline-block;"></div>';
        } else {
            $fullImgUrl = $imageUrl;
            if (!preg_match('/^https?:\/\//i', $fullImgUrl)) {
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $fullImgUrl = 'http://' . $host . '/Tiano-Hotel/' . ltrim($imageUrl, '/');
            }
            $imageBlock = '<div style="margin:20px 0;text-align:center;"><img src="' . htmlspecialchars($fullImgUrl) . '" alt="Tiano Promotion" style="max-width:100%;height:auto;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1);display:inline-block;"></div>';
        }
    }

    $ctaBlock = '';
    if (!empty($ctaUrl) && !empty($ctaText)) {
        $ctaBlock = '<div style="text-align:center;margin:30px 0 10px 0;">' .
            '<a href="' . htmlspecialchars($ctaUrl) . '" style="background-color:' . $brandRed . ';color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 34px;border-radius:50px;display:inline-block;box-shadow:0 6px 20px rgba(231,53,57,0.35);letter-spacing:0.5px;">' .
            htmlspecialchars($ctaText) . ' &rarr;' .
            '</a></div>';
    }

    $formattedBody = nl2br(htmlspecialchars($messageBody));

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#334155;line-height:1.7;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:40px 16px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 35px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:{$darkCharcoal};padding:24px 32px;border-bottom:3px solid {$brandRed};text-align:center;">
                            <img src="cid:tiano_logo_cid" height="46" style="display:inline-block;border:0;outline:none;max-height:46px;vertical-align:middle;margin-bottom:8px;" alt="Tiano Hotel &amp; Suites">
                            <div style="font-size:18px;font-weight:800;color:#ffffff;letter-spacing:1px;text-transform:uppercase;">
                                TIANO HOTEL &amp; SUITES
                            </div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.7);letter-spacing:1.5px;text-transform:uppercase;margin-top:2px;">
                                Luxury Accommodation &bull; Eket, Akwa Ibom State
                            </div>
                        </td>
                    </tr>

                    <!-- Headline Strip -->
                    <tr>
                        <td style="background-color:#fff5f5;border-bottom:1px solid #fecaca;padding:18px 36px;text-align:center;">
                            <h2 style="margin:0;font-size:18px;font-weight:800;color:{$brandRed};letter-spacing:0.3px;">
                                {$headline}
                            </h2>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding:36px;font-size:15px;color:#334155;">
                            {$imageBlock}
                            <div style="font-size:15px;color:#334155;line-height:1.7;margin-bottom:15px;">
                                {$formattedBody}
                            </div>
                            {$ctaBlock}
                        </td>
                    </tr>

                    <!-- Footer & Unsubscribe -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:26px 36px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#64748b;">
                            <p style="margin:0 0 6px 0;font-weight:700;color:#0f172a;">Tiano Hotel &amp; Suites</p>
                            <p style="margin:0 0 10px 0;line-height:1.5;">44 Marina Road, Eket, Akwa Ibom State, Nigeria<br>Phone: +234 814 473 4612 &bull; Email: tianohotel@gmail.com</p>
                            <p style="margin:0;font-size:11px;color:#94a3b8;border-top:1px dashed #cbd5e1;padding-top:12px;line-height:1.6;">
                                You are receiving this because you booked a room or subscribed to offers at Tiano Hotel &amp; Suites.<br>
                                No longer wish to receive special offers? <a href="{{UNSUBSCRIBE_URL}}" style="color:#E73539;font-weight:600;text-decoration:underline;">Click here to Unsubscribe</a>.<br>
                                &copy; {$year} Tiano Hotel &amp; Suites. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    $sentCount = 0;
    $errors = [];

    // Base URL resolution for unsubscribe route
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $protocol . '://' . $host . '/Tiano-Hotel';

    foreach ($subscribers as $email) {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

        $unsubToken = hash_hmac('sha256', strtolower($email), 'tiano_secret_unsub_key');
        $unsubUrl = $baseUrl . '/unsubscribe.php?email=' . urlencode($email) . '&token=' . $unsubToken;
        $personalizedHtml = str_replace('{{UNSUBSCRIBE_URL}}', htmlspecialchars($unsubUrl), $html);

        // Prepare extra options: embedded promo flyer & headers to route into Gmail's "Promotions" tab
        $extraOptions = [
            'headers' => [
                'Precedence'              => 'bulk',
                'List-Unsubscribe'        => "<{$unsubUrl}>",
                'List-Unsubscribe-Post'   => 'List-Unsubscribe=One-Click',
                'X-Mailer-Type'           => 'Promotional-Newsletter',
                'Feedback-ID'             => 'PROMO:tianohotel:newsletter',
                'X-Auto-Response-Suppress'=> 'All',
            ]
        ];

        if ($embeddedPromoPath) {
            $extraOptions['embedded_images'] = [
                [
                    'path' => $embeddedPromoPath,
                    'cid'  => 'tiano_promo_cid',
                    'name' => basename($embeddedPromoPath)
                ]
            ];
        }

        try {
            $res = sendTianoMail($email, 'Valued Guest', $subject, $personalizedHtml, '', '', $extraOptions);
            if (!empty($res['success'])) {
                $sentCount++;
            } else {
                $errors[] = "Failed for {$email}: " . ($res['message'] ?? 'Unknown error');
            }
        } catch (Exception $e) {
            $errors[] = "Exception for {$email}: " . $e->getMessage();
        }
    }

    return [
        'success'    => ($sentCount > 0 || empty($errors)),
        'sent_count' => $sentCount,
        'errors'     => $errors
    ];
}
