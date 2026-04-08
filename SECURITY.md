# Безопасность / Security Guide

Важные рекомендации по безопасности при развёртывании и использовании проекта.

⚠️ **ВАЖНО**: Это учебный проект. Перед использованием в production рекомендуется провести полный security audit.

## Критические моменты 🔒

### 1. Переменные окружения

**Никогда** не коммитьте файл `.env` с реальными данными!

```bash
# ✅ ПРАВИЛЬНО - .gitignore исключает .env
.env

# ✅ Коммитьте только пример
.env.example

# ❌ НЕПРАВИЛЬНО - не делайте так!
.env  # содержит пароли
secrets.json
config.php  # с хардкодированными паролями
```

### 2. Пароли БД

Используйте надёжные пароли:
- Минимум 12 символов
- Комбинация букв, цифр, спецсимволов
- Генерируйте уникальные пароли для каждого окружения

```bash
# Генерация сильного пароля в Linux/Mac:
openssl rand -base64 16

# Или используйте password manager как 1Password, LastPass
```

### 3. SQL Injection

Всегда используйте подготовленные запросы:

```php
// ❌ НЕБЕЗОПАСНО!
$result = $conn->query("SELECT * FROM users WHERE id = " . $_GET['id']);

// ✅ БЕЗОПАСНО!
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$result = $stmt->get_result();
```

### 4. XSS (Cross-Site Scripting)

Экранируйте пользовательский ввод:

```php
// ❌ НЕБЕЗОПАСНО!
echo "<h1>" . $_POST['title'] . "</h1>";

// ✅ БЕЗОПАСНО!
echo "<h1>" . htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') . "</h1>";
```

```javascript
// ✅ БЕЗОПАСНО в современных браузерах
element.textContent = userInput;  // Текст без HTML

// ❌ НЕБЕЗОПАСНО
element.innerHTML = userInput;  // Может выполнить скрипты
```

### 5. CSRF (Cross-Site Request Forgery)

Используйте токены CSRF для критичных операций:

```php
// Генерация токена
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// В форме
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// При обработке
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('Ошибка CSRF!');
}
```

### 6. Загрузка файлов

Используйте строгую валидацию:

```php
// ✅ БЕЗОПАСНАЯ загрузка
function is_safe_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Проверьте MIME type по содержимому, не по расширению
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    return in_array($mimetype, $allowed_types);
}
```

### 7. Хеширование паролей

Используйте bcrypt или argon2:

```php
// ✅ ПРАВИЛЬНО
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Проверка
if (password_verify($password, $hashed)) {
    // Пароль верен
}

// ❌ НЕПРАВИЛЬНО
$hashed = md5($password);  // Уязвимо к перебору
$hashed = sha1($password); // Уязвимо к перебору
```

## Развёртывание в Production 🚀

### 1. HTTPS

```apache
# Обязательно используйте HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 2. Permissions (Права доступа)

```bash
# PHP файлы: read-only для веб-сервера
chmod 644 *.php
chmod 644 -R css/
chmod 644 -R js/

# Директории с записью: более лимитированные права
chmod 755 uploads/
chmod 755 avatars/
chmod 755 music/uploads/
chmod 755 video/uploads/

# Запретить выполнение PHP в upload директориях
echo "php_flag engine off" > uploads/.htaccess
echo "php_flag engine off" > avatars/.htaccess
```

### 3. Конфигурация Apache/Nginx

```apache
# .htaccess для Apache
<IfModule mod_headers.c>
    # Защита от XSS
    Header set X-XSS-Protection "1; mode=block"
    
    # Защита от Clickjacking
    Header set X-Frame-Options "SAMEORIGIN"
    
    # Content Security Policy (базовая)
    Header set Content-Security-Policy "default-src 'self'"
</IfModule>
```

```nginx
# Nginx конфигурация
server {
    # ...
    
    # Запретить доступ к скрытым файлам
    location ~ /\. {
        deny all;
    }
    
    # Запретить выполнение PHP в директориях загрузок
    location ~ ^/(uploads|avatars)/.+\.php$ {
        deny all;
    }
}
```

### 4. Логирование

```php
// Логируйте подозрительоническую активность
function log_security_event($event, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'details' => $details
    ];
    
    error_log(json_encode($log_entry));
}

// Использование
log_security_event('Failed login attempt', ['username' => $username]);
```

## Регулярное обслуживание 🔧

- [ ] Еженедельные резервные копии БД
- [ ] Ежемесячная проверка логов на аномальную активность
- [ ] Обновление PHP и MySQL при выходе патчей безопасности
- [ ] Аудит прав доступа файлов
- [ ] Проверка на уязвимости зависимостей

## Инструменты для проверки безопасности 🛠️

- [OWASP ZAP](https://www.zaproxy.org/) - сканирование уязвимостей
- [Burp Suite Community](https://portswigger.net/burp/communitydownload) - тестирование безопасности
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) - статический анализ
- [SonarQube](https://www.sonarqube.org/) - анализ качества кода

## Реагирование на инциденты 🚨

Если обнаружена уязвимость:

1. **Немедленно исправьте** на production
2. **Проверьте логи** на следы компрометации
3. **Уведомите пользователей** если скомпрометированы данные
4. **Создайте security patch** в отдельной ветке
5. **Проведите post-mortem** анализ

## Дополнительные ресурсы 📚

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Mozilla Security Guidelines](https://infosec.mozilla.org/)
- [CWE List](https://cwe.mitre.org/)

---

Безопасность - это ответственность каждого разработчика! 🛡️
