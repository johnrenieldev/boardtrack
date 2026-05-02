<?php
// Generate a proper bcrypt hash for the password
$password = 'Password123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Bcrypt Hash:</strong></p>";
echo "<pre style='background:#f0f0f0; padding:10px; border-radius:5px;'>" . htmlspecialchars($hash) . "</pre>";

echo "<h3>SQL Query to Update the Landlord Account:</h3>";
echo "<pre style='background:#f0f0f0; padding:10px; border-radius:5px;'>";
echo "UPDATE users SET password = '" . $hash . "' WHERE email = 'newlandlord@example.com';\n";
echo "</pre>";

echo "<p><strong>After running this SQL:</strong></p>";
echo "<ul>";
echo "<li>Email: <code>newlandlord@example.com</code></li>";
echo "<li>Password: <code>Password123</code></li>";
echo "</ul>";
?>
