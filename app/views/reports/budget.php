<?php
/**
 * View: Budget vs Actual Report
 * Variables: $month, $label, $rows, $total_budget, $total_spent,
 *            $total_remain, $over_budget, $utilisation, $canExport
 *
 * Each $rows row: id, category_id, amount (budget), spent,
 *                 category_name, category_color, category_icon
 */
$fmt  = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp   = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$prev = date('Y-m', strtotime($month . '-01 -1 month'));
$next = date('Y-m', strtotime($month . '-01 +1 month'));

// Colour-code utilisation tier: green <70, yellow 70-90, red >90
$tierClass = function(float $budget, float $spent): string {
    if ($budget <= 0) return 'text-on-surface-variant';
    $pct = $spent / $budget * 100;
    if ($pct >= 100) return 'text-error';
    if ($pct >= 90)  return 'text-on-tertiary-fixed-variant';
    if ($pct >= 70)  return 'text-on-tertiary-fixed-variant/70';
    return 'text-secondary';
};
$barColor = function(float $budget, float $spent): string {
    if ($budget <= 0) return '#6b7280';
    $pct = $spent / $budget * 100;
    if ($pct >= 100) return '#B3261E';
    if ($pct >= 90)  return '#E8855A';
    if ($pct >= 70)  return '#FAB76A';
    return '#4EDEA3';
};
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ງົບປະມານ vs ຕົວຈິງ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5"><?= htmlspecialchars($label) ?></p>
    </div>
    <div class="flex items-center gap-2">
        <a href="?month=<?= $prev ?>" class="btn-outline">← ກ່ອນ</a>
        <form method="GET" class="inline">
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>"
                   max="<?= date('Y-m') ?>" onchange="this.form.submit()"
                   class="px-3 py-2 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
        </form>
        <?php if ($month < date('Y-m')): ?>
        <a href="?month=<?= $next ?>" class="btn-outline">ຕໍ່ໄປ →</a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Over-Budget Alert ─────────────────────────────────────────── -->
<?php if (!empty($over_budget)): ?>
<div class="mb-6 p-4 rounded-lg bg-error/10 border border-error/20 flex items-start gap-3">
    <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div>
        <p class="font-label text-label-sm font-bold text-error mb-1">ເກີນງົບ <?= count($over_budget) ?> ໝວດ</p>
        <p class="font-body text-body-xs text-on-surface-variant">
            <?= implode(', ', array_map(fn($r) => htmlspecialchars($r['category_name']), $over_budget)) ?>
        </p>
    </div>
</div>
<?php endif; ?>

<!-- ── KPI Cards ────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <p class="section-label mb-1">ງົບທັງໝົດ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface"><?= $fmt($total_budget) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ໃຊ້ໄປແລ້ວ</p>
        <p class="font-headline font-bold text-title-lg text-on-tertiary-fixed-variant"><?= $fmt($total_spent) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຍັງເຫຼືອ</p>
        <p class="font-headline font-bold text-title-lg <?= $total_remain >= 0 ? 'text-secondary' : 'text-error' ?>">
            <?= ($total_remain >= 0 ? '' : '−') . $fmt(abs($total_remain)) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ການນຳໃຊ້</p>
        <?php
            $uPct = (float) $utilisation;
            $uClass = $uPct >= 100 ? 'text-error' : ($uPct >= 90 ? 'text-on-tertiary-fixed-variant' : ($uPct >= 70 ? 'text-on-tertiary-fixed-variant/70' : 'text-secondary'));
        ?>
        <p class="font-headline font-bold text-title-lg <?= $uClass ?>"><?= $uPct ?>%</p>
        <!-- Overall utilisation progress bar -->
        <div class="mt-2 progress-bar h-1.5">
            <div class="progress-fill <?= $uPct >= 100 ? 'bg-error' : ($uPct >= 70 ? 'bg-amber-400' : 'bg-secondary') ?>"
                 style="width:<?= min(100, $uPct) ?>%"></div>
        </div>
    </div>
</div>

<!-- ── Per-Category Breakdown ────────────────────────────────────── -->
<?php if (empty($rows)): ?>
<div class="card py-16 text-center">
    <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">ບໍ່ມີງົບປະມານໃນເດືອນນີ້</p>
    <p class="font-body text-body-sm text-on-surface-variant">ຕັ້ງງົບໄດ້ທີ່ໜ້າ <a href="<?= BASE_URL ?>/budgets" class="text-primary hover:underline">ງົບປະມານ</a></p>
