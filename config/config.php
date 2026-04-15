<?php
// ── Application Configuration ─────────────────────────────────

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'wat_financial');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// App
define('APP_ENV', 'development'); // 'production'

// Default fallbacks — overridden at bootstrap from the settings table
define('APP_NAME_DEFAULT',      'Wat Financial Dashboard');
define('CURRENCY_DEFAULT',      '₭');
define('CURRENCY_CODE_DEFAULT', 'LAK');
define('LOCALE_DEFAULT',        'lo_LA');
define('TIMEZONE_DEFAULT',      'Asia/Vientiane');

// ── Lao Month Helpers ─────────────────────────────────────────
function laoMonthFull(int $m): string {
    static $months = [
        1 => 'ມັງກອນ', 2 => 'ກຸມພາ',  3 => 'ມີນາ',
        4 => 'ເມສາ',   5 => 'ພຶດສະພາ', 6 => 'ມິຖຸນາ',
        7 => 'ກໍລະກົດ', 8 => 'ສິງຫາ',  9 => 'ກັນຍາ',
        10 => 'ຕຸລາ',  11 => 'ພະຈິກ',  12 => 'ທັນວາ',
    ];
    return $months[$m] ?? '';
}

function laoMonthAbbr(string $label): string {
    static $map = [
        'Jan' => 'ມ.ກ', 'Feb' => 'ກ.ພ', 'Mar' => 'ມີ.ນ',
        'Apr' => 'ເມ.ສ', 'May' => 'ພ.ພ', 'Jun' => 'ມິ.ຖ',
        'Jul' => 'ກ.ລ',  'Aug' => 'ສ.ຫ', 'Sep' => 'ກ.ຍ',
        'Oct' => 'ຕ.ລ',  'Nov' => 'ພ.ຈ', 'Dec' => 'ທ.ວ',
    ];
    foreach ($map as $en => $lo) {
        if (str_starts_with($label, $en)) {
            return str_replace($en, $lo, $label);
        }
    }
    return $label;
}
