<?php
session_start();
require_once "db.php";
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

$query = "
SELECT p.*, u.name AS user_name, u.profile_image,
(SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) AS like_count,
(SELECT COUNT(*) FROM post_likes WHERE post_id = p.id AND user_id = ?) AS user_liked
FROM posts p
JOIN users u ON p.user_id = u.id
ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$posts = [];
while ($row = $res->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode(['success' => true, 'posts' => $posts]);
