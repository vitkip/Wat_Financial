<?php
// Flash is available via $flash variable (injected by Controller::view)
// Also keep backward compat for URL-based if any remain
?>

<!-- Brand -->
<div class="text-center mb-8">
    <div class="inline-flex items-center justify-center mb-4">
        <img src="<?= BASE_URL ?>/public/img/logo.png" alt="Logo" class="object-contain" style="width:6.5rem;height:6.5rem;">
    </div>
    <h1 class="font-headline font-bold text-headline-sm text-on-surface">
        <?= htmlspecialchars(APP_NAME) ?>
    </h1>
    <p class="font-label text-label-md text-on-surface-variant mt-1">
        ເຂົ້າສູ່ລະບົບຈັດການການເງິນ
    </p>
</div>

<!-- Flash / Error messages -->
<?php if (!empty($flash)): ?>
    <?php $isErr = $flash['type'] === 'error'; ?>
    <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-md
                <?= $isErr ? 'bg-error-container text-on-error-container' : 'bg-secondary-fixed text-on-secondary-fixed' ?>
                font-label text-label-md animate-fadeIn">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <?php if ($isErr): ?>
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            <?php else: ?>
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            <?php endif; ?>
        </svg>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
<?php endif; ?>

<!-- Login Card -->
<div class="card shadow-ambient-md">
    <form method="POST" action="<?= BASE_URL ?>/auth/login" class="space-y-4">

        <!-- CSRF Token -->
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

        <div>
            <label for="email" class="section-label block mb-2">ອີເມລ</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                placeholder="admin@watfinancial.local" class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                          bg-surface-container border-0 text-on-surface
                          focus:outline-none focus:ring-2 focus:ring-primary/20
                          placeholder:text-on-surface-variant/40">
        </div>

        <div>
            <label for="password" class="section-label block mb-2">ລະຫັດຜ່ານ</label>
            <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                          bg-surface-container border-0 text-on-surface
                          focus:outline-none focus:ring-2 focus:ring-primary/20
                          placeholder:text-on-surface-variant/40">
        </div>

        <button type="submit" class="btn-primary w-full justify-center mt-2">
            ເຂົ້າສູ່ລະບົບ
        </button>

    </form>
</div>

<p class="text-center font-label text-label-sm text-on-surface-variant mt-6">
    <?= htmlspecialchars(APP_NAME) ?> &copy; <?= date('Y') ?>
</p>