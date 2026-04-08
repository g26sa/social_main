<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$user_id = (int)$_SESSION['id'];
$title = trim((string)($_POST['title'] ?? ''));
$text = trim((string)($_POST['text'] ?? ''));

if ($title === '') {
    $title = 'Пост';
}

if ($text === '' && empty($_FILES['image']['name'])) {
    echo json_encode(['success' => false, 'message' => 'Empty post']);
    exit;
}

$imagePath = null;
if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed, true)) {
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $newName = "post_" . $user_id . "_" . time() . "." . $ext;
        $targetFsPath = $uploadDir . '/' . $newName;
        if (move_uploaded_file($file['tmp_name'], $targetFsPath)) {
            $imagePath = 'uploads/' . $newName;
        }
    }
}

$stmt = $connect->prepare(
    "INSERT INTO posts (user_id, title, content, image_url, created_at) VALUES (?, ?, ?, ?, NOW())"
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

$img = $imagePath !== null ? $imagePath : '';
$stmt->bind_param('isss', $user_id, $title, $text, $img);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => (bool)$ok]);
