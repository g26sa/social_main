<?php
session_start();
require_once __DIR__ . '/../db.php';

header('Content-Type: text/plain; charset=utf-8');

$action = $_POST['action'] ?? '';

function init(mysqli $conn): void {
    $idsRaw = (string)($_POST['id_product'] ?? '');
    $ids = array_filter(array_map('intval', explode(',', $idsRaw)));
    if (!$ids) {
        echo json_encode([]);
        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("SELECT id, name, price FROM merchendise WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();

    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    $stmt->close();

    echo json_encode($out, JSON_UNESCAPED_UNICODE);
}

function putData(mysqli $conn): void {
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo "not_auth";
        return;
    }

    $Data_Order = $_POST['Data_Order'] ?? null;
    if (!is_array($Data_Order)) {
        http_response_code(400);
        echo "bad_request";
        return;
    }

    $arrProd = explode(',', rtrim((string)($Data_Order["product"] ?? ''), ','));
    $arrPrice = explode(',', rtrim((string)($Data_Order["price"] ?? ''), ','));
    $arrCount = explode(',', rtrim((string)($Data_Order["count_product"] ?? ''), ','));
    $arrSum = explode(',', rtrim((string)($Data_Order["summa"] ?? ''), ','));

    $userId = (int)$_SESSION["id"];
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    $num_order = (int)($Data_Order["num_order"] ?? 0);
    $date_order = (string)($Data_Order["date_order"] ?? '');
    if ($num_order <= 0 || $date_order === '') {
        http_response_code(400);
        echo "bad_order";
        return;
    }

    $stmt = $conn->prepare("INSERT INTO `order` (num_order, date_order, users, product, price, count_product, summa, paid) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    for ($i = 0; $i < count($arrProd); $i++) {
        $product = (string)($arrProd[$i] ?? '');
        if ($product === '') continue;
        $price = (int)($arrPrice[$i] ?? 0);
        $count = (int)($arrCount[$i] ?? 1);
        $sum = (int)($arrSum[$i] ?? ($price * $count));

        $stmt->bind_param('isssiii', $num_order, $date_order, $email, $product, $price, $count, $sum);
        $stmt->execute();
    }
    $stmt->close();

    echo "ok";
}

switch ($action) {
    case 'init':
        init($conn);
        break;
    case 'inputData':
        putData($conn);
        break;
    default:
        http_response_code(400);
        echo "unknown_action";
        break;
}