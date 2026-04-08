// loader.js

window.addEventListener('load', function() {
    // Находим элемент прелоадера
    var loader = document.querySelector('.loader');
    
    // Проверяем, существует ли элемент, чтобы избежать ошибок
    if (loader) {
        // Устанавливаем задержку перед началом исчезновения (3 секунды)
        setTimeout(function() {
            // Добавляем transition для плавного исчезновения
            loader.style.transition = 'opacity 0.5s ease-in-out';
            // Устанавливаем нулевую прозрачность для начала анимации исчезновения
            loader.style.opacity = '0';
            
            // После завершения анимации исчезновения скрываем прелоадер
            setTimeout(function() {
                loader.style.display = 'none'; // Скрываем прелоадер
                loader.style.zIndex = 'auto'; // Устанавливаем z-index на auto
            }, 500); // 0.5 секунды на анимацию исчезновения
            
        }, 3000); // 3 секунды - длительность отображения прелоадера
    }
});