</div>
<?php else: ?>
<div class="card overflow-hidden !p-0 mb-6">
    <div class="px-5 py-3 bg-surface-container-low border-b border-outline-variant/10">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-3 section-label">ໝວດໝູ່</div>
            <div class="col-span-5 section-label">ຄວາມຄືບໜ້າ</div>
            <div class="col-span-2 section-label text-right">ງົບ</div>
            <div class="col-span-1 section-label text-right">ໃຊ້</div>
            <div class="col-span-1 section-label text-right">ເຫຼືອ</div>
        </div>
    </div>
    <div class="divide-y divide-outline-variant/10">
        <?php foreach ($rows as $row):
            $bAmt   = (float) $row['amount'];
            $spent  = (float) $row['spent'];
            $remain = $bAmt - $spent;
            $pct    = $bAmt > 0 ? min(100, round($spent / $bAmt * 100, 1)) : 0;
            $color  = $barColor($bAmt, $spent);
            $tClass = $tierClass($bAmt, $spent);
        ?>
        <div class="px-5 py-4 grid grid-cols-12 gap-4 items-center hover:bg-surface-container-low/40 transition-colors">
            <div class="col-span-3 flex items-center gap-2">
                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($row['category_color'] ?? '#6b7280') ?>"></span>
                <span class="font-label text-label-sm font-semibold text-on-surface truncate">
                    <?= htmlspecialchars($row['category_name'] ?? 'ບໍ່ລະບຸ') ?>
                </span>
            </div>
            <div class="col-span-5">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-label text-label-xs text-on-surface-variant"><?= $pct ?>%</span>
                    <?php if ($spent > $bAmt): ?>
                    <span class="font-label text-label-xs font-bold text-error">ເກີນງົບ!</span>
                    <?php endif; ?>
                </div>
                <div class="progress-bar h-2">
                    <div class="progress-fill rounded-full transition-all" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
                </div>
            </div>
            <div class="col-span-2 text-right font-label text-label-sm text-on-surface-variant">
                <?= $fmt($bAmt) ?>
            </div>
            <div class="col-span-1 text-right font-label text-label-sm font-semibold <?= $tClass ?>">
                <?= $fmt($spent) ?>
            </div>
            <div class="col-span-1 text-right font-label text-label-sm font-semibold <?= $remain >= 0 ? 'text-secondary' : 'text-error' ?>">
                <?= ($remain >= 0 ? '' : '−') . $fmt(abs($remain)) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Footer totals — match KPI cards (same PHP variables) -->
    <div class="px-5 py-3.5 grid grid-cols-12 gap-4 bg-surface-container border-t border-outline-variant/20">
        <div class="col-span-8 font-label text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">ລວມທັງໝົດ</div>
        <div class="col-span-2 text-right font-headline font-bold text-body-sm text-on-surface"><?= $fmt($total_budget) ?></div>
        <div class="col-span-1 text-right font-headline font-bold text-body-sm text-on-tertiary-fixed-variant"><?= $fmt($total_spent) ?></div>
        <div class="col-span-1 text-right font-headline font-bold text-body-sm <?= $total_remain >= 0 ? 'text-secondary' : 'text-error' ?>">
            <?= ($total_remain >= 0 ? '' : '−') . $fmt(abs($total_remain)) ?>
        </div>
    </div>
</div>

<!-- ── Chart ─────────────────────────────────────────────────────── -->
<?php if (count($rows) > 0): ?>
<div class="card">
    <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ງົບ vs ຕົວຈິງ ຕາມໝວດ</h3>
    <div class="h-56"><canvas id="budgetChart"></canvas></div>
</div>
<?php endif; ?>

<script nonce="<?= CSP_NONCE ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const ctx = document.getElementById('budgetChart');
    if (!ctx) return;
    const labels  = <?= json_safe(array_column($rows, 'category_name')) ?>;
    const budgets = <?= json_safe(array_map(fn($r) => (float) $r['amount'], $rows)) ?>;
    const spent   = <?= json_safe(array_map(fn($r) => (float) $r['spent'], $rows)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'ງົບ',      data: budgets, backgroundColor: 'rgba(9,20,38,0.08)', borderRadius: 4, borderSkipped: false },
                { label: 'ໃຊ້ຈິງ', data: spent,   backgroundColor: spent.map((v, i) => v > budgets[i] ? '#B3261E' : '#4EDEA3'), borderRadius: 4, borderSkipped: false },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Phetsarath', size: 11 }, color: '#45474C', boxWidth: 10, boxHeight: 10, borderRadius: 3, useBorderRadius: true, padding: 12 } },
                tooltip: { backgroundColor: '#1E293B', callbacks: { label: ctx => ' ₭ ' + ctx.raw.toLocaleString() } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Phetsarath', size: 11 }, color: '#75777D' }, border: { display: false } },
                y: { grid: { color: 'rgba(117,119,125,0.12)' }, ticks: { font: { size: 11 }, color: '#75777D', callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v }, border: { display: false } },
            },
        },
    });
})();
});
</script>
<?php endif; ?>
