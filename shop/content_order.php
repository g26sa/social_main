<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth.php");
    exit;
}
require_once __DIR__ . '/../db.php';

$num_id = (int)($_GET["OrderNum"] ?? 0);
if ($num_id <= 0) {
    header("Location: history_order.php");
    exit;
}

// Заглушка, если у товара/статуса нет картинки в базе или в каталоге.
$placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="140" viewBox="0 0 200 140"><rect width="200" height="140" fill="#353746"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="14" fill="#FFC000">No image</text></svg>';
$placeholderSvgBase64 = base64_encode($placeholderSvg);

$cartSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#0f0f0f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="9" cy="20" r="1.5" fill="#0f0f0f" stroke="none"/>
  <circle cx="17" cy="20" r="1.5" fill="#0f0f0f" stroke="none"/>
  <path d="M3 3h2l2.5 14h12.5l2-9H6.5"/>
</svg>';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Содержание заказа</title>
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/content_order.css">
</head>
<body>
<?php include(__DIR__ . "/../menu.php"); ?>

<!-- Заголовок -->
<header>
    <h1>Содержание заказа</h1>
</header>

<?php
// Шапка заказа
$stmt = $conn->prepare("SELECT num_order, date_order, paid FROM `order` WHERE num_order = ? GROUP BY num_order, date_order, paid");
$stmt->bind_param("i", $num_id);
$stmt->execute();
$res = $stmt->get_result();
$header = $res->fetch_assoc();
$stmt->close();

if (!$header) {
    echo "<div class='table_order'>Заказ не найден</div>";
    exit;
}
?>
<?php
// Верхняя строка заказа (как на фото)
$paid = (int)$header['paid'];
$statusText = $paid === 0 ? 'Не оплачен' : 'Оплачен';
$circleText = $paid === 0 ? 'X' : '✓';
$circleClass = $paid === 0 ? 'order-circle order-circle--danger' : 'order-circle order-circle--ok';
?>

<div class="order-detail-header">
    <div class="order-detail-header__icon"><?php echo $cartSvg; ?></div>
    <div class="order-detail-header__main">
        <div class="order-detail-header__title">
            Заказ №<?php echo (int)$header['num_order']; ?> от <?php echo htmlspecialchars((string)$header['date_order']); ?>
        </div>
        <div class="order-detail-header__status"><?php echo htmlspecialchars((string)$statusText); ?></div>
    </div>
    <span class="<?php echo $circleClass; ?>" aria-label="Статус заказа">
        <?php echo $circleText; ?>
    </span>
</div>

<?php
// Товары в заказе
$stmt = $conn->prepare("SELECT o.product, o.price, o.count_product, o.summa,
                               m.id AS merch_id,
                               m.picture
                        FROM `order` o
                        LEFT JOIN merchendise m ON o.product = m.name
                        WHERE o.num_order = ?");
$stmt->bind_param("i", $num_id);
$stmt->execute();
$res = $stmt->get_result();

$sum = 0;
?>

<div class="table_order">
    <table class="table_shop1">
        <tr>
            <th colspan="2">Товар</th>
            <th>Цена</th>
            <th>Количество</th>
            <th>Сумма</th>
        </tr>
        <?php while ($elem = $res->fetch_assoc()): ?>
            <?php $sum += (int)$elem['summa']; ?>
            <tr>
                <td>
                    <?php
                    $productName = (string)$elem['product'];
                    $productNameLower = function_exists('mb_strtolower') ? mb_strtolower($productName) : strtolower($productName);
                    $merchId = isset($elem['merch_id']) ? (int)$elem['merch_id'] : 0;
                    $src = '';

                    if (!empty($elem['picture'])) {
                        $src = 'data:image/jpg;base64' . base64_encode($elem['picture']);
                    } else {
                        // Если картинка в БД пустая — пытаемся взять фото из папки shop/img по id товара.
                        // Ожидаем: shop/img/<merch_id>.png|jpg|jpeg|gif
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
                            // Мини-иконки как на фото (fallback по названию товара)
                            if ($productNameLower === 'ручка') {
                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="80" viewBox="0 0 120 80" fill="none" stroke="#0f0f0f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10 70l50-50"/><path d="M50 20l20 20"/><path d="M70 40L90 20"/><path d="M92 22l8 8"/><path d="M100 30l8-8"/></svg>';
                            } elseif ($productNameLower === 'мяч') {
                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" viewBox="0 0 90 90" fill="none" stroke="#0f0f0f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="45" cy="45" r="30"/><path d="M45 15v60"/><path d="M15 45h60"/><path d="M26 26l38 38"/><path d="M64 26L26 64"/></svg>';
                            } else {
                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="80" viewBox="0 0 120 80" fill="none" stroke="#0f0f0f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="18" y="18" width="84" height="44" rx="8"/><path d="M30 62l10-24h40l10 24"/></svg>';
                            }
                            $src = 'data:image/svg+xml;base64,' . base64_encode($svg);
                        }
                    }
                    ?>
                    <img style="width: 70px;" src="<?php echo $src; ?>" alt="">
                </td>
                <td><?php echo htmlspecialchars((string)$elem['product']); ?></td>
                <td><?php echo (int)$elem['price']; ?></td>
                <td><?php echo (int)$elem['count_product']; ?></td>
                <td><?php echo (int)$elem['summa']; ?></td>
            </tr>
        <?php endwhile; ?>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;">Итоговая сумма:</td>
                <td><?php echo (int)$sum; ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
$stmt->close();
?>

</body>
</html>
