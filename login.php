<?php
/* ============================================================================
   login.php — Авторизация пользователя.
   Требование: при неверном логине/пароле показывать сообщение.
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$error = '';
$old_login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password']    ?? '';
    $old_login = $login;

    // Ищем пользователя по логину И паролю (пароль открытым текстом — как решили).
    $user = one('SELECT * FROM users WHERE login = ? AND password = ?', [$login, $password]);

    if ($user) {
        login_user((int)$user['id']);
        header('Location: cabinet.php');
        exit;
    }
    $error = 'Неверный логин или пароль';   // сообщение о некорректном вводе
}

layout_header('Вход');
?>
<div class="auth">
<h1><span class="sec-ico">🔑</span>Вход</h1>
<p class="section-caption">Войдите под своим логином и паролем.</p>
<form method="post" class="card">
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Логин
        <input name="login" value="<?= e($old_login) ?>" required>
    </label>
    <label>Пароль
        <input type="password" name="password" required>
    </label>
    <button type="submit">Войти</button>
    <p class="muted">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</form>
</div>
<?php
layout_footer();
