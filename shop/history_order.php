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
<title>История заказов</title>
<link rel="stylesheet" href="../css/menu.css">
<link rel="stylesheet" href="../css/history_order.css">
</head>
<body>
<?php
include("../menu.php");
?>
<!-- Заголовок-->
<header>
<h1>История заказов</h1>
</header>
<?php
require_once __DIR__ . '/../db.php';

$cartSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="#0f0f0f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <circle cx="9" cy="20" r="1.5" fill="#0f0f0f" stroke="none"/>
  <circle cx="17" cy="20" r="1.5" fill="#0f0f0f" stroke="none"/>
  <path d="M3 3h2l2.5 14h12.5l2-9H6.5"/>
</svg>';

$userId = (int)$_SESSION["id"];
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

$test_query = "SELECT num_order, date_order, paid
               FROM `order`
               WHERE users = ?
               GROUP BY num_order, date_order, paid
               ORDER BY date_order DESC, num_order DESC";
$stmt = $conn->prepare($test_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
?>

<div class="orders-list">
    <?php while ($elem = $res->fetch_assoc()): 
        $numOrder = (int)$elem['num_order'];
        $dateOrder = htmlspecialchars((string)$elem['date_order']);
        $paid = (int)$elem['paid'];
        $statusText = $paid === 0 ? 'Не оплачен' : 'Оплачен';
        $circleText = $paid === 0 ? 'X' : '✓';
        $circleClass = $paid === 0 ? 'order-circle order-circle--danger' : 'order-circle order-circle--ok';
    ?>
        <a class="order-card-link" href="content_order.php?OrderNum=<?php echo $numOrder; ?>" aria-label="Смотреть заказ">
            <div class="order-card">
                <div class="order-card__icon"><?php echo $cartSvg; ?></div>
                <div class="order-card__main">
                    <div class="order-card__title">Заказ №<?php echo $numOrder; ?> от <?php echo $dateOrder; ?></div>
                    <div class="order-card__status"><?php echo $statusText; ?></div>
                </div>
                <span class="<?php echo $circleClass; ?>"><?php echo $circleText; ?></span>
            </div>
        </a>
    <?php endwhile; 
    $stmt->close();
    ?>
</div>
</body>
</html>