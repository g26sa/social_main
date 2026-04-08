<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главные Новости</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/profile-styles.css">
    <link rel="stylesheet" href="css/main-post.css">
</head>
<body class="posts-feed-page">
    <?php include __DIR__ . '/menu.php'; ?>

    <header class="posts-page-header">
        <h1>Посты</h1>
    </header>

    <div class="posts-feed-inner">
        <?php if (isset($_SESSION['id'])): ?>
            <div class="create-post-wrap">
                <a href="createpost.php" class="create-post-button">Создать пост</a>
            </div>
        <?php endif; ?>

        <div class="posts">
            <?php include __DIR__ . '/showposts.php'; ?>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var posts = document.querySelectorAll('.post');
    posts.forEach(function(post) {
        post.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#50576b';
        });
        post.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>
</body>
</html>
