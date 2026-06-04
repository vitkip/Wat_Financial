<?php
/**
 * Shared Report Navigation Bar
 * Included at the top of every report view.
 * Expects: $activeNav = 'reports' (set by controller)
 */
$reportLinks = [
    ['href' => 'reports',           'label' => 'ທັງປີ',      'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ['href' => 'reports/summary',   'label' => 'ສະຫຼຸບວັດ',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ['href' => 'reports/monthly',   'label' => 'ລາຍເດືອນ',  'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['href' => 'reports/daily',     'label' => 'ລາຍວັນ',     'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['href' => 'reports/donations', 'label' => 'ເງິນທານ',    'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
    ['href' => 'reports/expenses',  'label' => 'ລາຍຈ່າຍ',    'icon' => 'M17 13l-5 5m0 0l-5-5m5 5V6'],
    ['href' => 'reports/cashflow',  'label' => 'Cash Flow',   'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'],
    ['href' => 'reports/budget',    'label' => 'ງົບ vs ຕົວຈິງ','icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
];

// Detect active report from the current URL
$currentUrl = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
// Strip leading project prefix (e.g. "Wat_Financial/")
$currentUrl = preg_replace('#^[^/]+/#', '', $currentUrl);
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="mb-6">
    <p class="section-label mb-1">ການວິເຄາະ</p>
    <h1 class="font-headline font-bold text-headline-lg text-on-surface">ລາຍງານ</h1>
</div>

<!-- ── Report Type Navigation ───────────────────────────────────── -->
<div class="flex items-center gap-2 overflow-x-auto pb-1 mb-6 scrollbar-hide">
    <?php foreach ($reportLinks as $link):
        $isActive = ($currentUrl === $link['href'] || rtrim($currentUrl, '/') === $link['href']);
    ?>
    <a href="<?= BASE_URL ?>/<?= $link['href'] ?>"
       class="flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full font-label text-label-sm font-semibold transition-all
              <?= $isActive
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' ?>">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $link['icon'] ?>"/>
        </svg>
        <?= $link['label'] ?>
    </a>
    <?php endforeach; ?>
</div>
