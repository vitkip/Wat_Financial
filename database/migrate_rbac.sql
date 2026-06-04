-- ═══════════════════════════════════════════════════════════════════
-- PHASE 7 — Role-Based Access Control (RBAC) Migration
-- Run after all previous migrations.
-- ═══════════════════════════════════════════════════════════════════

USE `wat_financial`;

-- ─── 1. Roles ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50)  NOT NULL UNIQUE  COMMENT 'machine slug: super_admin, temple_admin, …',
    `label`       VARCHAR(100) NOT NULL,
    `label_lao`   VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1 = cannot be deleted via UI',
    `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_roles_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `roles` (`id`,`name`,`label`,`label_lao`,`description`,`is_system`,`sort_order`) VALUES
(1, 'super_admin',   'Super Admin',    'ຜູ້ດູແລລະບົບສູງສຸດ',  'Full unrestricted system access',                        1, 1),
(2, 'temple_admin',  'Temple Admin',   'ຜູ້ຈັດການວັດ',       'Manages temple operations, users, and approvals',        1, 2),
(3, 'accountant',    'Accountant',     'ນັກບັນຊີ',            'Creates and manages transactions and donor records',     1, 3),
(4, 'treasurer',     'Treasurer',      'ຄັງເງິນ',             'Approves transactions and manages budgets',              1, 4),
(5, 'auditor',       'Auditor',        'ຜູ້ກວດສອບ',           'Read-only access to all financial data and audit logs',  1, 5),
(6, 'monk',          'Monk',           'ພຣະ / ສາມະເນນ',       'Can record donations and view income summaries',         1, 6),
(7, 'viewer',        'Viewer',         'ຜູ້ເບິ່ງ',            'Read-only access to approved transactions and reports',  1, 7)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`);


-- ─── 2. Permissions ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`        SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`      VARCHAR(80)  NOT NULL UNIQUE  COMMENT 'resource.action',
    `label`     VARCHAR(120) NOT NULL,
    `label_lao` VARCHAR(120) NOT NULL,
    `resource`  VARCHAR(50)  NOT NULL         COMMENT 'Used for grouping in the UI',
    `created_at` TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_perm_resource` (`resource`),
    INDEX `idx_perm_name`     (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `permissions` (`name`,`label`,`label_lao`,`resource`) VALUES
-- Transactions
('transactions.view_own',     'View own transactions',          'ເບິ່ງທຸລະກຳຂອງຕົນເອງ',        'transactions'),
('transactions.view_all',     'View all transactions',          'ເບິ່ງທຸລະກຳທັງໝົດ',           'transactions'),
('transactions.create',       'Create transaction',             'ສ້າງທຸລະກຳ',                  'transactions'),
('transactions.edit_own',     'Edit own pending transactions',  'ແກ້ໄຂທຸລະກຳລໍຖ້າຂອງຕົນ',    'transactions'),
('transactions.approve',      'Approve transactions',           'ອະນຸມັດທຸລະກຳ',               'transactions'),
('transactions.reject',       'Reject transactions',            'ປະຕິເສດທຸລະກຳ',              'transactions'),
('transactions.delete',       'Delete transactions',            'ລຶບທຸລະກຳ',                   'transactions'),
-- Categories
('categories.view',           'View categories',                'ເບິ່ງໝວດໝູ່',                  'categories'),
('categories.create',         'Create category',                'ສ້າງໝວດໝູ່',                  'categories'),
('categories.edit',           'Edit category',                  'ແກ້ໄຂໝວດໝູ່',                'categories'),
('categories.delete',         'Delete category',                'ລຶບໝວດໝູ່',                  'categories'),
-- Users
('users.view',                'View users',                     'ເບິ່ງຜູ້ໃຊ້',                  'users'),
('users.create',              'Create user',                    'ສ້າງຜູ້ໃຊ້',                  'users'),
('users.edit',                'Edit user profile',              'ແກ້ໄຂໂປຣໄຟລ໌ຜູ້ໃຊ້',         'users'),
('users.delete',              'Delete user',                    'ລຶບຜູ້ໃຊ້',                   'users'),
('users.manage_roles',        'Assign / change user roles',     'ຈັດການ Role ຜູ້ໃຊ້',          'users'),
-- Budgets
('budgets.view',              'View budgets',                   'ເບິ່ງງົບປະມານ',               'budgets'),
('budgets.create',            'Create / update budget',         'ສ້າງ / ອັບເດດງົບ',            'budgets'),
('budgets.delete',            'Delete budget',                  'ລຶບງົບ',                      'budgets'),
-- Reports
('reports.view',              'View reports',                   'ເບິ່ງລາຍງານ',                 'reports'),
('reports.export',            'Export CSV / PDF',               'ສ່ງອອກ CSV / PDF',             'reports'),
-- Settings
('settings.view',             'View system settings',           'ເບິ່ງການຕັ້ງຄ່າ',              'settings'),
('settings.edit',             'Edit system settings',           'ແກ້ໄຂການຕັ້ງຄ່າ',            'settings'),
-- Audit Log
('audit_log.view',            'View audit log',                 'ເບິ່ງ Audit Log',              'audit_log'),
-- Donors
('donors.view',               'View donor list',                'ເບິ່ງລາຍຊື່ຜູ້ບໍລິຈາກ',      'donors'),
('donors.create',             'Create donor',                   'ສ້າງຜູ້ບໍລິຈາກ',             'donors'),
('donors.edit',               'Edit donor',                     'ແກ້ໄຂຜູ້ບໍລິຈາກ',            'donors'),
-- Accounts
('accounts.view',             'View accounts',                  'ເບິ່ງບັນຊີ',                  'accounts'),
('accounts.create',           'Create account',                 'ສ້າງບັນຊີ',                   'accounts'),
('accounts.edit',             'Edit account',                   'ແກ້ໄຂບັນຊີ',                 'accounts'),
-- Recurring Transactions
('recurring.view',            'View recurring rules',           'ເບິ່ງລາຍການຊ້ຳ',              'recurring'),
('recurring.create',          'Create recurring rule',          'ສ້າງລາຍການຊ້ຳ',              'recurring'),
('recurring.generate',        'Generate from recurring rule',   'ສ້າງທຸລະກຳຈາກລາຍການຊ້ຳ',   'recurring'),
('recurring.delete',          'Delete recurring rule',          'ລຶບລາຍການຊ້ຳ',              'recurring'),
-- Savings Goals
('goals.view',                'View savings goals',             'ເບິ່ງເປົ້າໝາຍ',               'goals'),
('goals.create',              'Create savings goal',            'ສ້າງເປົ້າໝາຍ',               'goals'),
('goals.add_amount',          'Contribute to goal',             'ເພີ່ມເງິນໃຫ້ເປົ້າໝາຍ',       'goals'),
('goals.delete',              'Delete savings goal',            'ລຶບເປົ້າໝາຍ',                'goals')
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`);


