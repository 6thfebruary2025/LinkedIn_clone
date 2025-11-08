<?php
session_start();
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$comment_id = intval($data['comment_id']);
$user_id = $_SESSION['user_id'];

$check = $conn->prepare("SELECT * FROM comment_likes WHERE comment_id=? AND user_id=?");
$check->bind_param("ii", $comment_id, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  $del = $conn->prepare("DELETE FROM comment_likes WHERE comment_id=? AND user_id=?");
  $del->bind_param("ii", $comment_id, $user_id);
  $del->execute();
  $status = "unliked";
} else {
  $ins = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
  $ins->bind_param("ii", $comment_id, $user_id);
  $ins->execute();
  $status = "liked";
}

$countRes = $conn->prepare("SELECT COUNT(*) AS likes FROM comment_likes WHERE comment_id=?");
$countRes->bind_param("i", $comment_id);
$countRes->execute();
$likes = $countRes->get_result()->fetch_assoc()['likes'];

echo json_encode(['success'=>true, 'status'=>$status, 'likes'=>$likes]);
?>
