<?php require BASE_PATH . '/app/views/layouts/head.php'; ?>
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">ລາຍຊື່ຜູ້ບໍລິຈາກ (Donors)</h1>
        <button onclick="document.getElementById('addModal').style.display='block'" class="bg-green-600 text-white px-4 py-2 rounded">ເພີ່ມຜູ້ບໍລິຈາກ</button>
    </div>
    <div class="bg-white rounded shadow">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4">ຊື່ຜູ້ບໍລິຈາກ</th>
                    <th class="p-4">ເບີໂທ</th>
                    <th class="p-4">ທີ່ຢູ່</th>
                    <th class="p-4 text-center">ຈຳນວນຄັ້ງບໍລິຈາກ</th>
                    <th class="p-4 text-right">ຍອດບໍລິຈາກທັງໝົດ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donors as $d): ?>
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="p-4 font-bold text-gray-800"><?= htmlspecialchars($d['name']) ?></td>
                    <td class="p-4 text-gray-600"><?= htmlspecialchars($d['phone'] ?: '—') ?></td>
                    <td class="p-4 text-gray-500"><?= htmlspecialchars($d['address'] ?: '—') ?></td>
                    <td class="p-4 text-center text-gray-700"><?= number_format($d['donation_count']) ?></td>
                    <td class="p-4 text-right font-semibold text-green-600">
                        <?= CURRENCY . ' ' . number_format((float)$d['total_donated'], defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">ເພີ່ມຜູ້ບໍລິຈາກ</h2>
        <form method="POST" action="<?= BASE_URL ?>/donations/store_donor">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">ຊື່ຜູ້ບໍລິຈາກ</label>
                <input type="text" name="name" required class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">ເບີໂທ</label>
                <input type="text" name="phone" class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">ທີ່ຢູ່</label>
                <textarea name="address" class="w-full border p-2 rounded"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="px-4 py-2 bg-gray-200 rounded">ຍົກເລີກ</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>
