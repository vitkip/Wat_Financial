<?php
/**
 * View: Users / index
 * Variables: $users
 */
$currentId = (int)($_SESSION['user_id'] ?? 0);
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <p class="section-label mb-1">ລະບົບ</p>
        <h1 class="font-headline font-bold text-headline-lg text-on-surface">ຜູ້ໃຊ້</h1>
        <p class="font-body text-body-sm text-on-surface-variant mt-1">
            <?= count($users) ?> ຜູ້ໃຊ້ທັງໝົດ
        </p>
    </div>
    <button onclick="openModal('addUserModal')" class="btn-primary">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        ເພີ່ມຜູ້ໃຊ້
    </button>
</div>

<!-- ── Users Table ──────────────────────────────────────────────── -->
<div class="card overflow-hidden !p-0">
    <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant/10">
        <div class="hidden md:grid grid-cols-12 gap-4">
            <div class="col-span-1 section-label">#</div>
            <div class="col-span-3 section-label">ຊື່</div>
            <div class="col-span-4 section-label">ອີເມລ</div>
            <div class="col-span-2 section-label">ສະຖານະ</div>
            <div class="col-span-2 section-label text-right">ດຳເນີນການ</div>
        </div>
    </div>

    <div class="divide-y divide-outline-variant/10">
        <?php if (empty($users)): ?>
            <div class="py-16 text-center">
                <p class="font-headline font-semibold text-title-md text-on-surface-variant mb-2">ຍັງບໍ່ມີຜູ້ໃຊ້</p>
                <button onclick="openModal('addUserModal')" class="btn-primary mt-4">ສ້າງຜູ້ໃຊ້</button>
            </div>
        <?php else: ?>
            <?php foreach ($users as $u):
                $isSelf   = ((int)$u['id'] === $currentId);
                $isActive = (bool)$u['is_active'];
            ?>
            <div class="px-6 py-4 hover:bg-surface-container-low/60 transition-colors">
                <!-- Mobile -->
                <div class="md:hidden flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full hero-gradient flex items-center justify-center flex-shrink-0">
                        <span class="text-on-primary font-headline font-bold text-sm">
                            <?= mb_strtoupper(mb_substr($u['name'], 0, 1)) ?>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-body text-body-sm font-semibold text-on-surface truncate">
                            <?= htmlspecialchars($u['name']) ?>
                            <?php if ($isSelf): ?>
                                <span class="chip-income ml-1">ຂ້ອຍ</span>
                            <?php endif; ?>
                        </p>
                        <p class="font-label text-label-sm text-on-surface-variant truncate">
                            <?= htmlspecialchars($u['email']) ?>
                        </p>
                    </div>
                    <span class="<?= $isActive ? 'chip-income' : 'chip-expense' ?>">
                        <?= $isActive ? 'ເປີດ' : 'ປິດ' ?>
                    </span>
                </div>

                <!-- Desktop -->
                <div class="hidden md:grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-1">
                        <div class="w-8 h-8 rounded-full hero-gradient flex items-center justify-center">
                            <span class="text-on-primary font-headline font-bold text-xs">
                                <?= mb_strtoupper(mb_substr($u['name'], 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-span-3">
                        <p class="font-body text-body-sm font-semibold text-on-surface">
                            <?= htmlspecialchars($u['name']) ?>
                            <?php if ($isSelf): ?>
                                <span class="chip-income ml-1">ຂ້ອຍ</span>
                            <?php endif; ?>
                        </p>
                        <p class="font-label text-label-xs text-on-surface-variant">
                            ສ້າງ: <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </p>
                    </div>
                    <div class="col-span-4 font-body text-body-sm text-on-surface-variant truncate">
                        <?= htmlspecialchars($u['email']) ?>
                    </div>
                    <div class="col-span-2">
                        <span class="<?= $isActive ? 'chip-income' : 'chip-expense' ?>">
                            <?= $isActive ? 'ເປີດໃຊ້' : 'ປິດໃຊ້' ?>
                        </span>
                        <?php if ($u['last_login_at']): ?>
                        <p class="font-label text-label-xs text-on-surface-variant mt-0.5">
                            Login: <?= date('d/m/Y', strtotime($u['last_login_at'])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-span-2 flex items-center justify-end gap-1">
                        <!-- ແກ້ໄຂ -->
                        <button onclick="openEditUser(<?= htmlspecialchars(json_encode(['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']])) ?>)"
                                class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors"
                                title="ແກ້ໄຂ">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <!-- Reset Password -->
                        <button onclick="openResetPwd(<?= (int)$u['id'] ?>, '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')"
                                class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container hover:text-secondary transition-colors"
                                title="Reset ລະຫັດ">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </button>
                        <?php if (!$isSelf): ?>
                        <!-- Toggle -->
                        <form method="POST" action="<?= BASE_URL ?>/users/toggle" class="inline">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit"
                                    class="p-1.5 rounded text-on-surface-variant
                                           hover:bg-surface-container transition-colors
                                           <?= $isActive ? 'hover:text-error' : 'hover:text-secondary' ?>"
                                    title="<?= $isActive ? 'ປິດ' : 'ເປີດ' ?>ໃຊ້">
                                <?php if ($isActive): ?>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <?php endif; ?>
                            </button>
                        </form>
                        <!-- ລຶບ -->
                        <form method="POST" action="<?= BASE_URL ?>/users/delete">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="button"
                                    onclick="confirmDelete(this)"
                                    data-desc="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>"
                                    data-sub="<?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?>"
                                    class="p-1.5 rounded text-on-surface-variant hover:bg-error-container hover:text-error transition-colors"
                                    title="ລຶບ">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ══ Modal: ເພີ່ມຜູ້ໃຊ້ ══════════════════════════════════════ -->
<div id="addUserModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('addUserModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ເພີ່ມຜູ້ໃຊ້</h2>
            <button onclick="closeModal('addUserModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/users/create" class="space-y-4">
            <div>
                <label class="section-label block mb-2">ຊື່ <span class="text-error">*</span></label>
                <input type="text" name="name" required maxlength="100" placeholder="ຊື່ເຕັມ"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ອີເມລ <span class="text-error">*</span></label>
                <input type="email" name="email" required maxlength="150" placeholder="email@example.com"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ລະຫັດຜ່ານ <span class="text-error">*</span></label>
                <input type="password" name="password" required minlength="6" placeholder="ຢ່າງໜ້ອຍ 6 ຕົວ"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('addUserModal')"
                        class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ສ້າງຜູ້ໃຊ້</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal: ແກ້ໄຂຜູ້ໃຊ້ ════════════════════════════════════ -->
<div id="editUserModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('editUserModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">ແກ້ໄຂຂໍ້ມູນ</h2>
            <button onclick="closeModal('editUserModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/users/update" class="space-y-4">
            <input type="hidden" name="id" id="editUserId">
            <div>
                <label class="section-label block mb-2">ຊື່ <span class="text-error">*</span></label>
                <input type="text" name="name" id="editUserName" required maxlength="100"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div>
                <label class="section-label block mb-2">ອີເມລ <span class="text-error">*</span></label>
                <input type="email" name="email" id="editUserEmail" required maxlength="150"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editUserModal')"
                        class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ Modal: Reset ລະຫັດ ═══════════════════════════════════ -->
<div id="resetPwdModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-on-surface/40 backdrop-blur-sm"
         onclick="closeModal('resetPwdModal')"></div>
    <div class="relative w-full max-w-md bg-surface-container-lowest rounded-xl
                shadow-ambient-md p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-headline font-semibold text-title-lg text-on-surface">Reset ລະຫັດຜ່ານ</h2>
            <button onclick="closeModal('resetPwdModal')"
                    class="p-1.5 rounded text-on-surface-variant hover:bg-surface-container transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/users/resetpassword" class="space-y-4">
            <input type="hidden" name="id" id="resetPwdUserId">
            <p class="font-body text-body-sm text-on-surface-variant">
                ຕັ້ງລະຫັດໃໝ່ສຳລັບ: <strong id="resetPwdUserName" class="text-on-surface"></strong>
            </p>
            <div>
                <label class="section-label block mb-2">ລະຫັດຜ່ານໃໝ່ <span class="text-error">*</span></label>
                <input type="password" name="password" required minlength="6" placeholder="ຢ່າງໜ້ອຍ 6 ຕົວ"
                       class="w-full px-4 py-2.5 rounded-md font-label text-label-md
                              bg-surface-container border-0 text-on-surface
                              focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('resetPwdModal')"
                        class="flex-1 btn-outline">ຍົກເລີກ</button>
                <button type="submit" class="flex-1 btn-primary">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  {
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

function openEditUser(u) {
    document.getElementById('editUserId').value    = u.id;
    document.getElementById('editUserName').value  = u.name;
    document.getElementById('editUserEmail').value = u.email;
    openModal('editUserModal');
}

function openResetPwd(id, name) {
    document.getElementById('resetPwdUserId').value    = id;
    document.getElementById('resetPwdUserName').textContent = name;
    openModal('resetPwdModal');
}
</script>
