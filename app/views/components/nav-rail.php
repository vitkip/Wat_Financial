<?php
/**
 * Component: Navigation Rail (Glass Sidebar)
 *
 * Expects:
 *   $activeNav  (string)     — current section key
 *   $perms      (Permission) — injected by Controller::view() for gate checks
 */

// Each item defines the minimum permission required to appear.
// NULL = visible to all authenticated users.
$navItems = [
    ['key' => 'dashboard',    'href' => '',            'label' => 'ໜ້າຫຼັກ',        'icon' => 'dashboard', 'perm' => null],
    ['key' => 'transactions', 'href' => 'transactions', 'label' => 'ລາຍການ',          'icon' => 'swap',      'perm' => 'transactions.view_own'],
    ['key' => 'budget',       'href' => 'budget',       'label' => 'ງົບປະມານ',        'icon' => 'budget',    'perm' => 'budgets.view'],
    ['key' => 'recurring',    'href' => 'recurring',    'label' => 'ລາຍການຊ້ຳ',      'icon' => 'recurring', 'perm' => 'recurring.view'],
    ['key' => 'goals',        'href' => 'goals',        'label' => 'ເປົ້າໝາຍ',        'icon' => 'goals',     'perm' => 'goals.view'],
    ['key' => 'reports',      'href' => 'reports',      'label' => 'ລາຍງານ',          'icon' => 'chart',     'perm' => 'reports.view'],
];

$adminItems = [
    ['key' => 'accounts',   'href' => 'accounts',   'label' => 'ບັນຊີ (Accounts)',        'icon' => 'budget',     'perm' => 'accounts.view'],
    ['key' => 'donations',  'href' => 'donations',  'label' => 'ຜູ້ບໍລິຈາກ (Donors)',     'icon' => 'users',      'perm' => 'donors.view'],
    ['key' => 'categories', 'href' => 'categories', 'label' => 'ໝວດໝູ່',                 'icon' => 'categories', 'perm' => 'categories.view'],
    ['key' => 'users',      'href' => 'users',      'label' => 'ຜູ້ໃຊ້ & ສິດທິ',         'icon' => 'users',      'perm' => 'users.view'],
];

// Filter lists down to what the current user can actually see
$visibleNav   = array_filter($navItems,   fn($i) => $i['perm'] === null || $perms->can($i['perm']));
$visibleAdmin = array_filter($adminItems, fn($i) => $perms->can($i['perm']));

function navIcon(string $icon): string
{
    $icons = [
        'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'swap'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>',
        'budget'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'recurring'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
        'goals'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
        'chart'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'categories' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'users'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    ];
    return $icons[$icon] ?? '';
}

function renderNavLink(array $item, string $activeNav): void {
    $isActive  = ($activeNav === $item['key']);
    $baseClass = 'flex items-center gap-3 px-3 py-2.5 rounded-md transition-colors duration-150 group relative';
    $stateClass = $isActive
        ? 'bg-primary-fixed text-primary font-semibold'
        : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface';
    $iconClass  = $isActive
        ? 'text-primary'
        : 'text-on-surface-variant group-hover:text-on-surface';
    ?>
    <a href="<?= BASE_URL ?>/<?= $item['href'] ?>" class="<?= $baseClass ?> <?= $stateClass ?>">
        <?php if ($isActive): ?>
            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-primary rounded-r-full"></span>
        <?php endif; ?>
        <svg class="w-5 h-5 flex-shrink-0 <?= $iconClass ?>"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <?= navIcon($item['icon']) ?>
        </svg>
        <span class="font-label text-sm"><?= $item['label'] ?></span>
    </a>
    <?php
}
?>

<aside id="sidebar" class="nav-rail fixed lg:relative z-40
              w-64 h-full flex-shrink-0 flex flex-col
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out">

    <!-- Brand -->
    <div class="px-6 py-7 border-b border-outline-variant/10">
        <a href="<?= BASE_URL ?>/" class="flex items-center gap-3 group">
            <img src="<?= BASE_URL ?>/public/img/logo.png" alt="Logo" class="w-9 h-9 object-contain flex-shrink-0">
            <div>
                <p class="font-headline font-bold text-title-sm text-primary leading-tight">
                    <?= htmlspecialchars(APP_NAME) ?>
                </p>
                <p class="font-label text-label-xs text-on-surface-variant uppercase tracking-widest">
                    <?= htmlspecialchars($_SESSION['user_role_label'] ?? $_SESSION['user_role'] ?? '') ?>
                </p>
            </div>
        </a>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto" aria-label="Main navigation">
        <p class="section-label px-3 mb-4">ເມນູຫຼັກ</p>

        <?php foreach ($visibleNav as $item): ?>
            <?php renderNavLink($item, $activeNav); ?>
        <?php endforeach; ?>

        <!-- ── Management Section (only shown when at least one item is visible) ── -->
        <?php if (!empty($visibleAdmin)): ?>
            <p class="section-label px-3 mt-5 mb-3">ຈັດການ</p>
            <?php foreach ($visibleAdmin as $item): ?>
                <?php renderNavLink($item, $activeNav); ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Settings (gated by settings.view) -->
        <?php if ($perms->can('settings.view')): ?>
            <?php renderNavLink(['key' => 'settings', 'href' => 'settings', 'label' => 'ການຕັ້ງຄ່າ', 'icon' => 'budget'], $activeNav); ?>
        <?php endif; ?>
    </nav>

    <!-- User Guide link (always visible) -->
    <div class="px-3 pb-1">
        <a href="<?= BASE_URL ?>/public/user-guide.html" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-md
                  transition-colors duration-150 group
                  text-on-surface-variant hover:bg-surface-container hover:text-on-surface">
            <svg class="w-5 h-5 flex-shrink-0 text-on-surface-variant group-hover:text-on-surface"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="font-label text-sm">ຄູ່ມືການໃຊ້ງານ</span>
            <svg class="w-3 h-3 ml-auto opacity-40 group-hover:opacity-70 flex-shrink-0"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    <!-- Footer: user info + logout -->
    <div class="px-6 py-4 border-t border-outline-variant/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full hero-gradient
                        flex items-center justify-center flex-shrink-0">
                <span class="text-on-primary font-headline font-bold text-xs">
                    <?= mb_strtoupper(mb_substr($authUser['name'] ?? 'W', 0, 1)) ?>
                </span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-label text-label-md text-on-surface font-semibold truncate">
                    <?= htmlspecialchars($authUser['name'] ?? 'ຜູ້ຈັດການການເງິນ') ?>
                </p>
                <p class="font-label text-label-sm text-on-surface-variant truncate">
                    <?= date('d M Y') ?>
                </p>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/auth/logout">
                <button type="submit"
                        class="p-1.5 rounded text-on-surface-variant hover:text-error
                               hover:bg-error-container transition-colors flex-shrink-0"
                        title="ອອກຈາກລະບົບ">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
