<?php
/**
 * Component: Navigation Rail (Glass Sidebar)
 *
 * Expects: $activeNav (string) — current section key
 */

$navItems = [
    ['key' => 'dashboard', 'href' => '', 'label' => 'ໜ້າຫຼັກ', 'icon' => 'dashboard'],
    ['key' => 'transactions', 'href' => 'transactions', 'label' => 'ລາຍການ', 'icon' => 'swap'],
    ['key' => 'budget', 'href' => 'budget', 'label' => 'ງົບປະມານ', 'icon' => 'budget'],
    ['key' => 'recurring', 'href' => 'recurring', 'label' => 'ລາຍການຊ້ຳ', 'icon' => 'recurring'],
    ['key' => 'goals', 'href' => 'goals', 'label' => 'ເປົ້າໝາຍ', 'icon' => 'goals'],
    ['key' => 'reports', 'href' => 'reports', 'label' => 'ລາຍງານ', 'icon' => 'chart'],
];

$adminItems = [
    ['key' => 'categories', 'href' => 'categories', 'label' => 'ໝວດໝູ່', 'icon' => 'categories'],
    ['key' => 'users', 'href' => 'users', 'label' => 'ຜູ້ໃຊ້', 'icon' => 'users'],
];

function navIcon(string $icon): string
{
    $icons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'swap' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>',
        'budget' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'recurring' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
        'goals' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'categories' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    ];
    return $icons[$icon] ?? '';
}
?>

<aside id="sidebar" class="nav-rail fixed lg:relative z-40
              w-64 h-full flex-shrink-0
              flex flex-col
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out">

    <!-- Brand -->
    <div class="px-6 py-7 border-b border-outline-variant/10">
        <a href="<?= BASE_URL ?>/" class="flex items-center gap-3 group">
            <div class="flex items-center justify-center flex-shrink-0">
                <img src="<?= BASE_URL ?>/public/img/logo.png" alt="Logo" class="w-9 h-9 object-contain">
            </div>
            <div>
                <p class="font-headline font-bold text-title-sm text-primary leading-tight">
                    <?= htmlspecialchars(APP_NAME) ?>
                </p>
                <p class="font-label text-label-sm text-on-surface-variant uppercase tracking-widest">
                    ໜ້າຫຼັກ
                </p>
            </div>
        </a>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto" aria-label="Main navigation">
        <p class="section-label px-3 mb-4">ເມນູຫຼັກ</p>

        <?php foreach ($navItems as $item):
            $isActive = ($activeNav === $item['key']);
            ?>
            <a href="<?= BASE_URL ?>/<?= $item['href'] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-md
                  transition-colors duration-150 group relative
                  <?= $isActive
                      ? 'bg-primary-fixed text-primary font-semibold'
                      : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' ?>">

                <?php if ($isActive): ?>
                    <span class="absolute left-0 top-1/2 -translate-y-1/2
                             w-1 h-7 bg-primary rounded-r-full"></span>
                <?php endif; ?>

                <svg class="w-5 h-5 flex-shrink-0
                        <?= $isActive ? 'text-primary' : 'text-on-surface-variant group-hover:text-on-surface' ?>"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <?= navIcon($item['icon']) ?>
                </svg>

                <span class="font-label text-sm"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>

        <!-- ── Admin Section ── -->
        <p class="section-label px-3 mt-5 mb-3">ຈັດການ</p>
        <?php foreach ($adminItems as $item):
            $isActive = ($activeNav === $item['key']);
            ?>
            <a href="<?= BASE_URL ?>/<?= $item['href'] ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-md
                  transition-colors duration-150 group relative
                  <?= $isActive
                      ? 'bg-primary-fixed text-primary font-semibold'
                      : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' ?>">
                <?php if ($isActive): ?>
                    <span class="absolute left-0 top-1/2 -translate-y-1/2
                             w-1 h-7 bg-primary rounded-r-full"></span>
                <?php endif; ?>
                <svg class="w-5 h-5 flex-shrink-0
                        <?= $isActive ? 'text-primary' : 'text-on-surface-variant group-hover:text-on-surface' ?>"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <?= navIcon($item['icon']) ?>
                </svg>
                <span class="font-label text-sm"><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Settings link -->
    <div class="px-3 pb-2">
        <a href="<?= BASE_URL ?>/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-md
                  transition-colors duration-150 group relative
                  <?= ($activeNav === 'settings')
                      ? 'bg-primary-fixed text-primary font-semibold'
                      : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' ?>">
            <?php if ($activeNav === 'settings'): ?>
                <span class="absolute left-0 top-1/2 -translate-y-1/2
                             w-1 h-7 bg-primary rounded-r-full"></span>
            <?php endif; ?>
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="font-label text-sm">ການຕັ້ງຄ່າ</span>
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
                <button type="submit" class="p-1.5 rounded text-on-surface-variant hover:text-error hover:bg-error-container
                               transition-colors flex-shrink-0" title="ອອກຈາກລະບົບ">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>