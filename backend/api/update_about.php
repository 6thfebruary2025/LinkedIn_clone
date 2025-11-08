<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

$about = trim($_POST['about'] ?? '');
$stmt = $pdo->prepare("UPDATE users SET about=? WHERE id=?");
$stmt->execute([$about, $_SESSION['user_id']]);
echo json_encode(['success'=>true]);
