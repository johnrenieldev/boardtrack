<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$db = Database::getInstance();
$stmt = $db->prepare(
    "SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = 'users' AND COLUMN_NAME = 'gcash_qr_path'"
);
$stmt->execute([':schema' => DB_NAME]);
if ((int) $stmt->fetchColumn() === 0) {
    $db->exec("ALTER TABLE users ADD COLUMN gcash_qr_path VARCHAR(255) DEFAULT NULL");
    echo "Added gcash_qr_path column.\n";
} else {
    echo "Column gcash_qr_path already exists.\n";
}
