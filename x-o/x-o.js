// x-o.js
// Игра Крестики-нолики

// Начинает игрок X
let currentPlayer = "X";

// Устанавливает начальное значение переменной, которая показывает, завершена ли игра
let gameEnded = false;

// Создает пустое поле для игры (9 ячеек)
let board = ["", "", "", "", "", "", "", "", ""];

// Определяет выигрышные комбинации для игры
const winPatterns = [
    [0, 1, 2], [3, 4, 5], [6, 7, 8], // Горизонтальные линии
    [0, 3, 6], [1, 4, 7], [2, 5, 8], // Вертикальные линии
    [0, 4, 8], [2, 4, 6]             // Диагональные линии
];

// Функция, вызываемая при клике на ячейку
function cellClicked(cellIndex) {
    // Проверяет, что игра не завершена и выбранная ячейка пуста
    if (!gameEnded && board[cellIndex] === "") {
        // Получает элемент ячейки по его индексу
        const cell = document.getElementById(`cell${cellIndex}`);
        
        // Устанавливает текст ячейки в значение текущего игрока
        cell.textContent = currentPlayer;
        
        // Устанавливает атрибут data-value в значение текущего игрока
        cell.setAttribute('data-value', currentPlayer);
        
        // Записывает значение текущего игрока в массив игрового поля
        board[cellIndex] = currentPlayer;
        
        // Проверяет, выиграл ли текущий игрок
        if (checkWinner(currentPlayer)) {
            // Выводит сообщение о победе текущего игрока
            document.getElementById("message").textContent = `Игрок ${currentPlayer} победил!`;
            // Устанавливает, что игра завершена
            gameEnded = true;
        } else if (isBoardFull()) {
            // Выводит сообщение о ничьей, если игровое поле полностью заполнено
            document.getElementById("message").textContent = "Ничья!";
            // Устанавливает, что игра завершена
            gameEnded = true;
        } else {
            // Переключает текущего игрока на другого
            currentPlayer = currentPlayer === "X" ? "O" : "X";
        }
    }
}

// Функция, которая проверяет, выиграл ли игрок
function checkWinner(player) {
    // Перебирает все выигрышные комбинации
    for (const pattern of winPatterns) {
        // Получает индексы ячеек из текущей выигрышной комбинации
        const [a, b, c] = pattern;
        
        // Проверяет, что все три ячейки имеют значение текущего игрока
        if (board[a] === player && board[b] === player && board[c] === player) {
            // Возвращает true, если текущий игрок выиграл
            return true;
        }
    }
    // Возвращает false, если текущий игрок не выиграл
    return false;
}

// Функция, которая проверяет, заполнено ли игровое поле
function isBoardFull() {
    // Возвращает true, если все ячейки заполнены
    return board.every(cell => cell !== "");
}

// Функция для сброса игры (опционально)
function resetGame() {
    // Сбрасываем состояние игры
    currentPlayer = "X";
    gameEnded = false;
    board = ["", "", "", "", "", "", "", "", ""];
    
    // Очищаем все ячейки
    for (let i = 0; i < 9; i++) {
        const cell = document.getElementById(`cell${i}`);
        if (cell) {
            cell.textContent = "";
            cell.removeAttribute('data-value');
        }
    }
    
    // Очищаем сообщение
    const messageElement = document.getElementById("message");
    if (messageElement) {
        messageElement.textContent = "";
    }
}