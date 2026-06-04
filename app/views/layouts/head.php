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

    <!-- Phetsarath Lao Font — non-blocking load (eliminates render-blocking round-trip) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload"
          href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap"
          as="style"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Phetsarath:wght@400;700&display=swap" rel="stylesheet">
    </noscript>

    <!-- Compiled Tailwind CSS (versioned for long-term browser caching) -->
    <?php $cssVer = @filemtime(BASE_PATH . '/public/css/app.css') ?: '1'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css?v=<?= $cssVer ?>">

    <!-- Chart.js — loaded only on pages that actually render charts -->
    <?php if (in_array($activeNav ?? '', ['dashboard', 'reports'], true)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"
            defer></script>
    <?php endif; ?>

    <!-- SweetAlert2 CSS — inlined critical styles; JS loaded lazily on first confirm() call -->
    <style>
        /* Minimal SweetAlert2 token overrides — full bundle loaded on demand */
        .swal-lao-popup  { font-family:'Phetsarath','Noto Sans Lao',sans-serif !important; border-radius:20px !important; }
        .swal-lao-title  { font-size:18px !important; font-weight:700 !important; }
        .swal-lao-btn    { font-family:'Phetsarath','Noto Sans Lao',sans-serif !important; font-size:14px !important; border-radius:999px !important; padding:8px 22px !important; }
    </style>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>₭</text></svg>">
</head>
