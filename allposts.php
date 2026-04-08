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

$sql = "SELECT posts.id, posts.title,
        COALESCE(NULLIF(TRIM(users.us_name), ''), users.login) AS author_name,
        posts.created_at
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die('Ошибка выполнения запроса: ' . htmlspecialchars($conn->error));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Все посты</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/tools.css">
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <h1>Все посты</h1>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Автор</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                        <td><?php echo htmlspecialchars((string)$row['created_at']); ?></td>
                        <td>
                            <a href="post.php?id=<?php echo (int)$row['id']; ?>">Просмотреть</a> |
                            <a href="delete_post.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Удалить пост?');">Удалить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Постов нет.</p>
    <?php endif; ?>
</body>
</html>
