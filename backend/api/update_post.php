<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postId = $input['post_id'] ?? null;
$content = trim($input['content'] ?? '');

if (!$postId || !$content) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$content, $postId, $_SESSION['user_id']]);
echo json_encode(['success' => true]);
