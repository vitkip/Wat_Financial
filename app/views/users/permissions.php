<?php
/**
 * View: Permission Matrix
 * Variables: $roles (array), $matrix (array keyed by resource)
 */

// Resource display names in Lao
$resourceLabels = [
    'transactions' => 'ທຸລະກຳ',
    'categories'   => 'ໝວດໝູ່',
    'users'        => 'ຜູ້ໃຊ້',
    'budgets'      => 'ງົບປະມານ',
    'reports'      => 'ລາຍງານ',
    'settings'     => 'ການຕັ້ງຄ່າ',
    'audit_log'    => 'Audit Log',
    'donors'       => 'ຜູ້ບໍລິຈາກ',
    'accounts'     => 'ບັນຊີ',
    'recurring'    => 'ລາຍການຊ້ຳ',
    'goals'        => 'ເປົ້າໝາຍ',
];

// Role colour accents
$roleColour = [
    'super_admin'  => '#091426',
    'temple_admin' => '#006C49',
    'accountant'   => '#3b82f6',
    'treasurer'    => '#f59e0b',
    'auditor'      => '#8b5cf6',
    'monk'         => '#f97316',
    'viewer'       => '#6b7280',
];
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ລະບົບ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">Permission Matrix</h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            ຕາຕະລາງສິດທິຄົບຖ້ວນ ສຳລັບທຸກ Role
        </p>
    </div>
    <a href="<?= BASE_URL ?>/users" class="btn-outline">
        ← ກັບຄືນ
    </a>
</div>

