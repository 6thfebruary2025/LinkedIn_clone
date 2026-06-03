<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) { 
    echo json_encode(['success'=>false,'error'=>'Not logged in']); 
    exit; 
}

$content = trim($_POST['content'] ?? '');
$imagePath = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'post_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $target = $dir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $imagePath = 'uploads/' . $filename;
    }
}

if ($content === '' && !$imagePath) { 
    echo json_encode(['success'=>false,'error'=>'Empty post']); 
    exit; 
}

$stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $_SESSION['user_id'], $content, $imagePath);
$stmt->execute();

echo json_encode(['success'=>true]);
?>
