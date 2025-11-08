<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$name || !$email) {
    echo json_encode(['success' => false, 'error' => 'Name and email required']);
    exit;
}

if ($password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
    $stmt->execute([$name, $email, $hash, $_SESSION['user_id']]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->execute([$name, $email, $_SESSION['user_id']]);
}
$_SESSION['user_name'] = $name;
echo json_encode(['success' => true]);
