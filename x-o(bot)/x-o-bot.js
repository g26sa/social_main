// Начинает игрок X
let currentPlayer = "X";

// Завершена ли игра
let gameEnded = false;

// Игровое поле
let board = ["", "", "", "", "", "", "", "", ""];

// Выигрышные комбинации
const winPatterns = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8],
    [0, 3, 6], [1, 4, 7], [2, 5, 8],
    [0, 4, 8], [2, 4, 6]
];

// Функция, вызываемая при клике на ячейку
function cellClicked(cellIndex) {
    // Если игра не завершена и ячейка пуста
    if (!gameEnded && board[cellIndex] === "") {
        const cell = document.getElementById(`cell${cellIndex}`);
        cell.textContent = currentPlayer;
        cell.setAttribute('data-value', currentPlayer);
        board[cellIndex] = currentPlayer;

        // Проверка победы или ничьей после хода игрока
        if (checkWinner(currentPlayer)) {
            document.getElementById("message").textContent = `Игрок ${currentPlayer} победил!`;
            gameEnded = true;
        } else if (isBoardFull()) {
            document.getElementById("message").textContent = "Ничья!";
            gameEnded = true;
        } else {
            // Смена игрока
            currentPlayer = (currentPlayer === "X") ? "O" : "X";

            // Если теперь ходит бот (O)
            if (currentPlayer === "O" && !gameEnded) {
                setTimeout(botMove, 300);
            }
        }
    }
}

// Функция хода бота
function botMove() {
    if (gameEnded) return;

    // Находим все пустые ячейки
    let emptyCells = [];
    for (let i = 0; i < board.length; i++) {
        if (board[i] === "") {
            emptyCells.push(i);
        }
    }

    if (emptyCells.length === 0) return;

    // Бот выбирает случайную пустую ячейку
    const randomIndex = Math.floor(Math.random() * emptyCells.length);
    const cellIndex = emptyCells[randomIndex];

    const cell = document.getElementById(`cell${cellIndex}`);
    cell.textContent = currentPlayer;
    cell.setAttribute('data-value', currentPlayer);
    board[cellIndex] = currentPlayer;

    // Проверка победы или ничьей после хода бота
    if (checkWinner(currentPlayer)) {
        document.getElementById("message").textContent = `Игрок ${currentPlayer} победил!`;
        gameEnded = true;
    } else if (isBoardFull()) {
        document.getElementById("message").textContent = "Ничья!";
        gameEnded = true;
    } else {
        // Снова ход игрока
        currentPlayer = (currentPlayer === "X") ? "O" : "X";
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