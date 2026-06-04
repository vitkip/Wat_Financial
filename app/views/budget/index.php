<?php
/**
 * View: Budget / index
 *
 * Variables: $budgets, $categories, $month, $totalBudgeted, $totalSpent
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$remaining = $totalBudgeted - $totalSpent;
$overallPct = $totalBudgeted > 0
    ? min(100, round(($totalSpent / $totalBudgeted) * 100))
    : 0;
?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ການວາງແຜນ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">
            ງົບປະມານ
        </h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            <?= date('F Y', strtotime($month . '-01')) ?>
        </p>
    </div>
    <div class="flex items-center gap-3">
        <!-- Month Picker -->
        <form method="GET" action="">
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   onchange="this.form.submit()"
                   class="px-3 py-2.5 rounded-md font-label text-label-md
                          bg-surface-container border-0 text-on-surface
                          focus:outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer">
        </form>
        <button onclick="openModal('addBudgetModal')" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            ຕັ້ງງົບ
        </button>
    </div>
</div>

<!-- ── Summary Cards ─────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="card">
        <p class="section-label mb-1">ງົບທັງໝົດ</p>
        <p class="font-headline font-bold text-headline-sm text-on-surface">
            <?= $fmt($totalBudgeted) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ໃຊ້ຈ່າຍທັງໝົດ</p>
        <p class="font-headline font-bold text-headline-sm
                  <?= $totalSpent > $totalBudgeted ? 'text-on-tertiary-fixed-variant' : 'text-on-surface' ?>">
            <?= $fmt($totalSpent) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຍັງເຫຼືອ</p>
        <p class="font-headline font-bold text-headline-sm
                  <?= $remaining < 0 ? 'text-on-tertiary-fixed-variant' : 'text-secondary' ?>">
            <?= $remaining < 0 ? '−' : '' ?><?= $fmt(abs($remaining)) ?>
        </p>
    </div>
</div>

<!-- ── Overall Progress ───────────────────────────────────────── -->
<?php if ($totalBudgeted > 0): ?>
<div class="card mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-headline font-semibold text-title-md text-on-surface">
            ການໃຊ້ຈ່າຍລວມ
        </h2>
        <span class="font-label text-label-md font-bold
                     <?= $overallPct > 90 ? 'text-on-tertiary-fixed-variant' : 'text-on-surface' ?>">
            <?= $overallPct ?>%
        </span>
    </div>
    <div class="progress-bar h-3">
        <div class="progress-fill <?= $overallPct > 90 ? 'bg-on-tertiary-fixed-variant' : 'bg-secondary' ?>"
             style="width: <?= $overallPct ?>%"></div>
    </div>
</div>
<?php endif; ?>

<!-- ── Budget Items ───────────────────────────────────────────── -->
<div class="card">
    <h2 class="font-headline font-semibold text-title-lg text-on-surface mb-4">
        ງົບຕາມໝວດໝູ່
    </h2>

    <?php if (empty($budgets)): ?>
        <div class="py-12 text-center">
            <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">
                ຍັງບໍ່ໄດ້ຕັ້ງງົບປະມານເດືອນນີ້
            </p>
            <p class="font-body text-body-sm text-on-surface-variant mb-4">
                ສ້າງງົບເພື່ອຕິດຕາມຂີດຈຳກັດການໃຊ້ຈ່າຍຕາມໝວດໝູ່.
            </p>
            <button onclick="openModal('addBudgetModal')" class="btn-primary">
                ຕັ້ງງົບທຳອິດ
            </button>
        </div>
    <?php else: ?>
        <div class="divide-y divide-outline-variant/10">
            <?php foreach ($budgets as $idx => $budget): ?>
                <div class="flex items-start gap-4 group">
                    <div class="flex-1">
                        <?php require BASE_PATH . '/app/views/components/budget-bar.php'; ?>
                    </div>
                    <form method="POST"
                          action="<?= BASE_URL ?>/budget/delete/<?= (int)$budget['id'] ?>"
                          class="mt-5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button"
                                onclick="confirmDelete(this)"
                                data-desc="<?= htmlspecialchars($budget['category_name'] ?? 'ງົບປະຈຳເດືອນ', ENT_QUOTES) ?>"
                                class="p-1.5 rounded text-error hover:bg-error-container transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ── Add Budget Modal ───────────────────────────────────────── -->
<div id="addBudgetModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('addBudgetModal')"></div>

    <div class="relative w-full max-w-sm bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-bold text-title-lg text-on-surface">ຕັ້ງງົບປະມານ</h2>
            <button onclick="closeModal('addBudgetModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/budget/store" class="space-y-4">
            <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">

            <div>
                <label class="section-label block mb-2">ໝວດໝູ່</label>
                <select name="category_id" required
                        class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                               bg-surface-container border-0 text-on-surface
                               focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">ເລືອກໝວດໝູ່</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="section-label block mb-2">ຈຳນວນງົບ (<?= CURRENCY ?>)</label>
                <input type="number" name="amount" min="1" step="any" required
                       placeholder="0"
                       class="w-full px-4 py-2.5 rounded-md font-headline font-bold text-title-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>

            <button type="submit" class="btn-primary w-full justify-center">
                ບັນທຶກງົບ
            </button>
        </form>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?? '' ?>">
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
