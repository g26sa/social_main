<?php
session_start();
require_once __DIR__ . '/db.php';
sync_session_admin_from_db();

if (!isset($_SESSION['id'])) {
    header('Location: auth.php');
    exit;
}
if ((int)($_SESSION['admin'] ?? 0) !== 1) {
    header('Location: accaunt.php');
    exit;
}

if (!isset($_GET['id'])) {
    die('ID не передан.');
}

$user_id = (int)$_GET['id'];
if ($user_id <= 0 || $user_id === (int)$_SESSION['id']) {
    die('Некорректный пользователь.');
}

// Комментарии пользователя к чужим постам
$stmt = $conn->prepare('DELETE FROM comments WHERE user_id = ?');
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

// Комментарии к постам этого пользователя
$sqlJoin = 'DELETE c FROM comments c INNER JOIN posts p ON c.post_id = p.id WHERE p.user_id = ?';
$stmt2 = $conn->prepare($sqlJoin);
if ($stmt2) {
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->close();
}

$sql_delete_posts = 'DELETE FROM posts WHERE user_id = ?';
$stmt_delete_posts = $conn->prepare($sql_delete_posts);
if (!$stmt_delete_posts) {
    die('Ошибка БД: ' . $conn->error);
}
$stmt_delete_posts->bind_param('i', $user_id);

if (!$stmt_delete_posts->execute()) {
    echo 'Ошибка при удалении постов: ' . htmlspecialchars($conn->error);
    $stmt_delete_posts->close();
    exit;
}
$stmt_delete_posts->close();

$stmt_msg = $conn->prepare('DELETE FROM messages WHERE user_id = ?');
if ($stmt_msg) {
    $stmt_msg->bind_param('i', $user_id);
    $stmt_msg->execute();
    $stmt_msg->close();
}

$stmt_dm = $conn->prepare('DELETE FROM direct_messages WHERE sender_id = ? OR receiver_id = ?');
if ($stmt_dm) {
    $stmt_dm->bind_param('ii', $user_id, $user_id);
    $stmt_dm->execute();
    $stmt_dm->close();
}

$sql_delete_user = 'DELETE FROM users WHERE id = ?';
$stmt_delete_user = $conn->prepare($sql_delete_user);
if (!$stmt_delete_user) {
    die('Ошибка БД');
}
$stmt_delete_user->bind_param('i', $user_id);
if ($stmt_delete_user->execute()) {
    $stmt_delete_user->close();
    header('Location: allusers.php');
    exit;
}

echo 'Ошибка при удалении пользователя: ' . htmlspecialchars($conn->error);
$stmt_delete_user->close();
