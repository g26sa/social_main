<?php
session_start();
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/chat_support_lib.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id']) || !chat_user_is_admin($connect, (int)$_SESSION['id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$adminIds = chat_support_admin_ids($connect);
if ($adminIds === []) {
    echo json_encode([]);
    exit;
}

$in = implode(',', array_fill(0, count($adminIds), '?'));
$sql = "SELECT sender_id, receiver_id FROM direct_messages
        WHERE sender_id IN ($in) OR receiver_id IN ($in)";

$stmt = $connect->prepare($sql);
if (!$stmt) {
    echo json_encode([]);
    exit;
}

$types = str_repeat('i', count($adminIds) * 2);
$params = array_merge($adminIds, $adminIds);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$adminSet = array_fill_keys($adminIds, true);
$clientIds = [];

while ($row = $res->fetch_assoc()) {
    $s = (int)$row['sender_id'];
    $r = (int)$row['receiver_id'];
    if (isset($adminSet[$s]) && !isset($adminSet[$r])) {
        $clientIds[$r] = true;
    } elseif (isset($adminSet[$r]) && !isset($adminSet[$s])) {
        $clientIds[$s] = true;
    }
}
$stmt->close();

$clientIds = array_keys($clientIds);
$threads = [];

foreach ($clientIds as $cid) {
    $name = 'Пользователь #' . $cid;
    $ns = $connect->prepare("SELECT COALESCE(NULLIF(TRIM(us_name), ''), login) AS n FROM users WHERE id = ?");
    if ($ns) {
        $ns->bind_param('i', $cid);
        $ns->execute();
        $nr = $ns->get_result()->fetch_assoc();
        if ($nr && $nr['n'] !== null && (string)$nr['n'] !== '') {
            $name = (string)$nr['n'];
        }
        $ns->close();
    }

    $lastText = '';
    $lastAt = '';
    $inA = implode(',', array_fill(0, count($adminIds), '?'));
    $lq = $connect->prepare(
        "SELECT message_text, timestamp FROM direct_messages
         WHERE ((sender_id = ? AND receiver_id IN ($inA)) OR (receiver_id = ? AND sender_id IN ($inA)))
         ORDER BY timestamp DESC LIMIT 1"
    );
    if ($lq) {
        $tp = 'i';
        $pp = [$cid];
        foreach ($adminIds as $a) {
            $pp[] = $a;
            $tp .= 'i';
        }
        $pp[] = $cid;
        $tp .= 'i';
        foreach ($adminIds as $a) {
            $pp[] = $a;
            $tp .= 'i';
        }
        $lq->bind_param($tp, ...$pp);
        $lq->execute();
        $lr = $lq->get_result()->fetch_assoc();
        if ($lr) {
            $lastText = (string)$lr['message_text'];
            $lastAt = (string)$lr['timestamp'];
        }
        $lq->close();
    }

    $threads[] = [
        'user_id' => $cid,
        'name' => $name,
        'last_text' => $lastText,
        'last_at' => $lastAt,
    ];
}

usort($threads, static function ($a, $b) {
    return strcmp($b['last_at'], $a['last_at']);
});

echo json_encode($threads, JSON_UNESCAPED_UNICODE);
