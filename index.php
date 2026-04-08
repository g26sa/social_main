<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Социальная сеть с 0</title>
    <link rel="stylesheet" href="css/menu.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffd43b;
            color: #d35400;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .landing {
            text-align: center;
            width: min(100%, 980px);
            padding: 40px 24px;
        }
        .landing__title {
            margin: 0;
            font-size: clamp(3.5rem, 8vw, 7rem);
            line-height: 0.9;
            font-weight: 900;
            letter-spacing: -0.05em;
            color: #f36f05;
            text-transform: uppercase;
            text-shadow: 0 10px 35px rgba(0, 0, 0, 0.1);
        }
        .landing__subtitle {
            margin: 20px auto 36px;
            max-width: 760px;
            font-size: clamp(1.1rem, 2vw, 1.8rem);
            color: #3f2b00;
            line-height: 1.5;
        }
        .landing__button {
            display: inline-block;
            padding: 18px 38px;
            border-radius: 999px;
            background: #ff8c00;
            color: #ffffff;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 700;
            transition: transform 180ms ease, background 180ms ease, box-shadow 180ms ease;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.12);
        }
        .landing__button:hover {
            transform: translateY(-3px);
            background: #d45b00;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.18);
        }
        .landing__hint {
            margin-top: 24px;
            font-size: 0.95rem;
            color: #553500;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/menu.php'; ?>
    <section class="landing">
        <h1 class="landing__title">Социальная сеть с 0</h1>
        <p class="landing__subtitle">Зарегистрируйся, создавай посты, общайся, играй и слушай музыку — всё в одной сети.</p>
        <a class="landing__button" href="<?php echo isset($_SESSION['id']) ? 'posts.php' : 'auth.php'; ?>">
            <?php echo isset($_SESSION['id']) ? 'Перейти к постам' : 'ойти в сеть'; ?>
        </a>
        <div class="landing__hint">Нажми на кнопку в углу, чтобы открыть меню.</div>
    </section>
</body>
</html>
