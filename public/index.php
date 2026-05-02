<?php
/**
 * BoardTrack — Application Entry Point
 * public/index.php
 *
 * ALL requests pass through here.
 * Load order matters — do not rearrange.
 */

// ─── 1. CONFIG ────────────────────────────────────────────────
// Must load first — defines constants used everywhere
require_once dirname(__DIR__) . '/config/config.php';

// ─── 2. SESSION ───────────────────────────────────────────────
// Configure session before starting it
session_name(SESSION_NAME);

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);   // Prevent JS access to cookie
ini_set('session.use_strict_mode', 1);  // Reject unrecognized session IDs
ini_set('session.cookie_samesite', 'Lax');

// Only set Secure flag on HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// ─── 3. CORE CLASSES ──────────────────────────────────────────
require_once CORE_PATH . '/Model.php';
require_once CORE_PATH . '/Controller.php';
require_once CORE_PATH . '/Router.php';

// ─── 4. DATABASE ──────────────────────────────────────────────
require_once CONFIG_PATH . '/database.php';

// ─── 5. AUTOLOADER (controllers + models) ────────────────────
// Simple PSR-0-style autoloader for app classes.
// Checks controllers/ then models/ directories.
spl_autoload_register(function (string $class): void {

    $locations = [
        APP_PATH . '/controllers/' . $class . 'Controller.php',
        APP_PATH . '/model/'       . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];

    foreach ($locations as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─── 6. BOOT APPLICATION ──────────────────────────────────────
require_once CORE_PATH . '/App.php';
new App();