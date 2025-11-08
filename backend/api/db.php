<?php
// Database configuration
$host = 'localhost';     // usually 'localhost'
$dbname = 'linkedin_clone'; // your database name
$username = 'root';      // your MySQL username
$password = '';          // your MySQL password (often empty for localhost)

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set UTF-8 encoding
$conn->set_charset('utf8mb4');

// Optionally, start session if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
