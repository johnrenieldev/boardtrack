<?php
/**
 * Login Debug Test
 * Test your login credentials here
 */

// Load config
require_once '../config/config.php';
require_once '../config/database.php';

// Test credentials - try both emails
$test_emails = [
    'landlord@boardtrack.local' => 'Admin@1234',
    'newlandlord@example.com' => 'Password123',
];

try {
    $db = Database::getInstance();
    
    echo "<h2>Login Debug Test</h2>";
    
    foreach ($test_emails as $test_email => $test_password) {
        echo "<hr><h3>Testing: " . htmlspecialchars($test_email) . "</h3>";
        
        $sql = "SELECT id, full_name, email, password, role, status, email_verified FROM users WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$test_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "<p style='color:red'><strong>❌ User NOT found in database</strong></p>";
            continue;
        } else {
            echo "<p style='color:green'><strong>✅ User found in database</strong></p>";
            echo "<pre>";
            echo "ID: " . $user['id'] . "\n";
            echo "Name: " . $user['full_name'] . "\n";
            echo "Email: " . $user['email'] . "\n";
            echo "Role: " . $user['role'] . "\n";
            echo "Status: " . $user['status'] . "\n";
            echo "Email Verified: " . $user['email_verified'] . "\n";
            echo "Password Hash: " . substr($user['password'], 0, 20) . "...\n";
            echo "</pre>";
            
            // Test password
            if (password_verify($test_password, $user['password'])) {
                echo "<p style='color:green'><strong>✅ Password matches!</strong></p>";
            } else {
                echo "<p style='color:red'><strong>❌ Password does NOT match (tested: " . htmlspecialchars($test_password) . ")</strong></p>";
            }
            
            // Test status
            if ($user['email_verified'] == 1) {
                echo "<p style='color:green'><strong>✅ Email is verified</strong></p>";
            } else {
                echo "<p style='color:red'><strong>❌ Email is NOT verified</strong></p>";
            }
            
            if ($user['status'] === 'active') {
                echo "<p style='color:green'><strong>✅ Account is ACTIVE</strong></p>";
            } else {
                echo "<p style='color:orange'><strong>⚠️ Account status is: " . $user['status'] . "</strong></p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}
?>
<hr>
<p>Delete this file after testing: <code>test_login.php</code></p>
