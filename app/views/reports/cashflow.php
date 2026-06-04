<?php
/**
 * View: Cash Flow Report
 * Variables: $months, $monthly, $totals, $canExport
 *
 * Each $monthly row: month, label, income, expense, net, running
 */
$fmt    = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp     = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$lastRow = !empty($monthly) ? end($monthly) : null;
reset($monthly);
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">Cash Flow</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5">
            ກ່ອຍລາຍຮັບ-ຈ່າຍ <?= $months ?> ເດືອນຜ່ານມາ
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Month range selector -->
        <?php foreach ([3, 6, 12, 24] as $opt): ?>
        <a href="?months=<?= $opt ?>"
           class="px-3 py-1.5 rounded-full font-label text-label-sm font-semibold transition-all
                  <?= $months === $opt
                      ? 'bg-primary text-on-primary'
                      : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high' ?>">
            <?= $opt ?>M
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── KPI Cards ────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <p class="section-label mb-1">ລວມລາຍຮັບ</p>
        <p class="font-headline font-bold text-title-lg text-secondary"><?= $fmt($totals['income']) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ລວມລາຍຈ່າຍ</p>
        <p class="font-headline font-bold text-title-lg text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></p>
    </div>
    <div class="hero-gradient rounded-lg p-6">
        <p class="font-label text-label-md text-primary-fixed/70 uppercase tracking-widest mb-1">ຍອດສຸດທິ</p>
        <p class="font-headline font-bold text-title-lg <?= $totals['net'] >= 0 ? 'text-secondary-fixed' : 'text-tertiary-fixed' ?>">
            <?= ($totals['net'] >= 0 ? '+' : '−') . $fmt(abs($totals['net'])) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຍອດສະສົມ</p>
        <p class="font-headline font-bold text-title-lg <?= ($lastRow && $lastRow['running'] >= 0) ? 'text-secondary' : 'text-error' ?>">
            <?= $lastRow ? (($lastRow['running'] >= 0 ? '+' : '−') . $fmt(abs($lastRow['running']))) : '—' ?>
        </p>
    </div>
</div>

<!-- ── Cash Flow Chart ────────────────────────────────────────────── -->
<?php if (!empty($monthly)): ?>
<div class="card mb-6">
    <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ກ່ອຍ Cash Flow</h3>
    <div class="h-64"><canvas id="cashflowChart"></canvas></div>
</div>
<?php endif; ?>

