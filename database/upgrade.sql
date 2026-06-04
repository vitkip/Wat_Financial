-- ═══════════════════════════════════════════════════════════════════════════
-- Wat Financial — Upgrade Migration
-- ໃຊ້: ນຳໃຊ້ກັບ database ທີ່ມີຢູ່ແລ້ວ (ຮອງຮັບຂໍ້ມູນເກົ່າ)
-- Run : mysql -u root -p wat_financial < upgrade.sql
-- ═══════════════════════════════════════════════════════════════════════════

USE `wat_financial`;
SET FOREIGN_KEY_CHECKS = 0;

-- ═══════════════════════════════════════════════════════════════════════════
-- §1  RBAC TABLES  (roles / permissions / role_permissions)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `roles` (
    `id`          TINYINT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50)       NOT NULL UNIQUE,
    `label`       VARCHAR(100)      NOT NULL,
    `label_lao`   VARCHAR(100)      NOT NULL,
    `description` VARCHAR(255)      NULL,
    `is_system`   TINYINT(1)        NOT NULL DEFAULT 0,
    `sort_order`  TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_roles_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
    `id`         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(80)       NOT NULL UNIQUE,
    `label`      VARCHAR(120)      NOT NULL,
    `label_lao`  VARCHAR(120)      NOT NULL,
    `resource`   VARCHAR(50)       NOT NULL,
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_perm_resource` (`resource`),
    INDEX `idx_perm_name`     (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       TINYINT UNSIGNED  NOT NULL,
    `permission_id` SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role`
        FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §2  PATCH users TABLE
-- ═══════════════════════════════════════════════════════════════════════════

-- 2a. ປ່ຽນ role column ຈາກ ENUM('admin','clerk') → VARCHAR(50)
ALTER TABLE `users`
    MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'viewer';

-- 2b. ເພີ່ມ role_id column (ຖ້າຍັງບໍ່ມີ)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `role_id` TINYINT UNSIGNED NULL AFTER `role`;

-- 2c. ເພີ່ມ FK constraint (ຖ້າຍັງບໍ່ມີ — ignore error ຖ້າມີຢູ່ແລ້ວ)
ALTER TABLE `users`
    ADD CONSTRAINT `fk_user_role_id`
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL;


-- ═══════════════════════════════════════════════════════════════════════════
-- §3  PATCH categories TABLE
-- ═══════════════════════════════════════════════════════════════════════════

ALTER TABLE `categories`
    ADD COLUMN IF NOT EXISTS `type`
        ENUM('income','expense','both') NOT NULL DEFAULT 'both' AFTER `icon`;


-- ═══════════════════════════════════════════════════════════════════════════
-- §4  PATCH transactions TABLE
-- ═══════════════════════════════════════════════════════════════════════════

ALTER TABLE `transactions`
    ADD COLUMN IF NOT EXISTS `updated_at`
        TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Full-text index ສຳລັບ search (ຖ້າຍັງບໍ່ມີ)
ALTER TABLE `transactions`
    ADD FULLTEXT INDEX IF NOT EXISTS `ft_tx_description` (`description`, `notes`);

-- donors index
ALTER TABLE `donors`
    ADD INDEX IF NOT EXISTS `idx_donors_phone` (`phone`);

