<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Попробуем удалить загруженный файл, если он существует.
$prev = $_SESSION['avatar'] ?? null;
if (is_string($prev) && $prev !== '' && file_exists(__DIR__ . '/' . $prev)) {
    @unlink(__DIR__ . '/' . $prev);
}

$_SESSION['avatar'] = null;

echo json_encode([
    'success' => true,
    'path' => 'avatars/placeholder.svg'
]);
?>
