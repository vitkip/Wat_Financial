-- ═══════════════════════════════════════════════════════
-- Wat Financial Dashboard — Database Schema
-- Run this in phpMyAdmin or via: mysql -u root < schema.sql
-- ═══════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS `wat_financial`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `wat_financial`;

-- ─── Categories ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(80)  NOT NULL,
    `color`      VARCHAR(7)   NOT NULL DEFAULT '#006C49',
    `icon`       VARCHAR(40)  NOT NULL DEFAULT 'tag',
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Transactions ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `transactions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `type`        ENUM('income','expense') NOT NULL,
    `amount`      DECIMAL(15,2) UNSIGNED   NOT NULL,
    `description` VARCHAR(255)             NOT NULL,
    `category_id` INT UNSIGNED             NULL,
    `date`        DATE                     NOT NULL,
    `notes`       TEXT                     NULL,
    `created_at`  TIMESTAMP                DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tx_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Budgets ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `budgets` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED  NOT NULL,
    `amount`      DECIMAL(15,2) UNSIGNED NOT NULL,
    `month`       CHAR(7)       NOT NULL COMMENT 'Format: YYYY-MM',
    `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_budget_cat_month` (`category_id`, `month`),
    CONSTRAINT `fk_budget_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Seed Data ───────────────────────────────────────────────
INSERT INTO `categories` (`name`, `color`, `icon`) VALUES
    ('Salary',        '#006C49', 'briefcase'),
    ('Freelance',     '#4EDEA3', 'code'),
    ('Food & Dining', '#FF8C00', 'utensils'),
    ('Transport',     '#5A7AF0', 'car'),
    ('Housing',       '#8B5CF6', 'home'),
    ('Healthcare',    '#EC4899', 'heart'),
    ('Education',     '#0EA5E9', 'book'),
    ('Shopping',      '#F59E0B', 'shopping-bag'),
    ('Entertainment', '#EF4444', 'film'),
    ('Utilities',     '#6B7280', 'zap')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ─── Sample Transactions ─────────────────────────────────────
INSERT INTO `transactions` (`type`, `amount`, `description`, `category_id`, `date`) VALUES
    ('income',  15000000, 'Monthly Salary',          1, DATE_FORMAT(NOW(), '%Y-%m-01')),
    ('income',   3500000, 'Freelance Project',        2, DATE_FORMAT(NOW(), '%Y-%m-05')),
    ('expense',  1200000, 'Grocery Shopping',         3, DATE_FORMAT(NOW(), '%Y-%m-08')),
    ('expense',   450000, 'Electric & Water Bill',   10, DATE_FORMAT(NOW(), '%Y-%m-10')),
    ('expense',   800000, 'Monthly Rent',             5, DATE_FORMAT(NOW(), '%Y-%m-01')),
    ('expense',   250000, 'Bus & Tuk-tuk',            4, DATE_FORMAT(NOW(), '%Y-%m-12')),
    ('expense',   350000, 'Books & Courses',          7, DATE_FORMAT(NOW(), '%Y-%m-09')),
    ('expense',   180000, 'Coffee & Snacks',          3, DATE_FORMAT(NOW(), '%Y-%m-14')),
    ('income',    500000, 'Bonus Payment',            1, DATE_FORMAT(NOW(), '%Y-%m-15')),
    ('expense',   650000, 'Medical Checkup',          6, DATE_FORMAT(NOW(), '%Y-%m-11'));

-- ─── Sample Budgets ──────────────────────────────────────────
INSERT INTO `budgets` (`category_id`, `amount`, `month`) VALUES
    (3,  2000000, DATE_FORMAT(NOW(), '%Y-%m')),
    (4,   600000, DATE_FORMAT(NOW(), '%Y-%m')),
    (5,  1000000, DATE_FORMAT(NOW(), '%Y-%m')),
    (6,   500000, DATE_FORMAT(NOW(), '%Y-%m')),
    (7,   500000, DATE_FORMAT(NOW(), '%Y-%m')),
    (10,  400000, DATE_FORMAT(NOW(), '%Y-%m'))
ON DUPLICATE KEY UPDATE `amount` = VALUES(`amount`);