<!-- ── Monthly Table ─────────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <div class="px-6 py-3 bg-surface-container-low border-b border-outline-variant/10">
        <div class="grid grid-cols-6 gap-4">
            <div class="col-span-2 section-label">ເດືອນ</div>
            <div class="section-label text-right">ລາຍຮັບ</div>
            <div class="section-label text-right">ລາຍຈ່າຍ</div>
            <div class="section-label text-right">ສຸດທິ</div>
            <div class="section-label text-right">ຍອດສະສົມ</div>
        </div>
    </div>
    <div class="divide-y divide-outline-variant/10">
        <?php foreach ($monthly as $m):
            $isNow   = ($m['month'] === date('Y-m'));
            $hasData = $m['income'] > 0 || $m['expense'] > 0;
        ?>
        <div class="px-6 py-3.5 grid grid-cols-6 gap-4 items-center
                    hover:bg-surface-container-low/50 transition-colors
                    <?= $isNow ? 'bg-primary-fixed/30' : '' ?>">
            <div class="col-span-2 flex items-center gap-2">
                <a href="<?= BASE_URL ?>/reports/monthly?month=<?= $m['month'] ?>"
                   class="font-label text-label-md text-on-surface hover:text-primary transition-colors">
                    <?= htmlspecialchars($m['label']) ?>
                </a>
                <?php if ($isNow): ?><span class="chip-income text-xs">ປັດຈຸບັນ</span><?php endif; ?>
            </div>
            <div class="text-right font-label text-label-md <?= $m['income'] > 0 ? 'text-secondary font-semibold' : 'text-on-surface-variant' ?>">
                <?= $m['income'] > 0 ? $fmt($m['income']) : '—' ?>
            </div>
            <div class="text-right font-label text-label-md <?= $m['expense'] > 0 ? 'text-on-tertiary-fixed-variant font-semibold' : 'text-on-surface-variant' ?>">
                <?= $m['expense'] > 0 ? $fmt($m['expense']) : '—' ?>
            </div>
            <div class="text-right font-label text-label-md font-bold
                        <?= $m['net'] > 0 ? 'text-secondary' : ($m['net'] < 0 ? 'text-on-tertiary-fixed-variant' : 'text-on-surface-variant') ?>">
                <?= $hasData ? (($m['net'] >= 0 ? '+' : '−') . $fmt(abs($m['net']))) : '—' ?>
            </div>
            <div class="text-right font-label text-label-md font-semibold
                        <?= $m['running'] >= 0 ? 'text-secondary' : 'text-error' ?>">
                <?= ($m['running'] >= 0 ? '' : '−') . $fmt(abs($m['running'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Footer totals -->
    <div class="px-6 py-3.5 grid grid-cols-6 gap-4 bg-surface-container border-t border-outline-variant/20">
        <div class="col-span-2 font-label text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">ລວມທັງໝົດ</div>
        <div class="text-right font-headline font-bold text-body-sm text-secondary"><?= $fmt($totals['income']) ?></div>
        <div class="text-right font-headline font-bold text-body-sm text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></div>
        <div class="text-right font-headline font-bold text-body-sm <?= $totals['net'] >= 0 ? 'text-secondary' : 'text-on-tertiary-fixed-variant' ?>">
            <?= ($totals['net'] >= 0 ? '+' : '−') . $fmt(abs($totals['net'])) ?>
        </div>
        <div class="text-right font-headline font-bold text-body-sm <?= ($lastRow && $lastRow['running'] >= 0) ? 'text-secondary' : 'text-error' ?>">
            <?= $lastRow ? (($lastRow['running'] >= 0 ? '' : '−') . $fmt(abs($lastRow['running']))) : '—' ?>
        </div>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const ctx = document.getElementById('cashflowChart');
    if (!ctx) return;
    const labels  = <?= json_safe(array_column($monthly, 'label')) ?>;
    const income  = <?= json_safe(array_column($monthly, 'income')) ?>;
    const expense = <?= json_safe(array_column($monthly, 'expense')) ?>;
    const running = <?= json_safe(array_column($monthly, 'running')) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'ລາຍຮັບ',  data: income,   backgroundColor: '#4EDEA3', borderRadius: 4, borderSkipped: false, order: 2 },
                { label: 'ລາຍຈ່າຍ', data: expense,  backgroundColor: '#FFB3AD', borderRadius: 4, borderSkipped: false, order: 2 },
                { label: 'ຍອດສະສົມ', data: running, type: 'line', borderColor: '#091426', backgroundColor: 'rgba(9,20,38,0.06)', borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#091426', tension: 0.35, fill: false, order: 1 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Phetsarath', size: 11, weight: '700' }, color: '#45474C', boxWidth: 10, boxHeight: 10, borderRadius: 3, useBorderRadius: true, padding: 16 } },
                tooltip: { backgroundColor: '#1E293B', titleFont: { family: 'Phetsarath', weight: '700' }, bodyFont: { family: 'Phetsarath' }, padding: 12, cornerRadius: 8, callbacks: { label: ctx => ' ₭ ' + ctx.raw.toLocaleString() } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Phetsarath', size: 11 }, color: '#75777D' }, border: { display: false } },
                y: { grid: { color: 'rgba(117,119,125,0.12)' }, ticks: { font: { family: 'Inter', size: 11 }, color: '#75777D', callback: v => '₭' + (Math.abs(v) >= 1000 ? (v/1000).toFixed(0)+'K' : v) }, border: { display: false } },
            },
        },
    });
})();
});
</script>
