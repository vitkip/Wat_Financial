<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ໃບບິນຮັບເງິນ - <?= htmlspecialchars($transaction['reference_no'] ?? '') ?></title>
    <style>
        body { font-family: 'Noto Serif Lao', serif; padding: 40px; color: #111; max-width: 800px; margin: auto; }
        .receipt-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .temple-name { font-size: 24px; font-weight: bold; margin: 0; }
        .receipt-title { font-size: 20px; font-weight: bold; margin-top: 10px; }
        .details { margin-bottom: 30px; line-height: 1.8; font-size: 16px; }
        .amount-box { border: 1px solid #000; padding: 10px 20px; font-size: 20px; font-weight: bold; display: inline-block; margin-top: 20px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .sig-line { width: 200px; border-bottom: 1px dashed #000; text-align: center; padding-bottom: 5px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom:20px;padding:10px 20px;cursor:pointer;">ພິມໃບບິນ (Print)</button>
    <div class="receipt-header">
        <h1 class="temple-name"><?= htmlspecialchars($settings['app_name'] ?? 'ວັດສາສະໜາ') ?></h1>
        <div class="receipt-title">ໃບຮັບເງິນອະນຸໂມທະນາບັດ (DONATION RECEIPT)</div>
        <div>ເລກທີ: <?= htmlspecialchars($transaction['reference_no'] ?? 'N/A') ?></div>
        <div>ວັນທີ: <?= date('d/m/Y', strtotime($transaction['date'])) ?></div>
    </div>
    
    <div class="details">
        <div><strong>ໄດ້ຮັບເງິນຈາກ (Received From):</strong> <?= htmlspecialchars($transaction['donor_name']) ?></div>
        <div><strong>ຈຳນວນເງິນ (Amount):</strong> <?= number_format($transaction['amount']) ?> ₭</div>
        <div><strong>ຈຸດປະສົງ (Purpose):</strong> <?= htmlspecialchars($transaction['description']) ?></div>
        <div><strong>ໝາຍເຫດ (Notes):</strong> <?= htmlspecialchars($transaction['notes'] ?? '-') ?></div>
    </div>
    
    <div class="amount-box">ລວມ: <?= number_format($transaction['amount']) ?> ₭</div>
    
    <div class="signatures">
        <div>
            <div class="sig-line">ຜູ້ບໍລິຈາກ (Donor)</div>
        </div>
        <div>
            <div class="sig-line">ຜູ້ຮັບເງິນ (Receiver)</div>
            <div style="text-align:center;margin-top:5px;font-size:14px;"><?= htmlspecialchars($transaction['creator_name']) ?></div>
        </div>
    </div>
</body>
</html>
