<?php
/**
 * View: Monthly Report
 * Variables: $month, $label, $rows, $daily, $by_cat, $totals, $canExport
 */
$fmt  = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp   = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));
$mNum = substr($month, 5, 2);
$year = substr($month, 0, 4);
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ລາຍງານລາຍເດືອນ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($label) ?></p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="?month=<?= $prev ?>" class="btn-outline">← ກ່ອນ</a>
        <form method="GET" class="inline">
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   max="<?= date('Y-m') ?>" onchange="this.form.submit()"
                   class="px-3 py-2 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
        </form>
        <?php if ($month < date('Y-m')): ?>
        <a href="?month=<?= $next ?>" class="btn-outline">ຕໍ່ໄປ →</a>
        <?php endif; ?>
        <?php if ($canExport && $totals['count'] > 0): ?>
        <a href="<?= BASE_URL ?>/export/report?format=csv&year=<?= $year ?>&month=<?= $mNum ?>" class="btn-outline">CSV</a>
        <a href="<?= BASE_URL ?>/export/report?format=pdf&year=<?= $year ?>&month=<?= $mNum ?>" class="btn-outline">PDF</a>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Daily Bar Chart -->
    <div class="lg:col-span-2 card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ກ່ອຍລາຍວັນ</h3>
        <?php if (empty($daily)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-8">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else: ?>
        <div class="h-48"><canvas id="dailyChart"></canvas></div>
        <?php endif; ?>
    </div>

    <!-- Category Breakdown -->
    <div class="card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ຕາມໝວດໝູ່</h3>
        <?php if (empty($by_cat)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-6">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxCat = max(array_map(fn($c) => $c['income'] + $c['expense'], $by_cat));
        ?>
        <div class="space-y-3">
            <?php foreach (array_slice($by_cat, 0, 6) as $cat):
                $total = $cat['income'] + $cat['expense'];
                $pct   = $maxCat > 0 ? round($total / $maxCat * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:<?= htmlspecialchars($cat['color']) ?>"></span>
                        <span class="font-label text-label-sm text-on-surface truncate max-w-[100px]"><?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                    <span class="font-label text-label-xs text-on-surface-variant"><?= $fmt($total) ?></span>
                </div>
                <div class="progress-bar h-1.5">
                    <div class="progress-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($cat['color']) ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Transactions Table ────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <div class="px-6 py-3 bg-surface-container-low border-b border-outline-variant/10">
        <div class="hidden md:grid grid-cols-12 gap-4">
            <div class="col-span-1 section-label">ວັນທີ</div>
            <div class="col-span-4 section-label">ລາຍລະອຽດ</div>
            <div class="col-span-3 section-label">ໝວດໝູ່</div>
            <div class="col-span-2 section-label">ຜູ້ທານ</div>
            <div class="col-span-2 section-label text-right">ຈຳນວນ</div>
        </div>
    </div>
    <?php if (empty($rows)): ?>
        <div class="py-16 text-center">
            <p class="font-headline font-semibold text-title-md text-on-surface-variant">ບໍ່ມີລາຍການ</p>
        </div>
    <?php else:
        $prevDay = '';
        $dayInc = $dayExp = 0.0;
    ?>
    <div class="divide-y divide-outline-variant/10">
        <?php foreach ($rows as $tx):
            $isInc = $tx['type'] === 'income';
            $day   = $tx['date'];
            if ($day !== $prevDay):
                if ($prevDay !== ''): ?>
        <div class="px-6 py-2 bg-surface-container-low/50 grid grid-cols-12 gap-4 items-center text-xs text-on-surface-variant">
            <div class="col-span-10 text-right">ສຸດທິ <?= $prevDay ?>:</div>
            <div class="col-span-2 text-right font-semibold <?= ($dayInc-$dayExp) >= 0 ? 'text-secondary' : 'text-error' ?>">
                <?= (($dayInc-$dayExp) >= 0 ? '+' : '−') . number_format(abs($dayInc-$dayExp),$dp,'.','') ?>
            </div>
        </div>
                <?php $dayInc = $dayExp = 0.0; ?>
                <?php endif; ?>
        <div class="px-6 py-2 bg-primary-fixed/20">
            <span class="font-label text-label-xs font-bold text-primary uppercase tracking-wider">
                <?= date('d/m/Y', strtotime($day)) ?><?php if ($day === date('Y-m-d')): ?> — ມື້ນີ້<?php endif; ?>
            </span>
        </div>
            <?php $prevDay = $day; endif; ?>
        <div class="px-6 py-3 grid grid-cols-12 gap-4 items-center hover:bg-surface-container-low/50 transition-colors">
            <div class="col-span-1 font-label text-label-xs text-on-surface-variant"><?= date('d', strtotime($day)) ?></div>
            <div class="col-span-4">
                <p class="font-body text-body-sm font-medium text-on-surface"><?= htmlspecialchars($tx['description']) ?></p>
                <?php if ($tx['notes']): ?><p class="font-label text-label-xs text-on-surface-variant mt-0.5"><?= htmlspecialchars($tx['notes']) ?></p><?php endif; ?>
            </div>
            <div class="col-span-3 font-label text-label-sm text-on-surface-variant"><?= htmlspecialchars($tx['category_name'] ?? '—') ?></div>
            <div class="col-span-2 font-label text-label-xs text-on-surface-variant"><?= htmlspecialchars($tx['donor_name'] ?? '—') ?></div>
            <div class="col-span-2 text-right font-headline font-bold text-body-sm <?= $isInc ? 'text-secondary' : 'text-error' ?>">
                <?= ($isInc ? '+' : '−') . number_format((float)$tx['amount'], $dp, '.', ',') ?>
            </div>
        </div>
        <?php
            if ($isInc) $dayInc += (float)$tx['amount'];
            else        $dayExp += (float)$tx['amount'];
        endforeach;
        // Last day subtotal
        if ($prevDay !== ''): ?>
        <div class="px-6 py-2 bg-surface-container-low/50 grid grid-cols-12 gap-4 items-center text-xs text-on-surface-variant">
            <div class="col-span-10 text-right">ສຸດທິ <?= $prevDay ?>:</div>
            <div class="col-span-2 text-right font-semibold <?= ($dayInc-$dayExp) >= 0 ? 'text-secondary' : 'text-error' ?>">
                <?= (($dayInc-$dayExp) >= 0 ? '+' : '−') . number_format(abs($dayInc-$dayExp),$dp,'.','') ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <!-- Grand total footer -->
    <div class="px-6 py-4 bg-surface-container border-t-2 border-outline-variant/20 grid grid-cols-12 gap-4">
        <div class="col-span-5 font-label text-label-sm font-semibold text-secondary">ລວມລາຍຮັບ: <?= $fmt($totals['income']) ?></div>
        <div class="col-span-5 font-label text-label-sm font-semibold text-on-tertiary-fixed-variant">ລວມລາຍຈ່າຍ: <?= $fmt($totals['expense']) ?></div>
        <div class="col-span-2 text-right font-headline font-bold <?= $totals['net'] >= 0 ? 'text-secondary' : 'text-error' ?>">
            <?= ($totals['net'] >= 0 ? '+' : '−') . number_format(abs($totals['net']),$dp,'.','') ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($daily)): ?>
<script nonce="<?= CSP_NONCE ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const ctx = document.getElementById('dailyChart');
    if (!ctx) return;
    const labels  = <?= json_safe(array_column($daily, 'date')) ?>.map(d => d.slice(8));
    const income  = <?= json_safe(array_column($daily, 'income')) ?>;
    const expense = <?= json_safe(array_column($daily, 'expense')) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'ລາຍຮັບ', data: income,  backgroundColor: '#4EDEA3', borderRadius: 3, borderSkipped: false },
                { label: 'ລາຍຈ່າຍ', data: expense, backgroundColor: '#FFB3AD', borderRadius: 3, borderSkipped: false },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { family: 'Phetsarath', size: 11 }, color: '#45474C', boxWidth: 10, boxHeight: 10, borderRadius: 3, useBorderRadius: true, padding: 12 } }, tooltip: { backgroundColor: '#1E293B', callbacks: { label: ctx => ' ₭ ' + ctx.raw.toLocaleString() } } },
            scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#75777D' }, border: { display: false } }, y: { grid: { color: 'rgba(117,119,125,0.12)' }, ticks: { font: { size: 10 }, color: '#75777D', callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v }, border: { display: false } } },
        },
    });
})();
});
</script>
<?php endif; ?>
