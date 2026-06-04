-- ═══════════════════════════════════════════════════════════════════════════
-- Wat Financial Dashboard — Complete Database Schema
-- Version : 4.0 (Consolidated — replaces all previous migration files)
-- Engine  : MySQL 8.0+ / MariaDB 10.6+  |  Charset: utf8mb4_unicode_ci
--
-- ການນຳໃຊ້:
--   mysql -u root -p < schema.sql
--   ຫຼື ວາງໃນ phpMyAdmin Query tab ແລ້ວ Execute
--
-- ໝາຍເຫດ: Script ນີ້ idempotent (ສາມາດ run ຊ້ຳໄດ້ — IF NOT EXISTS / ON DUPLICATE KEY)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS `wat_financial`
    CHARACTER SET  utf8mb4
    COLLATE        utf8mb4_unicode_ci;

USE `wat_financial`;

SET FOREIGN_KEY_CHECKS = 0;   -- ປິດ FK checks ຊົ່ວຄາວ ເພື່ອ drop+create ລຳດັບໃດກໍ່ໄດ້
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


-- ═══════════════════════════════════════════════════════════════════════════
-- §1  ROLES & PERMISSIONS  (ຕ້ອງສ້າງກ່ອນ users)
-- ═══════════════════════════════════════════════════════════════════════════

