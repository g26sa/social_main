// Выполнение функции после загрузки страницы
var cart = {};

$(document).ready(function() {
    loadCart();
});
// Функция получения данных из localStorage
function loadCart() {
    // Проверка существования значения в localStorage
    if (localStorage.getItem('cart')) {
        // Помещение данных из localStorage в переменную
        cart = JSON.parse(localStorage.getItem('cart'));
        
        // Формирование строки из значений localStorage
        var out = '';
        for (var key in cart) {
            out += key + ",";
        }
        
        // Передача строки методом post в файл core.php
        $.post(
            "core.php",
            { "action": "init", "id_product": String(out) },
            showCart
        );
    } else {
        // Если данных в корзине нет, то выводим сообщение, что корзину пуста
        var mainCartElement = document.querySelector('.main-cart');
        mainCartElement.innerHTML = 'Корзина пуста!';
        mainCartElement.style.color = '#FFC000';
        mainCartElement.style.fontSize = '24px';
        mainCartElement.style.textAlign = 'center';
        mainCartElement.style.marginTop = '20px';
    }
}

// Функция для вывода товаров на страницу
function showCart(data) {
    // Преобразуем полученные данные из файла core.php в список
    data = JSON.parse(data);
    
    // Переменная для подсчёта итоговой суммы
    var fullsum = 0;
    
    // Создаём строку кода для формирования таблицы
    var table_cart = "<table class='table_cart'><tr><th>Товар</th><th>Цена</th><th>Количество</th><th>Сумма</th></tr>";
    
    // Заполнение ячеек таблицы данными
    for (var id in data) {
        // Изменение значения итоговой суммы
        fullsum += Number(data[id].price);
        
        // Перезаписываем данные в списке cart
        cart[data[id].id] = {
            'name': data[id].name,
            'price': Number(data[id].price),
            'count': 1,
            'sum': Number(data[id].price)
        };
        
        // Формируем строку
        table_cart += "<tr>";
        // Заполняем ячейку названием товара
        table_cart += '<td>' + data[id].name + '</td>';
        // Заполняем ячейку ценой товара
        table_cart += '<td>' + data[id].price + '</td>';
        // Ячейка с количеством товара и кнопками
        table_cart += '<td><div class="count_prod' + data[id].id + '">1</div>';
        // Кнопка уменьшения количества
        table_cart += '<button class="count-product-min" type="submit" onclick="CountProduct(this.id,' + data[id].price + ')" id="1' + data[id].id + '">-</button>';
        // Кнопка увеличения количества
        table_cart += '<button class="count-product-max" type="submit" onclick="CountProduct(this.id,' + data[id].price + ')" id="2' + data[id].id + '">+</button></td>';
        // Ячейка с суммой по товару
        table_cart += '<td class="sum_stroka' + data[id].id + '">' + data[id].price + '</td>';
        table_cart += '</tr>';
    }
    
    // Формируем подвал таблицы с итоговой суммой по заказу
    table_cart += "<tfoot><tr><td>Итоговая сумма:</td><td></td><td></td><td class='FullSum'>" + fullsum + "</td></tr></tfoot></table>";
    
    // Помещение строки кода в контейнер main-cart
    $('.main-cart').html(table_cart);
}

// Функция изменения количества товара
function CountProduct(button_id, price) {
    // Получаем первую цифру id кнопки (уменьшать или увеличивать)
    var click_button = button_id[0];
    
    // Получаем последующие цифры id кнопки (какое значение изменяем)
    var count_update = button_id.slice(1);
    
    // Если нажата кнопка уменьшения количества товара
    if (click_button == "1") {
        cart[count_update].count--;
        // Меньше одной единицы товара купить не получится
        if (cart[count_update].count <= 1) {
            cart[count_update].count = 1;
        }
    } else if (click_button == "2") {
        // Если нажата кнопка увеличения количества товара
        cart[count_update].count++;
    }
    
    // Помещаем значение суммы по конкретному товару в список
    cart[count_update].sum = price * cart[count_update].count;
    
    // Выводим значение суммы в таблицу
    $('.sum_stroka' + count_update).html(cart[count_update].sum);
    
    // Выводим значение количества по товару в таблицу
    $('.count_prod' + count_update).html(cart[count_update].count);
    
    // Цикл для подсчёта итоговой суммы по заказу
    var out = 0;
    for (var key in cart) {
        out += cart[key].sum;
    }
    
    // Вывод итоговой суммы в таблицу
    $('.FullSum').html(out);
}

// Список с данными для заказа
var strData = {
    "num_order": "",
    "date_order": "",
    "users": "",
    "product": "",
    "price": "",
    "count_product": "",
    "summa": ""
};

// Функция для передачи данных в файл core.php
function POSTData() {
    // Получаем номер заказа из localStorage
    var num = localStorage.getItem('num_order');
    num++;
    localStorage.setItem("num_order", num);
    
    // Создаём объект Date для работы с датой
    var today = new Date();
    
    // Получаем локальную дату в строковом формате
    var dateSrc = today.toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'numeric',
        day: 'numeric'
    });
    
    // Преобразуем дату из формата 01.01.2024 в 2024-01-01
    dateDst = dateSrc.split(".").reverse().join("-");
    
    // Помещаем в список номер заказа
    strData.num_order = num;
    
    // Помещаем в список дату заказа
    strData.date_order = dateDst;
    
    // Заполняем список остальными данными
    for (var key in cart) {
        strData.product += cart[key].name + ",";
        strData.price += cart[key].price + ",";
        strData.count_product += cart[key].count + ",";
        strData.summa += cart[key].sum + ",";
    }
    
    // Передача данных методом post в файл core.php
    $.post(
        "core.php",
        { "action": "inputData", "Data_Order": strData }
    );
    
    // Удаляем значение из localStorage
    localStorage.removeItem('cart');
    
    var mainCartElement = document.getElementById("main-cart");
    mainCartElement.innerHTML = "";
    mainCartElement.innerHTML = "Заказ оформлен! Детали заказа можно посмотреть в личном кабинете по кнопке История заказов.";
    mainCartElement.style.color = "#FFC000";
    mainCartElement.style.fontSize = "24px";
    mainCartElement.style.textAlign = "center";
    mainCartElement.style.marginTop = "20px";
}