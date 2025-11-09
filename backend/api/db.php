<?php
// db.php - uses environment variables when available, otherwise falls back to local defaults

// Prefer Railway-style env vars, fall back to sensible local defaults
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQL_URL') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'linkedin_clone';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: ''; 
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

// Create connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set UTF-8 encoding
$conn->set_charset('utf8mb4');

// Ensure session started if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
