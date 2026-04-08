<?php
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: auth.php');
    exit;
}

require_once __DIR__ . '/db.php';
sync_session_admin_from_db();

$userId = (int)$_SESSION['id'];
$stmt = $conn->prepare('SELECT us_name, login, email, age, created, info FROM users WHERE id = ?');
if (!$stmt) {
    die('Ошибка БД');
}
$stmt->bind_param('i', $userId);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$userRow) {
    die('Пользователь не найден.');
}

$displayName = trim((string)($userRow['us_name'] ?? '')) !== ''
    ? $userRow['us_name']
    : ($userRow['login'] ?? '');
$email = htmlspecialchars((string)($userRow['email'] ?? ''));
$age = htmlspecialchars((string)($userRow['age'] ?? ''));
$created = htmlspecialchars((string)($userRow['created'] ?? ''));
$info = htmlspecialchars((string)($userRow['info'] ?? ''));
$avatar = isset($_SESSION['avatar']) && is_string($_SESSION['avatar']) && $_SESSION['avatar'] !== ''
    ? htmlspecialchars($_SESSION['avatar'])
    : 'avatars/placeholder.svg';

$isAdmin = ((int)($_SESSION['admin'] ?? 0) === 1);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/profile-styles.css">
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>

    <header>
        <h1>Профиль пользователя</h1>
        <a href="logout.php"><button type="button" id="logout-btn">Выйти</button></a>
    </header>

    <section id="profile-info">
        <h2>Информация о пользователе</h2>
        <div id="profile-picture-container">
            <img src="<?php echo $avatar; ?>" alt="Аватар" id="profile-picture">
            <input type="file" id="avatar-input" accept="image/*" hidden>
        </div>

        <div id="profile-fields">
            <div class="profile-field">
                <span class="label">ФИО:</span><span class="value"><?php echo htmlspecialchars($displayName); ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Возраст:</span><span class="value"><?php echo $age; ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Дата создания аккаунта:</span><span class="value"><?php echo $created; ?></span>
            </div>
            <div class="profile-field">
                <span class="label">Email:</span><span class="value"><?php echo $email; ?></span>
            </div>
            <div class="profile-field">
                <span class="label">О себе:</span><span class="value"><?php echo $info !== '' ? $info : '—'; ?></span>
            </div>
        </div>
    </section>

    <section id="profile-panel">
        <h3>Панель управления</h3>
        <div id="profile-actions">
            <button type="button" id="edit-profile">Редактировать профиль</button>
            <button type="button" id="update-picture">Обновить картинку</button>
            <button type="button" id="delete-picture">Удалить картинку</button>
            <button type="button" id="admin-chat-btn" onclick="location.href='chat.php'">Написать администратору</button>
            <button type="button" onclick="location.href='createpost.php'">Создать пост</button>
            <button type="button"
                class="admin-panel-btn<?php echo $isAdmin ? '' : ' admin-panel-btn--locked'; ?>"
                <?php echo $isAdmin ? 'onclick="location.href=\'allusers.php\'"' : 'disabled title="Только для администратора"'; ?>>
                Все пользователи
            </button>
            <button type="button"
                class="admin-panel-btn<?php echo $isAdmin ? '' : ' admin-panel-btn--locked'; ?>"
                <?php echo $isAdmin ? 'onclick="location.href=\'allposts.php\'"' : 'disabled title="Только для администратора"'; ?>>
                Все посты
            </button>
            <button type="button"
                class="admin-panel-btn<?php echo $isAdmin ? '' : ' admin-panel-btn--locked'; ?>"
                <?php echo $isAdmin ? 'onclick="location.href=\'allcomments.php\'"' : 'disabled title="Только для администратора"'; ?>>
                Все комментарии
            </button>
        </div>
    </section>

    <section id="user-posts">
        <h2>Мои посты</h2>
        <button type="button" id="create-post-btn">Создать пост</button>

        <section id="create-post" style="display:none;">
            <form id="post-form" enctype="multipart/form-data">
                <textarea id="post-text" name="text" placeholder="Текст поста (или только фото)"></textarea>
                <label>Изображение (необязательно):</label>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Опубликовать</button>
            </form>
        </section>

        <div id="posts-container"></div>
    </section>

    <script src="js/profile.js"></script>
</body>
</html>
