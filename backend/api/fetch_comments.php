<?php
session_start();
require 'db.php';

$post_id = intval($_GET['post_id']);
$user_id = $_SESSION['user_id'];

// Fetch all comments for this post
$sql = "SELECT c.id, c.user_id, c.post_id, c.parent_id, c.text, c.created_at,
               u.name AS user_name, u.profile_image,
               (SELECT COUNT(*) FROM comment_likes WHERE comment_id=c.id) AS like_count,
               (SELECT COUNT(*) FROM comment_likes WHERE comment_id=c.id AND user_id=?) AS user_liked
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id=?
        ORDER BY c.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$res = $stmt->get_result();

$comments = [];
while($row = $res->fetch_assoc()){
  $comments[$row['id']] = $row;
  $comments[$row['id']]['replies'] = [];
}

// nest replies
$tree = [];
foreach ($comments as $id => &$comment) {
  if ($comment['parent_id']) {
    $comments[$comment['parent_id']]['replies'][] = &$comment;
  } else {
    $tree[] = &$comment;
  }
}

echo json_encode(['success'=>true, 'comments'=>$tree]);
?>
