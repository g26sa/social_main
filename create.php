<?php
session_start();
include_once __DIR__ . '/db.php';

if (!isset($_SESSION['id'])) {
    header('Location: auth.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: createpost.php');
    exit;
}

$title = trim((string)($_POST['title'] ?? ''));
$content = trim((string)($_POST['content'] ?? ''));
$user_id = (int)$_SESSION['id'];
$image_url = '';

if ($title === '' || $content === '') {
    die('Заполните заголовок и текст.');
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = __DIR__ . '/uploads/';
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image']['name']));
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_url = 'uploads/' . $file_name;
    }
}

$stmt = $conn->prepare(
    'INSERT INTO posts (title, content, image_url, user_id, created_at) VALUES (?, ?, ?, ?, NOW())'
);
if (!$stmt) {
    die('Ошибка БД: ' . $conn->error);
}

$stmt->bind_param('sssi', $title, $content, $image_url, $user_id);
if ($stmt->execute()) {
    header('Location: posts.php');
    exit();
}

echo 'Ошибка: ' . $stmt->error;
$stmt->close();
