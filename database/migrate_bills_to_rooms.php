<?php
/**
 * One-time migration: bills per room instead of per tenant.
 * Run: php database/migrate_bills_to_rooms.php
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$db = Database::getInstance();

function columnExists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND COLUMN_NAME = :col"
    );
    $stmt->execute([':schema' => DB_NAME, ':table' => $table, ':col' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

try {
    if (!columnExists($db, 'bills', 'room_id')) {
        $db->exec("ALTER TABLE bills ADD COLUMN room_id INT NULL AFTER id");
        $db->exec("ALTER TABLE bills ADD INDEX idx_room_status (room_id, status)");
        echo "Added room_id column.\n";
    }

    $db->exec("UPDATE bills b
        INNER JOIN tenants t ON b.tenant_id = t.id
        SET b.room_id = t.room_id
        WHERE b.room_id IS NULL AND t.room_id IS NOT NULL");
    echo "Backfilled room_id from tenants.\n";

    // Drop old FK if exists, make tenant_id nullable, add room FK
    $db->exec("ALTER TABLE bills MODIFY COLUMN tenant_id INT NULL");

    $fks = $db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = 'bills' AND CONSTRAINT_TYPE = 'FOREIGN KEY'")
        ->fetchAll(PDO::FETCH_COLUMN);

    foreach ($fks as $fk) {
        if (stripos($fk, 'tenant') !== false) {
            $db->exec("ALTER TABLE bills DROP FOREIGN KEY `{$fk}`");
            echo "Dropped FK {$fk}.\n";
        }
    }

    if (!in_array('fk_bills_room', $fks, true)) {
        try {
            $db->exec("ALTER TABLE bills MODIFY COLUMN room_id INT NOT NULL");
            $db->exec("ALTER TABLE bills ADD CONSTRAINT fk_bills_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE");
            echo "Set room_id NOT NULL and added FK.\n";
        } catch (PDOException $e) {
            echo "Note: Could not set room_id NOT NULL — ensure all bills have a valid room_id. " . $e->getMessage() . "\n";
        }
    }

    echo "Migration complete.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
