<?php
// Railway environment variables
$DB_HOST = getenv('MYSQLHOST') ?: 'localhost';
$DB_NAME = getenv('MYSQL_DATABASE') ?: 'your_db_name';
$DB_USER = getenv('MYSQLUSER') ?: 'root';
$DB_PASS = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

