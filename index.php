<?php
// ── Security Headers ──────────────────────────────────────────
// ສົ່ງ headers ກ່ອນ output ໃດໆ
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
    "font-src 'self' https://fonts.gstatic.com; " .
    "img-src 'self' data:; " .
    "connect-src 'self'; " .
    "frame-ancestors 'self'; " .
    "base-uri 'self'; " .
    "form-action 'self';"
);

// ── Hardened Session Cookie ───────────────────────────────────
// ຕ້ອງ set ກ່ອນ session_start()
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;

session_set_cookie_params([
    'lifetime' => 0,               // ຫຼົ່ນຕອນປິດ browser
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,        // HTTPS ເທົ່ານັ້ນ (ຖ້າມີ)
    'httponly' => true,            // JavaScript ບໍ່ສາມາດ access cookie ໄດ້
    'samesite' => 'Strict',        // ກັນ CSRF ຜ່ານ cookie
]);

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
