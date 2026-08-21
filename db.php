<?php
// Database Configuration
$host = 'localhost';
$db   = 'vsms_db';
$user = 'root';
$pass = '';

// Establish MySQL Connection using MySQLi
$conn = mysqli_connect($host, $user, $pass, $db);

// Check if connection failed
if (!$conn) {
    die("<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:5px; margin:20px;'>
        <h3>Database Connection Error</h3>
        <p>" . htmlspecialchars(mysqli_connect_error()) . "</p>
        <p><strong>Note:</strong> Make sure MySQL is running in your XAMPP Control Panel and you have imported <code>database.sql</code>.</p>
    </div>");
}

// Set character set to utf8mb4 for full Unicode support
mysqli_set_charset($conn, "utf8mb4");
?>