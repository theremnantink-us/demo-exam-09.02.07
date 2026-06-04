<?php
/* ============================================================================
   layout.php — общая шапка/подвал. Внешний вид берётся из theme.php:
   цвета подставляются в CSS-переменные, подключаются шрифты (Google + фолбэк).
   Использование:
       layout_header('Заголовок', '🔑', 'Подпись раздела');
       ... контент ...
       layout_footer();
   ========================================================================== */
require_once __DIR__ . '/auth.php';

function layout_header(string $title, string $icon = '', string $caption = ''): void {
    $u = current_user();
    $c = theme('colors');
    $f = theme('fonts');
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — <?= e(theme('site_name')) ?></title>

    <?php /* Шрифты: Google-ссылка (если есть интернет) + локальный фолбэк из theme */ ?>
    <?php if (!empty($f['google_link'])): ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= e($f['google_link']) ?>" rel="stylesheet">
    <?php endif; ?>
    <?php if (!empty($f['local_faces'])): ?><style><?= $f['local_faces'] ?></style><?php endif; ?>

    <?php /* Цвета темы -> CSS-переменные. Меняешь theme.php — меняется весь сайт. */ ?>
    <style>
        :root {
            --accent: <?= e($c['accent']) ?>;
            --accent-2: <?= e($c['accent_2']) ?>;
            --bg: <?= e($c['bg']) ?>;
            --surface: <?= e($c['surface']) ?>;
            --surface-2: <?= e($c['surface_2']) ?>;
            --text: <?= e($c['text']) ?>;
            --muted: <?= e($c['muted']) ?>;
            --border: <?= e($c['border']) ?>;
            --font-heading: <?= $f['heading'] ?>;
            --font-body: <?= $f['body'] ?>;
        }
    </style>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">
        <?php if (theme('logo_img')): ?>
            <img src="<?= e(theme('logo_img')) ?>" alt="<?= e(theme('site_name')) ?>" class="logo-img">
        <?php else: ?>
            <span class="logo-text"><?= e(theme('logo_text')) ?></span>
        <?php endif; ?>
    </a>
    <nav class="nav">
        <a href="index.php">Главная</a>
        <?php if ($u): ?>
            <a href="cabinet.php">Мои <?= e(app('entity_label_gen')) ?></a>
            <a href="order.php">Новая <?= e(app('entity_label')) ?></a>
            <span class="nav-user">👤 <?= e($u['fio']) ?></span>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
            <a href="admin_login.php">Админ</a>
        <?php endif; ?>
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
    $ct = theme('contacts');
    ?>
</main>
<footer class="footer">
    <div class="footer-cols">
        <div><strong><?= e(theme('site_name')) ?></strong><br><span class="muted"><?= e(theme('tagline')) ?></span></div>
        <div class="muted">
            📞 <?= e($ct['phone']) ?><br>
            ✉ <?= e($ct['email']) ?><br>
            📍 <?= e($ct['address']) ?>
        </div>
    </div>
    <div class="muted small">© <?= date('Y') ?> <?= e(theme('site_name')) ?></div>
</footer>
<script src="assets/app.js"></script>
</body>
</html>
<?php
}
