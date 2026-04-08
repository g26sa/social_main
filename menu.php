<input class="menu-icon" type="checkbox" id="menu-icon" name="menu-icon"/>
<label for="menu-icon"></label>
<nav class="nav">
<ul class="pt-5">
<li><a href="<?php echo (!empty($_SERVER['SCRIPT_NAME']) && (strpos($_SERVER['SCRIPT_NAME'], '/shop/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/games/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/x-o') !== false || strpos($_SERVER['SCRIPT_NAME'], '/music/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/video/') !== false)) ? '../' : ''; ?>index.php">Главная</a></li>
<?php
$prefix = (!empty($_SERVER['SCRIPT_NAME']) && (strpos($_SERVER['SCRIPT_NAME'], '/shop/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/games/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/x-o') !== false || strpos($_SERVER['SCRIPT_NAME'], '/music/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/video/') !== false)) ? '../' : '';
if (isset($_SESSION['id'])) {
    echo '<li><a href="' . $prefix . 'accaunt.php">Личный кабинет</a></li>';
    echo '<li><a href="' . $prefix . 'chat.php">Чат</a></li>';
    echo '<li><a href="' . $prefix . 'news.php">Новости</a></li>';
    echo '<li><a href="' . $prefix . 'shop/shop.php">Магазин</a></li>';
    echo '<li><a href="' . $prefix . 'games/games.php">Игры</a></li>';
    echo '<li><a href="' . $prefix . 'video/all_videos.php">Видео</a></li>';
    echo '<li><a href="' . $prefix . 'music/music_list.php">Музыка</a></li>';
} else {
    echo '<li><a href="' . $prefix . 'auth.php">Личный кабинет</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Чат</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Новости</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Магазин</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Игры</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Видео</a></li>';
    echo '<li><a href="' . $prefix . 'auth.php">Музыка</a></li>';
}
?>
</ul>
</nav>
