<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance();
    
    echo "Starting migration: Gender Support\n";
    
    // Add gender to tenants
    echo "Adding gender to tenants table...\n";
    $db->query("ALTER TABLE tenants ADD COLUMN gender ENUM('male', 'female') NULL AFTER room_type_preference");
    
    // Add allowed_gender to rooms
    echo "Adding allowed_gender to rooms table...\n";
    $db->query("ALTER TABLE rooms ADD COLUMN allowed_gender ENUM('male', 'female', 'any') DEFAULT 'any' AFTER room_type");
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
