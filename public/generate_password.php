<?php
if (APP_ENV !== 'development') {
    http_response_code(404);
    die('Not found');
}

// Temporary script to generate a hashed password
if (isset($_GET['password'])) {
    $password = $_GET['password'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    echo "<p>Original Password: <strong>{$password}</strong></p>";
    echo "<p>Hashed Password: <strong>{$hashedPassword}</strong></p>";
} else {
    echo "<form method='GET'>
            <label for='password'>Enter Password:</label>
            <input type='text' id='password' name='password' required>
            <button type='submit'>Generate Hash</button>
          </form>";
}
?>