<?php
include_once __DIR__ . '/db.php';

$sql = "SELECT id, title, content, image_url FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    echo '<p>Не удалось загрузить посты.</p>';
    return;
}

if ($result->num_rows > 0) {
    $shown = 0;
    while ($row = $result->fetch_assoc()) {
        $row = array_change_key_case($row, CASE_LOWER);

        $title = trim((string)($row['title'] ?? ''));
        $full = (string)($row['content'] ?? '');
        $body = trim($full);
        $img = trim((string)($row['image_url'] ?? ''));

        if ($title === '' && $body === '' && $img === '') {
            continue;
        }

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

        $hasImg = $img !== '';

        echo '<div class="post">';
        echo '<div class="post-img-container' . ($hasImg ? '' : ' post-img-container--empty') . '">';
        if ($hasImg) {
            echo '<img src="' . htmlspecialchars($img) . '" alt="">';
        }
        echo '</div>';
        echo '<div class="post-content">';
        echo '<h2><a href="post.php?id=' . (int)$row['id'] . '" class="post-feed-link">'
            . htmlspecialchars($title) . '</a></h2>';

        $preview = $body;
        if (function_exists('mb_substr')) {
            $preview = mb_substr($body, 0, 200);
            $long = mb_strlen($body);
        } else {
            $preview = substr($body, 0, 200);
            $long = strlen($body);
        }
        echo '<p class="post-feed-text">' . nl2br(htmlspecialchars($preview)) . ($long > 200 ? '...' : '') . '</p>';
        echo '</div>';
        echo '</div>';
        $shown++;
    }
    if ($shown === 0) {
        echo '<p>Нет постов с текстом или изображением.</p>';
    }
} else {
    echo 'Постов пока нет.';
}
