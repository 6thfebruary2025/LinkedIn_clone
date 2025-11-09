<?php
// config.php - Railway-ready

// Fetch environment variables set by Railway
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_URL');       // Database host
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE'); // Database name
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER');     // DB username
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD'); // DB password
$port = getenv('MYSQLPORT') ?: 3306;                     // Default MySQL port

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8mb4");

// Now you can use $conn in all your backend API files (fetch_posts.php, add_comment.php, etc.)
<<<<<<< HEAD
?>
=======
?>
>>>>>>> a0e986e (Update config and feed files)
