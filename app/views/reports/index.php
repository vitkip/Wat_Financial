<?php
/**
 * View: Reports / Annual Hub
 * Variables: $year, $monthly, $exp_by_cat, $totals, $canExport
 */
$fmt  = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp   = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$prev = $year - 1;
$next = $year + 1;
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ລາຍງານປະຈຳປີ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5">
            ສະຫຼຸບລາຍຮັບ-ລາຍຈ່າຍທຸກເດືອນ ປີ <?= $year ?>
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Year navigator -->
        <div class="flex items-center gap-1">
            <a href="?year=<?= $prev ?>" class="px-3 py-2 rounded font-label text-label-md bg-surface-container hover:bg-surface-container-high text-on-surface transition-colors">
                ← <?= $prev ?>
            </a>
            <span class="px-3 py-2 font-headline font-bold text-title-sm text-primary bg-primary-fixed rounded">
                <?= $year ?>
            </span>
            <?php if ($year < (int) date('Y')): ?>
            <a href="?year=<?= $next ?>" class="px-3 py-2 rounded font-label text-label-md bg-surface-container hover:bg-surface-container-high text-on-surface transition-colors">
                <?= $next ?> →
            </a>
            <?php endif; ?>
        </div>
        <?php if ($canExport): ?>
        <a href="<?= BASE_URL ?>/export/report?format=csv&year=<?= $year ?>"
           class="btn-outline">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            CSV
        </a>
        <a href="<?= BASE_URL ?>/export/report?format=pdf&year=<?= $year ?>"
           class="btn-outline">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            PDF
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── KPI Cards ────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="card">
        <p class="section-label mb-1">ລາຍຮັບທັງປີ</p>
        <p class="font-headline font-bold text-headline-sm text-secondary"><?= $fmt($totals['income']) ?></p>
        <p class="font-label text-label-xs text-on-surface-variant mt-1"><?= $totals['count'] ?> ລາຍການ</p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ລາຍຈ່າຍທັງປີ</p>
        <p class="font-headline font-bold text-headline-sm text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></p>
    </div>
    <div class="hero-gradient rounded-lg p-6">
        <p class="font-label text-label-md text-primary-fixed/70 uppercase tracking-widest mb-1">ຍອດສຸດທິ</p>
        <p class="font-headline font-bold text-headline-sm
                  <?= $totals['net'] >= 0 ? 'text-secondary-fixed' : 'text-tertiary-fixed' ?>">
            <?= $totals['net'] < 0 ? '−' : '' ?><?= $fmt(abs($totals['net'])) ?>
        </p>
    </div>
</div>

<!-- ── Annual Chart ──────────────────────────────────────────────── -->
<div class="card mb-6">
    <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ລາຍລະອຽດລາຍເດືອນ</h3>
    <div class="h-64"><canvas id="annualChart"></canvas></div>
</div>

