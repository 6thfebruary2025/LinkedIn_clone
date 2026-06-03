<?php
// api/config.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Grab database credentials automatically from your hosting platform (Railway/Render)
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQL_URL') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'linkedin_clone';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: ''; 
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

// 2. Set up the MySQLi connection (Fixes your feed, likes, and comments files)
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}
$conn->set_charset('utf8mb4');

// 3. Set up the PDO connection (Fixes your login, signup, and profile files)
try {
    $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
