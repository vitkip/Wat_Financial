<?php
/**
 * Component: Budget Progress Bar
 *
 * @param array $budget — budget record with category_name, amount, spent, category_color
 */

$budget       = $budget ?? [];
$budgeted     = (float)($budget['amount']         ?? 0);
$spent        = (float)($budget['spent']          ?? 0);
$remaining    = $budgeted - $spent;
$pct          = $budgeted > 0 ? min(100, round(($spent / $budgeted) * 100)) : 0;
$overBudget   = $spent > $budgeted;
$category     = htmlspecialchars($budget['category_name'] ?? 'ບໍ່ຈັດໝວດ');
$color        = $budget['category_color'] ?? '#006C49';
$barColor     = $overBudget ? '#930013' : $color;

$fmtBudgeted  = CURRENCY . ' ' . number_format($budgeted, 0, '.', ',');
$fmtSpent     = CURRENCY . ' ' . number_format($spent,    0, '.', ',');
$fmtRemaining = CURRENCY . ' ' . number_format(abs($remaining), 0, '.', ',');
?>

<div class="py-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                 style="background-color: <?= htmlspecialchars($color) ?>;"></div>
            <span class="font-label text-label-md text-on-surface font-medium">
                <?= $category ?>
            </span>
            <?php if ($overBudget): ?>
                <span class="chip-expense text-xs">ເກີນງົບ</span>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <span class="font-label text-label-md font-semibold
                         <?= $overBudget ? 'text-on-tertiary-fixed-variant' : 'text-on-surface' ?>">
                <?= $fmtSpent ?>
            </span>
            <span class="font-label text-label-sm text-on-surface-variant ml-1">
                / <?= $fmtBudgeted ?>
            </span>
        </div>
    </div>

    <!-- Progress Track -->
    <div class="progress-bar">
        <div class="progress-fill"
             style="width: <?= $pct ?>%; background-color: <?= htmlspecialchars($barColor) ?>;">
        </div>
    </div>

    <!-- Subtext -->
    <div class="flex items-center justify-between mt-1.5">
        <span class="font-label text-label-sm text-on-surface-variant">
            <?= $pct ?>% ໃຊ້ແລ້ວ
        </span>
        <span class="font-label text-label-sm
                     <?= $overBudget ? 'text-on-tertiary-fixed-variant' : 'text-secondary' ?>">
            <?= $overBudget ? '' : '' ?>
            <?= $fmtRemaining ?> <?= $overBudget ? 'ເກີນ' : 'ຍັງເຫຼືອ' ?>
        </span>
    </div>
</div>
