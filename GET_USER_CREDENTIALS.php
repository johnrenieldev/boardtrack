<?php
// Get all user credentials from database
require_once __DIR__ . '/config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "BOARDTRACK USER CREDENTIALS\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Get all users
$sql = "SELECT id, email, name, role, status FROM users ORDER BY role DESC, created_at ASC";
$result = $conn->query($sql);

$landlords = [];
$tenants = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['role'] === 'landlord') {
            $landlords[] = $row;
        } else {
            $tenants[] = $row;
        }
    }
}

// Display landlords
echo "👨‍💼 LANDLORDS:\n";
echo str_repeat("─", 63) . "\n";
if (count($landlords) > 0) {
    foreach ($landlords as $idx => $user) {
        echo ($idx + 1) . ". " . $user['name'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Status: " . $user['status'] . "\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "\n";
    }
} else {
    echo "No landlords found.\n\n";
}

// Display tenants
echo "\n👤 TENANTS:\n";
echo str_repeat("─", 63) . "\n";
if (count($tenants) > 0) {
    foreach ($tenants as $idx => $user) {
        echo ($idx + 1) . ". " . $user['name'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Status: " . $user['status'] . "\n";
        echo "   ID: " . $user['id'] . "\n";
        echo "\n";
    }
} else {
    echo "No tenants found.\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "⚠️ NOTE: Passwords are hashed and cannot be retrieved!\n";
echo "If you forgot password, use 'Forgot Password' link to reset.\n";
echo "═══════════════════════════════════════════════════════════════\n";

$conn->close();
?>
