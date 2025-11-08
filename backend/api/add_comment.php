<?php
session_start();
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$post_id = isset($data['post_id']) ? intval($data['post_id']) : null;
$parent_id = isset($data['parent_id']) ? intval($data['parent_id']) : null;
$text = trim($data['text']);
$user_id = $_SESSION['user_id'];

if (!$text) {
  echo json_encode(['success'=>false, 'error'=>'Empty comment']);
  exit;
}

if (!$post_id && !$parent_id) {
  echo json_encode(['success'=>false, 'error'=>'Invalid target']);
  exit;
}

// If it's a reply, fetch its post_id
if (!$post_id && $parent_id) {
  $res = $conn->prepare("SELECT post_id FROM comments WHERE id=?");
  $res->bind_param("i", $parent_id);
  $res->execute();
  $r = $res->get_result()->fetch_assoc();
  $post_id = $r['post_id'];
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, parent_id, user_id, text, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiis", $post_id, $parent_id, $user_id, $text);
$ok = $stmt->execute();

echo json_encode(['success'=>$ok]);
?>
