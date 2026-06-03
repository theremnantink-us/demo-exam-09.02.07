<?php
/* ============================================================================
   register.php — Страница регистрации.
   Требования задания: логин, пароль, ФИО, телефон, email — все обязательны.
   Профиль (Модуль 3): уникальный логин, пароль ≥6, ФИО кириллица, телефон
   +7(XXX)-XXX-XX-XX, email; ошибки валидации выводятся на форме.
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$errors = [];
$old = ['login' => '', 'fio' => '', 'phone' => '', 'email' => ''];   // чтобы не терять ввод

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Считываем поля.
    $login    = trim($_POST['login']    ?? '');
    $password = $_POST['password']       ?? '';
    $fio      = trim($_POST['fio']      ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $email    = trim($_POST['email']    ?? '');
    $old = compact('login', 'fio', 'phone', 'email');

    // 2. Валидация каждого поля (см. helpers.php).
    $errors = collect_errors([
        'login'    => err_login($login),
        'password' => err_password($password),
        'fio'      => err_fio($fio),
        'phone'    => err_phone($phone),
        'email'    => err_email($email),
    ]);

    // 3. Проверка уникальности логина в БД.
    if (!isset($errors['login']) && one('SELECT id FROM users WHERE login = ?', [$login])) {
        $errors['login'] = 'Такой логин уже занят';
    }

    // 4. Если ошибок нет — заносим в базу и сразу логиним.
    if (!$errors) {
        run('INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)',
            [$login, $password, $fio, $phone, $email]);
        login_user((int)last_id());
        header('Location: cabinet.php');
        exit;
    }
}

layout_header('Регистрация');
?>
<div class="auth">
<h1><span class="sec-ico">📝</span>Регистрация</h1>
<p class="section-caption">Создайте аккаунт, чтобы оставлять и отслеживать заявки.</p>
<form method="post" class="card" novalidate>
    <label>Логин
        <input name="login" value="<?= e($old['login']) ?>" required>
        <?php if (isset($errors['login'])): ?><span class="err"><?= e($errors['login']) ?></span><?php endif; ?>
    </label>
    <label>Пароль (мин. 6 символов)
        <input type="password" name="password" required>
        <?php if (isset($errors['password'])): ?><span class="err"><?= e($errors['password']) ?></span><?php endif; ?>
    </label>
    <label>ФИО
        <input name="fio" value="<?= e($old['fio']) ?>" placeholder="Иванов Иван Иванович" required>
        <?php if (isset($errors['fio'])): ?><span class="err"><?= e($errors['fio']) ?></span><?php endif; ?>
    </label>
    <label>Телефон
        <!-- data-mask включает маску ввода в app.js -->
        <input name="phone" value="<?= e($old['phone']) ?>" data-mask="phone" placeholder="+7(XXX)-XXX-XX-XX" required>
        <?php if (isset($errors['phone'])): ?><span class="err"><?= e($errors['phone']) ?></span><?php endif; ?>
    </label>
    <label>Email
        <input type="email" name="email" value="<?= e($old['email']) ?>" placeholder="user@mail.ru" required>
        <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
    </label>
    <button type="submit">Зарегистрироваться</button>
    <p class="muted">Уже есть аккаунт? <a href="login.php">Войти</a></p>
</form>
</div>
<?php
layout_footer();
