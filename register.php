<?php
/* ============================================================================
   register.php — Регистрация. Валидация: логин (латиница+цифры, ≥6, уникальный),
   пароль ≥8, ФИО кириллица, телефон 8(XXX)XXX-XX-XX, email. Ошибки на форме.
   Пароль сохраняется ХЕШЕМ (security.php). Форма защищена CSRF-токеном.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

if (current_user()) { header('Location: cabinet.php'); exit; }

$errors = [];
$old = ['login' => '', 'fio' => '', 'phone' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();   // защита от CSRF

    $login    = sanitize($_POST['login']    ?? '');
    $password = (string)($_POST['password'] ?? '');
    $fio      = sanitize($_POST['fio']      ?? '');
    $phone    = sanitize($_POST['phone']    ?? '');
    $email    = sanitize($_POST['email']    ?? '');
    $old = compact('login', 'fio', 'phone', 'email');

    $errors = collect_errors([
        'login'    => err_login($login),
        'password' => err_password($password),
        'fio'      => err_fio($fio),
        'phone'    => err_phone($phone),
        'email'    => err_email($email),
    ]);

    if (!isset($errors['login']) && one('SELECT id FROM users WHERE login = ?', [$login])) {
        $errors['login'] = 'Такой логин уже занят';
    }

    if (!$errors) {
        run('INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)',
            [$login, hash_password($password), $fio, $phone, $email]);
        login_user((int)last_id());
        header('Location: cabinet.php');
        exit;
    }
}

layout_header('');   // заголовок рисуем сами по центру (.auth)
?>
<div class="auth">
<h1><span class="sec-ico">📝</span>Регистрация</h1>
<p class="section-caption">Создайте аккаунт, чтобы записываться на курсы.</p>
<form method="post" class="card" novalidate>
    <?= csrf_field() ?>
    <label>Логин
        <input name="login" value="<?= e($old['login']) ?>" placeholder="латиница и цифры, ≥6" required>
        <?php if (isset($errors['login'])): ?><span class="err"><?= e($errors['login']) ?></span><?php endif; ?>
    </label>
    <label>Пароль (мин. 8 символов)
        <input type="password" name="password" required>
        <?php if (isset($errors['password'])): ?><span class="err"><?= e($errors['password']) ?></span><?php endif; ?>
    </label>
    <label>ФИО
        <input name="fio" value="<?= e($old['fio']) ?>" placeholder="Иванов Иван Иванович" required>
        <?php if (isset($errors['fio'])): ?><span class="err"><?= e($errors['fio']) ?></span><?php endif; ?>
    </label>
    <label>Телефон
        <input name="phone" value="<?= e($old['phone']) ?>" data-mask="phone" placeholder="8(XXX)XXX-XX-XX" required>
        <?php if (isset($errors['phone'])): ?><span class="err"><?= e($errors['phone']) ?></span><?php endif; ?>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($old['email']) ?>" placeholder="user@mail.ru" required>
        <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
    </label>
    <button type="submit">Зарегистрироваться</button>
    <p class="muted center">Уже зарегистрированы? <a href="login.php">Войти</a></p>
</form>
</div>
<?php
layout_footer();
