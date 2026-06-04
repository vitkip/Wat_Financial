<?php require BASE_PATH . '/app/views/layouts/head.php'; ?>
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">ບັນຊີການເງິນ (Accounts)</h1>
        <button onclick="document.getElementById('addModal').style.display='block'" class="bg-blue-600 text-white px-4 py-2 rounded">ເພີ່ມບັນຊີ</button>
    </div>
    <div class="bg-white rounded shadow">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4">ຊື່ບັນຊີ</th>
                    <th class="p-4">ປະເພດ</th>
                    <th class="p-4 text-right">ຍອດເງິນປັດຈຸບັນ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accounts as $acc): ?>
                <tr class="border-b">
                    <td class="p-4"><?= htmlspecialchars($acc['name']) ?></td>
                    <td class="p-4"><?= htmlspecialchars($acc['type']) ?></td>
                    <td class="p-4 text-right font-bold"><?= number_format($acc['balance']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">ເພີ່ມບັນຊີໃໝ່</h2>
        <form method="POST" action="<?= BASE_URL ?>/accounts/store">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">ຊື່ບັນຊີ</label>
                <input type="text" name="name" required class="w-full border p-2 rounded">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">ປະເພດ</label>
                <select name="type" class="w-full border p-2 rounded">
                    <option value="asset">Asset (ຊັບສິນ/ເງິນສົດ/ທະນາຄານ)</option>
                    <option value="liability">Liability (ໜີ້ສິນ)</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="px-4 py-2 bg-gray-200 rounded">ຍົກເລີກ</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>
