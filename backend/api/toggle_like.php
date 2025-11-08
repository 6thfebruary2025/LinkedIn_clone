<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$post_id = $data['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
    exit;
}

// Check if liked
$stmt = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // Unlike
    $conn->query("DELETE FROM post_likes WHERE post_id = $post_id AND user_id = $user_id");
    $status = 'unliked';
} else {
    // Like
    $stmt2 = $conn->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
    $stmt2->bind_param("ii", $post_id, $user_id);
    $stmt2->execute();
    $status = 'liked';
}

// Get total likes
$countRes = $conn->query("SELECT COUNT(*) AS total FROM post_likes WHERE post_id = $post_id");
$count = $countRes->fetch_assoc()['total'];

echo json_encode(['success' => true, 'status' => $status, 'likes' => $count]);
