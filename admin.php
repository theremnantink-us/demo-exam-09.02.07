<?php
/* ============================================================================
   admin.php — Панель администратора.
   Видны ВСЕ заявки. Админ меняет статус (в работе / выполнено / отменено).
   При отмене обязательно указывается причина.
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

require_admin();   // только для админа

$statuses = app('statuses');

// --- Обработка смены статуса (POST) ----------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status']   ?? '';
    $reason = trim($_POST['cancel_reason'] ?? '');

    // Разрешаем только известные статусы.
    if ($id && isset($statuses[$status])) {
        // При отмене причина обязательна.
        if ($status === 'canceled' && $reason === '') {
            $flash = 'Для отмены нужно указать причину';
        } else {
            run('UPDATE orders SET status = ?, cancel_reason = ? WHERE id = ?',
                [$status, $status === 'canceled' ? $reason : null, $id]);
            header('Location: admin.php');   // PRG-паттерн: избегаем повторной отправки
            exit;
        }
    }
}

// Все заявки + ФИО заявителя + название услуги.
$orders = q(
    'SELECT o.*, u.fio, u.email, s.name AS service_name
     FROM orders o
     JOIN users u    ON u.id = o.user_id
     LEFT JOIN services s ON s.id = o.service_id
     ORDER BY o.created_at DESC'
);

layout_header('Админ-панель');
?>
<div class="row-between">
    <h1><span class="sec-ico">🛠</span><?= e(app('entity_label_plural')) ?> — все заявки</h1>
    <a href="admin_logout.php">Выйти</a>
</div>
<p class="section-caption">Меняйте статус заявок; при отмене укажите причину.</p>
<?php if (!empty($flash)): ?><div class="alert"><?= e($flash) ?></div><?php endif; ?>

<table class="table">
    <thead>
        <tr><th>#</th><th>Заявитель</th><th>Контакты</th><th>Услуга</th><th>Адрес</th><th>Дата</th><th>Оплата</th><th>Статус</th><th>Сменить статус</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= e($o['id']) ?></td>
            <td><?= e($o['fio']) ?></td>
            <td><?= e($o['phone']) ?><br><span class="muted small"><?= e($o['email']) ?></span></td>
            <td><?= e($o['service_name'] ?? $o['other_service']) ?></td>
            <td><?= e($o['address']) ?></td>
            <td><?= e(date('d.m.Y H:i', strtotime($o['desired_date']))) ?></td>
            <td><?= e($o['payment_type']) ?></td>
            <td>
                <span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']] ?? $o['status']) ?></span>
                <?php if ($o['status'] === 'canceled' && $o['cancel_reason']): ?>
                    <div class="muted small">Причина: <?= e($o['cancel_reason']) ?></div>
                <?php endif; ?>
            </td>
            <td>
                <!-- Форма смены статуса. data-status-form включает показ поля причины при «Отменено» -->
                <form method="post" class="status-form" data-status-form>
                    <input type="hidden" name="id" value="<?= e($o['id']) ?>">
                    <select name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $o['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <!-- поле причины показывается JS-ом только при выборе «Отменено» -->
                    <input type="text" name="cancel_reason" class="reason" placeholder="Причина отмены"
                           value="<?= e($o['cancel_reason'] ?? '') ?>"
                           style="display:<?= $o['status'] === 'canceled' ? 'block' : 'none' ?>">
                    <button type="submit">OK</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
layout_footer();
