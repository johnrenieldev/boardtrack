<?php
/**
 * Migration: Unify Tenant Statuses
 * File: database/migrations/update_tenant_approval_status.php
 */
require_once __DIR__ . '/../../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. Migrate existing statuses to new unified values
    // unverified -> pending (waiting landlord review)
    $pdo->exec("UPDATE users SET status = 'pending' WHERE status = 'unverified'");
    
    // active / waiting_list -> approved (accepted)
    $pdo->exec("UPDATE users SET status = 'approved' WHERE status = 'active' OR status = 'waiting_list'");
    
    // 2. Modify status column ENUM to pending, approved, rejected, moved_out
    $pdo->exec("ALTER TABLE users MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'moved_out') DEFAULT 'pending'");

    echo "Successfully unified user statuses and modified users table schema!\n";
} catch (PDOException $e) {
    echo "Error updating tenant statuses: " . $e->getMessage() . "\n";
}
