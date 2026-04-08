<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/db.php';
sync_session_admin_from_db();

$isAdmin = ((int)($_SESSION['admin'] ?? 0) === 1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $isAdmin ? 'Обращения пользователей' : 'Чат с администратором'; ?></title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/chat.css">
</head>
<body class="<?php echo $isAdmin ? 'chat-page chat-page--admin' : 'chat-page'; ?>">
    <?php include __DIR__ . '/menu.php'; ?>

    <div class="chat-layout<?php echo $isAdmin ? ' chat-layout--admin' : ''; ?>">
        <?php if ($isAdmin): ?>
        <div class="chat-admin-shell">
            <aside class="chat-threads" id="chat-threads" aria-label="Список пользователей">
                <div class="chat-threads__head">
                    <span class="chat-threads__head-title">Диалоги</span>
                    <span class="chat-threads__head-hint">Выберите пользователя</span>
                </div>
                <div class="chat-threads__list" id="threads-list">
                    <p class="chat-threads__empty">Загрузка…</p>
                </div>
            </aside>

        <?php endif; ?>

        <div class="chat-container" id="chat-main">
            <div class="chat-header">
                <h2 id="chat-title"><?php echo $isAdmin ? 'Выберите диалог в панели слева' : 'Чат с администратором'; ?></h2>
                <?php if (!$isAdmin): ?>
                    <p class="chat-sub">Ваши сообщения видят администраторы сайта. Ответ придёт здесь же.</p>
                <?php endif; ?>
            </div>

            <div id="chat-box" class="chat-box">
                <div class="message system" id="chat-placeholder">
                    <?php echo $isAdmin ? 'Выберите диалог' : 'Загрузка сообщений…'; ?>
                </div>
            </div>

            <form id="chat-form" class="chat-input-area">
                <input type="text" id="message-text" placeholder="Введите сообщение..." autocomplete="off" <?php echo $isAdmin ? 'disabled' : ''; ?>>
                <button type="submit" id="send-btn" <?php echo $isAdmin ? 'disabled' : ''; ?>>Отправить</button>
            </form>
        </div>
        <?php if ($isAdmin): ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        window.CHAT_IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        window.CHAT_MY_ID = <?php echo (int)$_SESSION['id']; ?>;
    </script>
    <script src="js/chat.js"></script>
</body>
</html>
