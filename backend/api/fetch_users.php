<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT id, name, profile_image FROM users ORDER BY RAND() LIMIT 5");
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success'=>true,'users'=>$users]);
?>
