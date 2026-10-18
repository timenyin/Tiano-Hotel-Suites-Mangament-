/**
 * Tiano Hotel & Suites - 24/7 Front-Desk Live Audio Alarm & Real-Time Alert System
 * - Synthesizes a luxury hotel concierge chime ringtone via Web Audio API & HTML5 Audio
 * - Polls for incoming bookings & inquiries every 2.5 seconds
 * - Dynamically updates dashboard tables, metrics, and badges in real-time WITHOUT RELOADING
 * - Displays high-priority floating visual alerts with audio chime loops
 */
(function () {
    'use strict';

    var audioCtx = null;
    var alarmInterval = null;
    var alarmTimeout = null;
    var isAlarmActive = false;
    var remainingSeconds = 30;
    var countdownInterval = null;
    var activeBookingId = null;
    var activeMessageId = null;
    var audioUnlocked = false;
    var titleBlinkInterval = null;
    var originalDocumentTitle = document.title;
    var audioElement = null;
    var alarmSoundEnabled = 1; // 1 = enabled, 0 = silent mode
    var currentAlarmTone = 'concierge_bell'; // concierge_bell, crystal_reception, royal_brass, modern_ding

    // Highest seen IDs in this session
    var lastBookingId = parseInt(sessionStorage.getItem('tiano_last_seen_booking_id'), 10) || null;
    var lastMessageId = parseInt(sessionStorage.getItem('tiano_last_seen_message_id'), 10) || null;
    var isFirstPoll = (lastBookingId === null);

    // Resolve absolute path to api/live_alerts.php
    function getApiEndpointUrl() {
        var path = window.location.pathname;
        var adminIdx = path.toLowerCase().indexOf('/admin');
        if (adminIdx !== -1) {
            return path.substring(0, adminIdx) + '/admin/api/live_alerts.php';
        }
        return 'api/live_alerts.php';
    }

    // ─── Programmatic 16-bit WAV Audio Synthesis (Concierge Bell Chime) ───
    function buildConciergeChimeWav() {
        try {
            var sampleRate = 22050;
            var duration = 2.4;
            var numSamples = Math.floor(sampleRate * duration);
            var freqs = [659.25, 830.61, 987.77, 1318.51]; // E5, G#5, B5, E6
            var times = [0.02, 0.18, 0.34, 0.52];
            var bytesPerSample = 2;
            var dataSize = numSamples * bytesPerSample;
            var buffer = new ArrayBuffer(44 + dataSize);
            var view = new DataView(buffer);

            // RIFF header
            writeString(view, 0, 'RIFF');
            view.setUint32(4, 36 + dataSize, true);
            writeString(view, 8, 'WAVE');
            writeString(view, 12, 'fmt ');
            view.setUint32(16, 16, true);
            view.setUint16(20, 1, true); // PCM
            view.setUint16(22, 1, true); // Mono
            view.setUint32(24, sampleRate, true);
            view.setUint32(28, sampleRate * bytesPerSample, true);
            view.setUint16(32, bytesPerSample, true);
            view.setUint16(34, 16, true); // Bits per sample
            writeString(view, 36, 'data');
            view.setUint32(40, dataSize, true);

            // Audio samples with realistic bell chime harmonics
            var offset = 44;
            for (var i = 0; i < numSamples; i++) {
                var t = i / sampleRate;
                var sample = 0;
                for (var j = 0; j < freqs.length; j++) {
                    var st = times[j];
                    if (t >= st) {
                        var dt = t - st;
                        var env = Math.exp(-dt * 3.0);
                        // Fundamental + metallic shimmer overtone
                        sample += Math.sin(2 * Math.PI * freqs[j] * dt) * env * 0.45;
                        sample += Math.sin(2 * Math.PI * (freqs[j] * 2.76) * dt) * Math.exp(-dt * 6.0) * 0.18;
                    }
                }
                sample = Math.max(-1, Math.min(1, sample));
                view.setInt16(offset, sample < 0 ? sample * 0x8000 : sample * 0x7FFF, true);
                offset += 2;
            }

            var blob = new Blob([buffer], { type: 'audio/wav' });
            return URL.createObjectURL(blob);
        } catch (e) {
            return null;
        }
    }

    function writeString(view, offset, string) {
        for (var i = 0; i < string.length; i++) {
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    }

    // Initialize HTML5 Audio Element with generated bell chime
    var chimeObjectUrl = buildConciergeChimeWav();
    if (chimeObjectUrl) {
        audioElement = new Audio(chimeObjectUrl);
        audioElement.preload = 'auto';
    }

    // Initialize or resume Web Audio context
    function getAudioContext() {
        if (!audioCtx) {
            var AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume().catch(function () {});
        }
        return audioCtx;
    }

    // Update UI indicator for audio readiness
    function updateAudioReadyIndicator(isReady) {
        var pill = document.getElementById('tianoAudioPill');
        if (pill) {
            if (alarmSoundEnabled === 0) {
                pill.className = 'btn btn-sm btn-light border rounded-pill d-inline-flex align-items-center gap-1 py-1 px-2 text-muted shadow-sm';
                pill.innerHTML = '<i class="bx bx-volume-mute text-secondary"></i> <span class="d-none d-md-inline">Chime Muted</span>';
                pill.title = 'Front-Desk Chime is Muted in Hotel Settings';
            } else if (isReady) {
                pill.className = 'btn btn-sm btn-light border rounded-pill d-inline-flex align-items-center gap-1 py-1 px-2 text-dark shadow-sm';
                pill.innerHTML = '<i class="bx bx-volume-full text-success"></i> <span class="d-none d-md-inline">Audio Active</span>';
                pill.title = '24/7 Front-Desk Audio Chime is Armed & Ready (' + currentAlarmTone.replace('_', ' ') + ')';
            } else {
                pill.className = 'btn btn-sm btn-warning border rounded-pill d-inline-flex align-items-center gap-1 py-1 px-2 text-dark shadow-sm';
                pill.innerHTML = '<i class="bx bx-volume-mute text-danger"></i> <span class="d-none d-md-inline">Click to Arm</span>';
                pill.title = 'Click anywhere to arm front-desk audio chime';
            }
        }
    }

    // Universal audio engine unlock (runs on first user interaction)
    function primeAudio(playPing) {
        // 1. Prime Web Audio
        var ctx = getAudioContext();
        if (ctx) {
            if (ctx.state === 'suspended') {
                ctx.resume().catch(function () {});
            }
            try {
                var buf = ctx.createBuffer(1, 1, 22050);
                var src = ctx.createBufferSource();
                src.buffer = buf;
                src.connect(ctx.destination);
                src.start(0);
            } catch (e) {}
        }

        // 2. Prime HTML5 Audio element
        if (audioElement) {
            var p = audioElement.play();
            if (p !== undefined) {
                p.then(function () {
                    audioElement.pause();
                    audioElement.currentTime = 0;
                    audioUnlocked = true;
                    updateAudioReadyIndicator(true);
                }).catch(function () {});
            }
        }

        audioUnlocked = true;
        updateAudioReadyIndicator(true);

        // Optional short pleasant ping on manual arming
        if (playPing === true && ctx && alarmSoundEnabled === 1) {
            playChimeNote(ctx, 1318.51, ctx.currentTime + 0.05, 0.6, 0.4);
        }
    }

    // Attach passive user gesture listeners for instant audio unlock
    window.addEventListener('click', function () { primeAudio(false); }, { passive: true });
    window.addEventListener('pointerdown', function () { primeAudio(false); }, { passive: true });
    window.addEventListener('keydown', function () { primeAudio(false); }, { passive: true });
    window.addEventListener('touchstart', function () { primeAudio(false); }, { passive: true });
    window.addEventListener('focus', function () {
        primeAudio(false);
        // If alarm was ringing while tab was in background, play chime now
        if (isAlarmActive) {
            playConciergeChime();
        }
    });

    // Synthesize a single luxury hotel concierge bell chime note via Web Audio
    function playChimeNote(ctx, freq, startTime, duration, gainValue) {
        try {
            var actualStart = Math.max(ctx.currentTime + 0.01, startTime);
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, actualStart);

            // Rich metallic overtone for luxury brass bell
            var overtone = ctx.createOscillator();
            var overtoneGain = ctx.createGain();
            overtone.type = 'triangle';
            overtone.frequency.setValueAtTime(freq * 2.76, actualStart);

            var vol = gainValue || 0.65;
            gain.gain.setValueAtTime(0.001, actualStart);
            gain.gain.linearRampToValueAtTime(vol, actualStart + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, actualStart + duration);

            overtoneGain.gain.setValueAtTime(0.001, actualStart);
            overtoneGain.gain.linearRampToValueAtTime(vol * 0.22, actualStart + 0.01);
            overtoneGain.gain.exponentialRampToValueAtTime(0.0001, actualStart + duration * 0.6);

            osc.connect(gain);
            gain.connect(ctx.destination);
            overtone.connect(overtoneGain);
            overtoneGain.connect(ctx.destination);

            osc.start(actualStart);
            osc.stop(actualStart + duration);
            overtone.start(actualStart);
            overtone.stop(actualStart + duration * 0.6);
        } catch (e) {
            console.warn('[Tiano Audio] Note play error', e);
        }
    }

    // Execute luxury melodic chime (supports 4 selectable melodies)
    function playConciergeChime(toneOverride) {
        if (alarmSoundEnabled === 0 && !toneOverride) {
            return; // Silent Mode: chime suppressed by hotel settings
        }

        var played = false;
        var tone = toneOverride || currentAlarmTone;

        // Engine 1: HTML5 Audio with pre-synthesized WAV (for default concierge_bell)
        if (tone === 'concierge_bell' && audioElement) {
            try {
                audioElement.currentTime = 0;
                var promise = audioElement.play();
                if (promise !== undefined) {
                    promise.then(function () {
                        played = true;
                    }).catch(function (e) {});
                }
            } catch (e) {}
        }

        // Engine 2: Web Audio API Oscillator synthesis
        var ctx = getAudioContext();
        if (ctx) {
            if (ctx.state === 'suspended') {
                ctx.resume().then(function () {
                    runWebAudioSequence(ctx, tone);
                }).catch(function () {});
            } else {
                runWebAudioSequence(ctx, tone);
            }
        }
    }

    function runWebAudioSequence(ctx, tone) {
        tone = tone || currentAlarmTone;
        try {
            var now = ctx.currentTime;
            if (tone === 'crystal_reception') {
                // Bright 3-note crystal chime: C5, G5, C6
                playChimeNote(ctx, 523.25, now + 0.02, 1.2, 0.65);
                playChimeNote(ctx, 783.99, now + 0.20, 1.4, 0.70);
                playChimeNote(ctx, 1046.50, now + 0.40, 2.2, 0.78);
            } else if (tone === 'royal_brass') {
                // Grand stately brass chime: D5, F#5, A5, D6
                playChimeNote(ctx, 587.33, now + 0.02, 1.3, 0.65);
                playChimeNote(ctx, 739.99, now + 0.18, 1.4, 0.70);
                playChimeNote(ctx, 880.00, now + 0.36, 1.5, 0.75);
                playChimeNote(ctx, 1174.66, now + 0.54, 2.5, 0.80);
            } else if (tone === 'modern_ding') {
                // Subtle modern double ding: A5, E6
                playChimeNote(ctx, 880.00, now + 0.02, 0.8, 0.62);
                playChimeNote(ctx, 1318.51, now + 0.16, 2.0, 0.76);
            } else {
                // Default: concierge_bell (Luxury 4-note melodic chime)
                playChimeNote(ctx, 659.25, now + 0.02, 1.4, 0.65); // E5
                playChimeNote(ctx, 830.61, now + 0.18, 1.5, 0.70); // G#5
                playChimeNote(ctx, 987.77, now + 0.34, 1.6, 0.75); // B5
                playChimeNote(ctx, 1318.51, now + 0.52, 2.5, 0.80); // E6
            }
        } catch (e) {}
    }

    // Start blinking document title for high-visibility tab alerts
    function startTitleBlink(text) {
        stopTitleBlink();
        var isAlert = false;
        titleBlinkInterval = setInterval(function () {
            document.title = isAlert ? originalDocumentTitle : ('🔔 ' + text + ' 🔔');
            isAlert = !isAlert;
        }, 800);
    }

    function stopTitleBlink() {
        if (titleBlinkInterval) {
            clearInterval(titleBlinkInterval);
            titleBlinkInterval = null;
        }
        document.title = originalDocumentTitle;
    }

    // Start repeating front-desk alarm (30 seconds)
    function triggerFrontDeskAlarm(title, subtitle, actionUrl, actionText, bookingId, messageId) {
        if (isAlarmActive) {
            silenceAlarm();
        }

        isAlarmActive = true;
        activeBookingId = bookingId || null;
        activeMessageId = messageId || null;
        remainingSeconds = 30;

        // Start chime immediately and loop every 2.8s
        playConciergeChime();
        alarmInterval = setInterval(function () {
            playConciergeChime();
        }, 2800);

        // Auto-silence after 30 seconds
        alarmTimeout = setTimeout(function () {
            silenceAlarm();
        }, 30000);

        // Blinking browser tab title
        startTitleBlink(title);

        // Display floating high-priority alert card
        showFloatingAlert(title, subtitle, actionUrl, actionText);

        // Countdown timer for 30s
        countdownInterval = setInterval(function () {
            remainingSeconds--;
            var countdownEl = document.getElementById('tianoAlarmCountdown');
            if (countdownEl) {
                countdownEl.textContent = remainingSeconds + 's remaining';
            }
            if (remainingSeconds <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    // Silence the alarm immediately
    function silenceAlarm() {
        if (alarmInterval) {
            clearInterval(alarmInterval);
            alarmInterval = null;
        }
        if (alarmTimeout) {
            clearTimeout(alarmTimeout);
            alarmTimeout = null;
        }
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        isAlarmActive = false;
        stopTitleBlink();

        // Mark active item as silenced in this session
        if (activeBookingId) {
            sessionStorage.setItem('tiano_silenced_b_' + activeBookingId, '1');
            activeBookingId = null;
        }
        if (activeMessageId) {
            sessionStorage.setItem('tiano_silenced_m_' + activeMessageId, '1');
            activeMessageId = null;
        }

        var alertBanner = document.getElementById('tianoLiveAlertBanner');
        if (alertBanner) {
            alertBanner.classList.remove('show');
            setTimeout(function () {
                if (alertBanner.parentNode) {
                    alertBanner.parentNode.removeChild(alertBanner);
                }
            }, 300);
        }
    }

    // Build and display floating high-priority alert banner
    function showFloatingAlert(title, subtitle, actionUrl, actionText) {
        var existing = document.getElementById('tianoLiveAlertBanner');
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }

        var banner = document.createElement('div');
        banner.id = 'tianoLiveAlertBanner';
        banner.className = 'tiano-floating-alarm show'; // Show immediately
        banner.innerHTML = `
            <div class="tiano-alarm-card">
                <div class="tiano-alarm-icon-wrap" style="width: 48px; height: 48px; border-radius: 50% !important; aspect-ratio: 1/1; background: #fee2e2; color: #E73539; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; position: relative;">
                    <i class="bx bxs-bell-ring bx-tada"></i>
                    <span class="tiano-alarm-pulse" style="position: absolute; inset: 0; border-radius: 50% !important; border: 2px solid #E73539; animation: alarmBorderPulse 1.4s infinite;"></span>
                </div>
                <div class="tiano-alarm-content" style="flex-grow: 1; min-width: 0;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="tiano-alarm-badge" style="font-size: 10px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; color: #E73539; background: #fff1f2; padding: 2px 8px; border-radius: 50px;">
                            24/7 FRONT DESK ALERT
                        </span>
                        <span class="tiano-alarm-timer" id="tianoAlarmCountdown" style="font-size: 11px; font-weight: 700; color: #64748b;">
                            30s remaining
                        </span>
                    </div>
                    <h6 class="tiano-alarm-title" style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 6px 0 2px;">
                        ${escapeHtml(title)}
                    </h6>
                    <p class="tiano-alarm-desc" style="font-size: 13px; color: #475569; margin-bottom: 12px; line-height: 1.4;">
                        ${escapeHtml(subtitle)}
                    </p>
                    <div class="tiano-alarm-actions d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-dark fw-bold px-3 py-1 rounded-pill" id="tianoSilenceBtn">
                            <i class="bx bx-volume-mute me-1"></i> Silence Alarm
                        </button>
                        ${actionUrl ? `
                            <a href="${actionUrl}" class="btn btn-sm btn-danger fw-bold px-3 py-1 rounded-pill" id="tianoViewDetailsBtn" style="background:#E73539;">
                                ${escapeHtml(actionText || 'Open Booking')} &rarr;
                            </a>
                        ` : ''}
                    </div>
                </div>
                <button type="button" class="tiano-alarm-close" id="tianoAlarmCloseBtn" aria-label="Dismiss" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0 4px; line-height: 1;">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        `;

        document.body.appendChild(banner);

        // Attach listeners
        var silenceBtn = document.getElementById('tianoSilenceBtn');
        if (silenceBtn) {
            silenceBtn.addEventListener('click', silenceAlarm);
        }
        var closeBtn = document.getElementById('tianoAlarmCloseBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', silenceAlarm);
        }
        var viewBtn = document.getElementById('tianoViewDetailsBtn');
        if (viewBtn) {
            viewBtn.addEventListener('click', function () {
                silenceAlarm();
            });
        }
        // Clicking on the banner card also triggers audio if it was muted
        banner.addEventListener('click', primeAudio);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Format date string to "15 Oct"
    function formatShortDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr.replace(/-/g, '/'));
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return (d.getDate() || '') + ' ' + (months[d.getMonth()] || '');
    }

    // ─── Real-Time Dynamic DOM Updates Without Reloading ───

    function updateLiveCounters(data) {
        // Sidebar Bookings badge
        var sBookings = document.getElementById('sidebarBookingsBadge');
        if (sBookings) {
            if (data.unread_bookings > 0) {
                sBookings.textContent = data.unread_bookings;
                sBookings.classList.remove('d-none');
            } else {
                sBookings.classList.add('d-none');
            }
        }

        // Sidebar Messages badge
        var sMessages = document.getElementById('sidebarMessagesBadge');
        if (sMessages) {
            if (data.unread_messages > 0) {
                sMessages.textContent = data.unread_messages;
                sMessages.classList.remove('d-none');
            } else {
                sMessages.classList.add('d-none');
            }
        }

        // Topbar Notification Bell badge
        var bellBadge = document.getElementById('headerAlertsBadge');
        if (bellBadge) {
            if (data.total_notifications > 0) {
                bellBadge.textContent = data.total_notifications;
                bellBadge.classList.remove('d-none');
            } else {
                bellBadge.classList.add('d-none');
            }
        }

        // Dashboard Metric Cards (if on dashboard.php)
        if (data.stats) {
            var statArrivals = document.getElementById('statArrivalsToday');
            if (statArrivals) statArrivals.textContent = data.stats.arrivals_today + ' Check-Ins';

            var statDepartures = document.getElementById('statDeparturesToday');
            if (statDepartures) statDepartures.textContent = data.stats.departures_today + ' Check-Outs';

            var statActive = document.getElementById('statActiveBookings');
            if (statActive) statActive.textContent = data.stats.active_bookings + ' Occupied';

            var statPendingRev = document.getElementById('statPendingReceivables');
            if (statPendingRev) statPendingRev.textContent = data.stats.pending_revenue_fmt;

            var statPendingCount = document.getElementById('statPendingCount');
            if (statPendingCount) statPendingCount.textContent = data.stats.unread_bookings_count + ' Pay on Arrival at Desk';
        }
    }

    // Prepend newly arrived booking to the topbar dropdown list
    function prependHeaderDropdownBooking(b) {
        var list = document.getElementById('headerRecentBookingsList');
        if (!list) return;

        var ref = escapeHtml(b.booking_reference || '');
        var guest = escapeHtml(b.guest_name || 'Guest');
        var item = document.createElement('a');
        item.href = 'bookings.php?search=' + encodeURIComponent(ref);
        item.className = 'dropdown-item p-2 rounded-3 d-flex align-items-start gap-2 text-wrap bg-danger-subtle';
        item.innerHTML = `
            <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; min-width: 36px; aspect-ratio: 1/1;">
                <i class="bx bx-bell fs-6"></i>
            </div>
            <div>
                <small class="fw-bold text-dark d-block">#${ref} &bull; ${guest}</small>
                <small class="text-danger fw-bold" style="font-size: 10px;">Just Now &bull; New Booking</small>
            </div>
        `;

        if (list.firstChild) {
            list.insertBefore(item, list.firstChild);
        } else {
            list.appendChild(item);
        }
    }

    // Prepend newly arrived booking to Dashboard Recent Ledger table without reloading
    function prependDashboardLedgerRow(b) {
        var tbody = document.getElementById('dashboardLedgerTbody');
        if (!tbody) return;

        // If table has empty row, remove it
        var emptyNotice = tbody.querySelector('td[colspan]');
        if (emptyNotice && emptyNotice.parentNode) {
            emptyNotice.parentNode.remove();
        }

        var ref = escapeHtml(b.booking_reference || '');
        var guest = escapeHtml(b.guest_name || 'Guest');
        var phone = escapeHtml(b.guest_phone || '');
        var tier = escapeHtml(b.category_name || 'Room');
        var checkIn = formatShortDate(b.check_in_date);
        var checkOut = formatShortDate(b.check_out_date);
        var nights = b.nights || 1;
        var nightly = Number(b.nightly_rate || 0).toLocaleString();
        var caution = Number(b.caution_deposit || 0).toLocaleString();
        var total = (Number(b.total_amount || 0) + Number(b.caution_deposit || 0)).toLocaleString();

        var tr = document.createElement('tr');
        tr.className = 'tiano-live-new-row';
        tr.innerHTML = `
            <td>
                <strong class="text-danger fw-bold">${ref}</strong>
                <span class="badge bg-danger text-white ms-1" style="font-size:9px;">NEW</span>
            </td>
            <td>
                <div class="fw-bold text-dark">${guest}</div>
                <small class="text-muted">${phone}</small>
            </td>
            <td>
                <span class="badge bg-light text-dark border">${tier}</span>
            </td>
            <td>
                <small class="text-dark fw-semibold">${checkIn} &rarr; ${checkOut}</small>
            </td>
            <td>${nights}</td>
            <td>₦${nightly}</td>
            <td>₦${caution}</td>
            <td class="fw-bold text-dark fs-6">₦${total}</td>
            <td>
                <span class="status-badge status-badge-warning">
                    <span class="status-dot"></span>
                    Pay at Desk
                </span>
            </td>
            <td class="text-end d-print-none">
                <a href="../voucher.php?ref=${encodeURIComponent(ref)}" target="_blank" class="btn btn-sm btn-outline-danger" style="border-radius:8px;" title="Open Boarding Pass">
                    <i class="bx bx-receipt"></i>
                </a>
            </td>
        `;

        if (tbody.firstChild) {
            tbody.insertBefore(tr, tbody.firstChild);
        } else {
            tbody.appendChild(tr);
        }
    }

    // Prepend newly arrived booking to Bookings Management table without reloading
    function prependBookingsTableRow(b) {
        var tbody = document.getElementById('bookingsTableTbody');
        if (!tbody) return;

        var emptyNotice = tbody.querySelector('td[colspan]');
        if (emptyNotice && emptyNotice.parentNode) {
            emptyNotice.parentNode.remove();
        }

        var ref = escapeHtml(b.booking_reference || '');
        var guest = escapeHtml(b.guest_name || 'Guest');
        var phone = escapeHtml(b.guest_phone || '');
        var tier = escapeHtml(b.category_name || 'Room');
        var checkIn = formatShortDate(b.check_in_date);
        var checkOut = formatShortDate(b.check_out_date);
        var nights = b.nights || 1;
        var total = Number(b.total_amount || 0).toLocaleString();

        var cleanPhone = (b.guest_phone || '').replace(/[^0-9]/g, '');
        var waMsg = "Hello " + (b.guest_name || 'Guest') + ", this is front desk at Tiano Hotel & Suites regarding your reservation (" + ref + ").";
        var waUrl = "https://wa.me/" + cleanPhone + "?text=" + encodeURIComponent(waMsg);

        var tr = document.createElement('tr');
        tr.className = 'tiano-live-new-row';
        tr.innerHTML = `
            <td class="text-center">
                <input type="checkbox" name="selected_booking_ids[]" value="${b.id || ''}" class="form-check-input booking-row-checkbox" onchange="updateBookingsSelection()">
            </td>
            <td class="text-center">
                <span class="badge bg-danger text-white fw-bold" style="font-size:11px;">NEW</span>
            </td>
            <td>
                <strong class="text-danger fw-bold fs-6 d-block">${ref}</strong>
                <small class="text-muted" style="font-size:11px;">Just Now</small>
            </td>
            <td>
                <div class="fw-bold text-dark">${guest}</div>
                <div class="small text-muted"><i class="bx bx-phone me-1"></i> ${phone}</div>
            </td>
            <td>
                <span class="badge bg-light text-dark border">${tier}</span>
                <span class="badge bg-secondary-subtle text-secondary small ms-1">Unassigned</span>
            </td>
            <td>
                <div class="small fw-bold text-dark">${checkIn} &rarr; ${checkOut}</div>
                <small class="text-muted d-block"><i class="bx bx-moon text-danger"></i> ${nights} Night(s) Stay</small>
            </td>
            <td>
                <div class="fw-bold text-dark">₦${total}</div>
                <span class="badge bg-warning text-dark" style="font-size:10px;">UNPAID (PAY ON ARRIVAL)</span>
            </td>
            <td>
                <span class="status-badge status-badge-warning">
                    <span class="status-dot"></span> Confirmed
                </span>
            </td>
            <td class="text-end align-middle">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-light border px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions &amp; Options">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; min-width:205px;">
                        <li>
                            <a class="dropdown-item text-dark fw-semibold" href="bookings.php?search=${encodeURIComponent(ref)}">
                                <i class="bx bx-check-square text-danger me-2"></i> Check-In &amp; Manage
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../voucher.php?ref=${encodeURIComponent(ref)}" target="_blank">
                                <i class="bx bx-receipt text-primary me-2"></i> Boarding Pass / Voucher
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-success" href="${waUrl}" target="_blank" rel="noopener">
                                <i class="bx bxl-whatsapp me-2"></i> Message on WhatsApp
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        `;

        if (tbody.firstChild) {
            tbody.insertBefore(tr, tbody.firstChild);
        } else {
            tbody.appendChild(tr);
        }
    }

    // ─── Polling Routine (Executes Every 2.5 Seconds) ───
    function pollAlerts() {
        var endpoint = getApiEndpointUrl();

        fetch(endpoint, { credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.success) return;

                // Live dynamic metric updates
                updateLiveCounters(data);

                // Synchronize live hotel chime settings
                if (data.alarm_sound_enabled !== undefined) {
                    alarmSoundEnabled = parseInt(data.alarm_sound_enabled, 10);
                }
                if (data.alarm_sound_tone) {
                    currentAlarmTone = data.alarm_sound_tone;
                }
                updateAudioReadyIndicator(audioUnlocked);

                var curLatestBookingId = parseInt(data.latest_booking_id, 10) || 0;
                var curLatestMessageId = parseInt(data.latest_message_id, 10) || 0;

                // ── Initial Poll Baseline ──
                if (isFirstPoll) {
                    isFirstPoll = false;
                    lastBookingId = curLatestBookingId;
                    lastMessageId = curLatestMessageId;
                    sessionStorage.setItem('tiano_last_seen_booking_id', lastBookingId);
                    sessionStorage.setItem('tiano_last_seen_message_id', lastMessageId);

                    // Check if the latest booking was placed in the last 15 minutes and is unhandled
                    if (data.latest_booking && curLatestBookingId > 0) {
                        var b = data.latest_booking;
                        var isUnpaid = (b.booking_status === 'confirmed' && b.payment_status === 'unpaid');
                        var isSilenced = (sessionStorage.getItem('tiano_silenced_b_' + curLatestBookingId) === '1');

                        if (isUnpaid && !isSilenced) {
                            var bTime = b.created_at ? new Date(b.created_at.replace(/-/g, '/')).getTime() : 0;
                            var nowTime = Date.now();
                            if (bTime > 0 && Math.abs(nowTime - bTime) < (20 * 60 * 1000)) {
                                var guest = b.guest_name || 'Guest';
                                var ref = b.booking_reference || '';
                                var amt = b.total_amount ? ('₦' + Number(b.total_amount).toLocaleString()) : '';

                                triggerFrontDeskAlarm(
                                    'New Reservation Received!',
                                    `${guest} placed booking #${ref} (${amt}). Awaiting front-desk settlement.`,
                                    'bookings.php?search=' + encodeURIComponent(ref),
                                    'Open Booking',
                                    curLatestBookingId,
                                    null
                                );
                            }
                        }
                    }
                    return;
                }

                // ── A Brand-New Booking Has Just Arrived in DB! ──
                if (curLatestBookingId > lastBookingId) {
                    lastBookingId = curLatestBookingId;
                    sessionStorage.setItem('tiano_last_seen_booking_id', lastBookingId);

                    var newB = data.latest_booking || {};
                    var newGuest = newB.guest_name || 'Guest';
                    var newRef = newB.booking_reference || '';
                    var newAmt = newB.total_amount ? ('₦' + Number(newB.total_amount).toLocaleString()) : '';

                    // 1. Fire Audio Chime Alarm & Floating Alert Card
                    triggerFrontDeskAlarm(
                        'New Reservation Received!',
                        `${newGuest} placed booking #${newRef} (${newAmt}). Awaiting front-desk settlement.`,
                        'bookings.php?search=' + encodeURIComponent(newRef),
                        'Open Booking',
                        curLatestBookingId,
                        null
                    );

                    // 2. Dynamically inject into Dashboard Ledger without reloading!
                    prependDashboardLedgerRow(newB);

                    // 3. Dynamically inject into Bookings Management Table without reloading!
                    prependBookingsTableRow(newB);

                    // 4. Dynamically prepend to Header Notification Bell Dropdown!
                    prependHeaderDropdownBooking(newB);

                    return;
                }

                // ── A Brand-New Contact Inquiry Arrived! ──
                if (curLatestMessageId > lastMessageId) {
                    lastMessageId = curLatestMessageId;
                    sessionStorage.setItem('tiano_last_seen_message_id', lastMessageId);

                    var newM = data.latest_message || {};
                    var sender = newM.name || 'Guest';
                    var subj = newM.subject || 'New message';

                    triggerFrontDeskAlarm(
                        'New Inbound Message!',
                        `From ${sender}: "${subj}". Unread contact inquiry.`,
                        'messages.php',
                        'Open Inbox',
                        null,
                        curLatestMessageId
                    );
                }
            })
            .catch(function (err) {
                console.warn('[Tiano Alerts Poll]', err.message);
            });
    }

    // High frequency 2.5s polling for instantaneous front-desk alert response
    setInterval(pollAlerts, 2500);
    setTimeout(pollAlerts, 400);

    // Global keyboard shortcut: Escape silences active alarm
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isAlarmActive) {
            silenceAlarm();
        }
    });

    // Expose utilities globally
    window.TianoAlerts = {
        silenceAlarm: silenceAlarm,
        playChime: playConciergeChime,
        primeAudio: primeAudio,
        previewTone: function (tone) {
            primeAudio(false);
            playConciergeChime(tone);
        },
        unlockAudio: function () {
            primeAudio(true);
        },
        testBookingAlarm: function () {
            primeAudio(false);
            triggerFrontDeskAlarm(
                'Front-Desk Concierge Chime Test',
                'Simulating live 24/7 front-desk reservation alert and chime ringtone.',
                'bookings.php',
                'Test Reservations Desk'
            );
        }
    };
})();
