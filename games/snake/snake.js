// Переменные для поля
var blockSize = 25;
var rows = 20;
var cols = 30;
var board;
var context;

// Переменные для координат головы змейки
var snakeX;
var snakeY;

// Переменные для хранения направления движения
var vX = 0;
var vY = 0;

// Массив для хранения ячеек змейки
var snakeBody = [];

// Переменные для координат еды
var foodX;
var foodY;

// Переменная счётчика
var score = 0;

// Переменная для завершения игры
var gameOver = false;

// Генерация случайных координат для яблока
function placeFood() {
    foodX = Math.floor(Math.random() * cols) * blockSize;
    foodY = Math.floor(Math.random() * rows) * blockSize;
}

// Генерация случайных координат для змейки
function placeSnake() {
    snakeX = Math.floor(Math.random() * cols) * blockSize;
    snakeY = Math.floor(Math.random() * rows) * blockSize;
}

// Функция для изменения направления змейки
function changeDirection(event) {
    if (event.code == "KeyW" && vY != 1) {
        vX = 0;
        vY = -1;
    } else if (event.code == "KeyS" && vY != -1) {
        vX = 0;
        vY = 1;
    } else if (event.code == "KeyA" && vX != 1) {
        vX = -1;
        vY = 0;
    } else if (event.code == "KeyD" && vX != -1) {
        vX = 1;
        vY = 0;
    }
}

// Функция перезапуска игры
function resetGame() {
    gameOver = false;
    placeSnake();
    placeFood();
    vX = 0;
    vY = 0;
    snakeBody = [];
    score = 0;
}

// Основная функция обновления
function update() {
    if (gameOver) {
        resetGame();
    }

    // Закрашиваем фон
    context.fillStyle = "black";
    context.fillRect(0, 0, board.width, board.height);

    // Рисуем еду
    context.fillStyle = "red";
    context.fillRect(foodX, foodY, blockSize, blockSize);

    // Проверка съедания яблока
    if (snakeX == foodX && snakeY == foodY) {
        snakeBody.push([foodX, foodY]);
        score += 1;
        placeFood();
    }

    // Движение тела змейки
    for (let i = snakeBody.length - 1; i > 0; i--) {
        snakeBody[i] = snakeBody[i - 1];
    }
    if (snakeBody.length) {
        snakeBody[0] = [snakeX, snakeY];
    }

    // Отрисовка счётчика
    context.fillStyle = "lightblue";
    context.font = "20px sans-serif";
    context.fillText(score, 10, 25);

    // Движение головы
    context.fillStyle = "lime";
    snakeX += vX * blockSize;
    snakeY += vY * blockSize;
    context.fillRect(snakeX, snakeY, blockSize, blockSize);

    // Отрисовка тела
    for (let i = 0; i < snakeBody.length; i++) {
        context.fillRect(snakeBody[i][0], snakeBody[i][1], blockSize, blockSize);
    }

    // Проверка столкновения со стенами
    if (snakeX < 0 || snakeX > cols * blockSize - blockSize ||
        snakeY < 0 || snakeY > rows * blockSize - blockSize) {
        gameOver = true;
        alert("Игра окончена! Ваш счёт: " + String(score) + "\nНажмите 'OK' для перезапуска игры");
    }

    // Проверка столкновения с телом
    for (let i = 0; i < snakeBody.length; i++) {
        if (snakeX == snakeBody[i][0] && snakeY == snakeBody[i][1]) {
            gameOver = true;
            alert("Игра окончена! Ваш счёт: " + String(score) + "\nНажмите 'OK' для перезапуска игры");
        }
    }
}

// Загрузка страницы
window.onload = function() {
    board = document.getElementById("board");
    board.height = rows * blockSize;
    board.width = cols * blockSize;
    context = board.getContext("2d");

    placeFood();
    placeSnake();

    document.addEventListener("keyup", changeDirection);
    setInterval(update, 100);
};