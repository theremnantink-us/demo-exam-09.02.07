<?php
/* ============================================================================
   layout.php — общая «шапка» и «подвал» страниц, чтобы не дублировать HTML.
   Использование на странице:
       layout_header('Заголовок вкладки');
       ... содержимое ...
       layout_footer();
   ========================================================================== */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';   // нужна функция e() для безопасного вывода

function layout_header(string $title): void {
    $u = current_user();
    ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <!-- Адаптив: обязательно для корректного отображения на смартфоне 390x844 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — <?= e(app('name')) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><?= e(app('name')) ?></a>
    <nav class="nav">
        <?php if ($u): ?>
            <span class="nav-user">👤 <?= e($u['fio']) ?></span>
            <a href="cabinet.php">Кабинет</a>
            <a href="order.php">Новая <?= e(app('entity_label')) ?></a>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
            <a href="admin_login.php">Админ</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container fade-in">
<?php
}

function layout_footer(): void {
    ?>
</main>
<footer class="footer">© <?= date('Y') ?> <?= e(app('name')) ?></footer>
<script src="assets/app.js"></script>
</body>
</html>
<?php
}
