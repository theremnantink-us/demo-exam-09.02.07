<?php
/* ----------------------------------------------------------------------------
   layout.php — общая шапка и подвал сайта (чтобы не повторять на каждой странице).
   ИНСТРУКЦИЯ (удали после настройки):
     • Название сайта «Корочки.есть» — меняешь во всех местах этого файла.
     • Шрифты подключены ссылкой Google Fonts в <head> (на экзамене без интернета
       можно скачать .woff2 и подключить локально). Цвета — в assets/style.css.
     • Контакты в подвале (телефон/почта/адрес) — поменяй на свои.
---------------------------------------------------------------------------- */
require_once __DIR__ . '/auth.php';

function layout_header(string $title, string $icon = '', string $caption = ''): void {
    $u   = current_user();
    $cur = basename($_SERVER['PHP_SELF'] ?? '');           // текущая страница (для подсветки пункта)
    $act = fn(string $p) => $cur === $p ? ' class="active"' : '';
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — Корочки.есть</title>
    <!-- Шрифты сайта: Manrope (заголовки) + Inter (текст) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><span class="logo-text">Корочки.есть</span></a>
    <nav class="nav">
        <div class="nav-links">
            <a href="index.php"<?= $act('index.php') ?>>Главная</a>
            <a href="index.php#reviews">Отзывы</a>
            <?php if ($u): ?>
                <a href="cabinet.php"<?= $act('cabinet.php') ?>>Личный кабинет</a>
                <a href="order.php"<?= $act('order.php') ?>>Подать заявку</a>
            <?php endif; ?>
        </div>
        <div class="nav-right">
            <?php if ($u): ?>
                <a class="nav-user" href="cabinet.php" title="Личный кабинет"><?= e($u['fio']) ?></a>
                <a class="btn-nav" href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php"<?= $act('login.php') ?>>Войти</a>
                <a class="btn-nav" href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main class="container fade-in">
    <?php if ($title !== ''): ?>
        <div class="page-head">
            <h1><?php if ($icon): ?><span class="sec-ico"><?= $icon ?></span><?php endif; ?><?= e($title) ?></h1>
            <?php if ($caption): ?><p class="section-caption"><?= e($caption) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>
<?php
}

function layout_footer(): void {
    $u = current_user();
    // Несколько курсов для подвала (берём из базы)
    $courses = q('SELECT name FROM courses WHERE is_active = 1 ORDER BY id LIMIT 4');
    ?>
</main>
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span class="logo-text">Корочки.есть</span>
            <p class="muted small">Онлайн-курсы дополнительного профессионального образования</p>
        </div>

        <div class="footer-col">
            <h4>Навигация</h4>
            <a href="index.php">Главная</a>
            <a href="index.php#reviews">Отзывы</a>
            <a href="index.php#contacts">Обратная связь</a>
            <?php if ($u): ?>
                <a href="cabinet.php">Личный кабинет</a>
                <a href="order.php">Подать заявку</a>
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h4>Курсы</h4>
            <?php foreach ($courses as $cr): ?>
                <a href="<?= $u ? 'order.php' : 'login.php' ?>"><?= e($cr['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="footer-col">
            <h4>Контакты</h4>
            <span class="muted small">8(800)555-35-35</span>
            <span class="muted small">info@korochki.example</span>
            <span class="muted small">г. Москва, ул. Образцовая, д. 1</span>
        </div>
    </div>
    <div class="footer-bottom">
        <span class="muted small">© <?= date('Y') ?> Корочки.есть</span>
    </div>
</footer>
<script src="assets/app.js"></script>
</body>
</html>
<?php
}
