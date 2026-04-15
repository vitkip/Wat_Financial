<?php
// ── Front Controller ─────────────────────────────────────────
session_start();
define('BASE_PATH', __DIR__);
define('BASE_URL', '/Wat_Financial');

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/App.php';

// ── Load dynamic settings from DB ────────────────────────────
(function () {
    try {
        $rows = Database::getInstance()->fetchAll(
            "SELECT `key`, `value` FROM settings WHERE `key` IN
             ('app_name','currency_symbol','currency_code','locale','timezone','items_per_page')"
        );
        $s = [];
        foreach ($rows as $row) {
            $s[$row['key']] = $row['value'];
        }
    } catch (Throwable $e) {
        $s = [];
    }

    define('APP_NAME',       $s['app_name']        ?? APP_NAME_DEFAULT);
    define('CURRENCY',       $s['currency_symbol'] ?? CURRENCY_DEFAULT);
    define('CURRENCY_CODE',  $s['currency_code']   ?? CURRENCY_CODE_DEFAULT);
    define('LOCALE',         $s['locale']          ?? LOCALE_DEFAULT);
    define('DECIMAL_PLACES', max(0, (int)($s['decimal_places'] ?? 0)));

    date_default_timezone_set($s['timezone'] ?? TIMEZONE_DEFAULT);
    define('ITEMS_PER_PAGE', max(5, (int)($s['items_per_page'] ?? 15)));
})();

// Autoload models and controllers
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$app = new App();
