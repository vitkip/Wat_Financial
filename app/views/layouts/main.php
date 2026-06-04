<?php require BASE_PATH . '/app/views/layouts/head.php'; ?>
<body class="h-full bg-background antialiased">

<!-- Mobile Menu Overlay -->
<div id="mobileOverlay" class="mobile-overlay hidden" onclick="closeMobileMenu()"></div>

<!-- App Shell -->
<div class="flex h-screen overflow-hidden">

    <!-- ── Sidebar Navigation (Glass Rail) ───────────────────── -->
    <?php require BASE_PATH . '/app/views/components/nav-rail.php'; ?>

    <!-- ── Main Content ──────────────────────────────────────── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Top Bar (Mobile) -->
        <header class="lg:hidden flex items-center justify-between px-4 py-3
                       bg-surface-container-lowest/90 backdrop-blur-glass
                       shadow-ambient border-b border-outline-variant/10 z-20">
            <button id="mobileMenuBtn"
                    onclick="openMobileMenu()"
                    class="p-2 rounded-md text-on-surface-variant hover:bg-surface-container
                           transition-colors"
                    aria-label="ເປີດເມນູ">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-headline font-bold text-title-md text-primary">
                <?= CURRENCY ?> <?= APP_NAME ?>
            </span>
            <div class="w-9"></div><!-- spacer -->
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto px-4 py-6 lg:px-8 lg:py-8">

        <!-- Flash Messages (session-based) -->
        <?php if (!empty($flash)): ?>
            <?php
                $isError   = ($flash['type'] === 'error' || $flash['type'] === 'warning');
                $flashCss  = $isError
                    ? 'bg-error-container text-on-error-container'
                    : 'bg-secondary-fixed text-on-secondary-fixed';
                $iconPath  = $isError
                    ? 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z'
                    : 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z';
            ?>
            <div id="flashMsg"
                 class="mb-6 flex items-center gap-3 px-4 py-3 rounded-md
                         <?= $flashCss ?> text-body-sm font-label animate-fadeIn">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="<?= $iconPath ?>" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($flash['msg']) ?>
                <button onclick="this.parentElement.remove()"
                        class="ml-auto opacity-60 hover:opacity-100 transition-opacity"
                        aria-label="ປິດ">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        <?php endif; ?>

            <!-- View Content Slot -->
            <?= $content ?>

        </main>
    </div><!-- /main content -->
</div><!-- /app shell -->

<script nonce="<?= CSP_NONCE ?? '' ?>">
// ── CSRF Auto-Inject ──────────────────────────────────────────
// ອ່ານ token ຈາກ <meta name="csrf-token"> ແລ້ວ inject ເຂົ້າທຸກ POST form
(function () {
    const meta  = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    const token = meta.getAttribute('content');
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach(form => {
        if (!form.querySelector('input[name="_csrf"]')) {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = '_csrf';
            inp.value = token;
            form.appendChild(inp);
        }
    });
    // ຕໍ່ forms ທີ່ຖືກ inject ໃນ DOM ຫຼັງຈາກ page load (modals, dynamic)
    const observer = new MutationObserver(() => {
        document.querySelectorAll('form[method="POST"]:not([data-csrf-done]), form[method="post"]:not([data-csrf-done])').forEach(form => {
            if (!form.querySelector('input[name="_csrf"]')) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = '_csrf';
                inp.value = token;
                form.appendChild(inp);
            }
            form.setAttribute('data-csrf-done', '1');
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();

// ── Mobile Menu ───────────────────────────────────────────────
function openMobileMenu() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('mobileOverlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('mobileOverlay').classList.add('hidden');
    document.body.style.overflow = '';
}

// ── SweetAlert2 — lazy-loaded on first confirmDelete() call ──
// SweetAlert2 JS (~50 KB) is only fetched when the user actually clicks a
// delete button, not on every page load.
function confirmDelete(btn, opts = {}) {
    const form = btn.closest('form');
    const desc = btn.dataset.desc || opts.desc || '';
    const sub  = btn.dataset.sub  || opts.sub  || '';

    const fire = () => {
        Swal.fire({
            title:             opts.title || 'ລຶບລາຍການນີ້?',
            html:              desc
                                 ? `<p style="color:#475569;font-size:14px;margin:0"><strong>${desc}</strong>${sub ? '<br><span style="color:#94a3b8;font-size:12px;">' + sub + '</span>' : ''}</p>`
                                 : '',
            icon:              'warning',
            iconColor:         '#e11d48',
            showCancelButton:  true,
            confirmButtonText: 'ລຶບ',
            cancelButtonText:  'ຍົກເລີກ',
            confirmButtonColor:'#e11d48',
            cancelButtonColor: '#64748b',
            reverseButtons:    true,
            focusCancel:       true,
            customClass: { popup:'swal-lao-popup', title:'swal-lao-title', confirmButton:'swal-lao-btn', cancelButton:'swal-lao-btn' },
        }).then(r => { if (r.isConfirmed) form.submit(); });
    };

    if (window.Swal) {
        fire();
        return;
    }
    // First call: load CSS + JS bundle, then fire
    const css = document.createElement('link');
    css.rel   = 'stylesheet';
    css.href  = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css';
    document.head.appendChild(css);

    const js  = document.createElement('script');
    js.src    = 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js';
    js.onload = fire;
    document.head.appendChild(js);
}

// ── Auto-dismiss Flash Messages ───────────────────────────────
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => {
    flash.style.transition = 'opacity 0.5s';
    flash.style.opacity = '0';
    setTimeout(() => flash.remove(), 500);
}, 5000);
</script>

</body>
</html>
