<?php
/**
 * Tiano Hotel & Suites - Administrative Hotel Configuration & Settings
 * Central control of contact details, operational policies, and WhatsApp concierge.
 */
$pageTitle = 'Hotel Settings & Policies';
$activePage = 'settings';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $settingsToUpdate = [
        'hotel_name'              => trim($_POST['hotel_name'] ?? 'Tiano Hotel and Suites'),
        'hotel_tagline'           => trim($_POST['hotel_tagline'] ?? ''),
        'hotel_phone'             => trim($_POST['hotel_phone'] ?? '+234 814 473 4612'),
        'hotel_alt_phone'         => trim($_POST['hotel_alt_phone'] ?? ''),
        'hotel_whatsapp'          => trim($_POST['hotel_whatsapp'] ?? '2348144734612'),
        'hotel_email'             => trim($_POST['hotel_email'] ?? 'support@tianohotelandsuites.com'),

        'hotel_address'           => trim($_POST['hotel_address'] ?? '44 Marina Road, Eket, Akwa Ibom State, Nigeria'),
        'checkin_time'            => trim($_POST['checkin_time'] ?? '1:00 PM'),
        'checkout_time'           => trim($_POST['checkout_time'] ?? '12:00 PM'),
        'caution_deposit_enabled' => (isset($_POST['caution_deposit_enabled']) && $_POST['caution_deposit_enabled'] === '1') ? '1' : '0',
        'caution_deposit_default' => trim($_POST['caution_deposit_default'] ?? '0'),
        'site_url'                => trim($_POST['site_url'] ?? 'https://tianohotelandsuites.com'),
        'facebook_url'            => trim($_POST['facebook_url'] ?? ''),
        'instagram_url'           => trim($_POST['instagram_url'] ?? ''),
        'alarm_sound_enabled'     => (isset($_POST['alarm_sound_enabled']) && $_POST['alarm_sound_enabled'] === '0') ? '0' : '1',
        'alarm_sound_tone'        => trim($_POST['alarm_sound_tone'] ?? 'concierge_bell')
    ];

    try {
        $updateStmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingsToUpdate as $key => $val) {
            $updateStmt->execute([$key, $val]);
        }

        log_activity($pdo, $admin['id'], $admin['fullname'], 'update_settings', 'system', 1, "Updated hotel operations & contact parameters");
        $success = "Hotel settings and policies updated successfully.";
    } catch (Exception $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
}

