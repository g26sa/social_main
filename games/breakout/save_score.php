<?php
require_once '../../db.php';

// Проверяем, был ли отправлен POST-запрос с данными о счёте и сессии авторизована
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['score']) && isset($_SESSION['login'])) {
    // Получаем счёт из POST-запроса и имя пользователя из сессии
    $score = (int)$_POST['score'];
    $username = $_SESSION['login'];
    
    // Получаем текущий рекорд пользователя из базы данных
    $sql = "SELECT score FROM high_scores_breakout WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($high_score);
    
    // Если у пользователя ранее не было рекорда, добавляем новую запись
    if ($stmt->num_rows == 0) {
        // Добавляем новую запись о рекорде пользователя в базу данных
        $sql = "INSERT INTO high_scores_breakout (username, score) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $score);
        if ($stmt->execute() === TRUE) {
            echo "Новый рекорд успешно сохранён";
        } else {
            echo "Ошибка: " . $stmt->error;
        }
    } else {
        // Если рекорд уже существует, получаем его значение
        $stmt->fetch();
        
        // Обновляем рекорд пользователя только если новый рекорд больше
        if ($score > $high_score) {
            $sql = "UPDATE high_scores_breakout SET score = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $score, $username);
            if ($stmt->execute()) {
                echo "Рекорд успешно обновлён";
            } else {
                echo "Ошибка при обновлении рекорда: " . $stmt->error;
            }
        } else {
            echo "Счёт не превышает предыдущий рекорд";
        }
    }
    
    // Закрываем запрос
    $stmt->close();
} else {
    echo "Неавторизованный пользователь или неверный запрос";
}

// Закрываем соединение с базой данных
if (isset($conn)) {
    $conn->close();
}
?>