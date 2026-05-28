<?php
/**
 * Application configuration bootstrap.
 *
 * Defines environment-aware constants used across runtime code.
 */

/**
 * Return environment variable value with fallback default.
 */
function envValue(string $key, $default = null)
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

/**
 * Return environment variable as boolean with fallback default.
 */
function envBool(string $key, bool $default = false): bool
{
    $value = envValue($key, null);
    if ($value === null) {
        return $default;
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
}

// Application metadata and environment.
define('APP_NAME',    'BoardTrack');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     envValue('APP_ENV', 'production'));   // 'development' | 'production'

// Base URL without trailing slash.
// Set APP_BASE_URL in production for a stable canonical URL.
$configuredBaseUrl = envValue('APP_BASE_URL', '');
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$publicPath = str_replace('/index.php', '', $script);
$detectedUrl = "$protocol://$host$publicPath";

if ($configuredBaseUrl !== '') {
    define('BASE_URL', rtrim($configuredBaseUrl, '/'));
} elseif (PHP_SAPI === 'cli' || empty($host)) {
    define('BASE_URL', envValue('APP_BASE_URL_CLI', 'http://localhost/boardtrack/public'));
} else {
    define('BASE_URL', rtrim($detectedUrl, '/'));
}
// Filesystem paths.
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CORE_PATH',    ROOT_PATH . '/core');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('UPLOAD_PATH',  PUBLIC_PATH . '/uploads');
define('LOG_PATH',     ROOT_PATH . '/logs');

// Upload directories.
define('UPLOAD_IDS',      UPLOAD_PATH . '/ids');
define('UPLOAD_PAYMENTS', UPLOAD_PATH . '/payments');
define('UPLOAD_GCASH',    UPLOAD_PATH . '/gcash');
// Database connection settings.
define('DB_HOST',    envValue('DB_HOST', 'localhost'));
define('DB_USER',    envValue('DB_USER', 'root'));
define('DB_PASS',    envValue('DB_PASS', ''));
define('DB_NAME',    envValue('DB_NAME', 'boardtrack'));
define('DB_CHARSET', envValue('DB_CHARSET', 'utf8mb4'));
// Session settings.
define('SESSION_NAME',     'boardtrack_sess');
define('SESSION_LIFETIME', 7200);       // 2 hours in seconds
// Upload validation rules.
define('UPLOAD_MAX_SIZE',    2 * 1024 * 1024); // 2 MB in bytes
define('UPLOAD_ALLOWED',     ['image/jpeg', 'image/png']);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png']);
// Outbound email configuration.
if (is_file(__DIR__ . '/mail.php')) {
    require_once __DIR__ . '/mail.php';
} else {
    define('MAIL_ENABLED',   envBool('MAIL_ENABLED', false));
    define('MAIL_HOST',      envValue('MAIL_HOST', 'smtp.hostinger.com'));
    define('MAIL_PORT',      (int) envValue('MAIL_PORT', 465));
    define('MAIL_USERNAME',  envValue('MAIL_USERNAME', ''));
    define('MAIL_PASSWORD',  envValue('MAIL_PASSWORD', ''));
    define('MAIL_FROM',      envValue('MAIL_FROM', MAIL_USERNAME));
    define('MAIL_FROM_NAME', envValue('MAIL_FROM_NAME', APP_NAME));
}
// Security token lifetimes.
// Email verification token expiry (24 hours).
define('VERIFY_TOKEN_TTL', 86400);

// Password reset token expiry (1 hour).
define('RESET_TOKEN_TTL', 3600);
// Router defaults.
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_METHOD',     'index');
// Error display policy by environment.
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}