<?php
/**
 * Application front controller.
 *
 * Initializes configuration, session security, core dependencies, and routing.
 */
// Load application configuration and constants first.
require_once dirname(__DIR__) . '/config/config.php';
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