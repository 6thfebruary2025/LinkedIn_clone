<?php

session_start();
include 'db.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'error' => 'Not logged in']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$parent_id = intval($data['parent_id']);
$text = trim($data['text'] ?? '');

if ($text === '') {
  echo json_encode(['success' => false, 'error' => 'Empty reply']);
  exit;
}

// Find post_id from parent comment
$res = $conn->query("SELECT post_id FROM comments WHERE id=$parent_id");
if (!$res->num_rows) {
  echo json_encode(['success' => false, 'error' => 'Parent comment not found']);
  exit;
}
$post_id = $res->fetch_assoc()['post_id'];

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, parent_id, text) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $post_id, $user_id, $parent_id, $text);
$stmt->execute();

if ($stmt->affected_rows > 0)
  echo json_encode(['success' => true]);
else
  echo json_encode(['success' => false, 'error' => 'Insert failed']);
?>