// Fetch current settings
$settings = [];
$rawSettings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
foreach ($rawSettings as $rs) {
    $settings[$rs['setting_key']] = $rs['setting_value'];
}
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Hotel Settings &amp; Brand Policies</h4>
        <p class="text-muted small mb-0">Configure front desk contact lines, concierge WhatsApp, arrival policies, and SEO parameters.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="settings.php">
            <input type="hidden" name="action" value="save_settings">

            <!-- General Identity -->
            <div class="admin-card p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="bx bx-buildings text-danger fs-4"></i> Hotel Identity &amp; Branding
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Hotel Trading Name</label>
                        <input type="text" name="hotel_name" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_name'] ?? 'Tiano Hotel and Suites'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Brand Tagline</label>
                        <input type="text" name="hotel_tagline" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_tagline'] ?? ''); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Physical Premises Address</label>
                        <input type="text" name="hotel_address" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_address'] ?? '44 Marina Road, Eket, Akwa Ibom State, Nigeria'); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Contact & Concierge -->
            <div class="admin-card p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="bx bx-phone-call text-danger fs-4"></i> Guest Contact &amp; Concierge Channels
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Primary Reception Telephone</label>
                        <input type="text" name="hotel_phone" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_phone'] ?? '+234 814 473 4612'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Manager Hotline</label>
                        <input type="text" name="hotel_alt_phone" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_alt_phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">WhatsApp Concierge Phone (Digits only with country code)</label>
                        <input type="text" name="hotel_whatsapp" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_whatsapp'] ?? '2348144734612'); ?>" required>
                        <small class="text-muted">Used for 1-click WhatsApp buttons on vouchers &amp; pages.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Official Support Email</label>
                        <input type="email" name="hotel_email" class="form-control" value="<?php echo htmlspecialchars($settings['hotel_email'] ?? 'support@tianohotelandsuites.com'); ?>" required>

                    </div>
                </div>
            </div>

            <!-- Operations & Policies -->
            <div class="admin-card p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="bx bx-time text-danger fs-4"></i> Arrival Policies &amp; Financials
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Check-In Start Time</label>
                        <input type="text" name="checkin_time" class="form-control" value="<?php echo htmlspecialchars($settings['checkin_time'] ?? '1:00 PM'); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Check-Out Deadline</label>
                        <input type="text" name="checkout_time" class="form-control" value="<?php echo htmlspecialchars($settings['checkout_time'] ?? '12:00 PM'); ?>">
                    </div>

                    <!-- Refundable Caution Deposit Toggle & Settings -->
                    <div class="col-12">
                        <div class="p-3 rounded-3 border" style="background:#fcfcfd;">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div>
                                    <label class="form-label small fw-bold text-dark text-uppercase mb-0 d-flex align-items-center gap-2">
                                        <i class="bx bx-shield-quarter text-danger fs-5"></i>
                                        Refundable Caution Deposit Policy
                                    </label>
                                    <small class="text-muted d-block mt-1">
                                        Toggle whether a refundable incidental deposit is charged and shown to guests on the front-end reservation summary.
                                    </small>
                                </div>
                                <div class="btn-group" role="group" aria-label="Caution Deposit Toggle">
                                    <input type="radio" class="btn-check" name="caution_deposit_enabled" id="cautionDepositActive" value="1" <?php echo (($settings['caution_deposit_enabled'] ?? '0') === '1') ? 'checked' : ''; ?> onchange="toggleCautionDeposit(true)">
                                    <label class="btn btn-outline-success fw-bold px-3 py-1" for="cautionDepositActive" style="font-size:12px;">
                                        <i class="bx bx-check-circle me-1"></i> Active (Visible)
                                    </label>

                                    <input type="radio" class="btn-check" name="caution_deposit_enabled" id="cautionDepositHidden" value="0" <?php echo (($settings['caution_deposit_enabled'] ?? '0') === '0') ? 'checked' : ''; ?> onchange="toggleCautionDeposit(false)">
                                    <label class="btn btn-outline-secondary fw-bold px-3 py-1" for="cautionDepositHidden" style="font-size:12px;">
                                        <i class="bx bx-hide me-1"></i> Hidden (Do Not Display)
                                    </label>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mt-2" id="cautionAmountWrapper">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Caution Deposit Amount (&#8358;)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">&#8358;</span>
                                        <input type="number" name="caution_deposit_default" id="cautionDepositInput" class="form-control" value="<?php echo htmlspecialchars($settings['caution_deposit_default'] ?? '0'); ?>" min="0" step="500">
                                    </div>
                                </div>
                                <div class="col-md-7 pt-md-4">
                                    <div id="cautionStatusNotice" class="small fw-medium p-2 rounded-2 <?php echo (($settings['caution_deposit_enabled'] ?? '0') === '1') ? 'bg-success-subtle text-success' : 'bg-light text-muted border'; ?>">
                                        <?php if (($settings['caution_deposit_enabled'] ?? '0') === '1'): ?>
                                            <i class="bx bx-check-circle me-1"></i> <strong>Active:</strong> Guests see this deposit itemised on the booking quote and digital passes.
                                        <?php else: ?>
                                            <i class="bx bx-hide me-1"></i> <strong>Hidden:</strong> Completely removed from front-end booking summary, live quote, and total calculation.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Canonical Production Site URL (For WhatsApp, Sitemap &amp; SEO previews)</label>
                        <input type="url" name="site_url" class="form-control font-monospace" value="<?php echo htmlspecialchars($settings['site_url'] ?? 'https://tianohotelandsuites.com'); ?>">
                    </div>
                </div>
            </div>

            <!-- 24/7 Front-Desk Alarm & Chime Controls -->
            <div class="admin-card p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bx bxs-bell-ring text-danger fs-4"></i> 24/7 Front-Desk Audio Alarm &amp; Chime Controls
                    </span>
                    <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-3 py-1" style="font-size: 11px;">
                        REAL-TIME CHIME
                    </span>
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Front-Desk Audio Status</label>
                        <select name="alarm_sound_enabled" class="form-select" id="alarmSoundToggle">
                            <option value="1" <?php echo (($settings['alarm_sound_enabled'] ?? '1') === '1') ? 'selected' : ''; ?>>
                                🔔 Enabled — Audible Concierge Chime Rings on Reservations
                            </option>
                            <option value="0" <?php echo (($settings['alarm_sound_enabled'] ?? '1') === '0') ? 'selected' : ''; ?>>
                                🔕 Disabled — Silent Mode (Visual Alerts Only)
                            </option>
                        </select>
                        <small class="text-muted d-block mt-1">Control whether front-desk computer rings on new bookings.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted text-uppercase">Alarm Chime Ringtone Melody</label>
                        <div class="input-group">
                            <select name="alarm_sound_tone" class="form-select" id="alarmToneSelect">
                                <option value="concierge_bell" <?php echo (($settings['alarm_sound_tone'] ?? 'concierge_bell') === 'concierge_bell') ? 'selected' : ''; ?>>
                                    Concierge Bell (Luxury 4-Note Melodic Chime)
                                </option>
                                <option value="crystal_reception" <?php echo (($settings['alarm_sound_tone'] ?? '') === 'crystal_reception') ? 'selected' : ''; ?>>
                                    Crystal Reception (Bright 3-Note Chime)
                                </option>
                                <option value="royal_brass" <?php echo (($settings['alarm_sound_tone'] ?? '') === 'royal_brass') ? 'selected' : ''; ?>>
                                    Royal Brass (Grand Stately Chime)
                                </option>
                                <option value="modern_ding" <?php echo (($settings['alarm_sound_tone'] ?? '') === 'modern_ding') ? 'selected' : ''; ?>>
                                    Modern Ding (Subtle Double Ding)
                                </option>
                            </select>
                            <button type="button" class="btn btn-outline-danger fw-semibold" id="btnPreviewTone" onclick="previewCurrentTone()">
                                <i class="bx bx-play-circle me-1"></i> Audition
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">Click "Audition" to test and listen to the selected tone.</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-danger fw-bold px-4 py-2" style="background:#E73539; border-radius: 8px;">
                    <i class="bx bx-save me-1"></i> Save All Settings
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Reference Card -->
    <div class="col-lg-4">
        <div class="admin-card p-4">
            <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">Operational Overview</h5>
            <div class="d-flex flex-column gap-3 small">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-check-shield text-success fs-5"></i>
                    <div>
                        <strong class="d-block text-dark">Zero Online Prepayment</strong>
                        <span class="text-muted">All accommodation and deposits settled physically upon check-in.</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-envelope text-primary fs-5"></i>
                    <div>
                        <strong class="d-block text-dark">Dual Email Dispatch</strong>
                        <span class="text-muted">Instant pass to guest + alert to hotel reception via secure SMTP.</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bxl-whatsapp text-success fs-5"></i>
                    <div>
                        <strong class="d-block text-dark">Concierge Sync</strong>
                        <span class="text-muted">One-tap messaging enabled for all pending reservations.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewCurrentTone() {
    const toneSelect = document.getElementById('alarmToneSelect');
    const tone = toneSelect ? toneSelect.value : 'concierge_bell';
    if (window.TianoAlerts && typeof window.TianoAlerts.previewTone === 'function') {
        window.TianoAlerts.previewTone(tone);
    }
}

function toggleCautionDeposit(active) {
    const notice = document.getElementById('cautionStatusNotice');
    const input = document.getElementById('cautionDepositInput');
    if (!notice) return;

    if (active) {
        notice.className = 'small fw-medium p-2 rounded-2 bg-success-subtle text-success';
        notice.innerHTML = '<i class="bx bx-check-circle me-1"></i> <strong>Active:</strong> Guests see this deposit itemised on the booking quote and digital passes.';
        if (input && Number(input.value) === 0) {
            input.value = 10000;
        }
    } else {
        notice.className = 'small fw-medium p-2 rounded-2 bg-light text-muted border';
        notice.innerHTML = '<i class="bx bx-hide me-1"></i> <strong>Hidden:</strong> Completely removed from front-end booking summary, live quote, and total calculation.';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
