<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$postId = (int)($data['post_id'] ?? 0);
if (!$postId || empty($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
$stmt = $pdo->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
$stmt->execute([$postId, $_SESSION['user_id']]);
echo json_encode(['success'=>true]);
