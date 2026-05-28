<?php
/**
 * BoardTrack — Hostinger Root Entry Point
 */

// Define absolute paths for Hostinger environment
define('ROOT_PATH', __DIR__);
define('APP_PATH',    ROOT_PATH . '/app');
define('CORE_PATH',   ROOT_PATH . '/core');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Set URL if not present
if (!isset($_GET['url'])) {
    $_GET['url'] = '';
}

// Manually load the public index (without redirecting)
require_once PUBLIC_PATH . '/index.php';
