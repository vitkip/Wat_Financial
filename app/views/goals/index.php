<?php
/**
 * View: Savings Goals / index
 * Variables: $goals, $totalSaved
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, 0, '.', ',');

$colors = [
    '#006C49' => 'ຂຽວ',
    '#5A7AF0' => 'ຟ້າ',
    '#8B5CF6' => 'ມ່ວງ',
    '#F59E0B' => 'ທອງ',
    '#EC4899' => 'ບົວ',
    '#EF4444' => 'ແດງ',
    '#0EA5E9' => 'ຟ້າອ່ອນ',
    '#4EDEA3' => 'ຂຽວສົດ',
];
?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ການເງິນ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">ເປົ້າໝາຍ</h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            ເງິນຝາກທັງໝົດ (ຍັງໄม່ສຳເລັດ): <strong class="text-secondary"><?= $fmt($totalSaved) ?></strong>
        </p>
    </div>
    <button onclick="openModal('addGoalModal')" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        ເພີ່ມເປົ້າໝາຍ
    </button>
</div>

<!-- ── Goals Grid ─────────────────────────────────────────────── -->
<?php if (empty($goals)): ?>
    <div class="card py-16 text-center">
        <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">ຍັງບໍ່ມີເປົ້າໝາຍ</p>
        <p class="font-body text-body-sm text-on-surface-variant mb-4">
            ສ້າງເປົ້າໝາຍທຳອິດ ເຊັ່ນ ຊື້ລົດ, ທ່ອງທ່ຽວ, ກອງທຶນສຸກເສີນ.
        </p>
        <button onclick="openModal('addGoalModal')" class="btn-primary">ສ້າງເປົ້າໝາຍ</button>
    </div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php foreach ($goals as $goal):
        $target  = (float)$goal['target_amount'];
        $current = (float)$goal['current_amount'];
        $pct     = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
        $done    = (bool)$goal['is_completed'];
        $color   = $goal['color'] ?? '#006C49';
        $remaining = $target - $current;
        $daysLeft = null;
        if ($goal['target_date']) {
            $diff = (new DateTime($goal['target_date']))->diff(new DateTime());
            $daysLeft = $diff->invert ? $diff->days : -$diff->days;
        }
    ?>
    <div class="card-elevated relative">
        <?php if ($done): ?>
            <div class="absolute top-4 right-4">
                <span class="chip-income text-xs">✓ ສຳເລັດ</span>
            </div>
        <?php endif; ?>

        <!-- Icon + Name -->
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-md flex items-center justify-center flex-shrink-0"
                 style="background-color: <?= htmlspecialchars($color) ?>20;">
                <div class="w-4 h-4 rounded-full" style="background-color: <?= htmlspecialchars($color) ?>;"></div>
            </div>
            <div class="min-w-0">
                <p class="font-headline font-bold text-title-sm text-on-surface truncate">
                    <?= htmlspecialchars($goal['name']) ?>
                </p>
                <?php if ($goal['description']): ?>
                    <p class="font-label text-label-sm text-on-surface-variant truncate">
                        <?= htmlspecialchars($goal['description']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Progress numbers -->
        <div class="flex items-end justify-between mb-2">
            <div>
                <p class="font-headline font-bold text-headline-sm text-on-surface">
                    <?= $fmt($current) ?>
                </p>
                <p class="font-label text-label-sm text-on-surface-variant">
                    ຈາກ <?= $fmt($target) ?>
                </p>
            </div>
            <span class="font-headline font-bold text-title-lg"
                  style="color: <?= htmlspecialchars($color) ?>;">
                <?= $pct ?>%
            </span>
        </div>

        <!-- Progress bar -->
        <div class="progress-bar mb-3">
            <div class="progress-fill"
                 style="width:<?= $pct ?>%; background-color:<?= htmlspecialchars($color) ?>;"></div>
        </div>

        <!-- Meta -->
        <div class="flex items-center justify-between text-label-sm font-label mb-4">
            <span class="text-on-surface-variant">
                <?php if (!$done): ?>
                    ຍັງຕ້ອງ <?= $fmt($remaining) ?>
                <?php else: ?>
                    ເກັບໄດ້ຄົບ
                <?php endif; ?>
            </span>
            <?php if ($goal['target_date']): ?>
                <span class="<?= $daysLeft < 0 ? 'text-error' : 'text-on-surface-variant' ?>">
                    <?php if ($daysLeft < 0): ?>
                        ໝົດກຳໝົດ <?= abs($daysLeft) ?> ວັນ
                    <?php else: ?>
                        ເຫຼືອ <?= $daysLeft ?> ວັນ
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <?php if (!$done): ?>
        <div class="flex gap-2">
            <button onclick="openAddModal(<?= (int)$goal['id'] ?>, '<?= htmlspecialchars($goal['name'], ENT_QUOTES) ?>')"
                    class="flex-1 px-3 py-2 rounded-md font-label text-label-md font-semibold
                           text-secondary bg-secondary/10 hover:bg-secondary/20 transition-colors text-center">
                + ເພີ່ມເງິນ
            </button>
            <form method="POST" action="<?= BASE_URL ?>/goals/delete/<?= (int)$goal['id'] ?>">
                <button type="button"
                        onclick="confirmDelete(this)"
                        data-desc="<?= htmlspecialchars($goal['name'], ENT_QUOTES) ?>"
                        class="px-3 py-2 rounded-md text-error hover:bg-error-container transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
        <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/goals/delete/<?= (int)$goal['id'] ?>">
            <button type="button"
                    onclick="confirmDelete(this)"
                    data-desc="<?= htmlspecialchars($goal['name'], ENT_QUOTES) ?>"
                    class="w-full px-3 py-2 rounded-md font-label text-label-md
                           text-on-surface-variant hover:bg-surface-container transition-colors">
                ລຶບ
            </button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Add Goal Modal ─────────────────────────────────────────── -->
<div id="addGoalModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('addGoalModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-bold text-title-lg text-on-surface">ສ້າງເປົ້າໝາຍ</h2>
            <button onclick="closeModal('addGoalModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/goals/store" class="space-y-4">
            <div>
                <label class="section-label block mb-2">ຊື່ເປົ້າໝາຍ</label>
                <input type="text" name="name" required placeholder="ຕົວຢ່າງ: ຊື້ລົດ"
                       class="w-full px-4 py-2.5 rounded-md font-body text-body-sm
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>
            <div>
                <label class="section-label block mb-2">ຄຳອະທິບາຍ <span class="normal-case font-normal text-on-surface-variant">(ບໍ່ບັງຄັບ)</span></label>
                <input type="text" name="description" placeholder="Honda PCX 150..."
                       class="w-full px-4 py-2.5 rounded-md font-body text-body-sm
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="section-label block mb-2">ຈຳນວນເປົ້າ (<?= CURRENCY ?>)</label>
                    <input type="number" name="target_amount" min="1" step="any" required placeholder="0"
                           class="w-full px-4 py-2.5 rounded-md font-headline font-bold text-title-md
                                  bg-surface-container border-0 text-on-surface
                                  focus:outline-none focus:ring-2 focus:ring-primary/20
                                  placeholder:text-on-surface-variant/40">
                </div>
                <div>
                    <label class="section-label block mb-2">ເງິນເລີ່ມຕົ້ນ (<?= CURRENCY ?>)</label>
                    <input type="number" name="current_amount" min="0" step="any" value="0"
                           class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                                  bg-surface-container border-0 text-on-surface
                                  focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>
            </div>
            <div>
                <label class="section-label block mb-2">ວັນທີເປົ້າ <span class="normal-case font-normal text-on-surface-variant">(ບໍ່ບັງຄັບ)</span></label>
                <input type="date" name="target_date"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ສີ</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colors as $hex => $name): ?>
                        <label class="relative cursor-pointer" title="<?= $name ?>">
                            <input type="radio" name="color" value="<?= $hex ?>"
                                   class="sr-only peer"
                                   <?= $hex === '#006C49' ? 'checked' : '' ?>>
                            <span class="block w-8 h-8 rounded-full transition-all duration-150
                                         ring-2 ring-transparent peer-checked:ring-offset-2
                                         peer-checked:ring-on-surface/50"
                                  style="background-color:<?= $hex ?>;"></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">ບັນທຶກເປົ້າໝາຍ</button>
        </form>
    </div>
</div>

<!-- ── Add Amount Modal ───────────────────────────────────────── -->
<div id="addAmountModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('addAmountModal')"></div>
    <div class="relative w-full max-w-xs bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10">
        <div class="flex items-center justify-between mb-4">
            <h2 id="addAmountTitle" class="font-headline font-bold text-title-md text-on-surface">ເພີ່ມເງິນ</h2>
            <button onclick="closeModal('addAmountModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="addAmountForm" method="POST" action="" class="space-y-4">
            <div>
                <label class="section-label block mb-2">ຈຳນວນ (<?= CURRENCY ?>)</label>
                <input type="number" name="amount" min="1" step="any" required placeholder="0"
                       class="w-full px-4 py-2.5 rounded-md font-headline font-bold text-title-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20
                              placeholder:text-on-surface-variant/40">
            </div>
            <button type="submit" class="btn-primary w-full justify-center">ເພີ່ມ</button>
        </form>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
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
function openAddModal(goalId, goalName) {
    document.getElementById('addAmountForm').action = BASE_URL + '/goals/add/' + goalId;
    document.getElementById('addAmountTitle').textContent = 'ເພີ່ມເງິນ: ' + goalName;
    openModal('addAmountModal');
}
</script>
