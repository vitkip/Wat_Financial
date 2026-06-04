-- ═══════════════════════════════════════════════════════
-- Wat Financial Dashboard — V2 Architecture Update
-- Adds Double-Entry Ledger, Donors, Accounts, and Workflows
-- ═══════════════════════════════════════════════════════

USE `wat_financial`;

-- ─── 1. Roles & Users Update ─────────────────────────────
ALTER TABLE `users`
ADD COLUMN `role` ENUM('admin', 'clerk') NOT NULL DEFAULT 'admin' AFTER `password`;

-- ─── 2. Accounts Table (Double-Entry Core) ───────────────
CREATE TABLE IF NOT EXISTS `accounts` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `type`        ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL DEFAULT 'asset',
    `balance`     DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Cached balance for quick display',
    `description` VARCHAR(255) NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default accounts
INSERT INTO `accounts` (`name`, `type`, `description`) VALUES 
('Main Bank Account', 'asset', 'BFL/BCEL Primary Account'),
('Cash Box (Donations)', 'asset', 'Physical cash from donation boxes'),
('General Expense Fund', 'expense', 'General temple expenses');

-- ─── 3. Donors Table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `donors` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(150) NOT NULL,
    `phone`       VARCHAR(20) NULL,
    `address`     TEXT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 4. Transactions Update (Header Table) ───────────────
ALTER TABLE `transactions`
ADD COLUMN `status` ENUM('draft', 'pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved' AFTER `id`,
ADD COLUMN `reference_no` VARCHAR(50) NULL AFTER `status`,
ADD COLUMN `created_by` INT UNSIGNED NULL AFTER `notes`,
ADD COLUMN `approved_by` INT UNSIGNED NULL AFTER `created_by`,
ADD COLUMN `donor_id` INT UNSIGNED NULL AFTER `approved_by`;

-- Add Foreign Keys
ALTER TABLE `transactions`
ADD CONSTRAINT `fk_tx_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_tx_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_tx_donor_id` FOREIGN KEY (`donor_id`) REFERENCES `donors`(`id`) ON DELETE SET NULL;

-- ─── 5. Ledger Entries (Lines Table) ─────────────────────
CREATE TABLE IF NOT EXISTS `ledger_entries` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_id` INT UNSIGNED NOT NULL,
    `account_id`     INT UNSIGNED NOT NULL,
    `entry_type`     ENUM('debit', 'credit') NOT NULL,
    `amount`         DECIMAL(15,2) UNSIGNED NOT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ledger_tx` FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ledger_account` FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── 6. Data Migration Script (Optional backward compat) ─
-- Converts existing Single-Entry transactions into Double-Entry Ledger format
-- Assuming ID 1 = Bank (Asset), ID 2 = Cash (Asset), ID 3 = Gen Expense
-- Note: This is a simplistic mapping. Existing 'income' credits Bank, 'expense' debits Expense Fund.
INSERT INTO `ledger_entries` (`transaction_id`, `account_id`, `entry_type`, `amount`)
SELECT id, 1, 'debit', amount FROM transactions WHERE type = 'income';

INSERT INTO `ledger_entries` (`transaction_id`, `account_id`, `entry_type`, `amount`)
SELECT id, 3, 'debit', amount FROM transactions WHERE type = 'expense';

-- Note: In a true double entry system, every transaction has balancing debits and credits.
-- For legacy data, we just inject the main side to maintain simple net balances, 
-- or we add the balancing side.
-- For Income: Debit Asset(Bank), Credit Revenue(Category)
-- For Expense: Debit Expense(Category), Credit Asset(Bank)
-- Since we don't have accounts for every category yet, we will just use the basic 1-sided for old data.

-- ═══════════════════════════════════════════════════════
-- Phase 2: Logic Improvement Patches
-- ═══════════════════════════════════════════════════════

-- ─── 7. Category Type Constraint ─────────────────────────
-- Prevents clerks from assigning expense-only categories to income transactions
ALTER TABLE `categories`
ADD COLUMN `type` ENUM('income', 'expense', 'both') NOT NULL DEFAULT 'both' AFTER `icon`;

-- ─── 8. Performance Indexes ──────────────────────────────
-- Speed up monthly aggregation queries used by dashboard & reports
CREATE INDEX `idx_tx_date_type_status` ON `transactions` (`date`, `type`, `status`);
CREATE INDEX `idx_tx_category_date`    ON `transactions` (`category_id`, `date`);
CREATE INDEX `idx_tx_status`           ON `transactions` (`status`);
CREATE INDEX `idx_ledger_account`      ON `ledger_entries` (`account_id`, `entry_type`);
CREATE INDEX `idx_ledger_tx`           ON `ledger_entries` (`transaction_id`);

-- ─── 9. Transaction Count Helper ─────────────────────────
-- Used by pagination when no filters are active
-- (ensures count() uses the index instead of a full table scan)
