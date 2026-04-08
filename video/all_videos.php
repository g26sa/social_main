<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список Видео</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/loader.css">
    <link rel="stylesheet" href="../css/videolist.css">
    <script src="../js/loader.js"></script>
</head>
<body>
<header>
    <h1>Список Видео</h1>
</header>
<?php
session_start();
include("../menu.php");
include("../loader.php");

if (isset($_SESSION['id'])) {
    echo '<a href="add_video.php" class="add-video-button">Опубликовать Видео</a>';
}

include_once '../db.php';
$sql = "SELECT * FROM videos";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $video_id = $row['id'];
        $video_name = $row['video_name'];
        $video_description = $row['video_description'];
        echo "<div class='video-container'>";
        echo "<a href='watch_video.php?id=$video_id'>";
        echo "<h2>$video_name</h2>";
        echo "<p>$video_description</p>";
        echo "</a>";
        echo "</div>";
    }
} else {
    echo "<p>Видео не найдены.</p>";
}
$conn->close();
?>
</body>
</html>