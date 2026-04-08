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
    die('ID поста не передан.');
}

$post_id = (int)$_GET['id'];

$delComments = $conn->prepare('DELETE FROM comments WHERE post_id = ?');
if ($delComments) {
    $delComments->bind_param('i', $post_id);
    $delComments->execute();
    $delComments->close();
}

$delPost = $conn->prepare('DELETE FROM posts WHERE id = ?');
if ($delPost) {
    $delPost->bind_param('i', $post_id);
    if ($delPost->execute()) {
        $delPost->close();
        header('Location: allposts.php');
        exit;
    }
    echo 'Ошибка при удалении поста: ' . htmlspecialchars($conn->error);
    $delPost->close();
} else {
    echo 'Ошибка БД';
}
