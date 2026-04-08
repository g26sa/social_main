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

$sql = "SELECT c.id, c.text AS content, c.post_id, p.title AS post_title,
               COALESCE(NULLIF(TRIM(u.us_name), ''), u.login) AS author_name, c.date AS created_at
        FROM comments c
        INNER JOIN posts p ON c.post_id = p.id
        INNER JOIN users u ON c.user_id = u.id
        ORDER BY c.date DESC";

$result = $conn->query($sql);
if (!$result) {
    die('Ошибка выполнения запроса: ' . htmlspecialchars($conn->error));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Все комментарии</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/tools.css">
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <h1>Все комментарии</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Комментарий</th>
                <th>Пост</th>
                <th>Пользователь</th>
                <th>Дата</th>
                <th>Действия</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['content']); ?></td>
                    <td>
                        <a href="post.php?id=<?php echo (int)$row['post_id']; ?>">
                            <?php echo htmlspecialchars($row['post_title']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['created_at']); ?></td>
                    <td>
                        <a href="delete_comment.php?id=<?php echo (int)$row['id']; ?>"
                           onclick="return confirm('Удалить комментарий?');">Удалить</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Комментариев нет.</p>
    <?php endif; ?>
</body>
</html>