<!-- ── Role Summary Cards ───────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
    <?php foreach ($roles as $role): ?>
    <div class="card !p-4 text-center">
        <div class="w-8 h-8 rounded-full mx-auto mb-2 flex items-center justify-center"
             style="background: <?= htmlspecialchars($roleColour[$role['name']] ?? '#6b7280') ?>18">
            <span class="font-headline font-bold text-sm"
                  style="color: <?= htmlspecialchars($roleColour[$role['name']] ?? '#6b7280') ?>">
                <?= mb_strtoupper(mb_substr($role['label'], 0, 1)) ?>
            </span>
        </div>
        <p class="font-label text-label-xs font-semibold text-on-surface leading-tight">
            <?= htmlspecialchars($role['label_lao']) ?>
        </p>
        <p class="font-label text-label-xs text-on-surface-variant">
            <?= htmlspecialchars($role['label']) ?>
        </p>
        <?php if ($role['is_system']): ?>
        <span class="mt-1.5 inline-block font-label text-label-xs px-1.5 py-0.5 rounded-full bg-primary/10 text-primary">
            System
        </span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Matrix Table ─────────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-surface-container-low border-b border-outline-variant/10">
                    <th class="px-5 py-3 font-label text-label-sm text-on-surface-variant uppercase tracking-wider w-64 sticky left-0 bg-surface-container-low z-10">
                        ສິດທິ
                    </th>
                    <?php foreach ($roles as $role): ?>
                    <th class="px-3 py-3 text-center font-label text-label-xs uppercase tracking-wider min-w-[90px]">
                        <div class="flex flex-col items-center gap-1">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-white font-bold text-xs"
                                  style="background: <?= htmlspecialchars($roleColour[$role['name']] ?? '#6b7280') ?>">
                                <?= mb_strtoupper(mb_substr($role['label'], 0, 1)) ?>
                            </span>
                            <span class="text-on-surface-variant" style="font-size: 10px; line-height:1.2;">
                                <?= htmlspecialchars($role['label']) ?>
                            </span>
                        </div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matrix as $resource => $perms): ?>
                <!-- Resource Section Header -->
                <tr class="bg-surface-container/50">
                    <td colspan="<?= count($roles) + 1 ?>"
                        class="px-5 py-2 font-label text-label-sm font-semibold text-on-surface uppercase tracking-widest border-b border-outline-variant/10">
                        <?= htmlspecialchars($resourceLabels[$resource] ?? $resource) ?>
                    </td>
                </tr>
                <!-- Permission Rows -->
                <?php foreach ($perms as $perm): ?>
                <tr class="border-b border-outline-variant/10 hover:bg-surface-container-low/50 transition-colors">
                    <td class="px-5 py-3 sticky left-0 bg-white/80 backdrop-blur-sm z-10">
                        <p class="font-label text-label-sm text-on-surface">
                            <?= htmlspecialchars($perm['label_lao']) ?>
                        </p>
                        <code class="font-mono text-xs text-on-surface-variant/70">
                            <?= htmlspecialchars($perm['name']) ?>
                        </code>
                    </td>
                    <?php foreach ($roles as $role): ?>
                    <td class="px-3 py-3 text-center">
                        <?php if ($perm['roles'][$role['id']]): ?>
                            <svg class="w-5 h-5 mx-auto text-secondary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        <?php else: ?>
                            <span class="block w-3 h-0.5 mx-auto bg-outline-variant/30 rounded-full"></span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Role Detail Cards ─────────────────────────────────────────── -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

    <?php
    // Pre-compute permission count per role for the summary cards
    $permCountPerRole = [];
    $totalPerms = 0;
    foreach ($matrix as $resource => $perms) {
        if ($totalPerms === 0) $totalPerms = count($perms); // first resource count for reference
        foreach ($perms as $p) {
            foreach ($roles as $role) {
                if ($p['roles'][$role['id']]) {
                    $permCountPerRole[$role['id']] = ($permCountPerRole[$role['id']] ?? 0) + 1;
                }
            }
        }
    }
    $totalPermCount = array_sum(array_map('count', $matrix));

    $roleDescriptions = [
        'super_admin'  => ['desc' => 'ສິດທິຄົບຖ້ວນ — ສາມາດດຳເນີນທຸກ action ໃນລະບົບ', 'approve' => 'ອະນຸມັດທຸກທຸລະກຳ', 'restricted' => 'ບໍ່ມີ'],
        'temple_admin' => ['desc' => 'ຈັດການການດຳເນີນງານວັດ, ຜູ້ໃຊ້, ແລະ ການອະນຸມັດ', 'approve' => 'ອະນຸມັດ / ປະຕິເສດທຸລະກຳ', 'restricted' => 'ບໍ່ສາມາດ assign Role ໄດ້'],
        'accountant'   => ['desc' => 'ສ້າງ ແລະ ຈັດການທຸລະກຳ, ຜູ້ບໍລິຈາກ', 'approve' => 'ສ້າງ → pending (ລໍຖ້າ Admin)', 'restricted' => 'ບໍ່ສາມາດ approve, ລຶບ, ຈັດການຜູ້ໃຊ້'],
        'treasurer'    => ['desc' => 'ອະນຸມັດທຸລະກຳ, ຈັດງົບ, ດຳເນີນລາຍການຊ້ຳ', 'approve' => 'ອະນຸມັດ / ປະຕິເສດທຸລະກຳ', 'restricted' => 'ບໍ່ສາມາດລຶບ, ຈັດການຜູ້ໃຊ້'],
        'auditor'      => ['desc' => 'ອ່ານ-ຢ່າງດຽວ, ລວມ Audit Log ແລະ ລາຍງານ', 'approve' => 'ບໍ່ສາມາດ approve ໄດ້', 'restricted' => 'ສ້າງ / ແກ້ໄຂ / ລຶບ ທຸກ action'],
        'monk'         => ['desc' => 'ບັນທຶກການທານ ແລະ ເບິ່ງສະຫຼຸບລາຍຮັບ', 'approve' => 'ສ້າງ → pending', 'restricted' => 'ລາຍຈ່າຍ, ງົບ, ຜູ້ໃຊ້, ການຕັ້ງຄ່າ'],
        'viewer'       => ['desc' => 'ເບິ່ງທຸລະກຳ approved ແລະ ລາຍງານ', 'approve' => 'ບໍ່ສາມາດ approve ໄດ້', 'restricted' => 'ສ້າງ / ແກ້ໄຂ / ລຶບ / ຈັດການທຸກ action'],
    ];
    ?>

    <?php foreach ($roles as $role):
        $info    = $roleDescriptions[$role['name']] ?? [];
        $count   = $permCountPerRole[$role['id']] ?? 0;
        $colour  = $roleColour[$role['name']] ?? '#6b7280';
        $pct     = $totalPermCount > 0 ? round($count / $totalPermCount * 100) : 0;
    ?>
    <div class="card border-l-4" style="border-left-color: <?= htmlspecialchars($colour) ?>">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="font-headline font-bold text-title-md text-on-surface">
                    <?= htmlspecialchars($role['label_lao']) ?>
                </p>
                <p class="font-label text-label-sm text-on-surface-variant">
                    <?= htmlspecialchars($role['label']) ?>
                </p>
            </div>
            <span class="font-label text-label-xs px-2.5 py-1 rounded-full text-white font-semibold"
                  style="background: <?= htmlspecialchars($colour) ?>">
                <?= $count ?> / <?= $totalPermCount ?>
            </span>
        </div>

        <p class="font-body text-body-sm text-on-surface mb-3">
            <?= htmlspecialchars($info['desc'] ?? '') ?>
        </p>

        <!-- Progress bar showing % of all permissions granted -->
        <div class="progress-bar mb-3">
            <div class="progress-fill" style="width: <?= $pct ?>%; background: <?= htmlspecialchars($colour) ?>"></div>
        </div>

        <div class="space-y-1.5">
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-secondary flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-label text-label-sm text-on-surface">
                    <strong>ການອະນຸມັດ:</strong> <?= htmlspecialchars($info['approve'] ?? '—') ?>
                </span>
            </div>
            <div class="flex items-start gap-2">
                <svg class="w-4 h-4 text-error flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="font-label text-label-sm text-on-surface">
                    <strong>ຂໍ້ຈຳກັດ:</strong> <?= htmlspecialchars($info['restricted'] ?? '—') ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
