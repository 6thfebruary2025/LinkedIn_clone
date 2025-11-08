<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['profile_image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
    $pathInDb = 'uploads/' . $filename;
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->execute([$pathInDb, $_SESSION['user_id']]);
    $_SESSION['profile_image'] = $pathInDb;
    echo json_encode(['success' => true, 'path' => $pathInDb]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move file']);
}
