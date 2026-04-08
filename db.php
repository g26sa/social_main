<?php
// Загрузка переменных окружения из .env файла
if (file_exists(__DIR__ . '/.env')) {
    $env_file = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env_file as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Удаляем кавычки если они есть
            $value = trim($value, '"\'');
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Конфигурация БД (с поддержкой переменных окружения)
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "mysql";
$dbname = getenv('DB_NAME') ?: "social";

// Единое подключение к БД (объект mysqli).
// В проекте встречаются разные имена переменных ($conn/$connect/$link) — поддержим все.
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Backward-compat: старые части кода используют $link или $connect.
$link = $conn;
$connect = $conn;

/**
 * Подтягивает admin из БД в сессию (если выставили админа вручную в MySQL).
 */
function sync_session_admin_from_db(): void {
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['id'])) {
        return;
    }
    global $conn;
    if (!($conn instanceof mysqli)) {
        return;
    }
    $uid = (int)$_SESSION['id'];
    if ($uid <= 0) {
        return;
    }
    $stmt = $conn->prepare('SELECT admin FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $_SESSION['admin'] = (int)($row['admin'] ?? 0);
    }
}
?>