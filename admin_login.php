<?php
/* ============================================================================
   admin_login.php — Вход в админку. Логин/пароль из config (Admin / KorokNET).
   Защита: CSRF-токен + ограничение попыток (брутфорс).
   ========================================================================== */
require_once __DIR__ . '/layout.php';

if (is_admin()) { header('Location: admin.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    if (!rate_limit('admin_login', 5, 60)) {
        $error = 'Слишком много попыток. Подождите минуту.';
    } else {
        $login    = sanitize($_POST['login'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $admin = app('admin');
        // hash_equals — сравнение без утечки по времени
        if (hash_equals($admin['login'], $login) && hash_equals($admin['password'], $password)) {
            login_admin();
            header('Location: admin.php');
            exit;
        }
        $error = 'Неверный логин или пароль администратора';
    }
}

layout_header('');
?>
<div class="auth">
<h1><span class="sec-ico">🛠</span>Панель администратора</h1>
<p class="section-caption">Служебный вход для управления заявками.</p>
<form method="post" class="card">
    <?= csrf_field() ?>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Логин <input name="login" required></label>
    <label>Пароль <input type="password" name="password" required></label>
    <button type="submit">Войти</button>
</form>
</div>
<?php
layout_footer();
