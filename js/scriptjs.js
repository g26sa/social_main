var cart = {};

// Клик по кнопке "Купить"
function func(click_id) {
    if (cart[click_id] === undefined) {
        cart[click_id] = 1;
    }
    saveCart();
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function loadCart() {
    const raw = localStorage.getItem('cart');
    if (raw) {
        cart = JSON.parse(raw);
    }
}

// Выполнение функции после загрузки страницы
$(document).ready(function () {
    loadCart();
});
