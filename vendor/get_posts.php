<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json; charset=utf-8');

$user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $connect->prepare(
    "SELECT p.id,
            COALESCE(p.title, '') AS title,
            COALESCE(p.content, '') AS text,
            COALESCE(p.image_url, '') AS image,
            p.created_at AS post_date,
            (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comments_count
     FROM posts p
     WHERE p.user_id = ?
     ORDER BY p.created_at DESC"
);

if (!$stmt) {
    echo json_encode([]);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = [];

while ($row = $result->fetch_assoc()) {
    $row = array_change_key_case($row, CASE_LOWER);

    $title = trim((string)($row['title'] ?? ''));
    $body = trim((string)($row['text'] ?? ''));
    $img = trim((string)($row['image'] ?? ''));

    // Полностью пустые записи в ленте не показываем
    if ($title === '' && $body === '' && $img === '') {
        continue;
    }

    // Заголовок для отображения: из поля title или первая строка текста
    if ($title === '') {
        if ($body !== '') {
            $parts = preg_split('/\R/u', $body, 2);
            $line = trim((string)($parts[0]));
            if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                $title = mb_strlen($line) > 100 ? mb_substr($line, 0, 100) . '…' : $line;
            } else {
                $title = strlen($line) > 100 ? substr($line, 0, 100) . '…' : $line;
            }
        } else {
            $title = 'Пост';
        }
    }

    $row['title'] = $title;
    $row['date'] = $row['post_date'] ?? '';
    unset($row['post_date']);

    $posts[] = $row;
}

$stmt->close();

echo json_encode($posts, JSON_UNESCAPED_UNICODE);
