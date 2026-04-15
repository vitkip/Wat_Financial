<!DOCTYPE html>
<html lang="lo" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageTitle) ?> — Sacred Ledger Financial Dashboard">
    <?php if (!empty($csrfToken)): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <?php endif; ?>
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Phetsarath Lao Font -->
    <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">

    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        .swal-lao-popup  { font-family:'Phetsarath','Noto Sans Lao',sans-serif !important; border-radius:20px !important; }
        .swal-lao-title  { font-size:18px !important; font-weight:700 !important; }
        .swal-lao-btn    { font-family:'Phetsarath','Noto Sans Lao',sans-serif !important; font-size:14px !important; border-radius:999px !important; padding:8px 22px !important; }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>₭</text></svg>">
</head>
