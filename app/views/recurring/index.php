<?php
/**
 * View: Recurring Transactions / index
 * Variables: $items, $categories, $csrfToken
 */
$fmt  = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$freq = ['daily'=>'ທຸກວັນ','weekly'=>'ທຸກອາທິດ','monthly'=>'ທຸກເດືອນ','yearly'=>'ທຸກປີ'];
$csrf = htmlspecialchars($csrfToken); // CSRF token ສຳລັບທຸກ form
?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ອັດຕະໂນມັດ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">ລາຍການຊ້ຳ</h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            ລາຍການທີ່ເກີດຂຶ້ນເປັນປະຈຳ — ກົດ "ສ້າງ" ເພື່ອເພີ່ມລາຍການຈິງ
        </p>
    </div>
    <button onclick="openModal('addRecurringModal')" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        ເພີ່ມລາຍການຊ້ຳ
    </button>
</div>

<!-- ── List ───────────────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <!-- Header -->
    <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant/10">
        <div class="hidden md:grid grid-cols-12 gap-4">
            <div class="col-span-1 section-label">ປະເພດ</div>
            <div class="col-span-3 section-label">ລາຍລະອຽດ</div>
            <div class="col-span-2 section-label">ໝວດໝູ່</div>
            <div class="col-span-1 section-label">ຄວາມຖີ່</div>
            <div class="col-span-2 section-label text-right">ຈຳນວນ</div>
            <div class="col-span-2 section-label text-center">ສະຖານະ</div>
            <div class="col-span-1 section-label text-right">ດຳເນີນ</div>
        </div>
    </div>

    <div class="divide-y divide-outline-variant/10">
        <?php if (empty($items)): ?>
            <div class="py-16 text-center">
                <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">
                    ຍັງບໍ່ມີລາຍການຊ້ຳ
                </p>
                <p class="font-body text-body-sm text-on-surface-variant mb-4">
                    ເພີ່ມລາຍການຊ້ຳ ເຊັ່ນ ເງິນເດືອນ, ຄ່າເຊົ່າ.
                </p>
                <button onclick="openModal('addRecurringModal')" class="btn-primary">ເພີ່ມດຽວນີ້</button>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item):
                $isIncome  = $item['type'] === 'income';
                $isActive  = (bool)$item['is_active'];
                $amount    = (float)$item['amount'];
                $nextRun   = $item['next_run'] ? date('d/m/Y', strtotime($item['next_run'])) : '—';
            ?>
            <div class="px-6 py-4 transition-colors
                        <?= $isActive
                            ? 'hover:bg-surface-container-low/60'
                            : 'bg-surface-container-lowest/40 hover:bg-surface-container-low/30' ?>">

                <!-- ── Mobile ───────────────────────────────────────── -->
                <div class="md:hidden flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="<?= $isIncome ? 'chip-income' : 'chip-expense' ?>">
                                <?= $isIncome ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ' ?>
                            </span>
                            <span class="font-label text-label-sm text-on-surface-variant">
                                <?= $freq[$item['frequency']] ?? $item['frequency'] ?>
                            </span>
                            <!-- Status badge (mobile) -->
                            <span class="font-label text-label-xs px-1.5 py-0.5 rounded-full
                                         <?= $isActive
                                             ? 'bg-secondary/15 text-secondary'
                                             : 'bg-error-container text-error' ?>">
                                <?= $isActive ? 'ເປີດ' : 'ປິດ' ?>
                            </span>
                        </div>
                        <p class="font-body text-body-sm font-semibold text-on-surface truncate">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>
                        <p class="font-label text-label-sm text-on-surface-variant">
                            ຄັ້ງຕໍ່ໄປ: <?= $nextRun ?>
                        </p>
                    </div>
                    <div class="flex-shrink-0 text-right space-y-2">
                        <p class="font-label font-bold text-title-sm
                                  <?= $isIncome ? 'text-secondary' : 'text-on-tertiary-fixed-variant' ?>">
                            <?= $isIncome ? '+' : '−' ?><?= $fmt($amount) ?>
                        </p>
                        <div class="flex items-center gap-2 justify-end">
                            <!-- Toggle -->
                            <form method="POST" action="<?= BASE_URL ?>/recurring/toggle/<?= (int)$item['id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <button type="submit"
                                        title="<?= $isActive ? 'ກົດເພື່ອປິດ' : 'ກົດເພື່ອເປີດ' ?>"
                                        class="w-11 h-6 rounded-full transition-colors duration-300 relative inline-flex items-center flex-shrink-0
                                               <?= $isActive ? 'bg-secondary' : 'bg-error' ?>">
                                    <span class="w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-300 absolute
                                                 <?= $isActive ? 'translate-x-5' : 'translate-x-0.5' ?>"></span>
                                </button>
                            </form>
                            <?php if ($isActive): ?>
                            <form method="POST" action="<?= BASE_URL ?>/recurring/generate/<?= (int)$item['id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <button type="submit"
                                        class="px-2 py-1 rounded font-label text-label-sm
                                               bg-secondary/10 text-secondary hover:bg-secondary/20 transition-colors">
                                    + ສ້າງ
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= BASE_URL ?>/recurring/delete/<?= (int)$item['id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <button type="button"
                                        onclick="confirmDelete(this)"
                                        data-desc="<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>"
                                        class="p-1.5 rounded text-error hover:bg-error-container transition-colors"
                                        title="ລຶບ">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ── Desktop ──────────────────────────────────────── -->
                <div class="hidden md:grid grid-cols-12 gap-4 items-center">
                    <!-- Type -->
                    <div class="col-span-1">
                        <span class="<?= $isIncome ? 'chip-income' : 'chip-expense' ?>">
                            <?= $isIncome ? '↑' : '↓' ?>
                        </span>
                    </div>
                    <!-- Description -->
                    <div class="col-span-3">
                        <p class="font-body text-body-sm font-semibold text-on-surface truncate">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>
                        <p class="font-label text-label-sm text-on-surface-variant">
                            ຄັ້ງຕໍ່ໄປ: <?= $nextRun ?>
                        </p>
                    </div>
                    <!-- Category -->
                    <div class="col-span-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 style="background-color:<?= htmlspecialchars($item['category_color'] ?? '#006C49') ?>"></div>
                            <span class="font-label text-label-md text-on-surface-variant truncate">
                                <?= htmlspecialchars($item['category_name'] ?? '—') ?>
                            </span>
                        </div>
                    </div>
                    <!-- Frequency -->
                    <div class="col-span-1">
                        <span class="font-label text-label-sm text-on-surface-variant">
                            <?= $freq[$item['frequency']] ?? $item['frequency'] ?>
                        </span>
                    </div>
                    <!-- Amount -->
                    <div class="col-span-2 text-right">
                        <span class="font-label font-bold text-title-sm
                                     <?= $isIncome ? 'text-secondary' : 'text-on-tertiary-fixed-variant' ?>">
                            <?= $isIncome ? '+' : '−' ?><?= $fmt($amount) ?>
                        </span>
                    </div>
                    <!-- Status: toggle + label -->
                    <div class="col-span-2">
                        <div class="flex flex-col items-center gap-1">
                            <form method="POST" action="<?= BASE_URL ?>/recurring/toggle/<?= (int)$item['id'] ?>">
                                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                                <button type="submit"
                                        title="<?= $isActive ? 'ກົດເພື່ອປິດ' : 'ກົດເພື່ອເປີດ' ?>"
                                        class="w-11 h-6 rounded-full transition-colors duration-300 relative inline-flex items-center
                                               <?= $isActive ? 'bg-secondary' : 'bg-error' ?>
                                               focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    <span class="w-5 h-5 rounded-full bg-white shadow-sm transition-transform duration-300 absolute
                                                 <?= $isActive ? 'translate-x-5' : 'translate-x-0.5' ?>"></span>
                                </button>
                            </form>
                            <span class="font-label text-label-xs font-semibold
                                         <?= $isActive ? 'text-secondary' : 'text-error' ?>">
                                <?= $isActive ? 'ເປີດໃຊ້' : 'ປິດໃຊ້' ?>
                            </span>
                        </div>
                    </div>
                    <!-- Actions: generate + delete (always visible) -->
                    <div class="col-span-1 flex items-center justify-end gap-1">
                        <?php if ($isActive): ?>
                        <form method="POST" action="<?= BASE_URL ?>/recurring/generate/<?= (int)$item['id'] ?>">
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <button type="submit"
                                    class="p-1.5 rounded text-secondary hover:bg-secondary/10 transition-colors"
                                    title="ສ້າງລາຍການ">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="p-1.5 text-on-surface-variant/25 cursor-not-allowed" title="ເປີດກ່ອນຈຶ່ງສ້າງໄດ້">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <?php endif; ?>
                        <form method="POST" action="<?= BASE_URL ?>/recurring/delete/<?= (int)$item['id'] ?>">
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <button type="button"
                                    onclick="confirmDelete(this)"
                                    data-desc="<?= htmlspecialchars($item['description'], ENT_QUOTES) ?>"
                                    class="p-1.5 rounded text-on-surface-variant hover:bg-error-container hover:text-error transition-colors"
                                    title="ລຶບ">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── Add Recurring Modal ────────────────────────────────────── -->
<div id="addRecurringModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('addRecurringModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-bold text-title-lg text-on-surface">ເພີ່ມລາຍການຊ້ຳ</h2>
            <button onclick="closeModal('addRecurringModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/recurring/store" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">

            <!-- Type -->
            <div>
                <label class="section-label block mb-2">ປະເພດ</label>
                <div class="grid grid-cols-2 gap-2 p-1 bg-surface-container rounded-md">
                    <label class="relative">
                        <input type="radio" name="type" value="income" class="sr-only peer">
                        <span class="block text-center py-2 rounded font-label text-label-md cursor-pointer
                                     text-on-surface-variant transition-all duration-150
                                     peer-checked:bg-surface-container-lowest peer-checked:text-secondary
                                     peer-checked:font-semibold peer-checked:shadow-ambient">↑ ລາຍຮັບ</span>
                    </label>
                    <label class="relative">
                        <input type="radio" name="type" value="expense" class="sr-only peer" checked>
                        <span class="block text-center py-2 rounded font-label text-label-md cursor-pointer
                                     text-on-surface-variant transition-all duration-150
                                     peer-checked:bg-surface-container-lowest peer-checked:text-on-tertiary-fixed-variant
                                     peer-checked:font-semibold peer-checked:shadow-ambient">↓ ລາຍຈ່າຍ</span>
                    </label>
                </div>
            </div>

            <!-- Amount -->
            <div>
                <label class="section-label block mb-2">ຈຳນວນ (<?= CURRENCY ?>)</label>
                <input type="number" name="amount" min="1" step="any" required placeholder="0"
                       class="w-full px-4 py-2.5 rounded-md font-headline font-bold text-title-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>

            <!-- Description -->
            <div>
                <label class="section-label block mb-2">ລາຍລະອຽດ</label>
                <input type="text" name="description" required placeholder="ຕົວຢ່າງ: ຄ່າເຊົ່າເຮືອນ"
                       class="w-full px-4 py-2.5 rounded-md font-body text-body-sm
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>

            <!-- Category + Frequency (2-col) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="section-label block mb-2">ໝວດໝູ່</label>
                    <select name="category_id"
                            class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                   bg-surface-container border-0 text-on-surface
                                   focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="">— ບໍ່ມີ —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="section-label block mb-2">ຄວາມຖີ່</label>
                    <select name="frequency"
                            class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                   bg-surface-container border-0 text-on-surface
                                   focus:outline-none focus:ring-2 focus:ring-primary/20">
                        <option value="monthly">ທຸກເດືອນ</option>
                        <option value="weekly">ທຸກອາທິດ</option>
                        <option value="daily">ທຸກວັນ</option>
                        <option value="yearly">ທຸກປີ</option>
                    </select>
                </div>
            </div>

            <!-- Day + Start date -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="section-label block mb-2">ວັນທີໃນເດືອນ</label>
                    <input type="number" name="day_of_month" min="1" max="31" value="1"
                           class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                  bg-surface-container border-0 text-on-surface
                                  focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
                <div>
                    <label class="section-label block mb-2">ວັນເລີ່ມຕົ້ນ</label>
                    <input type="date" name="start_date" value="<?= date('Y-m-d') ?>"
                           class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                  bg-surface-container border-0 text-on-surface
                                  focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
            </div>

            <!-- End Date -->
            <div>
                <label class="section-label block mb-2">ວັນໝົດກຳນົດ <span class="normal-case font-normal text-on-surface-variant">(ບໍ່ບັງຄັບ)</span></label>
                <input type="date" name="end_date"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <!-- Notes -->
            <div>
                <label class="section-label block mb-2">ໝາຍເຫດ <span class="normal-case font-normal text-on-surface-variant">(ບໍ່ບັງຄັບ)</span></label>
                <textarea name="notes" rows="2" placeholder="ໝາຍເຫດເພີ່ມເຕີມ..."
                          class="w-full px-4 py-2.5 rounded-md font-body text-body-sm
                                 bg-surface-container border-0 text-on-surface resize-none
                                 focus:outline-none focus:ring-2 focus:ring-primary/20
                                 placeholder:text-on-surface-variant/40"></textarea>
            </div>

            <button type="submit" class="btn-primary w-full justify-center">ບັນທຶກ</button>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    const el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
    document.body.style.overflow = '';
}
</script>