-- ─── 3. Role ↔ Permission Mapping ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       TINYINT UNSIGNED  NOT NULL,
    `permission_id` SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`
        FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Helper: insert by name (avoid hard-coding numeric IDs)
-- super_admin (1) → ALL permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- temple_admin (2) → everything except users.manage_roles
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions`
WHERE `name` NOT IN ('users.manage_roles')
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- accountant (3)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.view_all','transactions.create','transactions.edit_own',
    'categories.view',
    'budgets.view',
    'reports.view','reports.export',
    'donors.view','donors.create','donors.edit',
    'accounts.view',
    'recurring.view',
    'goals.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- treasurer (4)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.view_all','transactions.create',
    'transactions.edit_own','transactions.approve','transactions.reject',
    'categories.view',
    'budgets.view','budgets.create','budgets.delete',
    'reports.view','reports.export',
    'donors.view','donors.create','donors.edit',
    'accounts.view',
    'recurring.view','recurring.create','recurring.generate',
    'goals.view','goals.create','goals.add_amount'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- auditor (5)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.view_all',
    'categories.view',
    'budgets.view',
    'reports.view','reports.export',
    'audit_log.view',
    'donors.view',
    'accounts.view',
    'recurring.view',
    'goals.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- monk (6)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.create',
    'reports.view',
    'donors.view','donors.create',
    'goals.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- viewer (7)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.view_all',
    'categories.view',
    'budgets.view',
    'reports.view',
    'accounts.view',
    'goals.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;


-- ─── 4. Add role_id to users ─────────────────────────────────────────
ALTER TABLE `users`
    ADD COLUMN `role_id` TINYINT UNSIGNED NULL AFTER `role`,
    ADD CONSTRAINT `fk_user_role_id`
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL;

-- Migrate existing ENUM values → role IDs
--   admin  → temple_admin (2)
--   clerk  → accountant   (3)
UPDATE `users` SET `role_id` = 2 WHERE `role` = 'admin'  AND `role_id` IS NULL;
UPDATE `users` SET `role_id` = 3 WHERE `role` = 'clerk'  AND `role_id` IS NULL;
-- Any unmapped rows default to viewer
UPDATE `users` SET `role_id` = 7 WHERE `role_id` IS NULL;
