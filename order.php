<?php
/* ============================================================================
   order.php — Формирование заявки на обучение.
   Поля: наименование курса (выпадающий список), дата начала (ДД.ММ.ГГГГ),
   способ оплаты. Кнопка «Отправить». Все поля обязательны. CSRF-защита.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

$user = require_login();

$courses  = q('SELECT name FROM courses WHERE is_active = 1 ORDER BY id');
$payments = app('payment_types');

$errors = [];
$old = ['course_name' => '', 'desired_date' => '', 'payment_type' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $course_name  = sanitize($_POST['course_name']  ?? '');
    $desired_date = sanitize($_POST['desired_date'] ?? '');
    $payment_type = sanitize($_POST['payment_type'] ?? '');
    $old = compact('course_name', 'desired_date', 'payment_type');

    $errors = collect_errors([
        'course_name'  => err_required($course_name, 'Курс'),
        'desired_date' => err_date($desired_date),
        'payment_type' => err_required($payment_type, 'Способ оплаты'),
    ]);

    if (!$errors) {
        run('INSERT INTO orders (user_id,course_name,desired_date,payment_type,status) VALUES (?,?,?,?,\'new\')',
            [$user['id'], $course_name, $desired_date, $payment_type]);
        header('Location: cabinet.php?ok=1');
        exit;
    }
}

layout_header('');
?>
<div class="auth">
<h1><span class="sec-ico">➕</span>Новая <?= e(app('entity_label')) ?></h1>
<p class="section-caption">Выберите курс и удобные условия — все поля обязательны.</p>
<form method="post" class="card" novalidate>
    <?= csrf_field() ?>
    <label>Наименование курса
        <select name="course_name" required>
            <option value="">— выберите курс —</option>
            <?php foreach ($courses as $cr): ?>
                <option value="<?= e($cr['name']) ?>" <?= $old['course_name'] === $cr['name'] ? 'selected' : '' ?>>
                    <?= e($cr['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['course_name'])): ?><span class="err"><?= e($errors['course_name']) ?></span><?php endif; ?>
    </label>
    <label>Желаемая дата начала обучения
        <input name="desired_date" value="<?= e($old['desired_date']) ?>" data-mask="date" placeholder="ДД.ММ.ГГГГ" required>
        <?php if (isset($errors['desired_date'])): ?><span class="err"><?= e($errors['desired_date']) ?></span><?php endif; ?>
    </label>
    <label>Способ оплаты
        <select name="payment_type" required>
            <option value="">— выберите —</option>
            <?php foreach ($payments as $p): ?>
                <option value="<?= e($p) ?>" <?= $old['payment_type'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['payment_type'])): ?><span class="err"><?= e($errors['payment_type']) ?></span><?php endif; ?>
    </label>
    <button type="submit">Отправить</button>
</form>
</div>
<?php
layout_footer();
