<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breakout</title>
    <!-- Подключение стилей -->
    <link rel="stylesheet" href="breakout.css">
    <link rel="stylesheet" href="../../css/menu.css">
    <link rel="stylesheet" href="../../css/loader.css">
    <!-- Подключение скриптов -->
    <script src="../../js/loader.js"></script>
    <script src="breakout.js"></script>
</head>
<body>
    <?php
    // Подключение меню в файл
    include("../../menu.php");
    // Подключение Loader в файл
    include("../../loader.php");
    ?>
    
    <header><h1>Breakout</h1></header>
    
    <!-- Область, в которой будет отображаться игра -->
    <canvas id="board"></canvas>
    
    <!-- Таблица рекордов -->
    <table>
        <thead>
            <tr>
                <th>Игрок</th>
                <th>Рекорд</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Подключение к базе данных
            require_once '../../db.php';
            
            // Запрос на выборку данных из таблицы high_scores_breakout
            $sql = "SELECT username, score FROM high_scores_breakout ORDER BY score DESC LIMIT 10";
            $result = $conn->query($sql);
            
            // Проверка наличия данных
            if ($result && $result->num_rows > 0) {
                // Выводим данные в виде HTML-таблицы
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["score"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>Нет сохранённых рекордов</td></tr>";
            }
            
            // Закрываем соединение с базой данных
            if (isset($conn)) {
                $conn->close();
            }
            ?>
        </tbody>
    </table>
</body>
</html>