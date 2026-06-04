<?php
/**
 * View: Transactions / index  — UI v2
 * Variables: $transactions, $categories, $page, $totalPages, $total,
 *            $filters, $hasFilter, $filterIncome, $filterExpense
 */
$fmt = fn(float $n) => CURRENCY . ' ' . number_format($n, defined('DECIMAL_PLACES') ? DECIMAL_PLACES : 0, '.', ',');

function filterQs(array $filters, string $key = '', mixed $val = null): string
{
    $f = $filters;
    if ($key !== '')
        $f[$key] = $val;
    $q = http_build_query(array_filter($f, fn($v) => $v !== '' && $v !== null));
    return $q ? '?' . $q : '';
}
?>

<style>
    /* ── Design Tokens ─────────────────────────────────────── */
    :root {
        --income-bg: #f0fdf4;
        --income-text: #16a34a;
        --income-dot: #22c55e;
        --expense-bg: #fff1f2;
        --expense-text: #e11d48;
        --expense-dot: #f43f5e;
        --tx-row-h: 68px;
        --radius-card: 16px;
        --radius-pill: 999px;
        --shadow-card: 0 1px 3px rgba(0, 0, 0, .06), 0 4px 16px rgba(0, 0, 0, .04);
        --shadow-modal: 0 8px 48px rgba(0, 0, 0, .18);
        --transition: 150ms cubic-bezier(.4, 0, .2, 1);
    }

    /* ── Layout ────────────────────────────────────────────── */
    .tx-page {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ── Summary Bar ────────────────────────────────────────── */
    .summary-bar {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: start;
        gap: 16px;
    }

    .summary-title {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -.4px;
        color: var(--color-on-surface, #1a1a1a);
        margin: 0 0 6px;
    }

    .summary-stats {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: var(--radius-pill);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1px;
    }

    .stat-chip.income {
        background: var(--income-bg);
        color: var(--income-text);
    }

    .stat-chip.expense {
        background: var(--expense-bg);
        color: var(--expense-text);
    }

    .stat-chip.total {
        background: #f1f5f9;
        color: #475569;
    }

    .stat-chip svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    /* ── Filter Card ───────────────────────────────────────── */
    .filter-card {
        background: #fff;
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, .06);
    }

    .filter-toolbar {
        display: flex;
        align-items: stretch;
        min-height: 56px;
    }

    .search-wrap {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 18px;
        color: #94a3b8;
        pointer-events: none;
        width: 18px;
        height: 18px;
    }

    .search-input {
        width: 100%;
        height: 56px;
        padding: 0 16px 0 48px;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        color: #1e293b;
        font-family: inherit;
    }

    .search-input::placeholder {
        color: #cbd5e1;
    }

    .toolbar-sep {
        width: 1px;
        background: rgba(0, 0, 0, .07);
        margin: 10px 0;
        flex-shrink: 0;
    }

    /* Type toggle group */
    .type-pills {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 0 14px;
        flex-shrink: 0;
    }

    .type-pill input[type=radio] {
        display: none;
    }

    .type-pill-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border-radius: var(--radius-pill);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        color: #64748b;
        transition: all var(--transition);
        white-space: nowrap;
        user-select: none;
    }

    .type-pill input:checked+.type-pill-label {
        font-weight: 700;
    }

    .type-pill input[value=""] :checked+.type-pill-label,
    .type-pill input[value=""]:checked+.type-pill-label {
        background: #f1f5f9;
        color: #0f172a;
    }

    .type-pill input[value="income"]:checked+.type-pill-label {
        background: var(--income-bg);
        color: var(--income-text);
    }

    .type-pill input[value="expense"]:checked+.type-pill-label {
        background: var(--expense-bg);
        color: var(--expense-text);
    }

    .type-pill-label:hover {
        background: #f8fafc;
    }

    /* Toolbar action buttons */
    .toolbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 18px;
        height: 56px;
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        font-family: inherit;
        flex-shrink: 0;
        transition: all var(--transition);
        border-left: 1px solid rgba(0, 0, 0, .07);
    }

    .toolbar-btn:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .toolbar-btn.active {
        color: var(--primary, #0057ff);
        background: #eff6ff;
    }

    .toolbar-btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    .toolbar-btn .badge {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--primary, #0057ff);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .toolbar-btn.clear {
        color: #e11d48;
    }

    .toolbar-btn.clear:hover {
        background: var(--expense-bg);
    }

    /* Advanced panel */
    .adv-panel {
        border-top: 1px solid rgba(0, 0, 0, .06);
        padding: 16px 20px;
        background: #fafbfc;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 12px;
        animation: slideDown .15s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .adv-panel.hidden {
        display: none !important;
    }

    .adv-field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 6px;
    }

    .adv-field input,
    .adv-field select {
        width: 100%;
        padding: 8px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
        color: #1e293b;
        background: #fff;
        font-family: inherit;
        outline: none;
        transition: border-color var(--transition);
        box-sizing: border-box;
    }

    .adv-field input:focus,
    .adv-field select:focus {
        border-color: var(--primary, #0057ff);
    }

    .adv-field.span2 {
        grid-column: span 2;
    }

    .amount-range {
        display: flex;
        gap: 8px;
    }

    .amount-range input {
        flex: 1;
    }

    .presets-row {
        grid-column: 1/-1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        padding-top: 4px;
    }

    .preset-btns {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
    }

    .preset-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .preset-btn {
        padding: 5px 12px;
        border-radius: var(--radius-pill);
        border: 1.5px solid #e2e8f0;
        background: #fff;
        font-size: 12px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        font-family: inherit;
        transition: all var(--transition);
    }

    .preset-btn:hover {
        border-color: var(--primary, #0057ff);
        color: var(--primary, #0057ff);
        background: #eff6ff;
    }

    /* ── Active chips ────────────────────────────────────────── */
    .active-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px 4px 12px;
        border-radius: var(--radius-pill);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all var(--transition);
    }

    .filter-chip.primary {
        background: #eff6ff;
        color: var(--primary, #0057ff);
    }

    .filter-chip.income {
        background: var(--income-bg);
        color: var(--income-text);
    }

    .filter-chip.expense {
        background: var(--expense-bg);
        color: var(--expense-text);
    }

    .filter-chip.neutral {
        background: #f1f5f9;
        color: #475569;
    }

    .filter-chip:hover {
        filter: brightness(.95);
    }

    .filter-chip svg {
        width: 12px;
        height: 12px;
        opacity: .6;
    }

    .filter-chip:hover svg {
        opacity: 1;
    }

    .chips-right {
        margin-left: auto;
        font-size: 12px;
        color: #94a3b8;
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .chips-right .inc {
        color: var(--income-text);
        font-weight: 600;
    }

    .chips-right .exp {
        color: var(--expense-text);
        font-weight: 600;
    }

    /* ── Transaction Table ──────────────────────────────────── */
    .tx-card {
        background: #fff;
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        border: 1px solid rgba(0, 0, 0, .06);
        overflow: hidden;
    }

    .tx-header {
        display: grid;
        grid-template-columns: 40px 1fr 130px 100px 100px 120px 100px;
        gap: 12px;
        align-items: center;
        padding: 10px 20px;
        background: #f8fafc;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .tx-header span {
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .tx-header .right {
        text-align: right;
    }

    .tx-body {}

    .tx-divider {
        height: 1px;
        background: rgba(0, 0, 0, .05);
        margin: 0 20px;
    }

    .tx-row {
        display: grid;
        grid-template-columns: 40px 1fr 130px 100px 100px 120px 100px;
        gap: 12px;
        align-items: center;
        padding: 14px 20px;
        min-height: var(--tx-row-h);
        transition: background var(--transition);
        cursor: default;
    }

    .tx-row:hover {
        background: #f8fbff;
    }

    .tx-row:hover .tx-actions {
        opacity: 1;
        transform: translateX(0);
    }

    /* Type indicator */
    .tx-type-dot {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .tx-type-dot.income {
        background: var(--income-bg);
        color: var(--income-text);
    }

    .tx-type-dot.expense {
        background: var(--expense-bg);
        color: var(--expense-text);
    }

    /* Description cell */
    .tx-desc {
        overflow: hidden;
    }

    .tx-desc-main {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .tx-desc-main mark {
        background: rgba(59, 130, 246, .15);
        color: #1d4ed8;
        border-radius: 3px;
        padding: 0 2px;
    }

    .tx-desc-note {
        font-size: 12px;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 2px;
    }

    /* Category cell */
    .tx-cat {
        display: flex;
        align-items: center;
        gap: 7px;
        overflow: hidden;
    }

    .tx-cat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .tx-cat-name {
        font-size: 13px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Date cell */
    .tx-date {
        font-size: 13px;
        color: #64748b;
    }

    /* Amount cell */
    .tx-amount {
        text-align: right;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: -.3px;
        font-variant-numeric: tabular-nums;
    }

    .tx-amount.income {
        color: var(--income-text);
    }

    .tx-amount.expense {
        color: var(--expense-text);
    }

    /* Actions cell */
    .tx-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 4px;
        opacity: 0;
        transform: translateX(6px);
        transition: opacity var(--transition), transform var(--transition);
    }

    .action-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        cursor: pointer;
        color: #94a3b8;
        transition: all var(--transition);
    }

    .action-btn:hover {
        background: #f1f5f9;
        color: #475569;
    }

    .action-btn.delete:hover {
        background: var(--expense-bg);
        color: var(--expense-text);
    }

    .action-btn svg {
        width: 15px;
        height: 15px;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 72px 24px;
    }

    .empty-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        background: #f1f5f9;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
    }

    .empty-icon svg {
        width: 24px;
        height: 24px;
    }

    .empty-title {
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 6px;
    }

    .empty-sub {
        font-size: 14px;
        color: #94a3b8;
        margin: 0 0 20px;
    }

    /* ── Pagination ─────────────────────────────────────────── */
    .pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        border-top: 1px solid rgba(0, 0, 0, .06);
        background: #fafbfc;
        flex-wrap: wrap;
        gap: 10px;
    }

    .page-info {
        font-size: 13px;
        color: #94a3b8;
    }

    .page-btns {
        display: flex;
        gap: 5px;
    }

    .page-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        cursor: pointer;
        transition: all var(--transition);
    }

    .page-btn:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #0f172a;
    }

    .page-btn.active {
        border-color: var(--primary, #0057ff);
        background: var(--primary, #0057ff);
        color: #fff;
    }

    .page-btn.nav {
        gap: 4px;
        font-weight: 600;
    }

    /* ── Add / Edit button ──────────────────────────────────── */
    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--radius-pill);
        background: var(--primary, #0057ff);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
        transition: all var(--transition);
        flex-shrink: 0;
        box-shadow: 0 2px 12px rgba(0, 87, 255, .3);
    }

    .btn-add:hover {
        filter: brightness(1.08);
        box-shadow: 0 4px 20px rgba(0, 87, 255, .4);
    }

    .btn-add svg {
        width: 16px;
        height: 16px;
    }

    .btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 18px;
        border-radius: var(--radius-pill);
        border: 1.5px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
        transition: all var(--transition);
    }

    .btn-outline:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    /* ── Export Dropdown ────────────────────────────────────── */
    .export-dropdown {
        position: relative;
    }

    .export-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 220px;
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,.12);
        z-index: 200;
        overflow: hidden;
    }

    .export-menu.open { display: block; }

    .export-menu-header {
        padding: 10px 14px 8px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px solid #f1f5f9;
    }

    .export-filter-badge {
        background: #eff6ff;
        color: #3b82f6;
        font-size: 10px;
        padding: 1px 6px;
        border-radius: 99px;
        font-weight: 600;
        letter-spacing: 0;
        text-transform: none;
    }

    .export-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 14px;
        text-decoration: none;
        color: #334155;
        transition: background var(--transition);
    }

    .export-item:hover { background: #f8fafc; }

    .export-item + .export-item {
        border-top: 1px solid #f1f5f9;
    }

    .export-item-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .export-icon-excel { background: #f0fdf4; color: #16a34a; }
    .export-icon-pdf   { background: #fef2f2; color: #dc2626; }

    .export-item-title {
        font-size: 13px;
        font-weight: 600;
        margin: 0 0 1px;
    }

    .export-item-sub {
        font-size: 11px;
        color: #94a3b8;
        margin: 0;
    }

    /* ── Modal ──────────────────────────────────────────────── */
    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, .5);
        backdrop-filter: blur(4px);
    }

    .modal-overlay.open {
        display: flex;
    }

    .modal-box {
        position: relative;
        width: 100%;
        max-width: 440px;
        background: #fff;
        border-radius: 20px;
        box-shadow: var(--shadow-modal);
        max-height: 90vh;
        overflow-y: auto;
        animation: modalIn .2s cubic-bezier(.34, 1.56, .64, 1);
    }

    @keyframes modalIn {
        from {
            opacity: 0;
            transform: scale(.94) translateY(16px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 22px 24px 0;
    }

    .modal-title {
        font-size: 19px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .modal-close {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: none;
        background: #f1f5f9;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: all var(--transition);
    }

    .modal-close:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    .modal-close svg {
        width: 15px;
        height: 15px;
    }

    .modal-body {
        padding: 20px 24px 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .form-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 7px;
    }

    .form-field input,
    .form-field select,
    .form-field textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        font-family: inherit;
        outline: none;
        transition: border-color var(--transition), box-shadow var(--transition);
        box-sizing: border-box;
    }

    .form-field input:focus,
    .form-field select:focus,
    .form-field textarea:focus {
        border-color: var(--primary, #0057ff);
        box-shadow: 0 0 0 3px rgba(0, 87, 255, .1);
    }

    .form-field input.amount-input {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -.3px;
        padding: 12px 14px;
        font-variant-numeric: tabular-nums;
    }

    .form-field textarea {
        resize: none;
    }

    /* Type switcher in modal */
    .type-switcher {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        padding: 5px;
        background: #f8fafc;
        border-radius: 14px;
    }

    .type-sw input {
        display: none;
    }

    .type-sw-label {
        display: block;
        text-align: center;
        padding: 9px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        color: #64748b;
        transition: all var(--transition);
    }

    .type-sw input[value="income"]:checked+.type-sw-label {
        background: #fff;
        color: var(--income-text);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .1);
    }

    .type-sw input[value="expense"]:checked+.type-sw-label {
        background: #fff;
        color: var(--expense-text);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .1);
    }

    .type-sw-label:hover {
        color: #0f172a;
    }

    .btn-submit {
        width: 100%;
        padding: 13px;
        border-radius: 12px;
        border: none;
        background: var(--primary, #0057ff);
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: all var(--transition);
        box-shadow: 0 2px 12px rgba(0, 87, 255, .3);
    }

    .btn-submit:hover {
        filter: brightness(1.08);
    }

    /* ── Mobile ─────────────────────────────────────────────── */
    @media(max-width:900px) {
        .tx-header {
            display: none;
        }

        .tx-row {
            grid-template-columns: 40px 1fr auto;
            grid-template-rows: auto auto;
            gap: 4px 10px;
            padding: 14px 16px;
        }

        .tx-row .tx-type-dot {
            grid-row: 1/3;
        }

        .tx-row .tx-desc {
            grid-column: 2;
        }

        .tx-row .tx-amount {
            grid-column: 3;
            grid-row: 1;
            font-size: 14px;
        }

        .tx-row .tx-cat,
        .tx-row .tx-date {
            display: none;
        }

        .tx-row .tx-actions {
            grid-column: 3;
            grid-row: 2;
            opacity: 1;
            transform: none;
            justify-content: flex-end;
        }
    }

    @media(max-width:600px) {
        .summary-bar {
            grid-template-columns: 1fr;
        }

        .type-pills {
            display: none;
        }

        .toolbar-btn span {
            display: none;
        }

        .adv-panel {
            grid-template-columns: 1fr;
        }

        .adv-field.span2 {
            grid-column: 1;
        }

        .tx-divider {
            margin: 0 16px;
        }
    }
</style>

<div class="tx-page">

    <!-- ── Page Header ───────────────────────────────────────── -->
    <div class="summary-bar">
        <div>
            <p
                style="font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#94a3b8;margin:0 0 6px;">
                ການເງິນ</p>
            <h1 class="summary-title">ລາຍການທຸລະກຳ</h1>
            <div class="summary-stats">
                <?php if ($hasFilter): ?>
                    <span class="stat-chip total">ພົບ
                        <?= number_format($total) ?> ລາຍການ
                    </span>
                    <?php if ($filterIncome > 0): ?>
                        <span class="stat-chip income">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <?= $fmt($filterIncome) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($filterExpense > 0): ?>
                        <span class="stat-chip expense">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <?= $fmt($filterExpense) ?>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="stat-chip total">
                        <?= number_format($total) ?> ລາຍການທັງໝົດ
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <!-- ── Action buttons group ─────────────────────────── -->
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">

            <!-- Export dropdown -->
            <div class="export-dropdown" id="exportDropdown">
                <button type="button" class="btn-outline" onclick="toggleExportMenu(event)"
                        title="Export ລາຍການ">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="export-menu" id="exportMenu">
                    <div class="export-menu-header">Export ທຸລະກຳ
                        <?php if ($hasFilter): ?>
                            <span class="export-filter-badge">ມີ filter</span>
                        <?php endif; ?>
                    </div>
                    <?php
                    // Build current filter query string for export links
                    $exportQs = http_build_query(array_filter([
                        'q'           => $filters['q'],
                        'type'        => $filters['type'],
                        'category_id' => $filters['category_id'],
                        'date_from'   => $filters['date_from'],
                        'date_to'     => $filters['date_to'],
                        'amount_min'  => $filters['amount_min'],
                        'amount_max'  => $filters['amount_max'],
                    ], fn($v) => $v !== ''));
                    $exportBase = BASE_URL . '/export/transactions' . ($exportQs ? '?' . $exportQs . '&' : '?');
                    ?>
                    <a href="<?= $exportBase ?>format=csv" class="export-item" target="_blank">
                        <span class="export-item-icon export-icon-excel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="export-item-title">Excel / CSV</p>
                            <p class="export-item-sub">ດາວໂຫຼດໄຟລ໌ .csv</p>
                        </div>
                    </a>
                    <a href="<?= $exportBase ?>format=pdf" class="export-item" target="_blank">
                        <span class="export-item-icon export-icon-pdf">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="export-item-title">PDF</p>
                            <p class="export-item-sub">ເປີດໜ້າພິມ / Save PDF</p>
                        </div>
                    </a>
                </div>
            </div>

            <button onclick="openModal('addTransactionModal')" class="btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                ເພີ່ມລາຍການ
            </button>
        </div>
    </div>

    <!-- ── Search & Filter ──────────────────────────────────── -->
    <?php
    $hasAdvanced = !empty($filters['date_from']) || !empty($filters['date_to'])
        || $filters['amount_min'] !== '' || $filters['amount_max'] !== '';
    $activeCount = (int) (bool) $filters['q'] + (int) (bool) $filters['type']
        + (int) (bool) $filters['category_id'] + (int) $hasAdvanced;
    ?>
    <form method="GET" action="<?= BASE_URL ?>/transactions" id="filterForm">
        <div class="filter-card">

            <!-- Toolbar -->
            <div class="filter-toolbar">
                <!-- Search -->
                <div class="search-wrap">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                    <input type="text" name="q" id="searchQ" class="search-input"
                        value="<?= htmlspecialchars($filters['q']) ?>" placeholder="ຄົ້ນຫາລາຍລະອຽດ, ໝວດໝູ່..."
                        autocomplete="off">
                </div>

                <!-- Type pills (desktop) -->
                <div class="type-pills toolbar-sep"
                    style="border-right:1px solid rgba(0,0,0,.07); margin:0; padding-left:1px;"></div>
                <div class="type-pills">
                    <?php foreach (['' => 'ທັງໝົດ', 'income' => '↑ ລາຍຮັບ', 'expense' => '↓ ລາຍຈ່າຍ'] as $val => $label): ?>
                        <label class="type-pill">
                            <input type="radio" name="type" value="<?= $val ?>" <?= $filters['type'] === $val ? 'checked' : '' ?>
                            onchange="this.form.submit()">
                            <span class="type-pill-label">
                                <?= $label ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Filter toggle -->
                <button type="button" id="toggleAdvanced" class="toolbar-btn <?= $hasAdvanced ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M9 16h6" />
                    </svg>
                    <span>Filter</span>
                    <?php if ($activeCount > 0): ?>
                        <span class="badge">
                            <?= $activeCount ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Clear -->
                <?php if ($hasFilter): ?>
                    <a href="<?= BASE_URL ?>/transactions" class="toolbar-btn clear">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>ລ້າງ</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Advanced panel -->
            <div id="advancedPanel" class="adv-panel <?= $hasAdvanced ? '' : 'hidden' ?>">

                <div class="adv-field span2">
                    <label>ໝວດໝູ່</label>
                    <select name="category_id">
                        <option value="">— ທຸກໝວດໝູ່ —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>" <?= (string) $filters['category_id'] === (string) $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="adv-field">
                    <label>ຈາກວັນທີ</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
                </div>
                <div class="adv-field">
                    <label>ຮອດວັນທີ</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
                </div>

                <div class="adv-field">
                    <label>ຈຳນວນ (ຂັ້ນຕ່ຳ – ສູງສຸດ)</label>
                    <div class="amount-range">
                        <input type="number" name="amount_min" min="0" step="any"
                            value="<?= htmlspecialchars($filters['amount_min']) ?>" placeholder="0">
                        <input type="number" name="amount_max" min="0" step="any"
                            value="<?= htmlspecialchars($filters['amount_max']) ?>" placeholder="∞">
                    </div>
                </div>

                <div class="presets-row">
                    <div class="preset-btns">
                        <span class="preset-label">ເລືອກໄວ:</span>
                        <?php foreach ([
                            'ເດືອນນີ້' => [date('Y-m-01'), date('Y-m-d')],
                            'ເດືອນກ່ອນ' => [date('Y-m-01', strtotime('-1 month')), date('Y-m-t', strtotime('-1 month'))],
                            '3 ເດືອນ' => [date('Y-m-d', strtotime('-3 months')), date('Y-m-d')],
                            'ປີນີ້' => [date('Y-01-01'), date('Y-m-d')],
                        ] as $label => [$from, $to]): ?>
                            <button type="button" class="preset-btn" onclick="setDatePreset('<?= $from ?>','<?= $to ?>')">
                                <?= $label ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn-add" style="padding:9px 20px;font-size:13px;">
                        ຄົ້ນຫາ
                    </button>
                </div>
            </div>
        </div>

        <!-- Active chips -->
        <?php if ($hasFilter): ?>
            <div class="active-chips" style="margin-top:10px;">
                <span
                    style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#94a3b8;">ກຳລັງ
                    filter:</span>

                <?php if ($filters['q']): ?>
                    <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'q', '') ?>" class="filter-chip primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                        </svg>
                        "
                        <?= htmlspecialchars(mb_strimwidth($filters['q'], 0, 28, '…')) ?>"
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <?php if ($filters['type']): ?>
                    <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'type', '') ?>"
                        class="filter-chip <?= $filters['type'] === 'income' ? 'income' : 'expense' ?>">
                        <?= $filters['type'] === 'income' ? '↑ ລາຍຮັບ' : '↓ ລາຍຈ່າຍ' ?>
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <?php if ($filters['category_id']):
                    $catName = array_column($categories, 'name', 'id')[$filters['category_id']] ?? 'ໝວດໝູ່'; ?>
                    <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'category_id', '') ?>"
                        class="filter-chip neutral">
                        <?= htmlspecialchars($catName) ?>
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                <?php endif; ?>

                <div class="chips-right">
                    <?php if ($filterIncome > 0): ?><span class="inc">+
                            <?= $fmt($filterIncome) ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($filterExpense > 0): ?><span class="exp">-
                            <?= $fmt($filterExpense) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </form>

    <!-- ── Transaction Table ─────────────────────────────────── -->
    <div class="tx-card">
        <!-- Desktop header -->
        <div class="tx-header">
            <span></span>
            <span>ລາຍລະອຽດ</span>
            <span>ໝວດໝູ່</span>
            <span>ສະຖານະ</span>
            <span>ວັນທີ</span>
            <span class="right">ຈຳນວນ</span>
            <span class="right">ດຳເນີນການ</span>
        </div>

        <!-- Rows -->
        <div class="tx-body">
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <?php if ($hasFilter): ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                            </svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasFilter): ?>
                        <p class="empty-title">ບໍ່ພົບລາຍການ</p>
                        <p class="empty-sub">ລອງປ່ຽນ keyword ຫຼື ລ້າງ filter</p>
                        <a href="<?= BASE_URL ?>/transactions" class="btn-outline">ລ້າງ filter ທັງໝົດ</a>
                    <?php else: ?>
                        <p class="empty-title">ຍັງບໍ່ມີລາຍການ</p>
                        <p class="empty-sub">ເພີ່ມລາຍຮັບ ຫຼື ລາຍຈ່າຍທຳອິດຂອງທ່ານ</p>
                        <button onclick="openModal('addTransactionModal')" class="btn-add" style="margin:0 auto;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            ເພີ່ມລາຍການ
                        </button>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <?php foreach ($transactions as $i => $tx):
                    $isIncome = $tx['type'] === 'income';
                    $amount = (float) $tx['amount'];
                    $desc = htmlspecialchars($tx['description']);
                    if ($filters['q']) {
                        $esc = preg_quote(htmlspecialchars($filters['q']), '/');
                        $desc = preg_replace("/($esc)/i", '<mark>$1</mark>', $desc);
                    }
                    ?>
                    <?php if ($i > 0): ?>
                        <div class="tx-divider"></div>
                    <?php endif; ?>
                    <div class="tx-row">

                        <!-- Type dot -->
                        <div class="tx-type-dot <?= $isIncome ? 'income' : 'expense' ?>">
                            <?= $isIncome ? '↑' : '↓' ?>
                        </div>

                        <!-- Description -->
                        <div class="tx-desc">
                            <div class="tx-desc-main">
                                <?= $desc ?>
                            </div>
                            <?php if (!empty($tx['notes'])): ?>
                                <div class="tx-desc-note">
                                    <?= htmlspecialchars($tx['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Category -->
                        <div class="tx-cat">
                            <div class="tx-cat-dot"
                                style="background:<?= htmlspecialchars($tx['category_color'] ?? '#94a3b8') ?>"></div>
                            <span class="tx-cat-name">
                                <?= htmlspecialchars($tx['category_name'] ?? '—') ?>
                            </span>
                        </div>

                        <!-- Status -->
                        <div>
                            <?php 
                            $status = $tx['status'] ?? 'approved';
                            if ($status === 'pending'): ?>
                                <span style="background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:bold;">ລໍຖ້າອະນຸມັດ</span>
                            <?php elseif ($status === 'rejected'): ?>
                                <span style="background:#fee2e2;color:#dc2626;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:bold;">ຖືກປະຕິເສດ</span>
                            <?php else: ?>
                                <span style="background:#dcfce3;color:#16a34a;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:bold;">ອະນຸມັດແລ້ວ</span>
                            <?php endif; ?>
                        </div>

                        <!-- Date -->
                        <div class="tx-date">
                            <?= date('d M Y', strtotime($tx['date'])) ?>
                        </div>

                        <!-- Amount -->
                        <div class="tx-amount <?= $isIncome ? 'income' : 'expense' ?>">
                            <?= $isIncome ? '+' : '−' ?>
                            <?= $fmt($amount) ?>
                        </div>

                        <!-- Actions -->
                        <div class="tx-actions">
                            <?php if ($perms->can('transactions.approve') && ($tx['status'] ?? 'approved') === 'pending'): ?>
                                <form method="POST" action="<?= BASE_URL ?>/transactions/approve/<?= (int) $tx['id'] ?>" style="display:contents;">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="action-btn" title="ອະນຸມັດ" style="color:#16a34a;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($tx), ENT_QUOTES) ?>)"
                                class="action-btn" title="ແກ້ໄຂ">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form method="POST" action="<?= BASE_URL ?>/transactions/delete/<?= (int) $tx['id'] ?>"
                                class="delete-form" style="display:contents;">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="button" class="action-btn delete" title="ລຶບ"
                                        onclick="confirmDelete(this)"
                                        data-desc="<?= htmlspecialchars($tx['description']) ?>"
                                        data-amount="<?= ($tx['type']==='income'?'+':'−') . $fmt((float)$tx['amount']) ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <span class="page-info">
                    ໜ້າ
                    <?= $page ?> /
                    <?= $totalPages ?>
                    &nbsp;·&nbsp;
                    <?= number_format($total) ?> ລາຍການ
                </span>
                <div class="page-btns">
                    <?php if ($page > 1): ?>
                        <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'page', $page - 1) ?>" class="page-btn nav">←
                            ກ່ອນ</a>
                    <?php endif; ?>
                    <?php
                    $start = max(1, min($page - 2, $totalPages - 4));
                    $end = min($totalPages, $start + 4);
                    for ($p = $start; $p <= $end; $p++): ?>
                        <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'page', $p) ?>"
                            class="page-btn <?= $p === $page ? 'active' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= BASE_URL ?>/transactions<?= filterQs($filters, 'page', $page + 1) ?>"
                            class="page-btn nav">ຕໍ່ໄປ →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Edit Modal ──────────────────────────────────────── -->
    <div id="editTransactionModal" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="absolute inset-0" onclick="closeModal('editTransactionModal')"></div>
        <div class="modal-box">
            <div class="modal-header">
                <h2 class="modal-title">ແກ້ໄຂລາຍການ</h2>
                <button class="modal-close" onclick="closeModal('editTransactionModal')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="editTransactionForm" method="POST" action="" style="display:contents;">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="form-field">
                        <label>ປະເພດ</label>
                        <div class="type-switcher">
                            <label class="type-sw">
                                <input type="radio" id="editTypeIncome" name="type" value="income">
                                <span class="type-sw-label">↑ ລາຍຮັບ</span>
                            </label>
                            <label class="type-sw">
                                <input type="radio" id="editTypeExpense" name="type" value="expense">
                                <span class="type-sw-label">↓ ລາຍຈ່າຍ</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>ຈຳນວນ (
                            <?= CURRENCY ?>)
                        </label>
                        <input type="number" id="editAmount" name="amount" min="1" step="any" required
                            class="amount-input" placeholder="0">
                    </div>

                    <div class="form-field">
                        <label>ລາຍລະອຽດ</label>
                        <input type="text" id="editDesc" name="description" required placeholder="ລາຍລະອຽດ...">
                    </div>

                    <div class="form-field">
                        <label>ໝວດໝູ່</label>
                        <select id="editCategory" name="category_id">
                            <option value="">— ບໍ່ມີໝວດໝູ່ —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>ວັນທີ</label>
                        <input type="date" id="editDate" name="date">
                    </div>

                    <div class="form-field">
                        <label>ໝາຍເຫດ <span
                                style="font-weight:400;text-transform:none;letter-spacing:0;color:#cbd5e1;">(ບໍ່ບັງຄັບ)</span></label>
                        <textarea id="editNotes" name="notes" rows="2" placeholder="ໝາຍເຫດ..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">ບັນທຶກການປ່ຽນແປງ</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Add Modal ───────────────────────────────────────── -->
    <div id="addTransactionModal" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="absolute inset-0" onclick="closeModal('addTransactionModal')"></div>
        <div class="modal-box">
            <div class="modal-header">
                <h2 class="modal-title">ເພີ່ມລາຍການໃໝ່</h2>
                <button class="modal-close" onclick="closeModal('addTransactionModal')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="<?= BASE_URL ?>/transactions/store" style="display:contents;">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="form-field">
                        <label>ປະເພດ</label>
                        <div class="type-switcher">
                            <label class="type-sw">
                                <input type="radio" name="type" value="income" required>
                                <span class="type-sw-label">↑ ລາຍຮັບ</span>
                            </label>
                            <label class="type-sw">
                                <input type="radio" name="type" value="expense" checked>
                                <span class="type-sw-label">↓ ລາຍຈ່າຍ</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>ຈຳນວນ (
                            <?= CURRENCY ?>)
                        </label>
                        <input type="number" name="amount" min="1" step="any" required class="amount-input"
                            placeholder="0">
                    </div>

                    <div class="form-field">
                        <label>ລາຍລະອຽດ</label>
                        <input type="text" name="description" required placeholder="ຕົວຢ່າງ: ຊື້ເຄື່ອງ">
                    </div>

                    <div class="form-field">
                        <label>ໝວດໝູ່</label>
                        <select name="category_id">
                            <option value="">— ບໍ່ມີໝວດໝູ່ —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field">
                        <label>ວັນທີ</label>
                        <input type="date" name="date" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-field">
                        <label>ໝາຍເຫດ <span
                                style="font-weight:400;text-transform:none;letter-spacing:0;color:#cbd5e1;">(ບໍ່ບັງຄັບ)</span></label>
                        <textarea name="notes" rows="2" placeholder="ໝາຍເຫດເພີ່ມເຕີມ..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">ບັນທຶກລາຍການ</button>
                </form>
            </div>
        </div>
    </div>

    <script nonce="<?= CSP_NONCE ?? '' ?>">
        const BASE_URL = '<?= BASE_URL ?>';

        /* ── Modal ─────────────────────────────────────── */
        function openModal(id) {
            const el = document.getElementById(id);
            el.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            el.classList.remove('open');
            document.body.style.overflow = '';
        }
        function openEditModal(tx) {
            document.getElementById('editTransactionForm').action =
                BASE_URL + '/transactions/update/' + tx.id;
            document.getElementById('editTypeIncome').checked = tx.type === 'income';
            document.getElementById('editTypeExpense').checked = tx.type === 'expense';
            document.getElementById('editAmount').value = tx.amount;
            document.getElementById('editDesc').value = tx.description;
            document.getElementById('editCategory').value = tx.category_id ?? '';
            document.getElementById('editDate').value = tx.date;
            document.getElementById('editNotes').value = tx.notes ?? '';
            openModal('editTransactionModal');
        }

        /* ── Filter panel ──────────────────────────────── */
        document.getElementById('toggleAdvanced')?.addEventListener('click', () => {
            const p = document.getElementById('advancedPanel');
            p.classList.toggle('hidden');
            document.getElementById('toggleAdvanced').classList.toggle('active', !p.classList.contains('hidden'));
        });

        /* ── Date presets ──────────────────────────────── */
        function setDatePreset(from, to) {
            const form = document.getElementById('filterForm');
            form.querySelector('[name="date_from"]').value = from;
            form.querySelector('[name="date_to"]').value = to;
            form.submit();
        }

        /* ── Search debounce ───────────────────────────── */
        let debounceTimer;
        document.getElementById('searchQ')?.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => this.form.submit(), 500);
        });

        /* ── ESC ───────────────────────────────────────── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeModal('editTransactionModal');
                closeModal('addTransactionModal');
                document.getElementById('exportMenu')?.classList.remove('open');
            }
        });

        /* ── Delete confirm (SweetAlert2) ─────────────── */
        function confirmDelete(btn) {
            const form   = btn.closest('form');
            const desc   = btn.dataset.desc   || 'ລາຍການນີ້';
            const amount = btn.dataset.amount || '';
            Swal.fire({
                title: 'ລຶບລາຍການ?',
                html: `<p style="color:#475569;font-size:14px;margin:0">
                           <strong>${desc}</strong><br>
                           <span style="color:#e11d48;font-weight:600;">${amount}</span>
                       </p>`,
                icon: 'warning',
                iconColor: '#e11d48',
                showCancelButton: true,
                confirmButtonText: 'ລຶບ',
                cancelButtonText: 'ຍົກເລີກ',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup:         'swal-lao-popup',
                    title:         'swal-lao-title',
                    confirmButton: 'swal-lao-btn',
                    cancelButton:  'swal-lao-btn',
                },
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        }

        /* ── Export dropdown ───────────────────────────── */
        function toggleExportMenu(e) {
            e.stopPropagation();
            document.getElementById('exportMenu').classList.toggle('open');
        }
        document.addEventListener('click', () => {
            document.getElementById('exportMenu')?.classList.remove('open');
        });
    </script>