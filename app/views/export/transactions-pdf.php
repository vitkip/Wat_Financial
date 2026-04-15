<?php
/**
 * Export View: Transactions PDF print
 * Variables: $rows, $filters, $income, $expense, $net
 */
$dp  = defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0;
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, $dp, '.', ',');
$dp0 = fn(float $n, string $prefix = '') => $prefix . number_format($n, $dp, '.', ',') . ' ' . CURRENCY_CODE;

// ສ້າງ filter label summary
$filterLabels = [];
if ($filters['q'])           $filterLabels[] = 'ຄົ້ນ: "' . htmlspecialchars($filters['q']) . '"';
if ($filters['type'])        $filterLabels[] = $filters['type'] === 'income' ? 'ລາຍຮັບ' : 'ລາຍຈ່າຍ';
if ($filters['date_from'])   $filterLabels[] = 'ຈາກ ' . $filters['date_from'];
if ($filters['date_to'])     $filterLabels[] = 'ຮອດ ' . $filters['date_to'];
?>

<div class="max-w-5xl mx-auto px-6 py-8 print:px-8 print:py-6">

    <!-- ── Document Header ──────────────────────────────────────── -->
    <div class="flex items-start justify-between mb-8 pb-6 border-b-2 border-gray-800">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars(APP_NAME) ?></h1>
            <h2 class="text-lg font-semibold text-gray-700 mt-1">ລາຍງານທຸລະກຳ</h2>
            <?php if ($filterLabels): ?>
                <p class="text-sm text-gray-500 mt-1">ຕົວກອງ: <?= implode(' · ', $filterLabels) ?></p>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">ວັນທີ export</p>
            <p class="text-sm font-medium text-gray-700"><?= date('d/m/Y H:i') ?></p>
            <p class="text-sm text-gray-500 mt-1">ຈຳນວນທັງໝົດ</p>
            <p class="text-sm font-bold text-gray-900"><?= number_format(count($rows)) ?> ລາຍການ</p>
        </div>
    </div>

    <!-- ── Summary Cards ────────────────────────────────────────── -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-xs text-green-600 font-medium uppercase tracking-wide mb-1">ລວມລາຍຮັບ</p>
            <p class="text-xl font-bold text-green-700"><?= $fmt($income) ?></p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-xs text-red-600 font-medium uppercase tracking-wide mb-1">ລວມລາຍຈ່າຍ</p>
            <p class="text-xl font-bold text-red-600"><?= $fmt($expense) ?></p>
        </div>
        <div class="<?= $net >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-orange-50 border-orange-200' ?> border rounded-lg p-4">
            <p class="text-xs <?= $net >= 0 ? 'text-blue-600' : 'text-orange-600' ?> font-medium uppercase tracking-wide mb-1">ຍອດສຸດທິ</p>
            <p class="text-xl font-bold <?= $net >= 0 ? 'text-blue-700' : 'text-orange-600' ?>"><?= $fmt($net) ?></p>
        </div>
    </div>

    <!-- ── Transactions Table ────────────────────────────────────── -->
    <?php if (empty($rows)): ?>
        <div class="text-center py-16 text-gray-400">
            <p class="text-lg">ບໍ່ມີລາຍການ</p>
        </div>
    <?php else: ?>
    <table class="w-full border-collapse text-sm">
        <thead>
            <tr class="bg-gray-800 text-white">
                <th class="px-3 py-2.5 text-left font-semibold w-10">#</th>
                <th class="px-3 py-2.5 text-left font-semibold w-24">ວັນທີ</th>
                <th class="px-3 py-2.5 text-left font-semibold w-20">ປະເພດ</th>
                <th class="px-3 py-2.5 text-left font-semibold">ລາຍລະອຽດ</th>
                <th class="px-3 py-2.5 text-left font-semibold w-28">ໝວດໝູ່</th>
                <th class="px-3 py-2.5 text-right font-semibold w-36">ຈຳນວນ (<?= CURRENCY_CODE ?>)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $tx):
                $isIncome = $tx['type'] === 'income';
                $isEven   = $i % 2 === 0;
            ?>
            <tr class="<?= $isEven ? 'bg-white' : 'bg-gray-50' ?> border-b border-gray-100">
                <td class="px-3 py-2 text-gray-400 text-xs"><?= $i + 1 ?></td>
                <td class="px-3 py-2 text-gray-600 whitespace-nowrap"><?= date('d/m/Y', strtotime($tx['date'])) ?></td>
                <td class="px-3 py-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                 <?= $isIncome ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                        <?= $isIncome ? '↑ ຮັບ' : '↓ ຈ່າຍ' ?>
                    </span>
                </td>
                <td class="px-3 py-2">
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($tx['description']) ?></p>
                    <?php if (!empty($tx['notes'])): ?>
                        <p class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($tx['notes']) ?></p>
                    <?php endif; ?>
                </td>
                <td class="px-3 py-2 text-gray-500"><?= htmlspecialchars($tx['category_name'] ?? '—') ?></td>
                <td class="px-3 py-2 text-right font-semibold <?= $isIncome ? 'text-green-600' : 'text-red-600' ?>">
                    <?= ($isIncome ? '+' : '−') . number_format((float)$tx['amount'], $dp, '.', ',') ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="bg-gray-100 border-t-2 border-gray-300">
                <td colspan="5" class="px-3 py-2.5 font-semibold text-gray-700 text-right">ລວມລາຍຮັບ</td>
                <td class="px-3 py-2.5 text-right font-bold text-green-600">+<?= number_format($income, $dp, '.', ',') ?></td>
            </tr>
            <tr class="bg-gray-100">
                <td colspan="5" class="px-3 py-2.5 font-semibold text-gray-700 text-right">ລວມລາຍຈ່າຍ</td>
                <td class="px-3 py-2.5 text-right font-bold text-red-600">−<?= number_format($expense, $dp, '.', ',') ?></td>
            </tr>
            <tr class="bg-gray-800 text-white">
                <td colspan="5" class="px-3 py-3 font-bold text-right">ຍອດສຸດທິ</td>
                <td class="px-3 py-3 text-right font-bold text-lg">
                    <?= ($net >= 0 ? '+' : '−') . number_format(abs($net), $dp, '.', ',') ?>
                    <span class="text-xs font-normal opacity-70 ml-1"><?= CURRENCY_CODE ?></span>
                </td>
            </tr>
        </tfoot>
    </table>
    <?php endif; ?>

    <!-- ── Footer ───────────────────────────────────────────────── -->
    <div class="mt-8 pt-4 border-t border-gray-200 flex items-center justify-between text-xs text-gray-400">
        <span><?= htmlspecialchars(APP_NAME) ?> — ລາຍງານສ້າງອັດຕະໂນມັດ</span>
        <span>ພິມ <?= date('d/m/Y H:i:s') ?></span>
    </div>
</div>
