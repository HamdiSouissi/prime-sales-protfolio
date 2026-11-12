<?php

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
    define('APP_PATH', ROOT_PATH . '/app');
    define('STORAGE_PATH', ROOT_PATH . '/storage');
}

$GLOBALS['app_config'] = require APP_PATH . '/Config/app.php';
$GLOBALS['database_config'] = require APP_PATH . '/Config/database.php';

$basePath = rtrim((string) ($GLOBALS['app_config']['base_path'] ?? ''), '/');
define('APP_BASE_PATH', $basePath);

date_default_timezone_set($GLOBALS['app_config']['timezone'] ?? 'UTC');

spl_autoload_register(static function (string $class): void {
    $prefix = 'PrimeSales\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require_once APP_PATH . '/Core/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionName = (string) ($GLOBALS['app_config']['session_name'] ?? 'prime_sales_session');
    session_name($sessionName);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_PATH !== '' ? APP_BASE_PATH . '/' : '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
