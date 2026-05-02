<?php
/**
 * BoardTrack — App Configuration
 * config/config.php
 *
 * Central place for all app-wide constants.
 * Change these values to match your environment.
 */

// ─── APP ──────────────────────────────────────────────────────
define('APP_NAME',    'BoardTrack');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development');   // 'development' | 'production'

// Base URL — no trailing slash.
// XAMPP local:  'http://localhost/boardtrack/public'
// Live server:  'https://yourdomain.com'
define('BASE_URL', 'http://localhost/boardtrack/public');

// ─── PATHS ────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CORE_PATH',    ROOT_PATH . '/core');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('UPLOAD_PATH',  PUBLIC_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');

// Upload sub-folders
define('UPLOAD_IDS',      UPLOAD_PATH . '/ids');
define('UPLOAD_PAYMENTS', UPLOAD_PATH . '/payments');

// ─── DATABASE ─────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');               // Change on production
define('DB_NAME',    'boardtrack');
define('DB_CHARSET', 'utf8mb4');

// ─── SESSION ──────────────────────────────────────────────────
define('SESSION_NAME',     'boardtrack_sess');
define('SESSION_LIFETIME', 7200);       // 2 hours in seconds

// ─── FILE UPLOAD RULES ────────────────────────────────────────
define('UPLOAD_MAX_SIZE',    2 * 1024 * 1024); // 2 MB in bytes
define('UPLOAD_ALLOWED',     ['image/jpeg', 'image/png']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png']);

// ─── EMAIL (PHPMailer / Gmail SMTP) ───────────────────────────
// Set MAIL_ENABLED to false during local development
// to suppress actual email sending (mock mode)
define('MAIL_ENABLED',   false);
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USERNAME',  'yourapp@gmail.com');   // Change this
define('MAIL_PASSWORD',  'your_app_password');   // Gmail App Password
define('MAIL_FROM',      'yourapp@gmail.com');
define('MAIL_FROM_NAME', APP_NAME);

// ─── SECURITY ─────────────────────────────────────────────────
// Email verification token expiry (seconds) — 24 hours
define('VERIFY_TOKEN_TTL', 86400);

// Password reset token expiry (seconds) — 1 hour
define('RESET_TOKEN_TTL', 3600);

// ─── DEFAULTS ─────────────────────────────────────────────────
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_METHOD',     'index');

// ─── ERROR DISPLAY ────────────────────────────────────────────
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}