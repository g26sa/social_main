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
    header('Location: allcomments.php');
    exit;
}

$comment_id = (int)$_GET['id'];
if ($comment_id <= 0) {
    header('Location: allcomments.php');
    exit;
}

$stmt = $conn->prepare('DELETE FROM comments WHERE id = ?');
if ($stmt) {
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: allcomments.php');
exit;
