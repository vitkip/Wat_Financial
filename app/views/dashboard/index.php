<?php
/**
 * View: Dashboard / index
 *
 * Variables: $balance, $totalIncome, $totalExpenses, $savingsRate,
 *            $recentTransactions, $monthlyTotals, $expensesByCategory, $budgets
 */

$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ພາບລວມ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">
            <?= laoMonthFull((int)date('n')) . ' ' . date('Y') ?>
        </h1>
    </div>
    <a href="<?= BASE_URL ?>/transactions"
       class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        ເພີ່ມລາຍການ
    </a>
</div>

<!-- ── Hero Balance Card ──────────────────────────────────────── -->
<div class="hero-gradient rounded-xl p-8 mb-6 relative overflow-hidden shadow-ambient-md">
    <!-- decorative circles -->
    <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full
                bg-white/5 pointer-events-none"></div>
    <div class="absolute -bottom-12 -left-4 w-32 h-32 rounded-full
                bg-white/5 pointer-events-none"></div>

    <div class="relative z-10">
        <p class="font-label text-label-md text-primary-fixed/70 uppercase tracking-widest mb-2">
            ຍອດເງິນປັດຈຸບັນ
        </p>
        <p class="kpi-number text-primary-fixed text-display-lg leading-none mb-5">
            <?= $fmt($balance) ?>
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
            <div>
                <p class="font-label text-label-sm text-primary-fixed/60 uppercase tracking-wider mb-1">
                    ລາຍຮັບ
                </p>
                <p class="font-headline font-bold text-headline-sm text-secondary-fixed">
                    <?= $fmt($totalIncome) ?>
                </p>
            </div>
            <div>
                <p class="font-label text-label-sm text-primary-fixed/60 uppercase tracking-wider mb-1">
                    ລາຍຈ່າຍ
                </p>
                <p class="font-headline font-bold text-headline-sm text-tertiary-fixed">
                    <?= $fmt($totalExpenses) ?>
                </p>
            </div>
            <div>
                <p class="font-label text-label-sm text-primary-fixed/60 uppercase tracking-wider mb-1">
                    ອັດຕາການຝາກ
                </p>
                <p class="font-headline font-bold text-headline-sm
                          <?= $savingsRate >= 20 ? 'text-secondary-fixed' : 'text-tertiary-fixed-dim' ?>">
                    <?= $savingsRate ?>%
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ── KPI Stat Cards ─────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <?php
    $cards = [
        [
            'label'    => 'ລາຍຮັບທັງໝົດ',
            'value'    => $fmt($totalIncome),
            'positive' => true,
            'change'   => null,
            'accent'   => 'bg-secondary-fixed',
            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/>',
        ],
        [
            'label'    => 'ລາຍຈ່າຍທັງໝົດ',
            'value'    => $fmt($totalExpenses),
            'positive' => false,
            'change'   => null,
            'accent'   => 'bg-tertiary-fixed',
            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>',
        ],
        [
            'label'    => 'ເງິນສຸດທິ',
            'value'    => $fmt($balance),
            'positive' => $balance >= 0,
            'change'   => null,
            'accent'   => 'bg-primary-fixed',
            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ],
        [
            'label'    => 'ອັດຕາການຝາກ',
            'value'    => $savingsRate . '%',
            'positive' => $savingsRate >= 20,
            'change'   => null,
            'accent'   => 'bg-surface-container',
            'icon'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        ],
    ];
    foreach ($cards as $item):
        extract($item);
    ?>
        <?php require BASE_PATH . '/app/views/components/stat-card.php'; ?>
    <?php endforeach; ?>
</div>

