<?php

declare(strict_types=1);

/**
 * Список id всех администраторов.
 *
 * @return int[]
 */
function chat_support_admin_ids(mysqli $db): array
{
    $ids = [];
    $res = $db->query('SELECT id FROM users WHERE admin = 1 ORDER BY id ASC');
    if ($res) {
        while ($row = $res->fetch_row()) {
            $ids[] = (int)$row[0];
        }
    }
    return $ids;
}

/** Первый админ (ящик для входящих от пользователей). */
function chat_primary_admin_id(mysqli $db): int
{
    $ids = chat_support_admin_ids($db);
    return $ids[0] ?? 0;
}

function chat_user_is_admin(mysqli $db, int $userId): bool
{
    if ($userId <= 0) {
        return false;
    }
    $stmt = $db->prepare('SELECT 1 FROM users WHERE id = ? AND admin = 1 LIMIT 1');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $ok = $stmt->get_result()->fetch_row() !== null;
    $stmt->close();
    return $ok;
}
