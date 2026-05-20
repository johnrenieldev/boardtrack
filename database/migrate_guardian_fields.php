<?php
/**
 * Add guardian_name, guardian_email, guardian_purpose to tenants table.
 * Run: php database/migrate_guardian_fields.php
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$db = Database::getInstance();

$columns = [
    'guardian_name'    => "VARCHAR(120) DEFAULT NULL",
    'guardian_email'   => "VARCHAR(255) DEFAULT NULL",
    'guardian_purpose' => "TEXT DEFAULT NULL",
];

foreach ($columns as $name => $definition) {
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = 'tenants' AND COLUMN_NAME = :col"
    );
    $stmt->execute([':schema' => DB_NAME, ':col' => $name]);
    if ((int) $stmt->fetchColumn() === 0) {
        $db->exec("ALTER TABLE tenants ADD COLUMN {$name} {$definition}");
        echo "Added tenants.{$name}\n";
    } else {
        echo "Column tenants.{$name} already exists.\n";
    }
}

echo "Guardian migration complete.\n";
