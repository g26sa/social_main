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

$sql = "SELECT * FROM users WHERE id != 0 ORDER BY id";
$result = $conn->query($sql);

if (!$result) {
    die('Ошибка запроса: ' . htmlspecialchars($conn->error));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Все пользователи</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/tools.css">
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <h1>Все пользователи</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Имя пользователя</th>
                <th>Email</th>
                <th>Возраст</th>
                <th>Аккаунт создан</th>
                <th>Действия</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['us_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['email']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['age']); ?></td>
                    <td><?php echo htmlspecialchars((string)($row['created'] ?? '')); ?></td>
                    <td>
                        <?php if ((int)$row['id'] !== (int)$_SESSION['id']): ?>
                            <a href="delete_user.php?id=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Удалить пользователя?');">Удалить</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Пользователей нет.</p>
    <?php endif; ?>
</body>
</html>
