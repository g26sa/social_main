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
$post_id = (int)($_POST['post_id'] ?? 0);
$text = trim((string)($_POST['text'] ?? ''));

if ($post_id <= 0 || $text === '') {
    echo json_encode(['success' => false, 'message' => 'Bad input']);
    exit;
}

// Ожидаем таблицу comments с полями: post_id, user_id, text, date
$stmt = $connect->prepare("INSERT INTO comments (post_id, user_id, text, date) VALUES (?, ?, ?, NOW())");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}

$stmt->bind_param('iis', $post_id, $user_id, $text);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => (bool)$ok]);
?>
