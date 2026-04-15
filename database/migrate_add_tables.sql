-- ═══════════════════════════════════════════════════════
-- Wat Financial Dashboard — Migration: Add Missing Tables
-- Run after schema.sql
-- ═══════════════════════════════════════════════════════

USE `wat_financial`;

-- ─── 1. Users ────────────────────────────────────────────────
--  ໃຊ້ສຳລັບ login / ຢືນຢັນຕົວຕົນ
--  password ເກັບເປັນ bcrypt hash (password_hash)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(100)   NOT NULL,
    `email`           VARCHAR(150)   NOT NULL UNIQUE,
    `password`        VARCHAR(255)   NOT NULL,
    `avatar`          VARCHAR(255)   NULL        COMMENT 'ທີ່ຢູ່ຮູບໂປຣໄຟລ໌',
    `is_active`       TINYINT(1)     NOT NULL DEFAULT 1,
    `last_login_at`   TIMESTAMP      NULL,
    `created_at`      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: default admin user  (password = "admin123" — ປ່ຽນທັນທີຫຼັງ login!)
INSERT INTO `users` (`name`, `email`, `password`) VALUES
    ('ຜູ້ດູແລລະບົບ', 'admin@watfinancial.local', '$2y$12$TEY1uZUSGRFQ6M5r6caEA.t8b5hc0eIbAowoEp8y3ALHW687pCreK')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);


-- ─── 2. Settings ─────────────────────────────────────────────
--  key-value store ສຳລັບການຕັ້ງຄ່າທີ່ຜູ້ໃຊ້ປ່ຽນໄດ້
--  ແທນທີ່ຄ່າ hardcode ໃນ config.php
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`         VARCHAR(80)  NOT NULL UNIQUE,
    `value`       TEXT         NOT NULL,
    `label`       VARCHAR(120) NOT NULL COMMENT 'ຊື່ສະແດງໃນໜ້າຕັ້ງຄ່າ',
    `type`        ENUM('text','number','select','color','boolean') NOT NULL DEFAULT 'text',
    `updated_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: ຄ່າເລີ່ມຕົ້ນ
INSERT INTO `settings` (`key`, `value`, `label`, `type`) VALUES
    ('app_name',       'Wat Financial',         'ຊື່ແອັບ',              'text'),
    ('currency_symbol','₭',                     'ສັນຍາລັກເງິນຕາ',       'text'),
    ('currency_code',  'LAK',                   'ລະຫັດເງິນຕາ',          'text'),
    ('locale',         'lo_LA',                 'ພາສາ / Locale',         'select'),
    ('timezone',       'Asia/Vientiane',        'ໂຊນເວລາ',              'select'),
    ('date_format',    'd/m/Y',                 'ຮູບແບບວັນທີ',           'text'),
    ('week_start',     'monday',                'ວັນເລີ່ມຕົ້ນອາທິດ',     'select'),
    ('decimal_places', '0',                     'ຈຳນວນທົດສະນິຍົມ',      'number'),
    ('theme_color',    '#091426',               'ສີຫຼັກ',               'color'),
    ('items_per_page', '15',                    'ລາຍການຕໍ່ໜ້າ',         'number')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);


-- ─── 3. Recurring Transactions ───────────────────────────────
--  ລາຍການທີ່ເກີດຊ້ຳ: ເງິນເດືອນ, ຄ່າເຊົ່າ, ຄ່ານ້ຳ-ໄຟ ໄລ່ເດືອນ
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `recurring_transactions` (
    `id`          INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `type`        ENUM('income','expense') NOT NULL,
    `amount`      DECIMAL(15,2) UNSIGNED   NOT NULL,
    `description` VARCHAR(255)             NOT NULL,
    `category_id` INT UNSIGNED             NULL,
    `frequency`   ENUM('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'monthly',
    `day_of_month` TINYINT UNSIGNED        NULL    COMMENT 'ວັນທີ ສຳລັບ monthly (1-31)',
    `start_date`  DATE                     NOT NULL,
    `end_date`    DATE                     NULL    COMMENT 'NULL = ບໍ່ມີວັນໝົດ',
    `last_run`    DATE                     NULL,
    `next_run`    DATE                     NULL,
    `is_active`   TINYINT(1)               NOT NULL DEFAULT 1,
    `notes`       TEXT                     NULL,
    `created_at`  TIMESTAMP                DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP                DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_recur_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: ຕົວຢ່າງລາຍການຊ້ຳ
INSERT INTO `recurring_transactions`
    (`type`, `amount`, `description`, `category_id`, `frequency`, `day_of_month`, `start_date`, `next_run`) VALUES
    ('income',  15000000, 'ເງິນເດືອນ',      1,    'monthly', 1,  DATE_FORMAT(NOW(), '%Y-%m-01'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')),
    ('expense',  800000, 'ຄ່າເຊົ່າເຮືອນ',    5,    'monthly', 1,  DATE_FORMAT(NOW(), '%Y-%m-01'), DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')),
    ('expense',  450000, 'ຄ່ານ້ຳ-ໄຟ',       10,   'monthly', 10, DATE_FORMAT(NOW(), '%Y-%m-01'), DATE_FORMAT(NOW(), '%Y-%m-10'))
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);


-- ─── 4. Savings Goals ────────────────────────────────────────
--  ເປົ້າໝາຍການເງິນ: ຊື້ລົດ, ທ່ອງທ່ຽວ, ສ້າງເຮືອນ
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `savings_goals` (
    `id`             INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    `name`           VARCHAR(100)   NOT NULL,
    `description`    TEXT           NULL,
    `target_amount`  DECIMAL(15,2) UNSIGNED NOT NULL,
    `current_amount` DECIMAL(15,2) UNSIGNED NOT NULL DEFAULT 0.00,
    `target_date`    DATE           NULL    COMMENT 'ວັນທີເປົ້າໝາຍ (NULL = ບໍ່ກຳນົດ)',
    `color`          VARCHAR(7)     NOT NULL DEFAULT '#006C49',
    `icon`           VARCHAR(40)    NOT NULL DEFAULT 'target',
    `is_completed`   TINYINT(1)     NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed: ຕົວຢ່າງເປົ້າໝາຍ
INSERT INTO `savings_goals` (`name`, `description`, `target_amount`, `current_amount`, `target_date`, `color`, `icon`) VALUES
    ('ຊື້ລົດ',          'ລົດຈັກ Honda PCX',   15000000, 3500000, DATE_ADD(NOW(), INTERVAL 8  MONTH), '#5A7AF0', 'car'),
    ('ທ່ອງທ່ຽວ',        'ທ່ອງທ່ຽວຕ່າງປະເທດ', 8000000,  1200000, DATE_ADD(NOW(), INTERVAL 6  MONTH), '#4EDEA3', 'airplane'),
    ('ກອງທຶນສຸກເສີນ', '3 ເດືອນຂອງລາຍຈ່າຍ', 10000000, 5000000, DATE_ADD(NOW(), INTERVAL 4  MONTH), '#006C49', 'shield')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);