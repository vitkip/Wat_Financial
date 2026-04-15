<?php
/**
 * Component: Stat Card (KPI)
 *
 * @param string $label        Card label
 * @param string $value        Formatted value string
 * @param string $change       e.g. "+12.5%"
 * @param bool   $positive     Whether change is positive (green) or negative (red)
 * @param string $icon         SVG path data
 * @param string $accent       Tailwind color class for icon bg, e.g. 'bg-secondary-fixed'
 */

$label    = $label    ?? 'Metric';
$value    = $value    ?? '0';
$change   = $change   ?? null;
$positive = $positive ?? true;
$icon     = $icon     ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
$accent   = $accent   ?? 'bg-primary-fixed';
?>

<div class="card-elevated group">
    <div class="flex items-start justify-between mb-4">
        <div class="w-10 h-10 rounded-md <?= $accent ?>
                    flex items-center justify-center flex-shrink-0
                    transition-transform duration-200 group-hover:scale-110">
            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="1.8">
                <?= $icon ?>
            </svg>
        </div>
        <?php if ($change !== null): ?>
            <span class="<?= $positive ? 'chip-income' : 'chip-expense' ?>">
                <?= $positive ? '↑' : '↓' ?> <?= htmlspecialchars($change) ?>
            </span>
        <?php endif; ?>
    </div>

    <p class="section-label mb-1"><?= htmlspecialchars($label) ?></p>
    <p class="font-headline font-bold text-headline-sm text-on-surface leading-none">
        <?= $value ?>
    </p>
</div>
