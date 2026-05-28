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
if (!defined('APP_NAME')) define('APP_NAME',    'BoardTrack');
if (!defined('APP_VERSION')) define('APP_VERSION', '1.0.0');
if (!defined('APP_ENV')) define('APP_ENV',     'development');

// Base URL detection
if (!defined('BASE_URL')) {
    $configuredBaseUrl = envValue('APP_BASE_URL', '');
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $publicPath = str_replace('/index.php', '', $script);
    $detectedUrl = "$protocol://$host$publicPath";

    // Priority: configured > environment variable > Hostinger domain > CLI > auto-detect
    if ($configuredBaseUrl !== '') {
        define('BASE_URL', rtrim($configuredBaseUrl, '/'));
    } elseif (strpos($host, 'bsit2a.com') !== false) {
        // Hardcoded for Hostinger production
        define('BASE_URL', 'https://boardtrack.bsit2a.com');
    } elseif (PHP_SAPI === 'cli' || empty($host)) {
        define('BASE_URL', envValue('APP_BASE_URL_CLI', 'http://localhost/boardtrack/public'));
    } else {
        define('BASE_URL', rtrim($detectedUrl, '/'));
    }
}

// Filesystem paths - Check if already defined by root index.php
if (!defined('ROOT_PATH'))   define('ROOT_PATH',    dirname(__DIR__));
if (!defined('APP_PATH'))    define('APP_PATH',     ROOT_PATH . '/app');
if (!defined('CORE_PATH'))   define('CORE_PATH',    ROOT_PATH . '/core');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH',  ROOT_PATH . '/config');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH',  ROOT_PATH . '/public');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH',  PUBLIC_PATH . '/uploads');
if (!defined('LOG_PATH'))    define('LOG_PATH',     ROOT_PATH . '/logs');

// Upload directories.
define('UPLOAD_IDS',      UPLOAD_PATH . '/ids');
define('UPLOAD_PAYMENTS', UPLOAD_PATH . '/payments');
define('UPLOAD_GCASH',    UPLOAD_PATH . '/gcash');
// Database connection settings (Updated for Hostinger).
define('DB_HOST',    envValue('DB_HOST', 'localhost'));
define('DB_USER',    envValue('DB_USER', 'u536627044_boardtrack'));
define('DB_PASS',    envValue('DB_PASS', 'Support098.'));
define('DB_NAME',    envValue('DB_NAME', 'u536627044_boardtrack'));
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