<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
$stmt = $pdo->query("SELECT id, name, profile_image FROM users ORDER BY RAND() LIMIT 5");
$users = $stmt->fetchAll();
echo json_encode(['success'=>true,'users'=>$users]);
