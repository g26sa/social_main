<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$userId = (int)$_SESSION['id'];
$postId = (int)($_GET['post_id'] ?? 0);

if ($postId <= 0) {
    echo json_encode([]);
    exit;
}

$check = $connect->prepare('SELECT id FROM posts WHERE id = ? AND user_id = ?');
if (!$check) {
    echo json_encode([]);
    exit;
}
$check->bind_param('ii', $postId, $userId);
$check->execute();
$ok = $check->get_result()->num_rows > 0;
$check->close();

if (!$ok) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT c.text AS body, c.date AS created,
        COALESCE(NULLIF(TRIM(u.us_name), ''), u.login) AS author
        FROM comments c
        INNER JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.date ASC";

$stmt = $connect->prepare($sql);
if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param('i', $postId);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) {
    $out[] = [
        'author' => $row['author'],
        'body' => $row['body'],
        'created' => $row['created'],
    ];
}
$stmt->close();

echo json_encode($out, JSON_UNESCAPED_UNICODE);
