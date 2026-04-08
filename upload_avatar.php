<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
        // Создаем уникальное имя файла
        $newName = "avatar_" . ($_SESSION['id'] ?? time()) . "_" . time() . "." . $ext;
        $targetPath = "avatars/" . $newName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $_SESSION['avatar'] = $targetPath; // Обновляем путь в сессии
            echo json_encode(['success' => true, 'path' => $targetPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка сервера при сохранении']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Неверный формат файла']);
    }
    exit;
}