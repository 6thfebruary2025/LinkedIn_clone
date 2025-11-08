<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }

$skills = trim($_POST['skills'] ?? '');
$stmt = $pdo->prepare("UPDATE users SET skills=? WHERE id=?");
$stmt->execute([$skills, $_SESSION['user_id']]);
echo json_encode(['success'=>true]);
