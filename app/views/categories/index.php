<?php
/**
 * View: Categories / index
 * Variables: $categories (with tx_count, total_expense, total_income, type)
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');

$iconList = [
    'tag',
    'briefcase',
    'code',
    'utensils',
    'car',
    'home',
    'heart',
    'book',
    'shopping-bag',
    'film',
    'zap',
    'coffee',
    'gift',
    'music',
    'globe',
    'trending-up',
    'dollar-sign',
    'credit-card',
    'tool',
    'truck',
];

$colorList = [
    '#006C49',
    '#4EDEA3',
    '#5A7AF0',
    '#8B5CF6',
    '#EC4899',
    '#EF4444',
    '#F59E0B',
    '#0EA5E9',
    '#FF8C00',
    '#6B7280',
];

/**
 * Helper to generate inline SVGs for category presets
 */
function getCategoryIconSvg(string $icon, string $color = 'currentColor'): string {
    $paths = [
        'tag'           => '<path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'briefcase'     => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'code'          => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'utensils'      => '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v8c0 1.1.9 2 2 2h3Zm0 0v5"/>',
        'car'           => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'home'          => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'heart'         => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'book'          => '<path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/>',
        'shopping-bag'  => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'film'          => '<rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="17" x2="22" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/>',
        'zap'           => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'coffee'        => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
        'gift'          => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'music'         => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'globe'         => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'trending-up'   => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
        'dollar-sign'   => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'credit-card'   => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'tool'          => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        'truck'         => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    ];

    $path = $paths[strtolower($icon)] ?? $paths['tag'];
    return '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="' . htmlspecialchars($color) . '" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

/**
 * Helper to render category cards inside columns
 */
function renderCategoryCard(array $cat, string $showType, Closure $fmt): void {
    $color   = $cat['color'] ?? '#006C49';
    $txCount = (int) $cat['tx_count'];
    $icon    = $cat['icon']  ?? 'tag';

    $badgeClass = '';
    $badgeText  = '';
    if ($cat['type'] === 'income') {
        $badgeClass = 'bg-secondary/10 text-secondary';
        $badgeText  = 'ລາຍຮັບ';
    } elseif ($cat['type'] === 'expense') {
        $badgeClass = 'bg-error/10 text-error';
        $badgeText  = 'ລາຍຈ່າຍ';
    } else {
        $badgeClass = 'bg-primary-fixed text-on-primary-fixed-variant';
        $badgeText  = 'ທັງສອງ';
    }

    $statLabel = '';
    $statVal   = 0.0;
    $statClass = '';
    if ($showType === 'income') {
        $statLabel = 'ຍອດຮັບລວມ';
        $statVal   = (float)$cat['total_income'];
        $statClass = 'text-secondary';
    } else {
        $statLabel = 'ຍອດຈ່າຍລວມ';
        $statVal   = (float)$cat['total_expense'];
        $statClass = 'text-error';
    }
    ?>
    <div class="group relative flex flex-col justify-between p-5 rounded-xl border border-outline-variant/10 bg-surface-container-lowest shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
        <!-- Top Details -->
        <div>
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                     style="background-color: <?= htmlspecialchars($color) ?>18">
                    <?= getCategoryIconSvg($icon, $color) ?>
                </div>
                <span class="font-label text-label-xs px-2.5 py-0.5 rounded-full <?= $badgeClass ?>">
                    <?= $badgeText ?>
                </span>
            </div>
            
            <h3 class="font-headline font-bold text-title-md text-on-surface truncate mb-0.5" title="<?= htmlspecialchars($cat['name']) ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </h3>
            <p class="font-label text-label-sm text-on-surface-variant mb-4">
                <?= $txCount ?> ລາຍການທຸລະກຳ
            </p>
        </div>
        
        <!-- Stats and Actions -->
        <div class="pt-3 border-t border-outline-variant/10">
            <div class="flex justify-between items-end">
                <div>
                    <p class="font-label text-label-xs text-on-surface-variant uppercase tracking-wider mb-0.5"><?= $statLabel ?></p>
                    <p class="font-headline font-bold text-headline-sm <?= $statClass ?>"><?= $fmt($statVal) ?></p>
                </div>
                
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity duration-150">
                    <button onclick="openEditCat(<?= htmlspecialchars(json_encode($cat)) ?>)"
                            class="p-2 rounded-lg text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"
                            title="ແກ້ໄຂ">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <?php if ($txCount === 0): ?>
                        <form method="POST" action="<?= BASE_URL ?>/categories/delete" class="inline">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="button"
                                    onclick="confirmDelete(this)"
                                    data-desc="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                    class="p-2 rounded-lg text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors"
                                    title="ລຶບ">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

$incomeCats  = array_filter($categories, fn($c) => in_array($c['type'], ['income', 'both'], true));
$expenseCats = array_filter($categories, fn($c) => in_array($c['type'], ['expense', 'both'], true));

$totalIncomeStat  = array_sum(array_map(fn($c) => (float)$c['total_income'],  array_values($incomeCats)));
$totalExpenseStat = array_sum(array_map(fn($c) => (float)$c['total_expense'], array_values($expenseCats)));
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ລະບົບ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">ໝວດໝູ່</h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            <?= count($categories) ?> ໝວດໝູ່ທັງໝົດ
        </p>
    </div>
    <button onclick="openModal('addCatModal')" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        ເພີ່ມໝວດໝູ່
    </button>
</div>

<!-- ── Two Columns Category Layout ─────────────────────────────── -->
<?php if (empty($categories)): ?>
    <div class="card py-16 text-center">
        <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">ຍັງບໍ່ມີໝວດໝູ່</p>
        <button onclick="openModal('addCatModal')" class="btn-primary mt-4">ສ້າງໝວດໝູ່</button>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Left: Income Categories -->
        <div class="space-y-4">
            <div class="pb-3 border-b border-outline-variant/10">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-secondary flex-shrink-0"></span>
                        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ໝວດໝູ່ລາຍຮັບ</h2>
                    </div>
                    <span class="font-label text-label-sm font-semibold px-2.5 py-0.5 rounded-full bg-secondary/10 text-secondary">
                        <?= count($incomeCats) ?> ໝວດໝູ່
                    </span>
                </div>
                <p class="font-label text-label-xs text-on-surface-variant uppercase tracking-wider">ຍອດຮັບລວມທຸກໝວດ</p>
                <p class="font-headline font-bold text-title-md text-secondary"><?= $fmt($totalIncomeStat) ?></p>
            </div>
            
            <?php if (empty($incomeCats)): ?>
                <p class="py-8 text-center font-body text-body-sm text-on-surface-variant">ຍັງບໍ່ມີໝວດໝູ່ລາຍຮັບ</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($incomeCats as $cat): ?>
                        <?php renderCategoryCard($cat, 'income', $fmt); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right: Expense Categories -->
        <div class="space-y-4">
            <div class="pb-3 border-b border-outline-variant/10">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-error flex-shrink-0"></span>
                        <h2 class="font-headline font-bold text-headline-sm text-on-surface">ໝວດໝູ່ລາຍຈ່າຍ</h2>
                    </div>
                    <span class="font-label text-label-sm font-semibold px-2.5 py-0.5 rounded-full bg-error/10 text-error">
                        <?= count($expenseCats) ?> ໝວດໝູ່
                    </span>
                </div>
                <p class="font-label text-label-xs text-on-surface-variant uppercase tracking-wider">ຍອດຈ່າຍລວມທຸກໝວດ</p>
                <p class="font-headline font-bold text-title-md text-error"><?= $fmt($totalExpenseStat) ?></p>
            </div>
            
            <?php if (empty($expenseCats)): ?>
                <p class="py-8 text-center font-body text-body-sm text-on-surface-variant">ຍັງບໍ່ມີໝວດໝູ່ລາຍຈ່າຍ</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($expenseCats as $cat): ?>
                        <?php renderCategoryCard($cat, 'expense', $fmt); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
<?php endif; ?>

<!-- ══ Modal: ເພີ່ມໝວດໝູ່ ═══════════════════════════════════════ -->
<div id="addCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="closeModal('addCatModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ເພີ່ມໝວດໝູ່</h2>
            <button onclick="closeModal('addCatModal')" class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/categories/create" class="space-y-4">
            
            <!-- Category Name -->
            <div>
                <label class="section-label block mb-2">ຊື່ໝວດໝູ່ <span class="text-error">*</span></label>
                <input type="text" name="name" required maxlength="80" placeholder="ເຊັ່ນ: ອາຫານ, ຄ່າໄຟ..." 
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <!-- Category Type -->
            <div>
                <label class="section-label block mb-2">ປະເພດໝວດໝູ່ <span class="text-error">*</span></label>
                <select name="type" required 
                        class="w-full px-4 py-2.5 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="both">ທັງສອງ (Both - Shared)</option>
                    <option value="income">ລາຍຮັບ (Income Only)</option>
                    <option value="expense">ລາຍຈ່າຍ (Expense Only)</option>
                </select>
            </div>

            <!-- Color Preset Swatches -->
            <div>
                <label class="section-label block mb-2">ສີໝວດໝູ່</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colorList as $c): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="<?= $c ?>" <?= $c === '#006C49' ? 'checked' : '' ?> class="sr-only peer">
                            <span class="w-7 h-7 rounded-full block ring-2 ring-transparent peer-checked:ring-on-surface peer-checked:ring-offset-2 transition-all" style="background-color:<?= $c ?>"></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="color" name="color_custom" id="addColorCustom" class="w-7 h-7 rounded-full cursor-pointer border-0 p-0" title="ເລືອກສີເອງ" onchange="syncCustomColor(this,'addCatModal')">
                </div>
            </div>

            <!-- Icon Picker Swatches -->
            <div>
                <label class="section-label block mb-2">ເລືອກໄອຄອນ</label>
                <div class="grid grid-cols-5 gap-2 max-h-48 overflow-y-auto p-2 bg-surface-container/30 rounded-lg">
                    <?php foreach ($iconList as $ic): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="<?= $ic ?>" <?= $ic === 'tag' ? 'checked' : '' ?> class="sr-only peer add-icon-radio">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center border border-outline-variant/30 text-on-surface-variant peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
                                <?= getCategoryIconSvg($ic) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addCatModal')" class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ບັນທຶກ</button>
            </div>

        </form>
    </div>
</div>

<!-- ══ Modal: ແກ້ໄຂໝວດໝູ່ ══════════════════════════════════════ -->
<div id="editCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="closeModal('editCatModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ແກ້ໄຂໝວດໝູ່</h2>
            <button onclick="closeModal('editCatModal')" class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/categories/update" class="space-y-4">
            <input type="hidden" name="id" id="editCatId">
            
            <!-- Category Name -->
            <div>
                <label class="section-label block mb-2">ຊື່ໝວດໝູ່ <span class="text-error">*</span></label>
                <input type="text" name="name" id="editCatName" required maxlength="80" 
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <!-- Category Type -->
            <div>
                <label class="section-label block mb-2">ປະເພດໝວດໝູ່ <span class="text-error">*</span></label>
                <select name="type" id="editCatType" required 
                        class="w-full px-4 py-2.5 rounded-md font-label text-label-md bg-surface-container border-0 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="both">ທັງສອງ (Both - Shared)</option>
                    <option value="income">ລາຍຮັບ (Income Only)</option>
                    <option value="expense">ລາຍຈ່າຍ (Expense Only)</option>
                </select>
            </div>

            <!-- Color Swatches -->
            <div>
                <label class="section-label block mb-2">ສີໝວດໝູ່</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colorList as $c): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="<?= $c ?>" class="sr-only peer edit-color-radio">
                            <span class="w-7 h-7 rounded-full block ring-2 ring-transparent peer-checked:ring-on-surface peer-checked:ring-offset-2 transition-all" style="background-color:<?= $c ?>"></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="color" name="color_custom" id="editColorCustom" class="w-7 h-7 rounded-full cursor-pointer border-0 p-0" title="ເລືອກສີເອງ" onchange="syncCustomColor(this,'editCatModal')">
                </div>
            </div>

            <!-- Icon Picker Swatches -->
            <div>
                <label class="section-label block mb-2">ເລືອກໄອຄອນ</label>
                <div class="grid grid-cols-5 gap-2 max-h-48 overflow-y-auto p-2 bg-surface-container/30 rounded-lg">
                    <?php foreach ($iconList as $ic): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="icon" value="<?= $ic ?>" class="sr-only peer edit-icon-radio">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center border border-outline-variant/30 text-on-surface-variant peer-checked:bg-primary/10 peer-checked:border-primary peer-checked:text-primary transition-all">
                                <?= getCategoryIconSvg($ic) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editCatModal')" class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ອັບເດດ</button>
            </div>

        </form>
    </div>
</div>

<script nonce="<?= CSP_NONCE ?? '' ?>">
    function openModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function openEditCat(cat) {
        document.getElementById('editCatId').value = cat.id;
        document.getElementById('editCatName').value = cat.name;
        document.getElementById('editCatType').value = cat.type ?? 'both';

        // Check matching Swatch or set Custom Color
        const colorRadios = document.querySelectorAll('.edit-color-radio');
        let colorMatched = false;
        colorRadios.forEach(r => {
            r.checked = (r.value === cat.color);
            if (r.value === cat.color) colorMatched = true;
        });
        
        document.getElementById('editColorCustom').value = cat.color ?? '#006C49';
        if (!colorMatched) {
            syncCustomColor(document.getElementById('editColorCustom'), 'editCatModal');
        } else {
            // Clean up custom color hidden input ifpreset color is matched
            const hidden = document.getElementById('editCatModal').querySelector('input[name="color"][type="hidden"]');
            if (hidden) hidden.remove();
        }

        // Check matching Icon swatch
        const iconRadios = document.querySelectorAll('.edit-icon-radio');
        iconRadios.forEach(r => {
            r.checked = (r.value === (cat.icon ?? 'tag'));
        });

        openModal('editCatModal');
    }

    function syncCustomColor(input, modalId) {
        const modal = document.getElementById(modalId);
        // Uncheck all preset radio buttons
        modal.querySelectorAll('input[type="radio"][name="color"]').forEach(r => r.checked = false);

        // Append or update hidden field containing the custom hex code
        let hidden = modal.querySelector('input[name="color"][type="hidden"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'color';
            modal.querySelector('form').appendChild(hidden);
        }
        hidden.value = input.value;
    }
</script>