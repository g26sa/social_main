// Текущий игрок (X всегда начинает)
let currentPlayer = "X";

// Завершена ли игра
let gameEnded = false;

// Игровое поле (9 ячеек)
let board = ["", "", "", "", "", "", "", "", ""];

// Выигрышные комбинации
const winPatterns = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8], // Горизонтали
    [0, 3, 6], [1, 4, 7], [2, 5, 8], // Вертикали
    [0, 4, 8], [2, 4, 6]             // Диагонали
];

// Функция, вызываемая при клике на ячейку
function cellClicked(cellIndex) {
    // Если игра не завершена, ячейка пуста и сейчас ход игрока (X)
    if (!gameEnded && board[cellIndex] === "" && currentPlayer === "X") {
        makeMove(cellIndex, "X");

        // Проверяем победу или ничью после хода игрока
        if (checkWinner("X")) {
            document.getElementById("message").textContent = "Игрок победил!";
            gameEnded = true;
        } else if (isBoardFull()) {
            document.getElementById("message").textContent = "Ничья!";
            gameEnded = true;
        } else {
            // Переключаем на бота (O)
            currentPlayer = "O";
            // Задержка перед ходом бота для лучшего UX
            setTimeout(botMove, 300);
        }
    }
}

// Функция выполнения хода (общая для игрока и бота)
function makeMove(cellIndex, player) {
    const cell = document.getElementById(`cell${cellIndex}`);
    cell.textContent = player;
    cell.setAttribute('data-value', player);
    board[cellIndex] = player;
}

// Ход бота с использованием алгоритма минимакс
function botMove() {
    if (gameEnded || currentPlayer !== "O") return;

    // Находим лучший ход для бота (O)
    let bestScore = -Infinity;
    let bestMove = -1;

    for (let i = 0; i < board.length; i++) {
        if (board[i] === "") {
            board[i] = "O"; // Пробуем поставить O
            let score = minimax(board, 0, false); // Оцениваем ход
            board[i] = ""; // Отменяем ход

            if (score > bestScore) {
                bestScore = score;
                bestMove = i;
            }
        }
    }

    // Делаем лучший ход
    if (bestMove !== -1) {
        makeMove(bestMove, "O");

        if (checkWinner("O")) {
            document.getElementById("message").textContent = "Бот победил!";
            gameEnded = true;
        } else if (isBoardFull()) {
            document.getElementById("message").textContent = "Ничья!";
            gameEnded = true;
        } else {
            currentPlayer = "X";
        }
    }
}

// Алгоритм минимакс
// isMaximizing = true  -> ход O (бот, максимизируем свой счёт)
// isMaximizing = false -> ход X (игрок, минимизируем счёт бота)
function minimax(board, depth, isMaximizing) {
    // Базовые случаи: проверка победы или ничьей
    if (checkWinner("O")) return 10 - depth;  // Победа бота (чем быстрее, тем лучше)
    if (checkWinner("X")) return depth - 10;  // Победа игрока (чем быстрее, тем хуже для бота)
    if (isBoardFull()) return 0;              // Ничья

    if (isMaximizing) {
        // Ход бота (O) — максимизируем результат
        let bestScore = -Infinity;
        for (let i = 0; i < board.length; i++) {
            if (board[i] === "") {
                board[i] = "O";
                let score = minimax(board, depth + 1, false);
                board[i] = "";
                bestScore = Math.max(score, bestScore);
            }
        }
        return bestScore;
    } else {
        // Ход игрока (X) — минимизируем результат (бот хочет, чтобы игрок не выиграл)
        let bestScore = Infinity;
        for (let i = 0; i < board.length; i++) {
            if (board[i] === "") {
                board[i] = "X";
                let score = minimax(board, depth + 1, true);
                board[i] = "";
                bestScore = Math.min(score, bestScore);
            }
        }
        return bestScore;
    }
}

// Функция проверки победителя
function checkWinner(player) {
    for (const pattern of winPatterns) {
        const [a, b, c] = pattern;
        if (board[a] === player && board[b] === player && board[c] === player) {
            return true;
        }
    }
    return false;
}

// Функция проверки заполненности поля
function isBoardFull() {
    return board.every(cell => cell !== "");
}

// Функция перезагрузки игры
function resetGame() {
    currentPlayer = "X";
    gameEnded = false;
    board = ["", "", "", "", "", "", "", "", ""];
    document.getElementById("message").textContent = "";
    
    // Очищаем все ячейки
    for (let i = 0; i < 9; i++) {
        const cell = document.getElementById(`cell${i}`);
        cell.textContent = "";
        cell.setAttribute('data-value', "");
    }
}