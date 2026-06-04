<?php
/**
 * View: Daily Report
 * Variables: $date, $rows, $totals, $canExport
 */
$fmt     = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp      = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$prev    = date('Y-m-d', strtotime($date . ' -1 day'));
$next    = date('Y-m-d', strtotime($date . ' +1 day'));
$isToday = ($date === date('Y-m-d'));
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ລາຍງານປະຈຳວັນ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5">
            <?= date('d/m/Y', strtotime($date)) ?>
            <?php if ($isToday): ?><span class="chip-income ml-2">ມື້ນີ້</span><?php endif; ?>
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="?date=<?= $prev ?>" class="btn-outline">← ກ່ອນ</a>
        <form method="GET" class="inline">
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>"
                   max="<?= date('Y-m-d') ?>"
                   onchange="this.form.submit()"
                   class="px-3 py-2 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
        </form>
        <?php if (!$isToday): ?>
        <a href="?date=<?= $next ?>" class="btn-outline">ຕໍ່ໄປ →</a>
        <?php endif; ?>
        <?php if ($canExport && $totals['count'] > 0): ?>
        <a href="<?= BASE_URL ?>/export/report?format=csv&year=<?= substr($date,0,4) ?>&month=<?= substr($date,5,2) ?>" class="btn-outline">CSV</a>
        <?php endif; ?>
    </div>
</div>

<!-- ── KPI Cards ────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <p class="section-label mb-1">ລາຍຮັບ</p>
        <p class="font-headline font-bold text-title-lg text-secondary"><?= $fmt($totals['income']) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ລາຍຈ່າຍ</p>
        <p class="font-headline font-bold text-title-lg text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຍອດສຸດທິ</p>
        <p class="font-headline font-bold text-title-lg <?= $totals['net'] >= 0 ? 'text-secondary' : 'text-error' ?>">
            <?= ($totals['net'] >= 0 ? '+' : '−') . $fmt(abs($totals['net'])) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຈຳນວນລາຍການ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface"><?= $totals['count'] ?></p>
    </div>
</div>

<!-- ── Transactions ──────────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <?php if (empty($rows)): ?>
        <div class="py-16 text-center">
            <p class="font-headline font-semibold text-title-md text-on-surface-variant">ບໍ່ມີລາຍການວັນນີ້</p>
        </div>
    <?php else: ?>
    <div class="px-6 py-3 bg-surface-container-low border-b border-outline-variant/10">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-1 section-label">#</div>
            <div class="col-span-3 section-label">ລາຍລະອຽດ</div>
            <div class="col-span-3 section-label">ໝວດໝູ່ / ຜູ້ທານ</div>
            <div class="col-span-2 section-label">ຜູ້ບັນທຶກ</div>
            <div class="col-span-1 section-label">ປະເພດ</div>
            <div class="col-span-2 section-label text-right">ຈຳນວນ</div>
        </div>
    </div>
    <div class="divide-y divide-outline-variant/10">
        <?php foreach ($rows as $i => $tx):
            $isInc = $tx['type'] === 'income';
        ?>
        <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center hover:bg-surface-container-low/50 transition-colors">
            <div class="col-span-1 font-label text-label-sm text-on-surface-variant"><?= $i + 1 ?></div>
            <div class="col-span-3">
                <p class="font-body text-body-sm font-semibold text-on-surface"><?= htmlspecialchars($tx['description']) ?></p>
                <?php if ($tx['notes']): ?>
                <p class="font-label text-label-xs text-on-surface-variant mt-0.5"><?= htmlspecialchars($tx['notes']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-span-3">
                <?php if ($tx['category_name']): ?>
                <p class="font-label text-label-sm text-on-surface"><?= htmlspecialchars($tx['category_name']) ?></p>
                <?php endif; ?>
                <?php if ($tx['donor_name']): ?>
                <p class="font-label text-label-xs text-on-surface-variant">👤 <?= htmlspecialchars($tx['donor_name']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-span-2 font-label text-label-xs text-on-surface-variant">
                <?= htmlspecialchars($tx['creator_name'] ?? '—') ?>
            </div>
            <div class="col-span-1">
                <span class="font-label text-label-xs px-2 py-0.5 rounded-full <?= $isInc ? 'bg-secondary/10 text-secondary' : 'bg-error/10 text-error' ?>">
                    <?= $isInc ? 'ຮັບ' : 'ຈ່າຍ' ?>
                </span>
            </div>
            <div class="col-span-2 text-right font-headline font-bold text-body-sm <?= $isInc ? 'text-secondary' : 'text-error' ?>">
                <?= ($isInc ? '+' : '−') . number_format((float)$tx['amount'], $dp, '.', ',') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Footer totals — computed from same $rows array in controller -->
    <div class="px-6 py-4 bg-surface-container border-t border-outline-variant/20 grid grid-cols-12 gap-4">
        <div class="col-span-10 text-right font-label text-label-sm font-semibold text-on-surface-variant">ຍອດສຸດທິ</div>
        <div class="col-span-2 text-right font-headline font-bold <?= $totals['net'] >= 0 ? 'text-secondary' : 'text-error' ?>">
            <?= ($totals['net'] >= 0 ? '+' : '−') . number_format(abs($totals['net']), $dp, '.', ',') ?>
        </div>
    </div>
    <?php endif; ?>
</div>
