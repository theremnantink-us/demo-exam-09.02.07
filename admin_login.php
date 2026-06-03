<?php
/* ============================================================================
   admin_login.php — Вход в админку. Логин/пароль из config (adminka/password).
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password']    ?? '';
    $admin = app('admin');

    if ($login === $admin['login'] && $password === $admin['password']) {
        login_admin();
        header('Location: admin.php');
        exit;
    }
    $error = 'Неверный логин или пароль администратора';
}

layout_header('Вход администратора');
?>
<div class="auth">
<h1><span class="sec-ico">🛠</span>Панель администратора</h1>
<p class="section-caption">Служебный вход для управления заявками.</p>
<form method="post" class="card">
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Логин <input name="login" required></label>
    <label>Пароль <input type="password" name="password" required></label>
    <button type="submit">Войти</button>
</form>
</div>
<?php
layout_footer();
