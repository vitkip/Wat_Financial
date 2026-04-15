<?php
/**
 * Component: Transaction Row
 *
 * @param array $tx — transaction record with category_name, type, amount, description, date
 */

$tx = $tx ?? [];
$isIncome  = ($tx['type'] ?? 'expense') === 'income';
$amount    = (float)($tx['amount'] ?? 0);
$formatted = CURRENCY . ' ' . number_format($amount, 0, '.', ',');
$category  = htmlspecialchars($tx['category_name'] ?? 'ບໍ່ຈັດໝວດ');
$desc      = htmlspecialchars($tx['description']   ?? '—');
$date      = isset($tx['date']) ? date('d M Y', strtotime($tx['date'])) : '—';
$color     = $tx['category_color'] ?? '#006C49';

// Inline colour dot (category color)
$dotStyle = 'background-color:' . htmlspecialchars($color) . ';';
?>

<div class="flex items-center gap-4 py-3.5 group
            hover:bg-surface-container-low rounded-md px-2 -mx-2
            transition-colors duration-150">

    <!-- Category Dot -->
    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center"
         style="<?= $dotStyle ?> opacity: 0.15;">
        <div class="w-3 h-3 rounded-full" style="<?= $dotStyle ?>"></div>
    </div>

    <!-- Description & Category -->
    <div class="flex-1 min-w-0">
        <p class="font-body text-body-sm text-on-surface font-semibold truncate">
            <?= $desc ?>
        </p>
        <p class="font-label text-label-sm text-on-surface-variant mt-0.5">
            <?= $category ?> · <?= $date ?>
        </p>
    </div>

    <!-- Amount -->
    <p class="font-label font-semibold text-title-sm flex-shrink-0
              <?= $isIncome ? 'text-secondary' : 'text-on-tertiary-fixed-variant' ?>">
        <?= $isIncome ? '+' : '−' ?><?= $formatted ?>
    </p>
</div>
