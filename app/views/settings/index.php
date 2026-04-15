<?php
/**
 * View: Settings / index
 * Variables: $settings (assoc key=>row), $user
 */
$s = fn(string $key) => htmlspecialchars($settings[$key]['value'] ?? '');
?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8">
    <p class="section-label mb-1">ລະບົບ</p>
    <h1 class="font-headline font-bold text-headline-lg text-on-surface">ການຕັ້ງຄ່າ</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ── Left Column ── -->
    <div class="lg:col-span-2 space-y-6">

        <!-- App Settings -->
        <form method="POST" action="<?= BASE_URL ?>/settings/update">
            <div class="card space-y-5">
                <h2 class="font-headline font-semibold text-title-lg text-on-surface">ການຕັ້ງຄ່າທົ່ວໄປ</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="section-label block mb-2">ຊື່ແອັບ</label>
                        <input type="text" name="app_name" value="<?= $s('app_name') ?>" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ສັນຍາລັກເງິນຕາ</label>
                        <input type="text" name="currency_symbol" value="<?= $s('currency_symbol') ?>" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ລະຫັດເງິນຕາ</label>
                        <input type="text" name="currency_code" value="<?= $s('currency_code') ?>"
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ລາຍການຕໍ່ໜ້າ</label>
                        <input type="number" name="items_per_page" min="5" max="100"
                               value="<?= $s('items_per_page') ?>"
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ຮູບແບບວັນທີ</label>
                        <select name="date_format"
                                class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                       bg-surface-container border-0 text-on-surface
                                       focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <?php foreach(['d/m/Y'=>'DD/MM/YYYY','m/d/Y'=>'MM/DD/YYYY','Y-m-d'=>'YYYY-MM-DD'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= $s('date_format')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="section-label block mb-2">ໂຊນເວລາ</label>
                        <select name="timezone"
                                class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                       bg-surface-container border-0 text-on-surface
                                       focus:outline-none focus:ring-2 focus:ring-primary/20">
                            <?php foreach(['Asia/Vientiane'=>'Asia/Vientiane (ICT)','Asia/Bangkok'=>'Asia/Bangkok','UTC'=>'UTC'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= $s('timezone')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="section-label block mb-2">ສີຫຼັກ</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="theme_color" value="<?= $s('theme_color') ?>"
                                   class="h-10 w-16 rounded-md cursor-pointer bg-surface-container border-0">
                            <span class="font-label text-label-sm text-on-surface-variant"><?= $s('theme_color') ?></span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">ບັນທຶກການຕັ້ງຄ່າ</button>
            </div>
        </form>

        <!-- Profile -->
        <form method="POST" action="<?= BASE_URL ?>/settings/profile">
            <div class="card space-y-4">
                <h2 class="font-headline font-semibold text-title-lg text-on-surface">ໂປຣໄຟລ໌</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="section-label block mb-2">ຊື່</label>
                        <input type="text" name="name"
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ອີເມລ</label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>
                <button type="submit" class="btn-primary">ອັບເດດໂປຣໄຟລ໌</button>
            </div>
        </form>

        <!-- Change Password -->
        <form method="POST" action="<?= BASE_URL ?>/settings/password">
            <div class="card space-y-4">
                <h2 class="font-headline font-semibold text-title-lg text-on-surface">ປ່ຽນລະຫັດຜ່ານ</h2>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'wrongpassword'): ?>
                    <p class="font-label text-label-sm text-error">ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ.</p>
                <?php elseif (isset($_GET['error']) && $_GET['error'] === 'password'): ?>
                    <p class="font-label text-label-sm text-error">ລະຫັດຜ່ານໃໝ່ບໍ່ຕ້ອງກັນ ຫຼື ສັ້ນເກີນ 6 ຕົວ.</p>
                <?php endif; ?>

                <div class="space-y-3">
                    <div>
                        <label class="section-label block mb-2">ລະຫັດຜ່ານປັດຈຸບັນ</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ລະຫັດຜ່ານໃໝ່</label>
                        <input type="password" name="new_password" minlength="6" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="section-label block mb-2">ຢືນຢັນລະຫັດຜ່ານ</label>
                        <input type="password" name="confirm_password" minlength="6" required
                               class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                      bg-surface-container border-0 text-on-surface
                                      focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>
                <button type="submit" class="btn-primary">ປ່ຽນລະຫັດຜ່ານ</button>
            </div>
        </form>
    </div>

    <!-- ── Right Column: Info ── -->
    <div class="space-y-6">
        <div class="card">
            <h2 class="font-headline font-semibold text-title-md text-on-surface mb-4">ຂໍ້ມູນລະບົບ</h2>
            <div class="space-y-3">
                <?php foreach([
                    ['ຊື່ແອັບ',        $s('app_name')],
                    ['ເງິນຕາ',         $s('currency_symbol') . ' ' . $s('currency_code')],
                    ['ໂຊນເວລາ',        $s('timezone')],
                    ['ຮູບແບບວັນທີ',     $s('date_format')],
                    ['PHP Version',   phpversion()],
                ] as [$label, $val]): ?>
                <div class="flex items-center justify-between py-2 border-b border-outline-variant/10 last:border-0">
                    <span class="font-label text-label-md text-on-surface-variant"><?= $label ?></span>
                    <span class="font-label text-label-md text-on-surface font-semibold"><?= htmlspecialchars($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2 class="font-headline font-semibold text-title-md text-on-surface mb-3">ບັນຊີ</h2>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full hero-gradient flex items-center justify-center flex-shrink-0">
                    <span class="text-on-primary font-headline font-bold text-sm">
                        <?= mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
                    </span>
                </div>
                <div>
                    <p class="font-label text-label-md text-on-surface font-semibold">
                        <?= htmlspecialchars($user['name'] ?? '') ?>
                    </p>
                    <p class="font-label text-label-sm text-on-surface-variant">
                        <?= htmlspecialchars($user['email'] ?? '') ?>
                    </p>
                </div>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/auth/logout">
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-md font-label text-label-md font-semibold
                               text-error hover:bg-error-container transition-colors text-center">
                    ອອກຈາກລະບົບ
                </button>
            </form>
        </div>
    </div>
</div>