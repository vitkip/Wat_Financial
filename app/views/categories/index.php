<?php
/**
 * View: Categories / index
 * Variables: $categories (with tx_count, total_expense, total_income)
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

<!-- ── Categories Grid ─────────────────────────────────────────── -->
<?php if (empty($categories)): ?>
    <div class="card py-16 text-center">
        <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">ຍັງບໍ່ມີໝວດໝູ່</p>
        <button onclick="openModal('addCatModal')" class="btn-primary mt-4">ສ້າງໝວດໝູ່</button>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($categories as $cat):
            $color = $cat['color'] ?? '#006C49';
            $txCount = (int) $cat['tx_count'];
            ?>
            <div class="card flex flex-col gap-4">
                <!-- Header -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md flex items-center justify-center flex-shrink-0"
                        style="background-color: <?= htmlspecialchars($color) ?>22">
                        <span class="w-3 h-3 rounded-full" style="background-color: <?= htmlspecialchars($color) ?>"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-label font-semibold text-label-lg text-on-surface truncate">
                            <?= htmlspecialchars($cat['name']) ?>
                        </p>
                        <p class="font-label text-label-sm text-on-surface-variant">
                            <?= $txCount ?> ລາຍການ
                        </p>
                    </div>
                    <!-- Actions -->
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="openEditCat(<?= htmlspecialchars(json_encode($cat)) ?>)"
                            class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors"
                            title="ແກ້ໄຂ">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <?php if ($txCount === 0): ?>
                            <form method="POST" action="<?= BASE_URL ?>/categories/delete">
                                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                <button type="button"
                                    onclick="confirmDelete(this)"
                                    data-desc="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                    class="p-1.5 rounded text-on-surface-variant hover:bg-error-container hover:text-error transition-colors"
                                    title="ລຶບ">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-outline-variant/10">
                    <div>
                        <p class="section-label mb-0.5">ລາຍຮັບ</p>
                        <p class="font-label font-semibold text-label-md text-secondary">
                            <?= $fmt((float) $cat['total_income']) ?>
                        </p>
                    </div>
                    <div>
                        <p class="section-label mb-0.5">ລາຍຈ່າຍ</p>
                        <p class="font-label font-semibold text-label-md text-error">
                            <?= $fmt((float) $cat['total_expense']) ?>
                        </p>
                    </div>
                </div>

                <!-- Edit inline buttons (always visible on non-hover) -->
                <div class="flex gap-2 pt-1">
                    <button onclick="openEditCat(<?= htmlspecialchars(json_encode($cat)) ?>)" class="flex-1 text-center font-label text-label-sm text-on-surface-variant
                           py-1.5 rounded hover:bg-surface-container transition-colors">
                        ແກ້ໄຂ
                    </button>
                    <?php if ($txCount === 0): ?>
                        <form method="POST" action="<?= BASE_URL ?>/categories/delete" class="flex-1">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="button"
                                    onclick="confirmDelete(this)"
                                    data-desc="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                    class="w-full font-label text-label-sm text-error
                                           py-1.5 rounded hover:bg-error-container transition-colors">
                                ລຶບ
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="flex-1 text-center font-label text-label-sm text-on-surface-variant/40 py-1.5"
                            title="ມີລາຍການຢູ່ ລຶບບໍ່ໄດ້">ລຶບບໍ່ໄດ້</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ══ Modal: ເພີ່ມໝວດໝູ່ ═══════════════════════════════════════ -->
<div id="addCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="closeModal('addCatModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ເພີ່ມໝວດໝູ່</h2>
            <button onclick="closeModal('addCatModal')"
                class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/categories/create" class="space-y-4">
            <div>
                <label class="section-label block mb-2">ຊື່ໝວດໝູ່ <span class="text-error">*</span></label>
                <input type="text" name="name" required maxlength="80" placeholder="ເຊັ່ນ: ອາຫານ, ຄ່າໄຟ..." class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ສີ</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colorList as $c): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="<?= $c ?>" <?= $c === '#006C49' ? 'checked' : '' ?>
                                class="sr-only peer">
                            <span class="w-7 h-7 rounded-full block ring-2 ring-transparent
                                     peer-checked:ring-on-surface peer-checked:ring-offset-2
                                     transition-all" style="background-color:<?= $c ?>"></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="color" name="color_custom" id="addColorCustom"
                        class="w-7 h-7 rounded-full cursor-pointer border-0 p-0" title="ເລືອກສີເອງ"
                        onchange="syncCustomColor(this,'addCatModal')">
                </div>
            </div>
            <div>
                <label class="section-label block mb-2">ໄອຄອນ (ຊື່ Lucide)</label>
                <input type="text" name="icon" value="tag" maxlength="40" placeholder="tag, car, home..." class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
                <p class="font-label text-label-xs text-on-surface-variant mt-1">
                    ຊື່ icon ຈາກ lucide.dev (ສຳລັບ reference ໃນອານາຄົດ)
                </p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addCatModal')" class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal: ແກ້ໄຂໝວດໝູ່ ══════════════════════════════════════ -->
<div id="editCatModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog"
    aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm" onclick="closeModal('editCatModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ແກ້ໄຂໝວດໝູ່</h2>
            <button onclick="closeModal('editCatModal')"
                class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/categories/update" class="space-y-4">
            <input type="hidden" name="id" id="editCatId">
            <div>
                <label class="section-label block mb-2">ຊື່ໝວດໝູ່ <span class="text-error">*</span></label>
                <input type="text" name="name" id="editCatName" required maxlength="80" class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ສີ</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colorList as $c): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="color" value="<?= $c ?>" class="sr-only peer edit-color-radio">
                            <span class="w-7 h-7 rounded-full block ring-2 ring-transparent
                                     peer-checked:ring-on-surface peer-checked:ring-offset-2
                                     transition-all" style="background-color:<?= $c ?>"></span>
                        </label>
                    <?php endforeach; ?>
                    <input type="color" name="color_custom" id="editColorCustom"
                        class="w-7 h-7 rounded-full cursor-pointer border-0 p-0" title="ເລືອກສີເອງ"
                        onchange="syncCustomColor(this,'editCatModal')">
                </div>
            </div>
            <div>
                <label class="section-label block mb-2">ໄອຄອນ (ຊື່ Lucide)</label>
                <input type="text" name="icon" id="editCatIcon" maxlength="40" class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editCatModal')" class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>
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
        document.getElementById('editCatIcon').value = cat.icon ?? 'tag';

        // Check matching swatch or set custom
        const radios = document.querySelectorAll('.edit-color-radio');
        let matched = false;
        radios.forEach(r => {
            r.checked = (r.value === cat.color);
            if (r.value === cat.color) matched = true;
        });
        document.getElementById('editColorCustom').value = cat.color ?? '#006C49';

        openModal('editCatModal');
    }

    function syncCustomColor(input, modalId) {
        // Uncheck preset swatches when custom color is picked
        const modal = document.getElementById(modalId);
        modal.querySelectorAll('input[type="radio"][name="color"]').forEach(r => r.checked = false);

        // Inject hidden input with the custom color value
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