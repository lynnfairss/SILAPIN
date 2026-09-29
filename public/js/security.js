/**
 * SILAPIN Client-Side Protection
 * Blokir context menu, shortcut inspect, deteksi DevTools (overlay freeze),
 * dan jaga atribut validasi form via MutationObserver.
 *
 * SAKLAR: Ctrl+Alt+Spasi (toggle on/off) atau URL ?sec=off
 * Status disimpan di sessionStorage (per tab; reset saat tab ditutup).
 *
 * CATATAN: Ini lapisan deterrent, BUKAN keamanan mutlak.
 * Keamanan inti tetap di server (validasi & otorisasi Laravel).
 */
(function () {
    'use strict';

    if (window.__silapinSecurityLoaded) return;
    window.__silapinSecurityLoaded = true;

    /* ======================= KONFIGURASI ======================= */
    var CONFIG = {
        // Shortcut yang diblokir
        blockedKeys: ['F12'],
        blockedCombos: [
            { ctrl: true, shift: true, key: 'I' }, // DevTools
            { ctrl: true, shift: true, key: 'J' }, // Console
            { ctrl: true, shift: true, key: 'C' }, // Element picker
            { ctrl: true, shift: true, key: 'K' }, // Firefox console
            { ctrl: true, key: 'U' }               // View source
        ],
        // Hotkey saklar on/off
        toggleCombo: { ctrl: true, alt: true, key: ' ' },
        // Atribut validasi form yang diproteksi
        protectedAttrs: [
            'required', 'readonly', 'pattern', 'minlength', 'maxlength',
            'min', 'max', 'step', 'novalidate', 'formnovalidate',
            'type', 'inputmode', 'accept', 'multiple'
        ],
        // Deteksi DevTools
        devtoolsCheckInterval: 1500, // ms antar pengecekan
        devtoolsConfirmHits: 3,      // berapa kali berturut sebelum bereaksi
        dimensionThreshold: 160,     // px selisih outer/inner
        debuggerThreshold: 150,      // ms delay debugger dianggap terbuka
        // Teks overlay freeze
        overlayTitle: 'Akses Ditolak',
        overlayMessage: 'Developer Tools terdeteksi aktif. Tutup Developer Tools untuk melanjutkan.'
    };

    var STORAGE_KEY = 'silapinSec';

    var state = {
        enabled: true,
        devtoolsHits: 0,
        frozen: false,
        restoring: false
    };

    /* ======================= 0. SAKLAR ON/OFF ======================= */
    function readInitialEnabled() {
        // 1) URL ?sec=off / ?sec=on (sekali muat, simpan ke sessionStorage)
        try {
            var m = /[?&]sec=(off|on)\b/.exec(window.location.search);
            if (m) {
                var val = m[1] === 'off' ? 'off' : 'on';
                sessionStorage.setItem(STORAGE_KEY, val);
                return val !== 'off';
            }
            // 2) Status tersimpan (tab yang sama)
            if (sessionStorage.getItem(STORAGE_KEY) === 'off') return false;
        } catch (err) { /* storage bisa diblokir */ }
        return true;
    }

    state.enabled = readInitialEnabled();

    function setEnabled(on, showToast) {
        state.enabled = !!on;
        try {
            sessionStorage.setItem(STORAGE_KEY, on ? 'on' : 'off');
        } catch (err) { /* ignore */ }

        if (!on) {
            // Lepas freeze bila sedang aktif
            if (state.frozen) removeOverlay();
            state.devtoolsHits = 0;
        }
        if (showToast) {
            showToastMsg(on ? 'Proteksi: AKTIF' : 'Proteksi: NONAKTIF', on);
        }
    }

    function showToastMsg(text, positive) {
        var t = document.createElement('div');
        t.textContent = text;
        t.style.cssText = [
            'position:fixed', 'bottom:20px', 'right:20px', 'z-index:2147483647',
            'padding:10px 18px', 'border-radius:8px', 'font-size:14px', 'font-weight:600',
            'font-family:Inter,Arial,sans-serif', 'color:#fff', 'box-shadow:0 4px 14px rgba(0,0,0,.3)',
            'background:' + (positive ? '#1d4ed8' : '#64748b'),
            'transition:opacity .4s', 'opacity:1', 'pointer-events:none'
        ].join(';');
        document.documentElement.appendChild(t);
        setTimeout(function () { t.style.opacity = '0'; }, 1300);
        setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 1800);
    }

    function comboMatches(e, combo) {
        var key = (e.key || '').toUpperCase();
        var ctrl = e.ctrlKey || e.metaKey;
        if (combo.ctrl !== ctrl) return false;
        if (!!combo.shift !== !!e.shiftKey) return false;
        if (!!combo.alt !== !!e.altKey) return false;
        return key === combo.key.toUpperCase();
    }

    /* ======================= 1. BLOCK KLIK KANAN ======================= */
    document.addEventListener('contextmenu', function (e) {
        if (!state.enabled) return;
        e.preventDefault();
        return false;
    }, true);

    /* ======================= 2. BLOCK SHORTCUT + TOGGLE ======================= */
    function isBlockedCombo(e) {
        for (var i = 0; i < CONFIG.blockedCombos.length; i++) {
            if (comboMatches(e, CONFIG.blockedCombos[i])) return true;
        }
        return false;
    }

    document.addEventListener('keydown', function (e) {
        // Saklar on/off SELALU aktif (meski proteksi sedang nonaktif)
        if (comboMatches(e, CONFIG.toggleCombo)) {
            e.preventDefault();
            e.stopPropagation();
            setEnabled(!state.enabled, true);
            return false;
        }

        if (!state.enabled) return;
        var key = e.key || '';
        if (CONFIG.blockedKeys.indexOf(key) !== -1 || isBlockedCombo(e)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    document.addEventListener('keyup', function (e) {
        if (!state.enabled) return;
        var key = e.key || '';
        if (CONFIG.blockedKeys.indexOf(key) !== -1 || isBlockedCombo(e)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    /* ======================= 3. DETEKSI DEVTOOLS ======================= */
    function checkDimensions() {
        var w = window.outerWidth - window.innerWidth;
        var h = window.outerHeight - window.innerHeight;
        return w > CONFIG.dimensionThreshold || h > CONFIG.dimensionThreshold;
    }

    function checkDebuggerTiming() {
        try {
            var start = performance.now();
            // eslint-disable-next-line no-debugger
            debugger;
            var end = performance.now();
            return (end - start) > CONFIG.debuggerThreshold;
        } catch (err) {
            return false;
        }
    }

    function detectDevtools() {
        if (document.hidden) return false;
        return checkDimensions() || checkDebuggerTiming();
    }

    /* ======================= 4. OVERLAY FREEZE ======================= */
    function removeOverlay() {
        var el = document.getElementById('silapin-security-overlay');
        if (el && el.parentNode) el.parentNode.removeChild(el);
        state.frozen = false;
    }

    function freezePage() {
        if (state.frozen) return;
        state.frozen = true;

        var el = document.createElement('div');
        el.id = 'silapin-security-overlay';
        el.style.cssText = [
            'position:fixed', 'top:0', 'left:0', 'width:100%', 'height:100%',
            'background:#0f172a', 'color:#e2e8f0', 'z-index:2147483647',
            'display:flex', 'flex-direction:column', 'align-items:center',
            'justify-content:center', 'text-align:center', 'font-family:Inter,Arial,sans-serif',
            'padding:24px', 'pointer-events:auto', 'cursor:not-allowed'
        ].join(';');

        el.innerHTML =
            '<div style="font-size:64px;margin-bottom:16px;">&#128274;</div>' +
            '<h1 style="font-size:28px;font-weight:700;margin:0 0 12px;color:#f8fafc;">' +
            CONFIG.overlayTitle + '</h1>' +
            '<p style="font-size:16px;margin:0;max-width:480px;line-height:1.6;color:#94a3b8;">' +
            CONFIG.overlayMessage + '</p>';

        try {
            document.documentElement.appendChild(el);
        } catch (err) { /* ignore */ }
    }

    function handleDetection(detected) {
        if (detected) {
            state.devtoolsHits++;
            if (state.devtoolsHits >= CONFIG.devtoolsConfirmHits && !state.frozen) {
                freezePage();
            }
        } else {
            // Butuh 2x miss berturut agar tidak flicker
            state.devtoolsHits = Math.max(0, state.devtoolsHits - 2);
            if (state.devtoolsHits === 0 && state.frozen) {
                removeOverlay();
            }
        }
    }

    function devtoolsTick() {
        if (!state.enabled) return;
        handleDetection(detectDevtools());
    }

    function startDevtoolsMonitor() {
        setInterval(devtoolsTick, CONFIG.devtoolsCheckInterval);
        devtoolsTick();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startDevtoolsMonitor);
    } else {
        startDevtoolsMonitor();
    }

    /* ======================= 5. PROTEKSI ATRIBUT VALIDASI ======================= */
    var baseline = new WeakMap(); // element -> { attr: value }

    function captureElement(el) {
        if (!el || el.nodeType !== 1) return;
        var tag = (el.tagName || '').toLowerCase();
        if (['input', 'textarea', 'select', 'form', 'button'].indexOf(tag) === -1) return;
        if (baseline.has(el)) return;

        var snap = {};
        for (var i = 0; i < CONFIG.protectedAttrs.length; i++) {
            var a = CONFIG.protectedAttrs[i];
            if (el.hasAttribute(a)) snap[a] = el.getAttribute(a);
        }
        baseline.set(el, snap);
    }

    function captureTree(root) {
        if (!root) return;
        if (root.nodeType === 1) captureElement(root);
        var all = root.querySelectorAll
            ? root.querySelectorAll('input, textarea, select, form, button')
            : [];
        for (var i = 0; i < all.length; i++) captureElement(all[i]);
    }

    function restoreAttributes(mutation) {
        var el = mutation.target;
        if (!el || el.nodeType !== 1) return;
        var snap = baseline.get(el);
        if (!snap) {
            captureElement(el);
            snap = baseline.get(el) || {};
        }

        var attr = mutation.attributeName;
        if (CONFIG.protectedAttrs.indexOf(attr) === -1) return;
        if (!(attr in snap)) {
            // Atribut aslinya tidak ada → biarkan.
            // Yang berbahaya = penghapusan atribut proteksi yang aslinya ada.
            return;
        }

        var current = el.getAttribute(attr);
        if (current === snap[attr]) return; // tidak berubah

        // Kembalikan nilai asli (anti-loop: flag restoring)
        state.restoring = true;
        try {
            el.setAttribute(attr, snap[attr]);
        } catch (err) { /* ignore */ }
        state.restoring = false;
    }

    function startMutationObserver() {
        captureTree(document.documentElement);

        var observer = new MutationObserver(function (mutations) {
            if (!state.enabled || state.restoring) return;
            for (var i = 0; i < mutations.length; i++) {
                var m = mutations[i];
                if (m.type === 'attributes') {
                    restoreAttributes(m);
                } else if (m.type === 'childList') {
                    // Elemen baru masuk (mis. hasil PJAX) → snapshot
                    var added = m.addedNodes;
                    for (var j = 0; j < added.length; j++) {
                        if (added[j].nodeType === 1) captureTree(added[j]);
                    }
                }
            }
        });

        observer.observe(document.documentElement, {
            subtree: true,
            childList: true,
            attributes: true,
            attributeFilter: CONFIG.protectedAttrs
        });

        // Re-snapshot setelah navigasi PJAX (konten diganti total)
        document.addEventListener('pjax:complete', function () {
            captureTree(document.documentElement);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startMutationObserver);
    } else {
        startMutationObserver();
    }
})();
