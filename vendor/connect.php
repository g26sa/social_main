<?php
// Общий файл подключения к БД для vendor-эндпоинтов.
require_once __DIR__ . '/../db.php';

// В vendor-скриптах используется переменная $connect.
// В db.php мы создаем $conn (mysqli) и алиасы, но зафиксируем здесь явно.
$connect = $conn;
?>
