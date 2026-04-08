<?php
session_start();
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/chat_support_lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id'])) {
    echo json_encode(['messages' => []]);
    exit;
}

$myId = (int)$_SESSION['id'];
$adminIds = chat_support_admin_ids($connect);

if ($adminIds === []) {
    echo json_encode(['error' => 'no_admin', 'messages' => []]);
    exit;
}

$isAdmin = chat_user_is_admin($connect, $myId);

if ($isAdmin) {
    $withUser = (int)($_GET['with'] ?? 0);
    if ($withUser <= 0 || chat_user_is_admin($connect, $withUser)) {
        echo json_encode(['messages' => []]);
        exit;
    }
    $clientId = $withUser;
} else {
    $clientId = $myId;
}

$in = implode(',', array_fill(0, count($adminIds), '?'));
$sql = "SELECT dm.sender_id, dm.message_text AS text, dm.timestamp AS date,
        COALESCE(u.us_name, u.login) AS name
        FROM direct_messages dm
        INNER JOIN users u ON u.id = dm.sender_id
        WHERE (
            (dm.sender_id = ? AND dm.receiver_id IN ($in))
            OR (dm.receiver_id = ? AND dm.sender_id IN ($in))
        )
        ORDER BY dm.timestamp ASC";

$stmt = $connect->prepare($sql);
if (!$stmt) {
    echo json_encode(['messages' => []]);
    exit;
}

$params = [$clientId];
foreach ($adminIds as $aid) {
    $params[] = $aid;
}
$params[] = $clientId;
foreach ($adminIds as $aid) {
    $params[] = $aid;
}

$types = str_repeat('i', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$messages = [];
while ($row = $res->fetch_assoc()) {
    $messages[] = [
        'user_name' => $row['name'],
        'text' => $row['text'],
        'is_my' => ((int)$row['sender_id'] === $myId),
        'date' => $row['date'],
    ];
}
$stmt->close();

echo json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