-- fiscal periods + audit_log (new tables)
CREATE TABLE IF NOT EXISTS `fiscal_periods` (
    `id`         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `period`     CHAR(7)          NOT NULL UNIQUE COMMENT 'YYYY-MM',
    `locked_at`  TIMESTAMP        NULL,
    `locked_by`  INT UNSIGNED     NULL,
    `created_at` TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_period_locked_by`
        FOREIGN KEY (`locked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_log` (
    `id`          BIGINT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED     NULL,
    `action`      VARCHAR(80)      NOT NULL,
    `resource`    VARCHAR(50)      NULL,
    `resource_id` INT UNSIGNED     NULL,
    `old_values`  JSON             NULL,
    `new_values`  JSON             NULL,
    `ip_address`  VARCHAR(45)      NULL,
    `user_agent`  VARCHAR(255)     NULL,
    `created_at`  TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_audit_user`     (`user_id`),
    INDEX `idx_audit_action`   (`action`),
    INDEX `idx_audit_resource` (`resource`, `resource_id`),
    INDEX `idx_audit_created`  (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §5  SEED ROLES
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO `roles` (`id`,`name`,`label`,`label_lao`,`description`,`is_system`,`sort_order`) VALUES
(1,'super_admin', 'Super Admin', 'ຜູ້ດູແລລະບົບສູງສຸດ','Full unrestricted system access',                       1,1),
(2,'temple_admin','Temple Admin','ຜູ້ຈັດການວັດ',      'Manages temple operations, users, and approvals',       1,2),
(3,'accountant',  'Accountant',  'ນັກບັນຊີ',          'Creates and manages transactions and donor records',    1,3),
(4,'treasurer',   'Treasurer',   'ຄັງເງິນ',           'Approves transactions and manages budgets',             1,4),
(5,'auditor',     'Auditor',     'ຜູ້ກວດສອບ',         'Read-only access to all financial data and audit logs', 1,5),
(6,'monk',        'Monk',        'ພຣະ / ສາມະເນນ',     'Can record donations and view income summaries',        1,6),
(7,'viewer',      'Viewer',      'ຜູ້ເບິ່ງ',          'Read-only access to approved transactions and reports', 1,7)
ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `label_lao`=VALUES(`label_lao`);


-- ═══════════════════════════════════════════════════════════════════════════
-- §6  SEED PERMISSIONS
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO `permissions` (`name`,`label`,`label_lao`,`resource`) VALUES
('transactions.view_own', 'View own transactions',         'ເບິ່ງທຸລະກຳຂອງຕົນເອງ',      'transactions'),
('transactions.view_all', 'View all transactions',         'ເບິ່ງທຸລະກຳທັງໝົດ',         'transactions'),
('transactions.create',   'Create transaction',            'ສ້າງທຸລະກຳ',                'transactions'),
('transactions.edit_own', 'Edit own pending transactions', 'ແກ້ໄຂທຸລະກຳລໍຖ້າຂອງຕົນ',  'transactions'),
('transactions.approve',  'Approve transactions',          'ອະນຸມັດທຸລະກຳ',             'transactions'),
('transactions.reject',   'Reject transactions',           'ປະຕິເສດທຸລະກຳ',            'transactions'),
('transactions.delete',   'Delete transactions',           'ລຶບທຸລະກຳ',                 'transactions'),
('categories.view',       'View categories',               'ເບິ່ງໝວດໝູ່',               'categories'),
('categories.create',     'Create category',               'ສ້າງໝວດໝູ່',               'categories'),
('categories.edit',       'Edit category',                 'ແກ້ໄຂໝວດໝູ່',              'categories'),
('categories.delete',     'Delete category',               'ລຶບໝວດໝູ່',                'categories'),
('users.view',            'View users',                    'ເບິ່ງຜູ້ໃຊ້',               'users'),
('users.create',          'Create user',                   'ສ້າງຜູ້ໃຊ້',               'users'),
('users.edit',            'Edit user profile',             'ແກ້ໄຂໂປຣໄຟລ໌ຜູ້ໃຊ້',      'users'),
('users.delete',          'Delete user',                   'ລຶບຜູ້ໃຊ້',                'users'),
('users.manage_roles',    'Assign / change user roles',    'ຈັດການ Role ຜູ້ໃຊ້',       'users'),
('budgets.view',          'View budgets',                  'ເບິ່ງງົບປະມານ',             'budgets'),
('budgets.create',        'Create / update budget',        'ສ້າງ / ອັບເດດງົບ',         'budgets'),
('budgets.delete',        'Delete budget',                 'ລຶບງົບ',                   'budgets'),
('reports.view',          'View reports',                  'ເບິ່ງລາຍງານ',              'reports'),
('reports.export',        'Export CSV / PDF',              'ສ່ງອອກ CSV / PDF',          'reports'),
('settings.view',         'View system settings',          'ເບິ່ງການຕັ້ງຄ່າ',           'settings'),
('settings.edit',         'Edit system settings',          'ແກ້ໄຂການຕັ້ງຄ່າ',         'settings'),
('audit_log.view',        'View audit log',                'ເບິ່ງ Audit Log',           'audit_log'),
('donors.view',           'View donor list',               'ເບິ່ງລາຍຊື່ຜູ້ບໍລິຈາກ',   'donors'),
('donors.create',         'Create donor',                  'ສ້າງຜູ້ບໍລິຈາກ',          'donors'),
('donors.edit',           'Edit donor',                    'ແກ້ໄຂຜູ້ບໍລິຈາກ',         'donors'),
('accounts.view',         'View accounts',                 'ເບິ່ງບັນຊີ',               'accounts'),
('accounts.create',       'Create account',                'ສ້າງບັນຊີ',                'accounts'),
('accounts.edit',         'Edit account',                  'ແກ້ໄຂບັນຊີ',              'accounts'),
('recurring.view',        'View recurring rules',          'ເບິ່ງລາຍການຊ້ຳ',           'recurring'),
('recurring.create',      'Create recurring rule',         'ສ້າງລາຍການຊ້ຳ',           'recurring'),
('recurring.generate',    'Generate from recurring rule',  'ສ້າງທຸລະກຳຈາກລາຍການຊ້ຳ', 'recurring'),
('recurring.delete',      'Delete recurring rule',         'ລຶບລາຍການຊ້ຳ',            'recurring'),
('goals.view',            'View savings goals',            'ເບິ່ງເປົ້າໝາຍ',             'goals'),
('goals.create',          'Create savings goal',           'ສ້າງເປົ້າໝາຍ',             'goals'),
('goals.add_amount',      'Contribute to goal',            'ເພີ່ມເງິນໃຫ້ເປົ້າໝາຍ',    'goals'),
('goals.delete',          'Delete savings goal',           'ລຶບເປົ້າໝາຍ',             'goals'),
('periods.view',          'View fiscal periods',           'ເບິ່ງໄຕມາດການເງິນ',        'periods'),
('periods.lock',          'Lock fiscal period',            'ລັອກໄຕມາດ',               'periods')
ON DUPLICATE KEY UPDATE `label`=VALUES(`label`);


-- ═══════════════════════════════════════════════════════════════════════════
-- §7  SEED ROLE ↔ PERMISSION ASSIGNMENTS
-- ═══════════════════════════════════════════════════════════════════════════

-- super_admin (1) → ALL
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 1, id FROM `permissions`
ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- temple_admin (2) → ທຸກຢ່າງ ຍົກເວັ້ນ users.manage_roles
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 2, id FROM `permissions` WHERE `name` NOT IN ('users.manage_roles')
ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- accountant (3)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 3, id FROM `permissions` WHERE `name` IN (
    'transactions.view_own','transactions.view_all','transactions.create','transactions.edit_own',
    'categories.view','budgets.view','reports.view','reports.export',
    'donors.view','donors.create','donors.edit','accounts.view',
    'recurring.view','goals.view','periods.view'
) ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- treasurer (4)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 4, id FROM `permissions` WHERE `name` IN (
    'transactions.view_own','transactions.view_all','transactions.create',
    'transactions.edit_own','transactions.approve','transactions.reject',
    'categories.view','budgets.view','budgets.create','budgets.delete',
    'reports.view','reports.export','donors.view','donors.create','donors.edit',
    'accounts.view','recurring.view','recurring.create','recurring.generate',
    'goals.view','goals.create','goals.add_amount','periods.view','periods.lock'
) ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- auditor (5)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 5, id FROM `permissions` WHERE `name` IN (
    'transactions.view_own','transactions.view_all','categories.view','budgets.view',
    'reports.view','reports.export','audit_log.view','donors.view',
    'accounts.view','recurring.view','goals.view','periods.view'
) ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- monk (6)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 6, id FROM `permissions` WHERE `name` IN (
    'transactions.view_own','transactions.create',
    'reports.view','donors.view','donors.create','goals.view'
) ON DUPLICATE KEY UPDATE `role_id`=`role_id`;

-- viewer (7)
INSERT INTO `role_permissions` (`role_id`,`permission_id`)
SELECT 7, id FROM `permissions` WHERE `name` IN (
    'transactions.view_own','transactions.view_all','categories.view',
    'budgets.view','reports.view','accounts.view','goals.view','periods.view'
) ON DUPLICATE KEY UPDATE `role_id`=`role_id`;


-- ═══════════════════════════════════════════════════════════════════════════
-- §8  MIGRATE EXISTING USERS → RBAC ROLES
-- ═══════════════════════════════════════════════════════════════════════════

-- ອັບເດດ slug ກ່ອນ (ENUM → VARCHAR ແລ້ວ slug ໃໝ່)
UPDATE `users` SET `role` = 'super_admin'  WHERE `role` = 'admin'  AND `role_id` IS NULL;
UPDATE `users` SET `role` = 'accountant'   WHERE `role` = 'clerk'  AND `role_id` IS NULL;

-- ຕັ້ງ role_id ຈາກ slug
UPDATE `users` u
JOIN   `roles`  r ON r.name = u.role
SET    u.role_id = r.id
WHERE  u.role_id IS NULL;

-- Fallback: ຖ້າ slug ບໍ່ match ໃຫ້ເປັນ viewer
UPDATE `users` SET `role` = 'viewer', `role_id` = 7
WHERE `role_id` IS NULL;


-- ═══════════════════════════════════════════════════════════════════════════
-- §9  SEED CATEGORIES  (ຖ້າຍັງວ່າງ)
-- ═══════════════════════════════════════════════════════════════════════════

INSERT INTO `categories` (`name`, `color`, `icon`, `type`)
SELECT * FROM (VALUES
    ROW('ເງິນທານ',             '#006C49', 'heart',        'income'),
    ROW('ເງິນຊ່ວຍເຫຼືອ',       '#4EDEA3', 'gift',         'income'),
    ROW('ລາຍຮັບອື່ນໆ',         '#0EA5E9', 'plus-circle',  'income'),
    ROW('ນະໂຍບາຍວັດ',         '#8B5CF6', 'landmark',     'expense'),
    ROW('ສ້ອມແປງ / ກໍ່ສ້າງ',  '#F59E0B', 'wrench',       'expense'),
    ROW('ອາຫານ / ເຄື່ອງໃຊ້',  '#FF8C00', 'utensils',     'expense'),
    ROW('ຄ່ານ້ຳ-ໄຟ',           '#6B7280', 'zap',          'expense'),
    ROW('ຄ່າເດີນທາງ',          '#5A7AF0', 'car',          'expense'),
    ROW('ສຸຂາພິບານ',           '#EC4899', 'heart',        'expense'),
    ROW('ການສຶກສາ / ໄອທີ',    '#0EA5E9', 'book',         'expense'),
    ROW('ງານບຸນ / ພິທີ',       '#EF4444', 'star',         'expense'),
    ROW('ລາຍຈ່າຍອື່ນໆ',        '#9CA3AF', 'tag',          'both')
) AS v(name, color, icon, type)
WHERE NOT EXISTS (SELECT 1 FROM `categories` LIMIT 1);


SET FOREIGN_KEY_CHECKS = 1;

-- ═══════════════════════════════════════════════════════════════════════════
-- ສຳເລັດ — ລະບົບ RBAC + ຄໍລັມທີ່ຂາດ ຖືກເພີ່ມທັງໝົດ
-- ═══════════════════════════════════════════════════════════════════════════
