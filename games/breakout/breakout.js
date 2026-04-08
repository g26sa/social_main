// Размеры окна и переменные для работы с полем
let board;
let bWidth = 500;
let bHeight = 500;
let context;

// Размеры ракетки
let pWidth = 80;
let pHeight = 10;
let pX = 9;

// Список, в котором содержатся координаты и размеры ракетки
let player = {
    x: 210,
    y: 480,
    width: pWidth,
    height: pHeight,
    vX: pX
};

// Размеры и скорость мяча
let ballWidth = 10;
let ballHeight = 10;
let ballVX = 4;
let ballVY = 3;

// Список с координатами мяча, его размерами и скоростью
let ball = {
    x: 250,
    y: 250,
    width: ballWidth,
    height: ballHeight,
    vX: ballVX,
    vY: ballVY
};

// Переменные для блоков
let blockArray = [];
let blockWidth = 50;
let blockHeight = 10;
let blockColumns = 8;
let blockRows = 3;
let blockMaxRows = 10;
let blockCount = 0;
let blockX = 15;
let blockY = 45;

// Счёт и состояние игры
let score = 0;
let gameOver = false;
let scoreSent = false; // Флаг для предотвращения повторной отправки рекорда

// === Функции для определения пересечений ===
function Collision(a, b) {
    return a.x < b.x + b.width && 
           a.x + a.width > b.x &&
           a.y < b.y + b.height &&
           a.y + a.height > b.y;
}

function tCollision(ball, block) {
    return Collision(ball, block) && (ball.y + ball.height) >= block.y && ball.y < block.y;
}

function bCollision(ball, block) {
    return Collision(ball, block) && (block.y + block.height) >= ball.y && ball.y + ball.height > block.y + block.height;
}

function lCollision(ball, block) {
    return Collision(ball, block) && (ball.x + ball.width) >= block.x && ball.x < block.x;
}

function rCollision(ball, block) {
    return Collision(ball, block) && (block.x + block.width) >= ball.x && ball.x + ball.width > block.x + block.width;
}

// === Функция создания блоков ===
function createBlocks() {
    blockArray = [];
    
    for (let c = 0; c < blockColumns; c++) {
        for (let r = 0; r < blockRows; r++) {
            let block = {
                x: blockX + c * blockWidth + c * 10,
                y: blockY + r * blockHeight + r * 10,
                width: blockWidth,
                height: blockHeight,
                break: false
            };
            blockArray.push(block);
        }
    }
    
    blockCount = blockArray.length;
}

// === Функция проверки выхода ракетки за границы ===
function outBoard(xPos) {
    return (xPos >= 0 && xPos + pWidth <= bWidth);
}

// === Функция отправки рекорда на сервер ===
function sendScoreToServer(score) {
    // Создаём объект FormData для передачи данных
    let formData = new FormData();
    formData.append('score', score);
    
    // Выполняем запрос к файлу на сервере
    fetch('save_score.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Обрабатываем ответ сервера
        if (!response.ok) {
            throw new Error('Сервер выдал ошибку');
        }
        return response.text();
    })
    .then(data => {
        // Обрабатываем полученные данные
        console.log('Ответ сервера:', data);
    })
    .catch(error => {
        // Обрабатываем ошибку, если запрос не удался
        console.error('Выполнить запрос не удалось:', error);
    });
}

// === Функция перезапуска игры ===
function resetGame() {
    gameOver = false;
    score = 0;
    scoreSent = false;
    
    player = {
        x: 210,
        y: 480,
        width: pWidth,
        height: pHeight,
        vX: pX
    };
    
    ball = {
        x: 250,
        y: 250,
        width: ballWidth,
        height: ballHeight,
        vX: ballVX,
        vY: ballVY
    };
    
    blockRows = 3;
    createBlocks();
}

// === Функция движения ракетки ===
function movePlayer(e) {
    if (gameOver) {
        if (e.code == "Space") {
            resetGame();
        }
        return;
    }
    
    if (e.code == "KeyA") {
        let nextX = player.x - player.vX;
        if (outBoard(nextX)) {
            player.x = nextX;
        }
    }
    else if (e.code == "KeyD") {
        let nextX = player.x + player.vX;
        if (outBoard(nextX)) {
            player.x = nextX;
        }
    }
}

// === Функция для перерисовки поля ===
function update() {
    requestAnimationFrame(update);
    
    if (gameOver) {
        context.fillStyle = "skyblue";
        context.font = "20px sans-serif";
        context.fillText("Game Over: Press 'Space' to Restart", 80, 400);
        return;
    }
    
    context.clearRect(0, 0, board.width, board.height);
    context.fillStyle = "black";
    context.fillRect(0, 0, board.width, board.height);
    
    // Обновление и отрисовка мяча
    ball.x += ball.vX;
    ball.y += ball.vY;
    
    context.fillStyle = "white";
    context.fillRect(ball.x, ball.y, ball.width, ball.height);
    
    // Отскок от ракетки
    if (tCollision(ball, player) || bCollision(ball, player)) {
        ball.vY *= -1;
    }
    else if (lCollision(ball, player) || rCollision(ball, player)) {
        ball.vX *= -1;
    }
    
    // Отскок от границ
    if (ball.y <= 0) {
        ball.vY *= -1;
    }
    if (ball.x <= 0 || (ball.x + ball.width >= bWidth)) {
        ball.vX *= -1;
    }
    
    // Проверка проигрыша
    if (ball.y + ball.height >= bHeight) {
        if (!scoreSent) {
            sendScoreToServer(score);
            scoreSent = true;
        }
        gameOver = true;
        context.fillStyle = "skyblue";
        context.font = "20px sans-serif";
        context.fillText("Game Over: Press 'Space' to Restart", 80, 400);
        return;
    }
    
    // Отрисовка и обработка блоков
    context.fillStyle = "lightgreen";
    
    for (let i = 0; i < blockArray.length; i++) {
        let block = blockArray[i];
        
        if (!block.break) {
            if (tCollision(ball, block) || bCollision(ball, block)) {
                block.break = true;
                ball.vY *= -1;
                blockCount--;
                score++;
            }
            else if (lCollision(ball, block) || rCollision(ball, block)) {
                block.break = true;
                ball.vX *= -1;
                blockCount--;
                score++;
            }
            
            context.fillRect(block.x, block.y, block.width, block.height);
        }
    }
    
    // Переход на новый уровень
    if (blockCount == 0) {
        blockRows = Math.min(blockRows + 1, blockMaxRows);
        createBlocks();
    }
    
    // Отрисовка ракетки
    context.fillStyle = "orange";
    context.fillRect(player.x, player.y, player.width, player.height);
    
    // Отрисовка счёта
    context.fillStyle = "skyblue";
    context.font = "20px sans-serif";
    context.fillText("Score: " + score, 10, 30);
}

// === Загрузка страницы ===
window.onload = function() {
    board = document.getElementById("board");
    board.height = bHeight;
    board.width = bWidth;
    context = board.getContext("2d");
    
    context.fillStyle = "black";
    context.fillRect(0, 0, board.width, board.height);
    
    context.fillStyle = "orange";
    context.fillRect(player.x, player.y, player.width, player.height);
    
    createBlocks();
    requestAnimationFrame(update);
    document.addEventListener("keydown", movePlayer);
};