<?php
session_start();
include ("db.php");

// Функция для установки значений в сессию
function setSession($id, $us_name, $admin, $age) {
    $_SESSION['id'] = $id;
    $_SESSION['login'] = $us_name;
    $_SESSION['admin'] = $admin;
    $_SESSION['age'] = $age;
}

// --- Регистрация нового пользователя ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-reg'])) {
    // Получаем данные из формы
    $us_name = $_POST['login'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $pass_first = $_POST['pass-first'];
    $pass_second = $_POST['pass-second'];

    // Проверяем, совпадают ли пароли
    if ($pass_first !== $pass_second) {
        echo "Пароли не совпадают.";
    } else {
        // Хэшируем пароль
        $hashed_password = password_hash($pass_first, PASSWORD_DEFAULT);

        // Проверяем, существует ли пользователь с таким email
        $check_email_query = "SELECT * FROM users WHERE email='$email'";
        $check_email_result = $conn->query($check_email_query);

        if ($check_email_result->num_rows > 0) {
            echo "Пользователь с таким адресом электронной почты уже существует.";
        } else {
            // Подготавливаем и выполняем запрос на вставку данных в базу
            $stmt = $conn->prepare("INSERT INTO users (admin, us_name, login, email, age, password, created, info) VALUES (0, ?, ?, ?, ?, ?, NOW(), NULL)");
            $stmt->bind_param("sssis", $us_name, $us_name, $email, $age, $hashed_password);

            if ($stmt->execute()) {
                echo "Регистрация успешна.";
                setSession($conn->insert_id, $us_name, 0, $age);
                header("Location: accaunt.php");
                exit();
            } else {
                echo "Ошибка при регистрации: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// --- Авторизация пользователя ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-log'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Подготавливаем запрос на выборку данных
    $stmt = $conn->prepare("SELECT id, COALESCE(us_name, login) AS name, admin, age, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Проверяем введенный пароль с хэшированным в базе
        if (password_verify($password, $row['password'])) {
            echo "Авторизация успешна. Добро пожаловать, " . $row['name'];
            setSession($row['id'], $row['name'], (int)$row['admin'], $row['age']);
            
            header("Location: accaunt.php");
            exit(); 
        } else {
            echo "Неверный пароль.";
        }
    } else {
        echo "Пользователь с таким адресом электронной почты не найден.";
    }
    $stmt->close();
}

// --- Обновление профиля (форма editer_profile.php) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-upd'])) {
    if (!isset($_SESSION['id'])) {
        header("Location: auth.php");
        exit();
    }

    $userId = (int)$_SESSION['id'];

    $us_name = trim((string)($_POST['user-name'] ?? ''));
    $age = trim((string)($_POST['user-age'] ?? ''));
    $email = trim((string)($_POST['user-email'] ?? ''));
    $info = trim((string)($_POST['user-info'] ?? ''));
    $pass_first = (string)($_POST['pass-first'] ?? '');
    $pass_second = (string)($_POST['pass-second'] ?? '');

    // Обновляем то, что реально присутствует в форме и не ломаемся на пустых полях.
    if ($us_name !== '') {
        $_SESSION['login'] = $us_name;
    }
    if ($age !== '') {
        $_SESSION['age'] = $age;
    }

    // Собираем динамический UPDATE под текущую БД.
    $fields = [];
    $types = '';
    $values = [];

    if ($us_name !== '') { $fields[] = "us_name=?"; $types .= 's'; $values[] = $us_name; }
    if ($email !== '')   { $fields[] = "email=?";   $types .= 's'; $values[] = $email; }
    if ($age !== '')     { $fields[] = "age=?";     $types .= 's'; $values[] = $age; }

    // Пароль меняем только если оба заполнены и совпадают.
    if ($pass_first !== '' || $pass_second !== '') {
        if ($pass_first !== $pass_second) {
            echo "Пароли не совпадают.";
            exit();
        }
        $hashed_password = password_hash($pass_first, PASSWORD_DEFAULT);
        $fields[] = "password=?";
        $types .= 's';
        $values[] = $hashed_password;
    }

    // Поле "info" есть в таблице users — сохраняем и в БД, и в сессии.
    if ($info !== '') {
        $_SESSION['user_info'] = $info;
        $fields[] = "info=?";
        $types .= 's';
        $values[] = $info;
    }

    if (count($fields) > 0) {
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id=?";
        $typesFinal = $types . 'i';
        $valuesFinal = array_merge($values, [$userId]);

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($typesFinal, ...$valuesFinal);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: accaunt.php");
    exit();
}
?>