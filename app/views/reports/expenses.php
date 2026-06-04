<?php
/**
 * View: Expense Report
 * Variables: $from, $to, $rows, $by_cat, $daily, $totals, $canExport
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');
$dp  = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
?>

<?php require BASE_PATH . '/app/views/reports/_nav.php'; ?>

<!-- ── Header ───────────────────────────────────────────────────── -->
<div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ລາຍງານລາຍຈ່າຍ</h2>
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
        <p class="section-label mb-1">ລວມລາຍຈ່າຍ</p>
        <p class="font-headline font-bold text-title-lg text-on-tertiary-fixed-variant"><?= $fmt($totals['expense']) ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ຈຳນວນລາຍການ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface"><?= $totals['count'] ?></p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ໝວດທີ່ໃຊ້ຫຼາຍສຸດ</p>
        <p class="font-headline font-bold text-title-md text-on-surface truncate">
            <?= !empty($by_cat) ? htmlspecialchars($by_cat[0]['name']) : '—' ?>
        </p>
    </div>
    <div class="card">
        <p class="section-label mb-1">ສະເລ່ຍ/ລາຍການ</p>
        <p class="font-headline font-bold text-title-lg text-on-surface">
            <?= $totals['count'] > 0 ? $fmt($totals['expense'] / $totals['count']) : '—' ?>
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <!-- Daily Trend Chart -->
    <div class="lg:col-span-2 card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ແນວໂນ້ມລາຍຈ່າຍ</h3>
        <?php if (empty($daily)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-8">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else: ?>
        <div class="h-48"><canvas id="expDailyChart"></canvas></div>
        <?php endif; ?>
    </div>

    <!-- Category Breakdown -->
    <div class="card">
        <h3 class="font-headline font-semibold text-title-md text-on-surface mb-4">ຕາມໝວດໝູ່</h3>
        <?php if (empty($by_cat)): ?>
            <p class="text-center font-body text-body-sm text-on-surface-variant py-6">ບໍ່ມີຂໍ້ມູນ</p>
        <?php else:
            $maxCat = max(array_column($by_cat, 'total'));
        ?>
        <div class="space-y-3">
            <?php foreach (array_slice($by_cat, 0, 8) as $cat):
                $pct      = $maxCat > 0 ? round($cat['total'] / $maxCat * 100) : 0;
                $sharePct = $totals['expense'] > 0 ? round($cat['total'] / $totals['expense'] * 100, 1) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($cat['color']) ?>"></span>
                        <span class="font-label text-label-sm text-on-surface truncate max-w-[110px]"><?= htmlspecialchars($cat['name']) ?></span>
                    </div>
                    <div class="text-right">
                        <p class="font-label text-label-xs font-semibold text-on-surface"><?= $fmt($cat['total']) ?></p>
                        <p class="font-label text-label-xs text-on-surface-variant"><?= $cat['count'] ?> ລາຍ · <?= $sharePct ?>%</p>
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
                    <th class="px-4 py-3 section-label">ຜູ້ບັນທຶກ</th>
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
                    <td class="px-4 py-3">
                        <?php if ($tx['category_name']): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($tx['category_color'] ?? '#6b7280') ?>"></span>
                            <span class="font-label text-label-sm text-on-surface"><?= htmlspecialchars($tx['category_name']) ?></span>
                        </div>
                        <?php else: ?>
                        <span class="text-on-surface-variant">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-label text-label-sm text-on-surface-variant">
                        <?= htmlspecialchars($tx['creator_name'] ?? '—') ?>
                    </td>
                    <td class="px-4 py-3 text-right font-headline font-bold text-on-tertiary-fixed-variant whitespace-nowrap">
                        −<?= number_format((float)$tx['amount'], $dp, '.', ',') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-surface-container border-t-2 border-outline-variant/20">
                    <td colspan="4" class="px-4 py-3 text-right font-label text-label-sm font-semibold text-on-surface-variant">ລວມລາຍຈ່າຍທັງໝົດ</td>
                    <td class="px-4 py-3 text-right font-headline font-bold text-on-tertiary-fixed-variant">
                        <?= $fmt($totals['expense']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($daily)): ?>
<script nonce="<?= CSP_NONCE ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
(function () {
    const ctx = document.getElementById('expDailyChart');
    if (!ctx) return;
    const rawDates = <?= json_safe(array_keys($daily)) ?>;
    const amounts  = <?= json_safe(array_values($daily)) ?>;
    const labels   = rawDates.map(d => d.slice(5).replace('-', '/'));
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'ລາຍຈ່າຍ', data: amounts, backgroundColor: '#FFB3AD', borderRadius: 3, borderSkipped: false },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1E293B', callbacks: { label: ctx => ' ₭ ' + ctx.raw.toLocaleString() } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#75777D' }, border: { display: false } },
                y: { grid: { color: 'rgba(117,119,125,0.12)' }, ticks: { font: { size: 10 }, color: '#75777D', callback: v => v >= 1000 ? (v/1000).toFixed(0)+'K' : v }, border: { display: false } },
            },
        },
    });
})();
});
</script>
<?php endif; ?>
