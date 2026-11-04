/* ============================================================
   SILAPIN — Admin Master Data helpers
   ============================================================ */
(function () {
    'use strict';

    const AdminUI = {};

    /* -------------------- Toast -------------------- */
    let toastTimer = null;

    AdminUI.toast = function (message, type) {
        if (!message) return;
        type = type || 'success';

        const wrap = document.getElementById('adminToast');
        const icon = document.getElementById('adminToastIcon');
        const text = document.getElementById('adminToastText');
        if (!wrap || !icon || !text) return;

        const styles = {
            success: ['fa-check-circle', 'text-emerald-400'],
            error: ['fa-exclamation-circle', 'text-rose-400'],
            info: ['fa-info-circle', 'text-blue-400'],
        };
        const style = styles[type] || styles.info;

        icon.className = 'fas ' + style[0] + ' mt-0.5 text-base ' + style[1];
        text.textContent = message;

        wrap.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            wrap.classList.add('hidden');
        }, 4000);
    };

    /* -------------------- Modal / Drawer -------------------- */
    function syncBodyLock() {
        const open = document.querySelector('.admin-modal:not(.hidden), .admin-drawer:not(.hidden)');
        document.body.classList.toggle('modal-open', !!open);
    }

    AdminUI.openModal = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        syncBodyLock();
    };

    AdminUI.closeModal = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
        syncBodyLock();
    };

    AdminUI.openDrawer = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex', 'justify-end');
        syncBodyLock();
    };

    AdminUI.closeDrawer = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex', 'justify-end');
        syncBodyLock();
    };

    AdminUI.open = AdminUI.openModal;
    AdminUI.close = AdminUI.closeModal;

    /* -------------------- Delete confirmation -------------------- */
    AdminUI.confirmDelete = function (action, name) {
        const form = document.getElementById('deleteForm');
        const label = document.getElementById('deleteName');
        if (form) form.action = action || '';
        if (label) label.textContent = name ? '"' + name + '"' : 'data ini';
        AdminUI.openModal('deleteModal');
    };

    /* -------------------- CSV export -------------------- */
    AdminUI.exportCsv = function (filename, headers, rows) {
        const escape = function (value) {
            return '"' + String(value === null || value === undefined ? '' : value).replace(/"/g, '""') + '"';
        };
        const lines = [headers].concat(rows).map(function (row) {
            return row.map(escape).join(',');
        });
        const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
        AdminUI.toast('Data berhasil diekspor.', 'success');
    };

    /* -------------------- View toggle -------------------- */
    function applyView(group, target, view) {
        group.querySelectorAll('[data-view]').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.dataset.view === view);
        });
        target.querySelectorAll('[data-view-panel]').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.viewPanel !== view);
        });
    }

    function initViewToggles() {
        document.querySelectorAll('[data-view-group]').forEach(function (group) {
            const target = document.querySelector(group.dataset.viewTarget);
            if (!target) return;

            const key = 'admin-view-' + (target.id || 'default');
            const saved = localStorage.getItem(key);
            if (saved === 'table' || saved === 'grid') {
                applyView(group, target, saved);
            }

            group.querySelectorAll('[data-view]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const view = btn.dataset.view;
                    if (view !== 'table' && view !== 'grid') return;
                    applyView(group, target, view);
                    localStorage.setItem(key, view);
                });
            });
        });
    }

    /* -------------------- Sidebar -------------------- */
    function initSidebar() {
        const toggle = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        const desktop = window.matchMedia('(min-width: 1024px)');

        if (desktop.matches && localStorage.getItem('admin-sidebar-collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        if (toggle) {
            toggle.addEventListener('click', function () {
                if (desktop.matches) {
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem(
                        'admin-sidebar-collapsed',
                        document.body.classList.contains('sidebar-collapsed') ? '1' : '0'
                    );
                } else {
                    document.body.classList.toggle('sidebar-open');
                }
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function () {
                document.body.classList.remove('sidebar-open');
            });
        }

        window.addEventListener('resize', function () {
            if (desktop.matches) {
                document.body.classList.remove('sidebar-open');
            }
        });
    }

    /* -------------------- Topbar -------------------- */
    function initTopbar() {
        const userToggle = document.getElementById('userMenuToggle');
        const userMenu = document.getElementById('userMenu');

        if (userToggle && userMenu) {
            userToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                userMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', function (event) {
                if (!userMenu.contains(event.target) && !userToggle.contains(event.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }

        const fullscreen = document.getElementById('fullscreenToggle');
        if (fullscreen) {
            fullscreen.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen && document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen && document.exitFullscreen();
                }
            });
        }
    }

    /* -------------------- Global bindings -------------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initTopbar();
        initViewToggles();

        document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const container = btn.closest('.admin-modal, .admin-drawer');
                if (container) {
                    AdminUI.close(container.id);
                }
            });
        });

        document.querySelectorAll('.admin-modal, .admin-drawer').forEach(function (el) {
            el.addEventListener('click', function (event) {
                if (event.target === el) {
                    AdminUI.close(el.id);
                }
            });
        });

        const toastClose = document.getElementById('adminToastClose');
        if (toastClose) {
            toastClose.addEventListener('click', function () {
                document.getElementById('adminToast').classList.add('hidden');
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            const open = document.querySelector('.admin-modal:not(.hidden), .admin-drawer:not(.hidden)');
            if (open) {
                AdminUI.close(open.id);
                return;
            }
            document.body.classList.remove('sidebar-open');
        });

        const flash = window.__flash || {};
        if (flash.success) AdminUI.toast(flash.success, 'success');
        if (flash.error) AdminUI.toast(flash.error, 'error');
        if (Array.isArray(flash.errors) && flash.errors.length) {
            AdminUI.toast(flash.errors[0], 'error');
        }
    });

    window.AdminUI = AdminUI;
})();
