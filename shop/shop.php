<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth.php");
    exit;
}
require_once __DIR__ . '/../db.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/shop.css">
</head>
<body>
<?php include(__DIR__ . "/../menu.php"); ?>

<div class="assortment">
    <div class="zagolovok-shop">
        <a class="korzina" href="cart.php">Моя корзина</a>
    </div>

    <div class="products">
        <table class='table_shop'>
            <tr>
                <th colspan="2">Товар</th>
                <th>Цена</th>
                <th>Добавить</th>
            </tr>
            <?php
            $product_query = "SELECT id, name, price, picture FROM merchendise";
            $res = mysqli_query($conn, $product_query);
            $rows = [];
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $rows[] = $row;
                }
            }

            foreach ($rows as $elem) {
                $img = '';
                $src = '';

                if (!empty($elem['picture'])) {
                    $img = base64_encode($elem['picture']);
                    $src = 'data:image/jpg;base64,' . $img;
                } else {
                    // Если в БД картинки нет — пробуем взять из папки shop/img по id.
                    $merchId = (int)$elem['id'];
                    $imgBaseDir = __DIR__ . '/img/';
                    $candidates = [];
                    if ($merchId > 0) {
                        $candidates = [
                            $imgBaseDir . $merchId . '.png',
                            $imgBaseDir . $merchId . '.jpg',
                            $imgBaseDir . $merchId . '.jpeg',
                            $imgBaseDir . $merchId . '.gif',
                        ];
                    }
                    $found = null;
                    foreach ($candidates as $fsPath) {
                        if (is_file($fsPath)) {
                            $found = $fsPath;
                            break;
                        }
                    }
                    if ($found) {
                        $ext = strtolower(pathinfo($found, PATHINFO_EXTENSION));
                        $src = 'img/' . $merchId . '.' . $ext;
                    } else {
                        // Fallback, если фото нет нигде.
                        $placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300"><rect width="100%" height="100%" fill="#464c5e"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="20" fill="#FFC000">No image</text></svg>';
                        $src = 'data:image/svg+xml;base64,' . base64_encode($placeholderSvg);
                    }
                }

                echo '<tr>';
                echo '<td><img style="width: 150px;" src="' . $src . '" /></td>';
                echo '<td>' . htmlspecialchars((string)$elem['name']) . '</td>';
                echo '<td>' . htmlspecialchars((string)$elem['price']) . '</td>';
                echo '<td><button class="add-to-cart" type="button" onclick="func(this.id)" id="' . (int)$elem['id'] . '">Купить</button></td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
</div>

<script src="../js/jquery-4.0.0.min.js"></script>
<script src="../js/scriptjs.js"></script>
</body>
</html>