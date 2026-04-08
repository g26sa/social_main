<?php
session_start();
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/chat_support_lib.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    exit;
}

$myId = (int)$_SESSION['id'];
$text = trim((string)($_POST['message'] ?? ''));
if ($text === '') {
    exit;
}

$adminIds = chat_support_admin_ids($connect);
if ($adminIds === []) {
    exit;
}

$isAdmin = chat_user_is_admin($connect, $myId);

if ($isAdmin) {
    $toUser = (int)($_POST['to_user_id'] ?? 0);
    if ($toUser <= 0 || $toUser === $myId) {
        exit;
    }
    if (chat_user_is_admin($connect, $toUser)) {
        exit;
    }
    $senderId = $myId;
    $receiverId = $toUser;
} else {
    $primary = chat_primary_admin_id($connect);
    if ($primary <= 0) {
        exit;
    }
    $senderId = $myId;
    $receiverId = $primary;
}

$stmt = $connect->prepare(
    'INSERT INTO direct_messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)'
);
if (!$stmt) {
    exit;
}

$stmt->bind_param('iis', $senderId, $receiverId, $text);
$stmt->execute();
$stmt->close();
