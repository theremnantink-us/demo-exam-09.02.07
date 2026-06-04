<?php
/* ============================================================================
   login.php — Авторизация. Сообщения при неверном вводе. Защита от перебора
   паролей (rate_limit). Ссылка-переход на регистрацию. CSRF-токен.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

if (current_user()) { header('Location: cabinet.php'); exit; }

$error = '';
$old_login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    // Защита от брутфорса: не более 5 попыток за 60 секунд.
    if (!rate_limit('login', 5, 60)) {
        $error = 'Слишком много попыток входа. Подождите минуту.';
    } else {
        $login    = sanitize($_POST['login'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $old_login = $login;

        $user = authenticate_user($login, $password);   // сверка пароля по хешу
        if ($user) {
            login_user((int)$user['id']);
            header('Location: cabinet.php');
            exit;
        }
        $error = 'Неверный логин или пароль';
    }
}

layout_header('');
?>
<div class="auth">
<h1><span class="sec-ico">🔑</span>Вход</h1>
<p class="section-caption">Войдите под своим логином и паролем.</p>
<form method="post" class="card">
    <?= csrf_field() ?>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Логин
        <input name="login" value="<?= e($old_login) ?>" required>
    </label>
    <label>Пароль
        <input type="password" name="password" required>
    </label>
    <button type="submit">Войти</button>
    <p class="muted center">Ещё не зарегистрированы? <a href="register.php">Регистрация</a></p>
</form>
</div>
<?php
layout_footer();
