<?php
/**
 * View: Temple Summary Dashboard
 * Variables: $summary, $canExport
 *
 * $summary keys:
 *   current_month, this_month{income,expense,net,tx_count},
 *   prev_month{income,expense,net}, income_change, expense_change,
 *   ytd{income,expense,net,tx_count}, pending{count,amount},
 *   top_expenses[], top_donors[]
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp  = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;

$tm   = $summary['this_month'];
$ytd  = $summary['ytd'];
$pend = $summary['pending'];

// Change badge helper: returns [sign, class, arrow]
$changeBadge = function(?float $change): array {
    if ($change === null) return ['', 'text-on-surface-variant', ''];
    if ($change > 0)  return ['+' . abs($change) . '%', 'text-secondary', '↑'];
    if ($change < 0)  return ['−' . abs($change) . '%', 'text-error', '↓'];
    return ['0%', 'text-on-surface-variant', '→'];
};

[$incSign, $incClass, $incArrow] = $changeBadge($summary['income_change']);
[$expSign, $expClass, $expArrow] = $changeBadge($summary['expense_change']);

// Current month label
[$y, $m] = explode('-', $summary['current_month']);
$monthLabel = laoMonthFull((int) $m) . ' ' . $y;
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ສະຫຼຸບວັດ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5">
            ສະຖານະທາງການເງິນ — <?= htmlspecialchars($monthLabel) ?>
        </p>
    </div>
    <?php if ($pend['count'] > 0): ?>
    <a href="<?= BASE_URL ?>/transactions?status=pending"
       class="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors">
        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-label text-label-sm font-semibold text-amber-700">
            <?= $pend['count'] ?> ລາຍການລໍຖ້າ (<?= $fmt($pend['amount']) ?>)
        </span>
    </a>
    <?php endif; ?>
</div>

<!-- ── Current Month KPI ─────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <!-- Income -->
    <div class="card">
        <div class="flex items-center justify-between mb-2">
            <p class="section-label">ລາຍຮັບເດືອນນີ້</p>
            <?php if ($incSign): ?>
            <span class="font-label text-label-xs font-bold <?= $incClass ?>"><?= $incArrow ?> <?= $incSign ?></span>
            <?php endif; ?>
        </div>
        <p class="font-headline font-bold text-headline-sm text-secondary"><?= $fmt($tm['income']) ?></p>
        <p class="font-label text-label-xs text-on-surface-variant mt-1">
            ເດືອນແລ້ວ: <?= $fmt($summary['prev_month']['income']) ?>
        </p>
    </div>

    <!-- Expense -->
    <div class="card">
        <div class="flex items-center justify-between mb-2">
            <p class="section-label">ລາຍຈ່າຍເດືອນນີ້</p>
            <?php if ($expSign): ?>
            <span class="font-label text-label-xs font-bold <?= $expClass ?>"><?= $expArrow ?> <?= $expSign ?></span>
            <?php endif; ?>
        </div>
        <p class="font-headline font-bold text-headline-sm text-on-tertiary-fixed-variant"><?= $fmt($tm['expense']) ?></p>
        <p class="font-label text-label-xs text-on-surface-variant mt-1">
            ເດືອນແລ້ວ: <?= $fmt($summary['prev_month']['expense']) ?>
        </p>
    </div>

    <!-- Net -->
    <div class="hero-gradient rounded-lg p-6">
        <p class="font-label text-label-md text-primary-fixed/70 uppercase tracking-widest mb-2">ຍອດສຸດທິ</p>
        <p class="font-headline font-bold text-headline-sm
                  <?= $tm['net'] >= 0 ? 'text-secondary-fixed' : 'text-tertiary-fixed' ?>">
            <?= ($tm['net'] >= 0 ? '+' : '−') . $fmt(abs($tm['net'])) ?>
        </p>
        <p class="font-label text-label-xs text-primary-fixed/60 mt-2">
            <?= $tm['tx_count'] ?> ລາຍການ
        </p>
    </div>
</div>

<!-- ── YTD Summary ───────────────────────────────────────────────── -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-headline font-semibold text-title-md text-on-surface">ສະສົມທັງປີ (YTD)</h3>
        <a href="<?= BASE_URL ?>/reports?year=<?= $y ?>" class="font-label text-label-sm text-primary hover:underline">
            ລາຍງານປີ →
        </a>
    </div>
    <div class="grid grid-cols-3 gap-6">
        <div>
            <p class="section-label mb-1">ລາຍຮັບ YTD</p>
            <p class="font-headline font-bold text-title-lg text-secondary"><?= $fmt($ytd['income']) ?></p>
        </div>
        <div>
            <p class="section-label mb-1">ລາຍຈ່າຍ YTD</p>
            <p class="font-headline font-bold text-title-lg text-on-tertiary-fixed-variant"><?= $fmt($ytd['expense']) ?></p>
        </div>
        <div>
            <p class="section-label mb-1">ສຸດທິ YTD</p>
            <p class="font-headline font-bold text-title-lg <?= $ytd['net'] >= 0 ? 'text-secondary' : 'text-error' ?>">
                <?= ($ytd['net'] >= 0 ? '+' : '−') . $fmt(abs($ytd['net'])) ?>
            </p>
            <p class="font-label text-label-xs text-on-surface-variant mt-0.5"><?= $ytd['tx_count'] ?> ລາຍການ</p>
        </div>
    </div>
    <!-- YTD income vs expense bar -->
    <?php
        $ytdTotal = $ytd['income'] + $ytd['expense'];
        $incWidthPct = $ytdTotal > 0 ? round($ytd['income'] / $ytdTotal * 100) : 50;
        $expWidthPct = 100 - $incWidthPct;
    ?>
    <div class="mt-4 flex rounded-full overflow-hidden h-3">
        <div class="bg-secondary transition-all" style="width:<?= $incWidthPct ?>%"></div>
        <div class="bg-on-tertiary-fixed-variant/40 transition-all" style="width:<?= $expWidthPct ?>%"></div>
    </div>
    <div class="flex justify-between mt-1">
        <span class="font-label text-label-xs text-secondary">ລາຍຮັບ <?= $incWidthPct ?>%</span>
        <span class="font-label text-label-xs text-on-tertiary-fixed-variant">ລາຍຈ່າຍ <?= $expWidthPct ?>%</span>
    </div>
</div>

<!-- ── Top Expenses & Top Donors ─────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Top Expense Categories -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-headline font-semibold text-title-md text-on-surface">ໝວດລາຍຈ່າຍສູງສຸດ</h3>
            <a href="<?= BASE_URL ?>/reports/expenses?from=<?= $summary['current_month'] . '-01' ?>&to=<?= date('Y-m-d') ?>"
               class="font-label text-label-sm text-primary hover:underline">ລາຍງານ →</a>
        </div>
        <?php if (empty($summary['top_expenses'])): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-6">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxExp = max(array_column($summary['top_expenses'], 'total'));
        ?>
        <div class="space-y-3">
            <?php foreach ($summary['top_expenses'] as $i => $exp):
                $pct = $maxExp > 0 ? round((float)$exp['total'] / $maxExp * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0"
                              style="background:<?= htmlspecialchars($exp['color'] ?? '#6b7280') ?>20">
                            <span class="font-bold text-on-surface-variant" style="font-size:9px"><?= $i + 1 ?></span>
                        </span>
                        <span class="font-label text-label-sm text-on-surface"><?= htmlspecialchars($exp['category'] ?? 'ບໍ່ລະບຸ') ?></span>
                    </div>
                    <span class="font-label text-label-sm font-bold text-on-tertiary-fixed-variant"><?= $fmt((float)$exp['total']) ?></span>
                </div>
                <div class="progress-bar h-1.5">
                    <div class="progress-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($exp['color'] ?? '#EF4444') ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Top Donors -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-headline font-semibold text-title-md text-on-surface">ຜູ້ທານ</h3>
            <a href="<?= BASE_URL ?>/reports/donations?from=<?= $summary['current_month'] . '-01' ?>&to=<?= date('Y-m-d') ?>"
               class="font-label text-label-sm text-primary hover:underline">ລາຍງານ →</a>
        </div>
        <?php if (empty($summary['top_donors'])): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-6">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxDon = max(array_column($summary['top_donors'], 'total'));
        ?>
        <div class="space-y-3">
            <?php foreach ($summary['top_donors'] as $i => $donor):
                $pct = $maxDon > 0 ? round((float)$donor['total'] / $maxDon * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full hero-gradient flex items-center justify-center flex-shrink-0">
                            <span class="text-on-primary font-bold" style="font-size:9px"><?= $i + 1 ?></span>
                        </span>
                        <span class="font-label text-label-sm text-on-surface"><?= htmlspecialchars($donor['donor'] ?? 'ບໍ່ລະບຸ') ?></span>
                    </div>
                    <span class="font-label text-label-sm font-bold text-secondary"><?= $fmt((float)$donor['total']) ?></span>
                </div>
                <div class="progress-bar h-1.5">
                    <div class="progress-fill bg-secondary" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Quick Links ────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <?php
    $links = [
        ['href' => 'reports/monthly?month=' . $summary['current_month'], 'label' => 'ລາຍງານເດືອນ', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['href' => 'reports/budget?month='  . $summary['current_month'], 'label' => 'ງົບ vs ຕົວຈິງ', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ['href' => 'reports/cashflow',                                    'label' => 'Cash Flow',    'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'],
        ['href' => 'reports/daily?date='    . date('Y-m-d'),             'label' => 'ລາຍງານມື້ນີ້', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    ?>
    <?php foreach ($links as $link): ?>
    <a href="<?= BASE_URL ?>/<?= $link['href'] ?>"
       class="card flex items-center gap-3 hover:bg-surface-container-high transition-colors group">
        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0 group-hover:bg-primary/20 transition-colors">
            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $link['icon'] ?>"/>
            </svg>
        </div>
        <span class="font-label text-label-sm font-semibold text-on-surface"><?= $link['label'] ?></span>
    </a>
    <?php endforeach; ?>
</div>
