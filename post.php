<?php
session_start();
include_once __DIR__ . '/db.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare('SELECT title, content, image_url, created_at FROM posts WHERE id = ?');
if (!$stmt) {
    die('Ошибка БД');
}
$stmt->bind_param('i', $post_id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
$stmt->close();

if (!$post) {
    die('Пост не найден.');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/main-post.css">
    <style>
        .full-post { max-width: 800px; margin: 40px auto; background: #464c5e; padding: 30px; border-radius: 12px; }
        .full-post img { width: 100%; border-radius: 8px; margin-bottom: 20px; }
        .full-post h1 { color: #FFC000; margin-top: 0; }
        .full-post .content { font-size: 18px; line-height: 1.6; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <div class="full-post">
        <?php if (!empty($post['image_url'])): ?>
            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="">
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <div class="content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
        <br><a href="posts.php" style="color:#FFC000;">← Назад к новостям</a>
    </div>
</body>
</html>
