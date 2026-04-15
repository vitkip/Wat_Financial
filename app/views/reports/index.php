<?php
/**
 * View: Reports / index
 *
 * Variables: $months, $year, $yearlyIncome, $yearlyExpenses, $yearlyNet, $topCategories
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
?>

<?php
// Export base URLs
$exportYearCsv = BASE_URL . '/export/report?format=csv&year=' . $year;
$exportYearPdf = BASE_URL . '/export/report?format=pdf&year=' . $year;
?>

<style>
/* ── Report Export Dropdown ─────────────────────────────── */
.rpt-export-wrap { position: relative; display: inline-block; }
.rpt-export-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 16px; border-radius: 999px;
    border: 1.5px solid #e2e8f0; background: #fff;
    color: #475569; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: inherit; text-decoration: none;
    transition: all 150ms ease;
}
.rpt-export-btn:hover { border-color: #cbd5e1; background: #f8fafc; }
.rpt-export-btn svg { flex-shrink: 0; }
.rpt-export-menu {
    display: none; position: absolute; top: calc(100% + 8px); right: 0;
    min-width: 220px; background: #fff;
    border: 1.5px solid #e2e8f0; border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.12); z-index: 200; overflow: hidden;
}
.rpt-export-menu.open { display: block; }
.rpt-export-menu-hd {
    padding: 10px 14px 8px; font-size: 11px; font-weight: 700;
    letter-spacing: .5px; text-transform: uppercase; color: #94a3b8;
    border-bottom: 1px solid #f1f5f9;
}
.rpt-export-item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 14px; text-decoration: none; color: #334155;
    transition: background 150ms ease;
}
.rpt-export-item:hover { background: #f8fafc; }
.rpt-export-item + .rpt-export-item { border-top: 1px solid #f1f5f9; }
.rpt-export-icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rpt-icon-excel { background: #f0fdf4; color: #16a34a; }
.rpt-icon-pdf   { background: #fef2f2; color: #dc2626; }
.rpt-export-item-title { font-size: 13px; font-weight: 600; margin: 0 0 1px; }
.rpt-export-item-sub   { font-size: 11px; color: #94a3b8; margin: 0; }
</style>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ການວິເຄາະ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">
            ລາຍງານປະຈຳປີ
        </h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            ສະຫຼຸບທັງປີ <?= $year ?>
        </p>
    </div>

    <!-- Right side: Year Switcher + Export -->
    <div class="flex items-center gap-3 flex-wrap">

        <!-- Year Switcher -->
        <div class="flex items-center gap-2">
            <a href="?year=<?= $year - 1 ?>"
               class="px-3 py-2 rounded font-label text-label-md bg-surface-container
                      hover:bg-surface-container-high text-on-surface transition-colors">
                ← <?= $year - 1 ?>
            </a>
            <span class="px-3 py-2 font-headline font-bold text-title-sm text-primary
                         bg-primary-fixed rounded">
                <?= $year ?>
            </span>
            <?php if ($year < (int)date('Y')): ?>
            <a href="?year=<?= $year + 1 ?>"
               class="px-3 py-2 rounded font-label text-label-md bg-surface-container
                      hover:bg-surface-container-high text-on-surface transition-colors">
                <?= $year + 1 ?> →
            </a>
            <?php endif; ?>
        </div>

        <!-- Export Dropdown -->
        <div class="rpt-export-wrap" id="rptExportWrap">
            <button type="button" class="rpt-export-btn" onclick="toggleRptExport(event)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export <?= $year ?>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="rpt-export-menu" id="rptExportMenu">
                <div class="rpt-export-menu-hd">ລາຍງານທັງປີ <?= $year ?></div>
                <a href="<?= htmlspecialchars($exportYearCsv) ?>" class="rpt-export-item" target="_blank">
                    <span class="rpt-export-icon rpt-icon-excel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="rpt-export-item-title">Excel / CSV ທັງປີ</p>
                        <p class="rpt-export-item-sub">ສະຫຼຸບ + ທຸລະກຳ <?= $year ?></p>
                    </div>
                </a>
                <a href="<?= htmlspecialchars($exportYearPdf) ?>" class="rpt-export-item" target="_blank">
                    <span class="rpt-export-icon rpt-icon-pdf">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="rpt-export-item-title">PDF ທັງປີ</p>
                        <p class="rpt-export-item-sub">ເປີດໜ້າພິມ / Save PDF</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Yearly Summary KPIs ───────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="card">
        <p class="section-label mb-1">ລາຍຮັບທັງປີ</p>
        <p class="font-headline font-bold text-headline-sm text-secondary">
            <?= $fmt($yearlyIncome) ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ລາຍຈ່າຍທັງປີ</p>
        <p class="font-headline font-bold text-headline-sm text-on-tertiary-fixed-variant">
            <?= $fmt($yearlyExpenses) ?>
        </p>
    </div>
    <div class="hero-gradient rounded-lg p-6">
        <p class="font-label text-label-md text-primary-fixed/70 uppercase tracking-widest mb-1">
            ເງິນສຸດທິ
        </p>
        <p class="font-headline font-bold text-headline-sm
                  <?= $yearlyNet >= 0 ? 'text-secondary-fixed' : 'text-tertiary-fixed' ?>">
            <?= $yearlyNet < 0 ? '−' : '' ?><?= $fmt(abs($yearlyNet)) ?>
        </p>
    </div>
</div>

<!-- ── Annual Bar Chart ─────────────────────────────────────── -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-headline font-semibold text-title-lg text-on-surface">
            ລາຍລະອຽດລາຍເດືອນ
        </h2>
    </div>
    <div class="h-64">
        <canvas id="annualChart"></canvas>
    </div>
</div>

<!-- ── Monthly Table + Top Categories ────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Monthly Detail Table -->
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
            <?php foreach ($months as $m):
                $net      = (float)$m['net'];
                $income   = (float)$m['income'];
                $expenses = (float)$m['expenses'];
                $isActive = ($m['month'] === date('Y-m'));
                $mNum     = substr($m['month'], 5, 2);   // '01'…'12'
                $mCsv     = BASE_URL . '/export/report?format=csv&year=' . $year . '&month=' . $mNum;
                $mPdf     = BASE_URL . '/export/report?format=pdf&year=' . $year . '&month=' . $mNum;
                $hasData  = $income > 0 || $expenses > 0;
            ?>
            <div class="px-6 py-3.5 grid grid-cols-5 gap-4 items-center
                        hover:bg-surface-container-low/50 transition-colors group
                        <?= $isActive ? 'bg-primary-fixed/30' : '' ?>">
                <div class="flex items-center gap-2 col-span-2">
                    <span class="font-label text-label-md text-on-surface font-medium">
                        <?= $m['label'] ?>
                    </span>
                    <?php if ($isActive): ?>
                        <span class="chip-income text-xs">ປັດຈຸບັນ</span>
                    <?php endif; ?>
                    <?php if ($hasData): ?>
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 ml-1">
                        <a href="<?= htmlspecialchars($mCsv) ?>" target="_blank"
                           title="Export CSV ເດືອນ <?= $m['label'] ?>"
                           class="p-1 rounded text-on-surface-variant/50 hover:text-green-600
                                  hover:bg-green-50 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                        <a href="<?= htmlspecialchars($mPdf) ?>" target="_blank"
                           title="Export PDF ເດືອນ <?= $m['label'] ?>"
                           class="p-1 rounded text-on-surface-variant/50 hover:text-red-600
                                  hover:bg-red-50 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="text-right font-label text-label-md
                            <?= $income > 0 ? 'text-secondary font-semibold' : 'text-on-surface-variant' ?>">
                    <?= $income > 0 ? $fmt($income) : '—' ?>
                </div>
                <div class="text-right font-label text-label-md
                            <?= $expenses > 0 ? 'text-on-tertiary-fixed-variant font-semibold' : 'text-on-surface-variant' ?>">
                    <?= $expenses > 0 ? $fmt($expenses) : '—' ?>
                </div>
                <div class="text-right font-label text-label-md font-bold
                            <?= $net > 0 ? 'text-secondary' : ($net < 0 ? 'text-on-tertiary-fixed-variant' : 'text-on-surface-variant') ?>">
                    <?= ($net > 0 ? '+' : '') . ($net != 0 ? $fmt(abs($net)) : '—') ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top Categories -->
    <div class="card">
        <h2 class="font-headline font-semibold text-title-lg text-on-surface mb-4">
            ລາຍຈ່າຍສູງສຸດ
        </h2>
        <?php if (empty($topCategories)): ?>
            <p class="font-body text-body-sm text-on-surface-variant text-center py-8">
                ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍປີນີ້.
            </p>
        <?php else:
            $maxTotal = max(array_column($topCategories, 'total'));
        ?>
            <div class="space-y-4">
                <?php foreach (array_slice($topCategories, 0, 6) as $cat):
                    $total = (float)$cat['total'];
                    $pct   = $maxTotal > 0 ? round(($total / $maxTotal) * 100) : 0;
                    $color = $cat['color'] ?? '#006C49';
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                 style="background-color: <?= htmlspecialchars($color) ?>;"></div>
                            <span class="font-label text-label-md text-on-surface truncate max-w-[100px]">
                                <?= htmlspecialchars($cat['category'] ?? 'ອື່ນໆ') ?>
                            </span>
                        </div>
                        <span class="font-label text-label-md text-on-surface-variant font-medium text-xs">
                            <?= $fmt($total) ?>
                        </span>
                    </div>
                    <div class="progress-bar h-1.5">
                        <div class="progress-fill"
                             style="width: <?= $pct ?>%; background-color: <?= htmlspecialchars($color) ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Annual Chart Script ─────────────────────────────────────── -->
<script>
(function () {
    const ctx = document.getElementById('annualChart');
    if (!ctx) return;

    const labels   = <?= json_encode(array_column($months, 'label')) ?>;
    const income   = <?= json_encode(array_map(fn($m) => (float)$m['income'],   $months)) ?>;
    const expenses = <?= json_encode(array_map(fn($m) => (float)$m['expenses'], $months)) ?>;
    const net      = <?= json_encode(array_map(fn($m) => (float)$m['net'],      $months)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'ລາຍຮັບ',
                    data: income,
                    backgroundColor: '#4EDEA3',
                    borderRadius: 4,
                    borderSkipped: false,
                    order: 2,
                },
                {
                    label: 'ລາຍຈ່າຍ',
                    data: expenses,
                    backgroundColor: '#FFB3AD',
                    borderRadius: 4,
                    borderSkipped: false,
                    order: 2,
                },
                {
                    label: 'ສຸດທິ',
                    data: net,
                    type: 'line',
                    borderColor: '#091426',
                    backgroundColor: 'rgba(9,20,38,0.08)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#091426',
                    tension: 0.35,
                    fill: true,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font:  { family: 'Phetsarath', size: 11, weight: '700' },
                        color: '#45474C',
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 3,
                        useBorderRadius: true,
                        padding: 16,
                    },
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleFont: { family: 'Phetsarath', weight: '700' },
                    bodyFont:  { family: 'Phetsarath' },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: { label: ctx => ' ₭ ' + ctx.raw.toLocaleString() },
                },
            },
            scales: {
                x: {
                    grid:   { display: false },
                    ticks:  { font: { family: 'Phetsarath', size: 11 }, color: '#75777D' },
                    border: { display: false },
                },
                y: {
                    grid:   { color: 'rgba(117,119,125,0.12)' },
                    ticks:  {
                        font: { family: 'Inter', size: 11 },
                        color: '#75777D',
                        callback: v => '₭' + (Math.abs(v) >= 1000 ? (v/1000).toFixed(0) + 'K' : v),
                    },
                    border: { display: false },
                },
            },
        },
    });
})();

/* ── Report export dropdown ───────────────────── */
function toggleRptExport(e) {
    e.stopPropagation();
    document.getElementById('rptExportMenu').classList.toggle('open');
}
document.addEventListener('click', () => {
    document.getElementById('rptExportMenu')?.classList.remove('open');
});
</script>
