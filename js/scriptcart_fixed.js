var cart = {};

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function loadCart() {
    const raw = localStorage.getItem('cart');
    cart = raw ? JSON.parse(raw) : {};
}

// Функция для вывода товаров на страницу
function showCart(data) {
    data = JSON.parse(data);

    var fullsum = 0;
    var table_cart = "<table class='table_cart'><tr><th>Товар</th><th>Цена</th><th>Количество</th><th>Сумма</th></tr>";

    data.forEach(function (item) {
        var id = String(item.id);
        var price = Number(item.price);
        var count = cart[id] ? Number(cart[id]) : 1;
        if (count < 1) count = 1;

        var sum = price * count;
        fullsum += sum;

        // После инициализации храним расширенные данные (для POSTData и CountProduct).
        cart[id] = { name: item.name, price: price, count: count, sum: sum };

        table_cart += "<tr>";
        table_cart += "<td>" + escapeHtml(item.name) + "</td>";
        table_cart += "<td>" + price + "</td>";
        table_cart += "<td><div class='count_prod" + id + "'>" + count + "</div>";
        table_cart += "<button class='count-product-min' type='button' onclick=\"CountProduct(this.id," + price + ")\" id='1" + id + "'>-</button>";
        table_cart += "<button class='count-product-max' type='button' onclick=\"CountProduct(this.id," + price + ")\" id='2" + id + "'>+</button></td>";
        table_cart += "<td class='sum_stroka" + id + "'>" + sum + "</td>";
        table_cart += "</tr>";
    });

    table_cart += "<tfoot><tr><td>Итоговая сумма:</td><td></td><td></td><td class='FullSum'>" + fullsum + "</td></tr></tfoot></table>";
    $('.main-cart').html(table_cart);
}

// Функция изменения количества товара
function CountProduct(button_id, price) {
    var click_button = String(button_id)[0];
    var count_update = String(button_id).slice(1);
    if (!cart[count_update]) return;

    if (click_button == "1") {
        cart[count_update].count--;
        if (cart[count_update].count < 1) cart[count_update].count = 1;
    } else if (click_button == "2") {
        cart[count_update].count++;
    }

    cart[count_update].sum = Number(price) * cart[count_update].count;
    $('.sum_stroka' + count_update).html(cart[count_update].sum);
    $('.count_prod' + count_update).html(cart[count_update].count);

    var out = 0;
    for (var key in cart) {
        if (cart[key] && typeof cart[key].sum !== 'undefined') {
            out += Number(cart[key].sum);
        }
    }
    $('.FullSum').html(out);

    // Сохраняем только количества, как ожидает скрипт магазина (scriptjs.js)
    var minimalCart = {};
    for (var key2 in cart) {
        if (cart[key2]) minimalCart[key2] = cart[key2].count;
    }
    localStorage.setItem('cart', JSON.stringify(minimalCart));
}

function POSTData() {
    var num = localStorage.getItem('num_order');
    num = num ? Number(num) : 0;
    num++;
    localStorage.setItem('num_order', num);

    var today = new Date();
    var dateSrc = today.toLocaleString('ru-RU', { year: 'numeric', month: 'numeric', day: 'numeric' });
    var dateDst = dateSrc.split(".").reverse().join("-");

    var strData = {
        num_order: String(num),
        date_order: dateDst,
        users: "",
        product: "",
        price: "",
        count_product: "",
        summa: ""
    };

    for (var key in cart) {
        if (!cart[key] || !cart[key].name) continue;
        strData.product += cart[key].name + ",";
        strData.price += cart[key].price + ",";
        strData.count_product += cart[key].count + ",";
        strData.summa += cart[key].sum + ",";
    }

    $.post(
        "core.php",
        { action: "inputData", Data_Order: strData },
        function () {
            var mainCartElement = document.getElementById("main-cart");
            if (mainCartElement) {
                mainCartElement.innerHTML = "Заказ оформлен! Детали заказа можно посмотреть в личном кабинете по кнопке История заказов.";
                mainCartElement.style.color = "#FFC000";
                mainCartElement.style.fontSize = "24px";
                mainCartElement.style.textAlign = "center";
                mainCartElement.style.marginTop = "20px";
            }
        }
    );

    localStorage.removeItem('cart');
}

$(document).ready(function () {
    loadCart();

    var mainCartElement = document.querySelector('.main-cart');
    if (!mainCartElement) return;

    var keys = Object.keys(cart);
    if (keys.length === 0) {
        mainCartElement.innerHTML = 'Корзина пуста!';
        mainCartElement.style.color = '#FFC000';
        mainCartElement.style.fontSize = '24px';
        mainCartElement.style.textAlign = 'center';
        mainCartElement.style.marginTop = '20px';
        return;
    }

    var out = keys.join(',') + ',';
    $.post("core.php", { action: "init", id_product: String(out) }, showCart);
});

