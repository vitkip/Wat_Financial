<?php
/**
 * View: Donation Report
 * Variables: $from, $to, $rows, $by_donor, $by_cat, $totals, $canExport
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp  = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ລາຍງານເງິນທານ</h2>
        <p class="font-body text-body-sm text-on-surface-variant mt-0.5">
            <?= date('d/m/Y', strtotime($from)) ?> – <?= date('d/m/Y', strtotime($to)) ?>
        </p>
    </div>
    <form method="GET" class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-1.5">
            <label class="font-label text-label-xs text-on-surface-variant">ຈາກ</label>
            <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" max="<?= date('Y-m-d') ?>"
                   class="px-3 py-2 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>
        <div class="flex items-center gap-1.5">
            <label class="font-label text-label-xs text-on-surface-variant">ຫາ</label>
            <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" max="<?= date('Y-m-d') ?>"
                   class="px-3 py-2 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
        </div>
        <button type="submit" class="btn-primary">ດຶງຂໍ້ມູນ</button>
        <?php if ($canExport && $totals['count'] > 0): ?>
        <a href="?from=<?= $from ?>&to=<?= $to ?>&export=csv" class="btn-outline">CSV</a>
        <?php endif; ?>
    </form>
</div>

<!-- ── KPI Cards ────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <p class="section-label mb-1">ລວມທານ</p>
        <p class="font-headline font-bold text-title-lg text-secondary"><?= $fmt($totals['income']) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຈຳນວນລາຍການ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface"><?= $totals['count'] ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຈຳນວນຜູ້ທານ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface"><?= count($by_donor) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ສະເລ່ຍ/ລາຍການ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface">
            <?= $totals['count'] > 0 ? $fmt($totals['income'] / $totals['count']) : '—' ?>
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Top Donors -->
    <div class="lg:col-span-2 card overflow-hidden !p-0">
        <div class="px-5 py-3 bg-surface-container-low border-b border-outline-variant/10">
            <h3 class="font-headline font-semibold text-title-sm text-on-surface">ຜູ້ທານ</h3>
        </div>
        <?php if (empty($by_donor)): ?>
            <p class="py-8 text-center font-body text-body-sm text-on-surface-variant">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxDonor = max(array_column($by_donor, 'total'));
        ?>
        <div class="divide-y divide-outline-variant/10">
            <?php foreach ($by_donor as $i => $donor):
                $pct = $maxDonor > 0 ? round($donor['total'] / $maxDonor * 100) : 0;
                $sharePct = $totals['income'] > 0 ? round($donor['total'] / $totals['income'] * 100, 1) : 0;
            ?>
            <div class="px-5 py-3">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full hero-gradient flex items-center justify-center flex-shrink-0">
                            <span class="text-on-primary font-bold" style="font-size:9px"><?= $i + 1 ?></span>
                        </span>
                        <div>
                            <p class="font-label text-label-sm font-semibold text-on-surface">
                                <?= htmlspecialchars($donor['donor_name']) ?>
                            </p>
                            <?php if ($donor['donor_phone']): ?>
                            <p class="font-label text-label-xs text-on-surface-variant"><?= htmlspecialchars($donor['donor_phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-headline font-bold text-label-md text-secondary"><?= $fmt($donor['total']) ?></p>
                        <p class="font-label text-label-xs text-on-surface-variant"><?= $donor['count'] ?> ຄັ້ງ · <?= $sharePct ?>%</p>
                    </div>
                </div>
                <div class="progress-bar h-1">
                    <div class="progress-fill bg-secondary" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- By Category -->
    <div class="card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ຕາມໝວດ</h3>
        <?php if (empty($by_cat)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-6">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxCat = max(array_column($by_cat, 'total'));
        ?>
        <div class="space-y-3">
            <?php foreach ($by_cat as $cat):
                $pct  = $maxCat > 0 ? round($cat['total'] / $maxCat * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:<?= htmlspecialchars($cat['color']) ?>"></span>
                        <span class="font-label text-label-sm text-on-surface truncate max-w-[110px]"><?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                    <div class="text-right">
                        <p class="font-label text-label-xs font-semibold text-on-surface"><?= $fmt($cat['total']) ?></p>
                        <p class="font-label text-label-xs text-on-surface-variant"><?= $cat['count'] ?> ລາຍ</p>
                    </div>
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

<!-- ── Transaction Detail ────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <div class="px-5 py-3 bg-surface-container-low border-b border-outline-variant/10 flex items-center justify-between">
        <h3 class="font-headline font-semibold text-title-sm text-on-surface">ລາຍລະອຽດທຸກລາຍການ (<?= count($rows) ?>)</h3>
    </div>
    <?php if (empty($rows)): ?>
        <p class="py-8 text-center font-body text-body-sm text-on-surface-variant">ບໍ່ມີລາຍການ</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-container-low/50">
                <tr>
                    <th class="px-4 py-3 section-label w-24">ວັນທີ</th>
                    <th class="px-4 py-3 section-label">ລາຍລະອຽດ</th>
                    <th class="px-4 py-3 section-label">ໝວດໝູ່</th>
                    <th class="px-4 py-3 section-label">ຜູ້ທານ</th>
                    <th class="px-4 py-3 section-label text-right">ຈຳນວນ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/10">
                <?php foreach ($rows as $tx): ?>
                <tr class="hover:bg-surface-container-low/50 transition-colors">
                    <td class="px-4 py-3 font-label text-label-sm text-on-surface-variant whitespace-nowrap">
                        <?= date('d/m/Y', strtotime($tx['date'])) ?>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-body text-body-sm font-medium text-on-surface"><?= htmlspecialchars($tx['description']) ?></p>
                        <?php if ($tx['notes']): ?><p class="font-label text-label-xs text-on-surface-variant"><?= htmlspecialchars($tx['notes']) ?></p><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-label text-label-sm text-on-surface-variant"><?= htmlspecialchars($tx['category_name'] ?? '—') ?></td>
                    <td class="px-4 py-3">
                        <?php if ($tx['donor_name']): ?>
                        <p class="font-label text-label-sm text-on-surface"><?= htmlspecialchars($tx['donor_name']) ?></p>
                        <?php if ($tx['donor_phone']): ?><p class="font-label text-label-xs text-on-surface-variant"><?= htmlspecialchars($tx['donor_phone']) ?></p><?php endif; ?>
                        <?php else: ?>
                        <span class="text-on-surface-variant">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right font-headline font-bold text-secondary whitespace-nowrap">
                        +<?= number_format((float)$tx['amount'], $dp, '.', ',') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-surface-container border-t-2 border-outline-variant/20">
                    <td colspan="4" class="px-4 py-3 text-right font-label text-label-sm font-semibold text-on-surface-variant">ລວມທານທັງໝົດ</td>
                    <td class="px-4 py-3 text-right font-headline font-bold text-secondary">
                        <?= $fmt($totals['income']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>