<!-- ── Charts + Recent Transactions ──────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">

    <!-- Monthly Chart (3/5 width) -->
    <div class="lg:col-span-3 card">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="section-label mb-1">ກະແສເງິນ</p>
                <h2 class="font-headline font-semibold text-title-lg text-on-surface">
                    ພາບລວມ 6 ເດືອນ
                </h2>
            </div>
        </div>
        <div class="h-56">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>

    <!-- Recent Transactions (2/5 width) -->
    <div class="lg:col-span-2 card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">
                ລ່າສຸດ
            </h2>
            <a href="<?= BASE_URL ?>/transactions" class="btn-ghost text-xs">
                ເບິ່ງທັງໝົດ →
            </a>
        </div>
        <div class="divide-y divide-outline-variant/10">
            <?php if (empty($recentTransactions)): ?>
                <p class="py-6 text-center font-body text-body-sm text-on-surface-variant">
                    ຍັງບໍ່ມີລາຍການ. ເພີ່ມດຽວນີ້!
                </p>
            <?php else: ?>
                <?php foreach ($recentTransactions as $tx): ?>
                    <?php require BASE_PATH . '/app/views/components/transaction-row.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Budget & Category Breakdown ───────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Budget Progress -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">
                ສະຖານະງົບ
            </h2>
            <a href="<?= BASE_URL ?>/budget" class="btn-ghost text-xs">
                ຈັດການ →
            </a>
        </div>

        <?php if (empty($budgets)): ?>
            <p class="py-6 text-center font-body text-body-sm text-on-surface-variant">
                ຍັງບໍ່ໄດ້ຕັ້ງງົບ. <a href="<?= BASE_URL ?>/budget" class="text-secondary underline">ຕັ້ງດຽວນີ້ →</a>
            </p>
        <?php else: ?>
            <div class="divide-y divide-outline-variant/10">
                <?php foreach (array_slice($budgets, 0, 4) as $budget): ?>
                    <?php require BASE_PATH . '/app/views/components/budget-bar.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Expense by Category — Donut -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">
                ລາຍຈ່າຍຕາມໝວດ
            </h2>
        </div>

        <?php if (empty($expensesByCategory)): ?>
            <p class="py-6 text-center font-body text-body-sm text-on-surface-variant">
                ບໍ່ມີຂໍ້ມູນລາຍຈ່າຍເດືອນນີ້.
            </p>
        <?php else: ?>
            <div class="flex items-center gap-6">
                <div class="w-36 h-36 flex-shrink-0">
                    <canvas id="donutChart"></canvas>
                </div>
                <div class="flex-1 space-y-2">
                    <?php foreach ($expensesByCategory as $cat): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                     style="background-color: <?= htmlspecialchars($cat['color'] ?? '#006C49') ?>;"></div>
                                <span class="font-label text-label-md text-on-surface-variant truncate max-w-[100px]">
                                    <?= htmlspecialchars($cat['category'] ?? 'Other') ?>
                                </span>
                            </div>
                            <span class="font-label text-label-md text-on-surface font-semibold">
                                <?= CURRENCY ?> <?= number_format((float)$cat['total'], 0, '.', ',') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Chart.js Scripts ───────────────────────────────────────── -->
<script>
(function () {
    const surfaceVariant = '#E0E3E5';
    const gridColor      = 'rgba(117,119,125,0.12)';

    // ── Cash Flow Bar Chart ─────────────────────────────
    const cashFlowCtx = document.getElementById('cashFlowChart');
    if (cashFlowCtx) {
        const labels   = <?= json_encode(array_map('laoMonthAbbr', array_column($monthlyTotals, 'label'))) ?>;
        const income   = <?= json_encode(array_map(fn($r) => (float)$r['income'],   $monthlyTotals)) ?>;
        const expenses = <?= json_encode(array_map(fn($r) => (float)$r['expenses'], $monthlyTotals)) ?>;

        new Chart(cashFlowCtx, {
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
                    },
                    {
                        label: 'ລາຍຈ່າຍ',
                        data: expenses,
                        backgroundColor: '#FFB3AD',
                        borderRadius: 4,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Phetsarath', size: 11, weight: '700' },
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
                        callbacks: {
                            label: ctx => ' ₭ ' + ctx.raw.toLocaleString(),
                        },
                    },
                },
                scales: {
                    x: {
                        grid:  { display: false },
                        ticks: { font: { family: 'Phetsarath', size: 11 }, color: '#75777D' },
                        border:{ display: false },
                    },
                    y: {
                        grid:  { color: gridColor },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            color: '#75777D',
                            callback: v => '₭' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v),
                        },
                        border: { display: false },
                    },
                },
            },
        });
    }

    // ── Donut Chart ─────────────────────────────────────
    const donutCtx = document.getElementById('donutChart');
    if (donutCtx) {
        const cats   = <?= json_encode(array_map(fn($c) => $c['category'] ?? 'Other', $expensesByCategory)) ?>;
        const totals = <?= json_encode(array_map(fn($c) => (float)$c['total'], $expensesByCategory)) ?>;
        const colors = <?= json_encode(array_map(fn($c) => $c['color'] ?? '#006C49', $expensesByCategory)) ?>;

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: cats,
                datasets: [{
                    data: totals,
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        titleFont: { family: 'Phetsarath', weight: '700' },
                        bodyFont:  { family: 'Phetsarath' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ' ₭ ' + ctx.raw.toLocaleString(),
                        },
                    },
                },
            },
        });
    }
})();
</script>
