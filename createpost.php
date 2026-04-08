<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: auth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создать пост</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/postcreate.css">
</head>
<body>
    <header><h1>Создать новый пост</h1></header>
    <?php include __DIR__ . '/menu.php'; ?>
    <main>
        <form action="create.php" method="post" enctype="multipart/form-data">
            <label for="title">Заголовок:</label>
            <input type="text" id="title" name="title" required>

            <label for="content">Содержание:</label>
            <textarea id="content" name="content" rows="6" required></textarea>

            <label for="image">Изображение:</label>
            <input type="file" id="image" name="image">

            <button type="submit">Создать пост</button>
        </form>
    </main>
</body>
</html>
