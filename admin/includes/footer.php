<?php
/**
 * Tiano Hotel & Suites - Admin Layout Footer
 */
?>
        </main> <!-- End .admin-content -->
    </div> <!-- End .admin-main -->
</div> <!-- End .admin-wrapper -->

<!-- Universal Confirmation Modal (Replaces browser confirm() dialogs) -->
<div class="modal fade" id="tianoConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: rgba(231, 53, 57, 0.1); color: #E73539;">
                    <i class="bx bx-error-circle" style="font-size: 38px;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="tianoConfirmTitle">Confirm Action</h5>
                <p class="text-muted mb-4 small" id="tianoConfirmMessage" style="font-size: 13.5px; line-height: 1.6;">Are you sure you wish to proceed with this operation?</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light fw-bold px-4 py-2 rounded-3 text-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger fw-bold px-4 py-2 rounded-3" id="tianoConfirmActionBtn" style="background: #E73539; border: none;">Proceed</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Toast Notification Container (Positioned Safely Below Topbar) -->
<div class="toast-container position-fixed end-0 p-3" style="top: 76px; z-index: 10060; pointer-events: none;" id="adminToastContainer">
    <?php if (!empty($success)): ?>
        <div class="toast align-items-center text-white border-0 show shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 14px; background-color: #10b981 !important; min-width: 320px;">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3 px-3">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-check fs-4 text-white"></i>
                    </div>
                    <div>
                        <strong class="d-block text-white" style="font-size: 13.5px;">Success</strong>
                        <span class="text-white-50" style="font-size: 12.5px; color: #ecfdf5 !important;"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="toast align-items-center text-white border-0 show shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 14px; background-color: #E73539 !important; min-width: 320px;">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3 px-3">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx bx-error fs-4 text-white"></i>
                    </div>
                    <div>
                        <strong class="d-block text-white" style="font-size: 13.5px;">Action Notice</strong>
                        <span class="text-white-50" style="font-size: 12.5px; color: #ffe4e6 !important;"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Mobile Off-Canvas Sidebar Backdrop Overlay -->
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<!-- Scripts -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/custom-select.js?v=<?php echo time(); ?>"></script>
<script src="assets/js/live-alerts.js?v=<?php echo time(); ?>"></script>
<script>
    // Universal Toast Notification Dispatcher
    window.showAdminToast = function(title, message, type) {
        type = type || 'success';
        const bgColors = {
            success: '#10b981',
            danger: '#E73539',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        const icons = {
            success: 'bx-check',
            danger: 'bx-error',
            warning: 'bx-error-circle',
            info: 'bx-info-circle'
        };
        const container = document.getElementById('adminToastContainer');
        if (!container) return;

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center text-white border-0 shadow-lg mb-2';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.style.borderRadius = '14px';
        toastEl.style.backgroundColor = (bgColors[type] || bgColors.success) + ' !important';
        toastEl.style.minWidth = '320px';
        toastEl.style.pointerEvents = 'auto';

        toastEl.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-3 py-3 px-3">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="bx ${icons[type] || 'bx-bell'} fs-4 text-white"></i>
                    </div>
                    <div>
                        <strong class="d-block text-white" style="font-size: 13.5px;">${title}</strong>
                        <span class="text-white-50" style="font-size: 12.5px; color: #ffffff !important;">${message}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 6000, autohide: true });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', function () {
            if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
        });
    };

    // Universal Confirmation Modal Invoker
    let pendingConfirmAction = null;
    window.tianoConfirm = function(title, message, confirmBtnText, onConfirm) {
        document.getElementById('tianoConfirmTitle').textContent = title || 'Confirm Action';
        document.getElementById('tianoConfirmMessage').textContent = message || 'Are you sure you want to proceed?';
        const actionBtn = document.getElementById('tianoConfirmActionBtn');
        actionBtn.textContent = confirmBtnText || 'Proceed';
        pendingConfirmAction = onConfirm;

        const modalEl = document.getElementById('tianoConfirmModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    document.getElementById('tianoConfirmActionBtn')?.addEventListener('click', function () {
        const modalEl = document.getElementById('tianoConfirmModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        if (typeof pendingConfirmAction === 'function') {
            pendingConfirmAction();
            pendingConfirmAction = null;
        }
    });

    // Auto-init server toasts on load
    document.addEventListener('DOMContentLoaded', function () {
        var toasts = document.querySelectorAll('#adminToastContainer .toast');
        toasts.forEach(function (el) {
            var bs = new bootstrap.Toast(el, { delay: 6500, autohide: true });
            bs.show();
        });
    });

    // Robust Mobile Off-Canvas Sidebar Drawer Controller
    (function () {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (backdrop) backdrop.classList.add('show');
            document.body.classList.add('sidebar-open');
        }

        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (backdrop) backdrop.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeSidebar();
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Close when clicking nav links on mobile
        if (sidebar) {
            sidebar.querySelectorAll('.admin-nav-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });
        }
    })();
</script>
</body>
</html>

