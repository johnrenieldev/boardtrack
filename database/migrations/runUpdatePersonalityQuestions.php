<?php
/**
 * Run Personality Questions Update Migration
 * Execute this file to update the personality questions in the database
 */

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

// Load config
require_once ROOT_PATH . '/config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Read the SQL migration file
    $sql = file_get_contents(__DIR__ . '/updatePersonalityQuestions.sql');

    // Execute the migration
    $pdo->exec($sql);

    echo "SUCCESS: Personality questions updated successfully!\n";
    echo "The database now contains 10 new social preference-focused questions.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

