<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Просмотр Видео</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/video_styles.css">
</head>
<body>
<h1>Просмотр Видео</h1>
<div class="video-container">
    <?php
    session_start();
    include("../menu.php");
    include_once '../db.php';

    if (isset($_GET['id'])) {
        $video_id = (int)$_GET['id'];
        $sql = "SELECT * FROM videos WHERE id = $video_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $video_name = $row['video_name'];
            $video_description = $row['video_description'];
            $video_source = $row['video_source'];
            $video_path = $row['video_path'];

            echo "<h2>$video_name</h2>";
            if ($video_source == 'file') {
                echo "<video controls class='video'>";
                echo "<source src='$video_path' type='video/mp4'>";
                echo "Ваш браузер не поддерживает видео в формате MP4.";
                echo "</video>";
            } elseif ($video_source == 'link') {
                echo "<iframe src='$video_path' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' allowfullscreen class='video'></iframe>";
            }
            echo "<p>$video_description</p>";
        } else {
            echo "Видео не найдено.";
        }
    } else {
        echo "Ошибка: ID видео не указан.";
    }

    // Форма комментария
    echo '<div class="comment-form-container">';
    echo '<h3>Оставить комментарий</h3>';
    echo '<form action="" method="post">';
    echo '<input type="hidden" name="video_id" value="' . $video_id . '">';
    echo '<label for="content">Комментарий:</label><br>';
    echo '<textarea id="content" name="content" rows="4" required></textarea><br><br>';
    echo '<button type="submit" name="submit_comment">Отправить комментарий</button>';
    echo '</form>';
    echo '</div>';

    if (isset($_POST['submit_comment'])) {
        $author_name = isset($_SESSION['login']) ? $conn->real_escape_string($_SESSION['login']) : 'Anonymous';
        $content = $conn->real_escape_string($_POST['content']);
        $video_id = (int)$_POST['video_id'];

        $sql_comment = "INSERT INTO comments_for_video (video_id, author_name, content) VALUES ('$video_id', '$author_name', '$content')";
        if ($conn->query($sql_comment) === TRUE) {
            echo "<script>window.location.href = window.location.href;</script>";
            exit;
        } else {
            echo "<script>alert('Ошибка: " . $conn->error . "');</script>";
        }
    }

    // Вывод комментариев
    $sql_comments = "SELECT author_name, content, upload_date FROM comments_for_video WHERE video_id = $video_id ORDER BY upload_date DESC";
    $result_comments = $conn->query($sql_comments);

    if ($result_comments->num_rows > 0) {
        echo "<div class='comments-container'>";
        echo "<h3>Комментарии:</h3>";
        while ($row = $result_comments->fetch_assoc()) {
            echo "<div class='comment'>";
            echo "<p><b>" . htmlspecialchars($row['author_name']) . "</b> (" . $row['upload_date'] . ")</p>";
            echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>Комментариев пока нет.</p>";
    }
    $conn->close();
    ?>
</div>
</body>
</html>