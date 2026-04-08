# Инструкция по первоначальной настройке БД / Database Setup

Этот файл содержит инструкции для инициализации базы данных.

## Быстрая настройка 🚀

### 1. Создание базы данных через phpMyAdmin:

```sql
CREATE DATABASE IF NOT EXISTS `social` 
  DEFAULT CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `social`;
```

### 2. Необходимые таблицы:

> **Примечание**: Вставьте приведённые SQL-скрипты ниже в phpMyAdmin или через команду MySQL CLI.

#### Таблица пользователей:
```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `avatar` VARCHAR(255),
  `bio` TEXT,
  `admin` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `username_idx` (`username`),
  KEY `email_idx` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица постов:
```sql
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `content` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` INT DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `user_id_idx` (`user_id`),
  KEY `created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица комментариев:
```sql
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `content` LONGTEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `deleted` INT DEFAULT 0,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `post_id_idx` (`post_id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица сообщений (Чат):
```sql
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `from_user_id` INT NOT NULL,
  `to_user_id` INT NOT NULL,
  `content` LONGTEXT,
  `is_read` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `from_user_idx` (`from_user_id`),
  KEY `to_user_idx` (`to_user_id`),
  KEY `created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица видео:
```sql
CREATE TABLE IF NOT EXISTS `videos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255),
  `filename` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица музыки:
```sql
CREATE TABLE IF NOT EXISTS `music` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255),
  `filename` VARCHAR(255) NOT NULL,
  `artist` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица заказов (Магазин):
```sql
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total` DECIMAL(10, 2),
  `status` VARCHAR(50) DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Таблица товаров (Магазин):
```sql
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2),
  `image` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Проверка подключения:

После создания БД проверьте подключение через браузер или запустив:

```bash
php -r "
require 'db.php';
if (\$conn->connect_error) {
    echo 'Connection failed: ' . \$conn->connect_error;
} else {
    echo 'Connected successfully!';
}
"
```

### 4. Тестовые данные (опционально):

```sql
-- Тестовый пользователь
INSERT INTO `users` (`username`, `email`, `password`) 
VALUES ('testuser', 'test@example.com', MD5('password'));

-- Тестовый пост
INSERT INTO `posts` (`user_id`, `content`) 
VALUES (1, 'Привет, это мой первый пост!');
```

## Важные замечания ⚠️

- Убедитесь что используется `utf8mb4` кодировка для поддержки emoji
- Регулярно создавайте резервные копии БД
- Используйте параметризованные запросы для защиты от SQL injection
- Никогда не коммитьте пароли или учётные данные в репозиторий

## Миграция с существующей БД 📦

Если у вас уже есть работающая социальная сеть:

1. Экспортируйте текущюю БД:
```bash
mysqldump -u root -p social > social_backup.sql
```

2. Проверьте совместимость структур таблиц
3. Применяйте SQL скрипты выше для отсутствующих таблиц
4. Обновите конфигурацию в `.env`

---

Для вопросов по настройке БД создавайте Issues в репозитории.
