<?php
/**
 * Application front controller.
 *
 * Initializes configuration, session security, core dependencies, and routing.
 */

// Load application configuration and constants first.
require_once dirname(__DIR__) . '/config/config.php';

set_exception_handler(static function (Throwable $e): void {
    error_log('[BoardTrack Uncaught] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);

    if (defined('APP_ENV') && APP_ENV === 'development') {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
        return;
    }

    echo '<!DOCTYPE html><html><head><title>Server Error</title></head><body style="font-family:sans-serif;text-align:center;padding:80px;background:#f9fafb;color:#111827"><h1>Something went wrong</h1><p>Please try again later.</p></body></html>';
});

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    error_log('[BoardTrack Fatal] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']);
});
// Configure session policy before calling session_start().
session_name(SESSION_NAME);

ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);
ini_set('session.use_strict_mode', '1');

$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();
// Load framework base classes.
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Router.php';
// Initialize database layer.
require_once CONFIG_PATH . '/database.php';
// Register lightweight app autoloader (controllers/models).
spl_autoload_register(function (string $class): void {

    $locations = [
        APP_PATH . '/controllers/' . $class . 'Controller.php',
        APP_PATH . '/models/'       . $class . '.php',
        APP_PATH . '/services/'     . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];

    foreach ($locations as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
// Boot HTTP application.
require_once CORE_PATH . '/App.php';
new App();