<!-- ── Monthly Table + Category Breakdown ────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Monthly Table -->
    <div class="lg:col-span-2 card !p-0 overflow-hidden">
        <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant/10">
            <div class="grid grid-cols-5 gap-4">
                <div class="section-label col-span-2">ເດືອນ</div>
                <div class="section-label text-right">ລາຍຮັບ</div>
                <div class="section-label text-right">ລາຍຈ່າຍ</div>
                <div class="section-label text-right">ສຸດທິ</div>
            </div>
        </div>
        <div class="divide-y divide-outline-variant/10">
            <?php foreach ($monthly as $m):
                $net    = $m['income'] - $m['expense'];
                $isNow  = ($m['month'] === date('Y-m'));
                $hasData = $m['income'] > 0 || $m['expense'] > 0;
                $mNum   = substr($m['month'], 5, 2);
            ?>
            <div class="px-6 py-3.5 grid grid-cols-5 gap-4 items-center
                        hover:bg-surface-container-low/50 transition-colors group
                        <?= $isNow ? 'bg-primary-fixed/30' : '' ?>">
                <div class="col-span-2 flex items-center gap-2">
                    <a href="<?= BASE_URL ?>/reports/monthly?month=<?= $m['month'] ?>"
                       class="font-label text-label-md text-on-surface hover:text-primary transition-colors">
                        <?= $m['label'] ?>
                    </a>
                    <?php if ($isNow): ?><span class="chip-income text-xs">ປັດຈຸບັນ</span><?php endif; ?>
                    <?php if ($hasData && $canExport): ?>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-1 ml-1">
                        <a href="<?= BASE_URL ?>/export/report?format=csv&year=<?= $year ?>&month=<?= $mNum ?>" target="_blank"
                           title="CSV" class="p-1 rounded hover:bg-green-50 hover:text-green-600 text-on-surface-variant/50 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="text-right font-label text-label-md <?= $m['income'] > 0 ? 'text-secondary font-semibold' : 'text-on-surface-variant' ?>">
                    <?= $m['income'] > 0 ? $fmt($m['income']) : '—' ?>
                </div>
                <div class="text-right font-label text-label-md <?= $m['expense'] > 0 ? 'text-on-tertiary-fixed-variant font-semibold' : 'text-on-surface-variant' ?>">
                    <?= $m['expense'] > 0 ? $fmt($m['expense']) : '—' ?>
                </div>
                <div class="text-right font-label text-label-md font-bold
                            <?= $net > 0 ? 'text-secondary' : ($net < 0 ? 'text-on-tertiary-fixed-variant' : 'text-on-surface-variant') ?>">
                    <?= $hasData ? (($net >= 0 ? '+' : '−') . $fmt(abs($net))) : '—' ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Footer totals — must match header KPI cards (same source array) -->
        <div class="px-6 py-3.5 grid grid-cols-5 gap-4 bg-surface-container border-t border-outline-variant/20">
            <div class="col-span-2 font-label text-label-sm font-semibold text-on-surface-variant uppercase tracking-wider">ລວມທັງປີ</div>
            <div class="text-right font-headline font-bold text-body-sm text-secondary"><?= $fmt($totals['income']) ?></div>
            <div class="text-right font-headline font-bold text-body-sm text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></div>
            <div class="text-right font-headline font-bold text-body-sm <?= $totals['net'] >= 0 ? 'text-secondary' : 'text-on-tertiary-fixed-variant' ?>">
                <?= ($totals['net'] >= 0 ? '+' : '−') . $fmt(abs($totals['net'])) ?>
            </div>
        </div>
    </div>

    <!-- Top Expense Categories -->
    <div class="card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ລາຍຈ່າຍຕາມໝວດ</h3>
        <?php if (empty($exp_by_cat)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-8">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxExp = max(array_column($exp_by_cat, 'total'));
        ?>
            <div class="space-y-3">
                <?php foreach (array_slice($exp_by_cat, 0, 8) as $cat):
                    $pct = $maxExp > 0 ? round($cat['total'] / $maxExp * 100) : 0;
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($cat['color']) ?>"></span>
                            <span class="font-label text-label-sm text-on-surface truncate max-w-[120px]">
                                <?= htmlspecialchars($cat['name']) ?>
                            </span>
                        </div>
                        <span class="font-label text-label-xs text-on-surface-variant"><?= $fmt($cat['total']) ?></span>
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

<script nonce="<?= CSP_NONCE ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const ctx = document.getElementById('annualChart');
    if (!ctx) return;
    const labels   = <?= json_safe(array_column($monthly, 'label')) ?>;
    const income   = <?= json_safe(array_column($monthly, 'income')) ?>;
    const expenses = <?= json_safe(array_column($monthly, 'expense')) ?>;
    const net      = income.map((v, i) => v - expenses[i]);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'ລາຍຮັບ',  data: income,   backgroundColor: '#4EDEA3', borderRadius: 4, borderSkipped: false, order: 2 },
                { label: 'ລາຍຈ່າຍ', data: expenses, backgroundColor: '#FFB3AD', borderRadius: 4, borderSkipped: false, order: 2 },
                { label: 'ສຸດທິ', data: net, type: 'line', borderColor: '#091426', backgroundColor: 'rgba(9,20,38,0.06)', borderWidth: 2, pointRadius: 4, pointBackgroundColor: '#091426', tension: 0.35, fill: true, order: 1 },
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
                y: { grid: { color: 'rgba(117,119,125,0.12)' }, ticks: { font: { family: 'Inter', size: 11 }, color: '#75777D', callback: v => '₭' + (Math.abs(v) >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }, border: { display: false } },
            },
        },
    });
})();
});
</script>
