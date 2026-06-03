<?php
// config.php - Unified Database Connection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQL_URL') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'linkedin_clone';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: ''; 
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// Alias to fix any files using $conn or $db
$db = $conn; 
?>