-- ─── 1.1  Roles ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          TINYINT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(50)       NOT NULL UNIQUE  COMMENT 'machine slug: super_admin, …',
    `label`       VARCHAR(100)      NOT NULL,
    `label_lao`   VARCHAR(100)      NOT NULL,
    `description` VARCHAR(255)      NULL,
    `is_system`   TINYINT(1)        NOT NULL DEFAULT 0 COMMENT '1 = ລຶບຜ່ານ UI ບໍ່ໄດ້',
    `sort_order`  TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_roles_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 1.2  Permissions ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(80)       NOT NULL UNIQUE  COMMENT 'resource.action',
    `label`      VARCHAR(120)      NOT NULL,
    `label_lao`  VARCHAR(120)      NOT NULL,
    `resource`   VARCHAR(50)       NOT NULL         COMMENT 'ໃຊ້ຈັດກຸ່ມໃນ UI',
    `created_at` TIMESTAMP         DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_perm_resource` (`resource`),
    INDEX `idx_perm_name`     (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 1.3  Role ↔ Permission Mapping ──────────────────────────────────────
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
-- §2  USERS
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)    NOT NULL,
    `email`         VARCHAR(150)    NOT NULL UNIQUE,
    `password`      VARCHAR(255)    NOT NULL,
    `avatar`        VARCHAR(255)    NULL              COMMENT 'ທີ່ຢູ່ຮູບໂປຣໄຟລ໌',
    `role`          VARCHAR(50)     NOT NULL DEFAULT 'viewer' COMMENT 'legacy slug — ອ້າງອີງ role.name',
    `role_id`       TINYINT UNSIGNED NULL,
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `last_login_at` TIMESTAMP       NULL,
    `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_user_role_id`
        FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL,
    INDEX `idx_users_role_id` (`role_id`),
    INDEX `idx_users_active`  (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §3  SETTINGS  (key-value config store)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(80)  NOT NULL UNIQUE,
    `value`      TEXT         NOT NULL,
    `label`      VARCHAR(120) NOT NULL  COMMENT 'ຊື່ສະແດງໃນໜ້າຕັ້ງຄ່າ',
    `type`       ENUM('text','number','select','color','boolean') NOT NULL DEFAULT 'text',
    `updated_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §4  CATEGORIES
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(80)  NOT NULL,
    `color`      VARCHAR(7)   NOT NULL DEFAULT '#006C49',
    `icon`       VARCHAR(40)  NOT NULL DEFAULT 'tag',
    `type`       ENUM('income','expense','both') NOT NULL DEFAULT 'both'
                     COMMENT 'ກຳນົດວ່າໝວດນີ້ໃຊ້ກັບປະເພດໃດ',
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §5  DONORS  (ຜູ້ບໍລິຈາກ)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `donors` (
    `id`         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(150)  NOT NULL,
    `phone`      VARCHAR(20)   NULL,
    `address`    TEXT          NULL,
    `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_donors_phone` (`phone`)          COMMENT 'ສຳລັບຊອກຫາຜູ້ທານຊ້ຳ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §6  ACCOUNTS  (Double-Entry Ledger Accounts)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `accounts` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `type`        ENUM('asset','liability','equity','revenue','expense') NOT NULL DEFAULT 'asset',
    `balance`     DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Cached balance — updated by recalculateBalance()',
    `description` VARCHAR(255)  NULL,
    `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_accounts_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §7  TRANSACTIONS  (Main financial ledger header)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `transactions` (
    `id`           INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    `status`       ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'approved',
    `reference_no` VARCHAR(50)     NULL               COMMENT 'ເລກອ້າງອີງ (optional)',
    `type`         ENUM('income','expense')            NOT NULL,
    `amount`       DECIMAL(15,2) UNSIGNED              NOT NULL,
    `description`  VARCHAR(255)                        NOT NULL,
    `category_id`  INT UNSIGNED    NULL,
    `date`         DATE                                NOT NULL,
    `notes`        TEXT            NULL,
    `created_by`   INT UNSIGNED    NULL,
    `approved_by`  INT UNSIGNED    NULL               COMMENT 'ຜູ້ອະນຸມັດ ຫຼື ປະຕິເສດ',
    `donor_id`     INT UNSIGNED    NULL,
    `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP       NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_tx_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tx_created_by`
        FOREIGN KEY (`created_by`)  REFERENCES `users`(`id`)      ON DELETE SET NULL,
    CONSTRAINT `fk_tx_approved_by`
        FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`)      ON DELETE SET NULL,
    CONSTRAINT `fk_tx_donor_id`
        FOREIGN KEY (`donor_id`)    REFERENCES `donors`(`id`)     ON DELETE SET NULL,

    -- ── Performance Indexes ──────────────────────────────────────────────
    -- Half-open date range queries use this composite index (date,type,status)
    INDEX `idx_tx_date_type_status` (`date`, `type`, `status`),
    -- Budget utilisation & category breakdown
    INDEX `idx_tx_category_date`    (`category_id`, `date`),
    -- Pending queue, approval filter
    INDEX `idx_tx_status`           (`status`),
    -- Donor report joins
    INDEX `idx_tx_donor_date`       (`donor_id`, `date`),
    -- User-scoped views (transactions.view_own)
    INDEX `idx_tx_created_by`       (`created_by`),
    -- Full-text search on description / notes (replaces slow LIKE scans)
    FULLTEXT INDEX `ft_tx_description` (`description`, `notes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §8  LEDGER ENTRIES  (Double-Entry journal lines)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `ledger_entries` (
    `id`             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT UNSIGNED  NOT NULL,
    `account_id`     INT UNSIGNED  NOT NULL,
    `entry_type`     ENUM('debit','credit') NOT NULL,
    `amount`         DECIMAL(15,2) UNSIGNED NOT NULL,
    `created_at`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_ledger_tx`
        FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ledger_account`
        FOREIGN KEY (`account_id`)     REFERENCES `accounts`(`id`)     ON DELETE RESTRICT,

    INDEX `idx_ledger_account` (`account_id`, `entry_type`),
    INDEX `idx_ledger_tx`      (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §9  BUDGETS  (ງົບປະມານລາຍເດືອນ)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `budgets` (
    `id`          INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED   NOT NULL,
    `amount`      DECIMAL(15,2) UNSIGNED NOT NULL,
    `month`       CHAR(7)        NOT NULL COMMENT 'ຮູບແບບ: YYYY-MM',
    `created_at`  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `uq_budget_cat_month` (`category_id`, `month`),
    INDEX `idx_budget_month` (`month`),

    CONSTRAINT `fk_budget_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §10  RECURRING TRANSACTIONS  (ລາຍການຊ້ຳ)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `recurring_transactions` (
    `id`           INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `type`         ENUM('income','expense') NOT NULL,
    `amount`       DECIMAL(15,2) UNSIGNED   NOT NULL,
    `description`  VARCHAR(255)             NOT NULL,
    `category_id`  INT UNSIGNED             NULL,
    `frequency`    ENUM('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'monthly',
    `day_of_month` TINYINT UNSIGNED         NULL    COMMENT 'ວັນທີ 1-31 ສຳລັບ monthly',
    `start_date`   DATE                     NOT NULL,
    `end_date`     DATE                     NULL    COMMENT 'NULL = ບໍ່ມີວັນໝົດ',
    `last_run`     DATE                     NULL,
    `next_run`     DATE                     NULL,
    `is_active`    TINYINT(1)               NOT NULL DEFAULT 1,
    `notes`        TEXT                     NULL,
    `created_at`   TIMESTAMP                DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP                DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_recur_next_run`  (`next_run`, `is_active`),

    CONSTRAINT `fk_recur_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §11  SAVINGS GOALS  (ເປົ້າໝາຍການເງິນ)
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `savings_goals` (
    `id`             INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(100)   NOT NULL,
    `description`    TEXT           NULL,
    `target_amount`  DECIMAL(15,2) UNSIGNED NOT NULL,
    `current_amount` DECIMAL(15,2) UNSIGNED NOT NULL DEFAULT 0.00,
    `target_date`    DATE           NULL    COMMENT 'NULL = ບໍ່ກຳນົດ',
    `color`          VARCHAR(7)     NOT NULL DEFAULT '#006C49',
    `icon`           VARCHAR(40)    NOT NULL DEFAULT 'target',
    `is_completed`   TINYINT(1)     NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §12  FISCAL PERIODS  (ການລັອກໄຕມາດ / ເດືອນ)
-- ═══════════════════════════════════════════════════════════════════════════
-- ຊ່ວຍປ້ອງກັນການແກ້ໄຂ ຫຼື ເພີ່ມທຸລະກຳໃນໄຕມາດທີ່ປິດແລ້ວ

CREATE TABLE IF NOT EXISTS `fiscal_periods` (
    `id`         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `period`     CHAR(7)          NOT NULL UNIQUE COMMENT 'YYYY-MM',
    `locked_at`  TIMESTAMP        NULL,
    `locked_by`  INT UNSIGNED     NULL,
    `created_at` TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_period_locked_by`
        FOREIGN KEY (`locked_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════════════════
-- §13  AUDIT LOG  (ບັນທຶກການເຄື່ອນໄຫວທຸກຢ່າງ)
-- ═══════════════════════════════════════════════════════════════════════════
-- ທຸກ action ທີ່ປ່ຽນ state (create / approve / reject / delete / role-change)
-- ຄວນຂຽນ 1 row ທີ່ນີ້. ບໍ່ຄວນລຶບ rows ໃນຕາຕະລາງນີ້ (append-only).

CREATE TABLE IF NOT EXISTS `audit_log` (
    `id`          BIGINT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED     NULL                       COMMENT 'ຜູ້ດຳເນີນການ (NULL = system)',
    `action`      VARCHAR(80)      NOT NULL                   COMMENT 'transaction.create, user.delete, …',
    `resource`    VARCHAR(50)      NULL                       COMMENT 'ຊື່ຕາຕະລາງ / module',
    `resource_id` INT UNSIGNED     NULL                       COMMENT 'PK ຂອງ record ທີ່ຖືກກະທຳ',
    `old_values`  JSON             NULL                       COMMENT 'snapshot ກ່ອນປ່ຽນ',
    `new_values`  JSON             NULL                       COMMENT 'snapshot ຫຼັງປ່ຽນ',
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


SET FOREIGN_KEY_CHECKS = 1;   -- ເປີດ FK checks ຄືນ


-- ═══════════════════════════════════════════════════════════════════════════
-- §14  SEED DATA
-- ═══════════════════════════════════════════════════════════════════════════

-- ─── 14.1  Roles ─────────────────────────────────────────────────────────
INSERT INTO `roles` (`id`,`name`,`label`,`label_lao`,`description`,`is_system`,`sort_order`) VALUES
(1, 'super_admin',  'Super Admin',  'ຜູ້ດູແລລະບົບສູງສຸດ', 'Full unrestricted system access',                       1, 1),
(2, 'temple_admin', 'Temple Admin', 'ຜູ້ຈັດການວັດ',       'Manages temple operations, users, and approvals',       1, 2),
(3, 'accountant',   'Accountant',   'ນັກບັນຊີ',           'Creates and manages transactions and donor records',    1, 3),
(4, 'treasurer',    'Treasurer',    'ຄັງເງິນ',            'Approves transactions and manages budgets',             1, 4),
(5, 'auditor',      'Auditor',      'ຜູ້ກວດສອບ',          'Read-only access to all financial data and audit logs', 1, 5),
(6, 'monk',         'Monk',         'ພຣະ / ສາມະເນນ',      'Can record donations and view income summaries',        1, 6),
(7, 'viewer',       'Viewer',       'ຜູ້ເບິ່ງ',           'Read-only access to approved transactions and reports', 1, 7)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `label_lao` = VALUES(`label_lao`);


-- ─── 14.2  Permissions ───────────────────────────────────────────────────
INSERT INTO `permissions` (`name`,`label`,`label_lao`,`resource`) VALUES
-- Transactions
('transactions.view_own',  'View own transactions',         'ເບິ່ງທຸລະກຳຂອງຕົນເອງ',      'transactions'),
('transactions.view_all',  'View all transactions',         'ເບິ່ງທຸລະກຳທັງໝົດ',         'transactions'),
('transactions.create',    'Create transaction',            'ສ້າງທຸລະກຳ',                'transactions'),
('transactions.edit_own',  'Edit own pending transactions', 'ແກ້ໄຂທຸລະກຳລໍຖ້າຂອງຕົນ',  'transactions'),
('transactions.approve',   'Approve transactions',          'ອະນຸມັດທຸລະກຳ',             'transactions'),
('transactions.reject',    'Reject transactions',           'ປະຕິເສດທຸລະກຳ',            'transactions'),
('transactions.delete',    'Delete transactions',           'ລຶບທຸລະກຳ',                 'transactions'),
-- Categories
('categories.view',        'View categories',               'ເບິ່ງໝວດໝູ່',               'categories'),
('categories.create',      'Create category',               'ສ້າງໝວດໝູ່',               'categories'),
('categories.edit',        'Edit category',                 'ແກ້ໄຂໝວດໝູ່',              'categories'),
('categories.delete',      'Delete category',               'ລຶບໝວດໝູ່',                'categories'),
-- Users
('users.view',             'View users',                    'ເບິ່ງຜູ້ໃຊ້',               'users'),
('users.create',           'Create user',                   'ສ້າງຜູ້ໃຊ້',               'users'),
('users.edit',             'Edit user profile',             'ແກ້ໄຂໂປຣໄຟລ໌ຜູ້ໃຊ້',      'users'),
('users.delete',           'Delete user',                   'ລຶບຜູ້ໃຊ້',                'users'),
('users.manage_roles',     'Assign / change user roles',    'ຈັດການ Role ຜູ້ໃຊ້',       'users'),
-- Budgets
('budgets.view',           'View budgets',                  'ເບິ່ງງົບປະມານ',             'budgets'),
('budgets.create',         'Create / update budget',        'ສ້າງ / ອັບເດດງົບ',         'budgets'),
('budgets.delete',         'Delete budget',                 'ລຶບງົບ',                   'budgets'),
-- Reports
('reports.view',           'View reports',                  'ເບິ່ງລາຍງານ',              'reports'),
('reports.export',         'Export CSV / PDF',              'ສ່ງອອກ CSV / PDF',          'reports'),
-- Settings
('settings.view',          'View system settings',          'ເບິ່ງການຕັ້ງຄ່າ',           'settings'),
('settings.edit',          'Edit system settings',          'ແກ້ໄຂການຕັ້ງຄ່າ',         'settings'),
-- Audit Log
('audit_log.view',         'View audit log',                'ເບິ່ງ Audit Log',           'audit_log'),
-- Donors
('donors.view',            'View donor list',               'ເບິ່ງລາຍຊື່ຜູ້ບໍລິຈາກ',   'donors'),
('donors.create',          'Create donor',                  'ສ້າງຜູ້ບໍລິຈາກ',          'donors'),
('donors.edit',            'Edit donor',                    'ແກ້ໄຂຜູ້ບໍລິຈາກ',         'donors'),
-- Accounts
('accounts.view',          'View accounts',                 'ເບິ່ງບັນຊີ',               'accounts'),
('accounts.create',        'Create account',                'ສ້າງບັນຊີ',                'accounts'),
('accounts.edit',          'Edit account',                  'ແກ້ໄຂບັນຊີ',              'accounts'),
-- Recurring Transactions
('recurring.view',         'View recurring rules',          'ເບິ່ງລາຍການຊ້ຳ',           'recurring'),
('recurring.create',       'Create recurring rule',         'ສ້າງລາຍການຊ້ຳ',           'recurring'),
('recurring.generate',     'Generate from recurring rule',  'ສ້າງທຸລະກຳຈາກລາຍການຊ້ຳ', 'recurring'),
('recurring.delete',       'Delete recurring rule',         'ລຶບລາຍການຊ້ຳ',            'recurring'),
-- Savings Goals
('goals.view',             'View savings goals',            'ເບິ່ງເປົ້າໝາຍ',             'goals'),
('goals.create',           'Create savings goal',           'ສ້າງເປົ້າໝາຍ',             'goals'),
('goals.add_amount',       'Contribute to goal',            'ເພີ່ມເງິນໃຫ້ເປົ້າໝາຍ',    'goals'),
('goals.delete',           'Delete savings goal',           'ລຶບເປົ້າໝາຍ',             'goals'),
-- Fiscal Periods
('periods.view',           'View fiscal periods',           'ເບິ່ງໄຕມາດການເງິນ',        'periods'),
('periods.lock',           'Lock fiscal period',            'ລັອກໄຕມາດ',               'periods')
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `label_lao` = VALUES(`label_lao`);


-- ─── 14.3  Role ↔ Permission Assignments ────────────────────────────────
-- super_admin (1) → ALL permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- temple_admin (2) → ທຸກຢ່າງ ຍົກເວັ້ນ users.manage_roles
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
    'goals.view',
    'periods.view'
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
    'goals.view','goals.create','goals.add_amount',
    'periods.view','periods.lock'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- auditor (5) — read-only ທຸກຢ່າງ ລວມ audit log
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
    'goals.view',
    'periods.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- monk (6) — ບັນທຶກທານ + ເບິ່ງລາຍງານ
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.create',
    'reports.view',
    'donors.view','donors.create',
    'goals.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;

-- viewer (7) — ອ່ານອຍ່າງດຽວ
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions`
WHERE `name` IN (
    'transactions.view_own','transactions.view_all',
    'categories.view',
    'budgets.view',
    'reports.view',
    'accounts.view',
    'goals.view',
    'periods.view'
)
ON DUPLICATE KEY UPDATE `role_id` = `role_id`;


-- ─── 14.4  Default Admin User ────────────────────────────────────────────
-- ⚠  ລະຫັດຜ່ານ = "admin123" — ປ່ຽນທັນທີຫຼັງ login ຄັ້ງທຳອິດ!
INSERT INTO `users` (`name`, `email`, `password`, `role`, `role_id`) VALUES
('ຜູ້ດູແລລະບົບ', 'admin@watfinancial.local',
 '$2y$12$TEY1uZUSGRFQ6M5r6caEA.t8b5hc0eIbAowoEp8y3ALHW687pCreK',
 'super_admin', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `role_id` = VALUES(`role_id`);


-- ─── 14.5  Settings ──────────────────────────────────────────────────────
INSERT INTO `settings` (`key`, `value`, `label`, `type`) VALUES
('app_name',        'Wat Financial',   'ຊື່ແອັບ',             'text'),
('currency_symbol', '₭',              'ສັນຍາລັກເງິນຕາ',      'text'),
('currency_code',   'LAK',            'ລະຫັດເງິນຕາ',         'text'),
('locale',          'lo_LA',          'ພາສາ / Locale',        'select'),
('timezone',        'Asia/Vientiane', 'ໂຊນເວລາ',             'select'),
('date_format',     'd/m/Y',          'ຮູບແບບວັນທີ',          'text'),
('week_start',      '1',              'ວັນເລີ່ມຕົ້ນອາທິດ',    'number'),
('decimal_places',  '0',             'ຈຳນວນທົດສະນິຍົມ',     'number'),
('theme_color',     '#091426',       'ສີຫຼັກ',              'color'),
('items_per_page',  '15',            'ລາຍການຕໍ່ໜ້າ',        'number')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);


-- ─── 14.6  Categories ────────────────────────────────────────────────────
INSERT INTO `categories` (`name`, `color`, `icon`, `type`) VALUES
('ເງິນທານ',            '#006C49', 'heart',        'income'),
('ເງິນຊ່ວຍເຫຼືອ',      '#4EDEA3', 'gift',         'income'),
('ລາຍຮັບອື່ນໆ',        '#0EA5E9', 'plus-circle',  'income'),
('ນະໂຍບາຍວັດ',        '#8B5CF6', 'landmark',     'expense'),
('ສ້ອມແປງ / ກໍ່ສ້າງ', '#F59E0B', 'wrench',       'expense'),
('ອາຫານ / ເຄື່ອງໃຊ້', '#FF8C00', 'utensils',     'expense'),
('ຄ່ານ້ຳ-ໄຟ',          '#6B7280', 'zap',          'expense'),
('ຄ່າເດີນທາງ',         '#5A7AF0', 'car',          'expense'),
('ສຸຂາພິບານ',          '#EC4899', 'heart',        'expense'),
('ການສຶກສາ / ໄອທີ',   '#0EA5E9', 'book',         'expense'),
('ງານບຸນ / ພິທີ',      '#EF4444', 'star',         'expense'),
('ລາຍຈ່າຍອື່ນໆ',       '#9CA3AF', 'tag',          'both')
ON DUPLICATE KEY UPDATE `color` = VALUES(`color`), `icon` = VALUES(`icon`);


-- ─── 14.7  Default Accounts ──────────────────────────────────────────────
INSERT INTO `accounts` (`id`, `name`, `type`, `description`) VALUES
(1, 'ບັນຊີທະນາຄານຫຼັກ',   'asset',   'BCEL / BFL — ບັນຊີຫຼັກຂອງວັດ'),
(2, 'ກ່ອງທານ (ເງິນສົດ)',  'asset',   'ເງິນສົດຈາກກ່ອງທານ'),
(3, 'ກອງທຶນລາຍຈ່າຍ',      'expense', 'ກອງທຶນລາຍຈ່າຍທົ່ວໄປ')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);


-- ─── 14.8  Sample Transactions (ທຸລະກຳຕົວຢ່າງ) ──────────────────────────
-- ໝາຍເຫດ: status = 'approved' ເພື່ອໃຫ້ Dashboard ສະແດງຂໍ້ມູນທັນທີ
INSERT INTO `transactions` (`type`, `amount`, `description`, `category_id`, `date`, `status`) VALUES
('income',  5000000,  'ເງິນທານປະຈຳເດືອນ',       1,  DATE_FORMAT(NOW(), '%Y-%m-01'), 'approved'),
('income',  2500000,  'ທານວຽງຈັນມາໄຫວ້ພະ',      1,  DATE_FORMAT(NOW(), '%Y-%m-05'), 'approved'),
('income',  1000000,  'ເງິນຊ່ວຍເຫຼືອງານບຸນ',    2,  DATE_FORMAT(NOW(), '%Y-%m-08'), 'approved'),
('expense',  450000,  'ຄ່ານ້ຳ-ໄຟ',               7,  DATE_FORMAT(NOW(), '%Y-%m-10'), 'approved'),
('expense',  800000,  'ອາຫານພຣະ / ສາມະເນນ',      6,  DATE_FORMAT(NOW(), '%Y-%m-01'), 'approved'),
('expense',  250000,  'ຄ່ານ້ຳມັນ',               8,  DATE_FORMAT(NOW(), '%Y-%m-12'), 'approved'),
('expense',  350000,  'ອຸປະກອນໄອທີ',            10,  DATE_FORMAT(NOW(), '%Y-%m-09'), 'approved'),
('expense',  120000,  'ເຄື່ອງໃຊ້ຫ້ອງນ້ຳ',        6,  DATE_FORMAT(NOW(), '%Y-%m-14'), 'approved'),
('income',   500000,  'ທານຄ່າທຳບຸນ',             1,  DATE_FORMAT(NOW(), '%Y-%m-15'), 'approved'),
('expense',  300000,  'ສ້ອມແປງຫ້ອງນ້ຳ',          5,  DATE_FORMAT(NOW(), '%Y-%m-11'), 'approved');


-- ─── 14.9  Sample Budgets ────────────────────────────────────────────────
INSERT INTO `budgets` (`category_id`, `amount`, `month`) VALUES
(6,  2000000, DATE_FORMAT(NOW(), '%Y-%m')),
(7,   600000, DATE_FORMAT(NOW(), '%Y-%m')),
(5,  1000000, DATE_FORMAT(NOW(), '%Y-%m')),
(9,   500000, DATE_FORMAT(NOW(), '%Y-%m')),
(10,  500000, DATE_FORMAT(NOW(), '%Y-%m')),
(8,   400000, DATE_FORMAT(NOW(), '%Y-%m'))
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);


-- ─── 14.10 Sample Recurring Transactions ────────────────────────────────
INSERT INTO `recurring_transactions`
    (`type`, `amount`, `description`, `category_id`, `frequency`, `day_of_month`, `start_date`, `next_run`)
VALUES
('expense',  800000, 'ຄ່ານ້ຳ-ໄຟ ປະຈຳເດືອນ', 7, 'monthly', 10,
    DATE_FORMAT(NOW(), '%Y-%m-01'),
    DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m-10')),
('expense',  250000, 'ຄ່ານ້ຳມັນລົດ',          8, 'monthly', 1,
    DATE_FORMAT(NOW(), '%Y-%m-01'),
    DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m-01'))
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);


-- ─── 14.11 Sample Savings Goals ──────────────────────────────────────────
INSERT INTO `savings_goals`
    (`name`, `description`, `target_amount`, `current_amount`, `target_date`, `color`, `icon`)
VALUES
('ສ້ອມແປງໂบสຖ', 'ສ້ອມແປງຜ້ານົ່ງໂບດ', 50000000, 12000000, DATE_ADD(NOW(), INTERVAL 12 MONTH), '#006C49', 'landmark'),
('ລະບົບ Solar',  'ຕິດຕັ້ງໄຟຟ້າສ່ຽງ', 30000000,  5000000, DATE_ADD(NOW(), INTERVAL  8 MONTH), '#F59E0B', 'zap'),
('ກອງທຶນສຸກເສີນ','3 ເດືອນຂອງລາຍຈ່າຍ',10000000,  4500000, DATE_ADD(NOW(), INTERVAL  4 MONTH), '#4EDEA3', 'shield')
ON DUPLICATE KEY UPDATE `target_amount` = VALUES(`target_amount`);


-- ═══════════════════════════════════════════════════════════════════════════
-- §15  LEDGER BOOTSTRAP
-- ═══════════════════════════════════════════════════════════════════════════
-- ສ້າງ ledger entries ສຳລັບ sample transactions ທີ່ seed ຢູ່ຂ້າງເທິງ
-- ໝາຍເຫດ: ນີ້ເປັນ single-side entries (asset side only) ເພື່ອ bootstrap.
-- Transaction::create() ໃໝ່ຈະສ້າງ double-sided entries ອັດຕະໂນມັດ.
INSERT INTO `ledger_entries` (`transaction_id`, `account_id`, `entry_type`, `amount`)
SELECT t.id, 1, 'debit', t.amount
FROM `transactions` t
WHERE t.type = 'income'
  AND NOT EXISTS (
    SELECT 1 FROM `ledger_entries` l WHERE l.transaction_id = t.id
  );

INSERT INTO `ledger_entries` (`transaction_id`, `account_id`, `entry_type`, `amount`)
SELECT t.id, 3, 'debit', t.amount
FROM `transactions` t
WHERE t.type = 'expense'
  AND NOT EXISTS (
    SELECT 1 FROM `ledger_entries` l WHERE l.transaction_id = t.id
  );


-- ═══════════════════════════════════════════════════════════════════════════
-- §16  ACCOUNT BALANCE INITIALISATION
-- ═══════════════════════════════════════════════════════════════════════════
-- ຄຳນວນ balance ເລີ່ມຕົ້ນຈາກ ledger entries ທີ່ seed ມາ
UPDATE `accounts` a
SET a.`balance` = (
    SELECT COALESCE(
        SUM(CASE
            WHEN a.type IN ('asset','expense')
                THEN (CASE WHEN l.entry_type='debit' THEN l.amount ELSE -l.amount END)
            ELSE (CASE WHEN l.entry_type='credit' THEN l.amount ELSE -l.amount END)
        END), 0)
    FROM `ledger_entries` l
    JOIN `transactions` t ON l.transaction_id = t.id
    WHERE l.account_id = a.id
      AND t.status = 'approved'
);


-- ═══════════════════════════════════════════════════════════════════════════
-- End of schema.sql
-- ═══════════════════════════════════════════════════════════════════════════
