<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change to your MySQL username
define('DB_PASS', '');             // Change to your MySQL password
define('DB_NAME', 'auth_app_db');

// Cookie settings
define('COOKIE_EMAIL', 'remember_email');
define('COOKIE_LAST_LOGIN', 'last_login_time');
define('COOKIE_EXPIRY', 30 * 24 * 60 * 60); // 30 days

// Connect to database
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
