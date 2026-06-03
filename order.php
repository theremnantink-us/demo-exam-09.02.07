<?php
/* ============================================================================
   order.php — Форма формирования заявки.
   Поля: адрес, телефон, дата/время, вид услуги (из справочника),
   чекбокс «Иная услуга» -> текстовое поле, тип оплаты. Все поля обязательны.
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();

$services = q('SELECT id, name FROM services WHERE is_active = 1 ORDER BY id');
$payments = app('payment_types');

$errors = [];
$old = ['address' => '', 'phone' => $user['phone'], 'desired_date' => '', 'service_id' => '', 'other_service' => '', 'payment_type' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address      = trim($_POST['address']        ?? '');
    $phone        = trim($_POST['phone']          ?? '');
    $desired_date = $_POST['desired_date']         ?? '';
    $is_other     = isset($_POST['is_other']);                 // чекбокс «Иная услуга»
    $service_id   = $_POST['service_id']           ?? '';
    $other_service= trim($_POST['other_service']  ?? '');
    $payment_type = $_POST['payment_type']         ?? '';
    $old = compact('address', 'phone', 'desired_date', 'service_id', 'other_service', 'payment_type');

    // Валидация: все поля обязательны.
    $errors = collect_errors([
        'address'      => err_required($address, 'Адрес'),
        'phone'        => err_phone($phone),
        'desired_date' => err_required($desired_date, 'Дата и время'),
        'payment_type' => err_required($payment_type, 'Тип оплаты'),
    ]);

    // Услуга: либо выбрана из списка, либо отмечена «иная» и заполнено поле.
    if ($is_other) {
        if ($other_service === '') $errors['other_service'] = 'Опишите нужную услугу';
        $service_id = null;
    } else {
        if ($service_id === '') $errors['service_id'] = 'Выберите вид услуги';
        $other_service = null;
    }

    if (!$errors) {
        run('INSERT INTO orders (user_id,address,phone,service_id,other_service,desired_date,payment_type,status)
             VALUES (?,?,?,?,?,?,?,\'new\')',
            [$user['id'], $address, $phone, $service_id, $other_service, $desired_date, $payment_type]);
        header('Location: cabinet.php');   // после создания — обратно в кабинет
        exit;
    }
}

layout_header('Новая заявка');
?>
<div class="auth">
<h1><span class="sec-ico">➕</span>Новая <?= e(app('entity_label')) ?></h1>
<p class="section-caption">Заполните данные — все поля обязательны.</p>
<form method="post" class="card" novalidate>
    <label>Адрес
        <input name="address" value="<?= e($old['address']) ?>" required>
        <?php if (isset($errors['address'])): ?><span class="err"><?= e($errors['address']) ?></span><?php endif; ?>
    </label>
    <label>Телефон
        <input name="phone" value="<?= e($old['phone']) ?>" data-mask="phone" placeholder="+7(XXX)-XXX-XX-XX" required>
        <?php if (isset($errors['phone'])): ?><span class="err"><?= e($errors['phone']) ?></span><?php endif; ?>
    </label>
    <label>Желаемые дата и время
        <input type="datetime-local" name="desired_date" value="<?= e($old['desired_date']) ?>" required>
        <?php if (isset($errors['desired_date'])): ?><span class="err"><?= e($errors['desired_date']) ?></span><?php endif; ?>
    </label>

    <label>Вид услуги
        <select name="service_id" id="service_id">
            <option value="">— выберите —</option>
            <?php foreach ($services as $s): ?>
                <option value="<?= e($s['id']) ?>" <?= ((string)$old['service_id'] === (string)$s['id']) ? 'selected' : '' ?>>
                    <?= e($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['service_id'])): ?><span class="err"><?= e($errors['service_id']) ?></span><?php endif; ?>
    </label>

    <!-- Чекбокс «Иная услуга»: по клику показывает поле ниже (логика в app.js) -->
    <label class="checkbox">
        <input type="checkbox" name="is_other" id="is_other" <?= $old['other_service'] !== '' && $old['other_service'] !== null ? 'checked' : '' ?>>
        Иная услуга (нет в списке)
    </label>
    <label id="other_wrap" style="display:none">Опишите услугу
        <input name="other_service" id="other_service" value="<?= e($old['other_service']) ?>">
        <?php if (isset($errors['other_service'])): ?><span class="err"><?= e($errors['other_service']) ?></span><?php endif; ?>
    </label>

    <label>Тип оплаты
        <select name="payment_type" required>
            <option value="">— выберите —</option>
            <?php foreach ($payments as $p): ?>
                <option value="<?= e($p) ?>" <?= $old['payment_type'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['payment_type'])): ?><span class="err"><?= e($errors['payment_type']) ?></span><?php endif; ?>
    </label>

    <button type="submit">Оставить <?= e(app('entity_label')) ?></button>
</form>
</div>
<?php
layout_footer();
