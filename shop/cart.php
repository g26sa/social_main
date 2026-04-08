<!-- Инициализация сессии -->
<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth.php");
    exit;
}
?>
<!-- Шаблон для создания html разметки -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Корзина</title>
<link rel="stylesheet" href="../css/menu.css">
<link rel="stylesheet" href="../css/cart.css">
</head>
<body>
<!-- Подключение меню в файл -->
<?php
include("../menu.php");
?>
<!-- Заголовок -->
<header>
<h1>Корзина</h1>
</header>
<!-- Контейнер, в котором будет отображаться информация о товарах заказа -->
<div class="main-cart" id="main-cart"></div>
<div class="zagalovok">
<button class = "post-bd" onclick="POSTData()">Оформить заказ</button>
</div>
<!-- Подключение библиотеки и файла с функциями -->
<script src="../js/jquery-4.0.0.min.js"></script>
<script src="../js/scriptcart_fixed.js"></script>
</body>
</html>