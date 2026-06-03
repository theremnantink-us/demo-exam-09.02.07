<?php
/* ============================================================================
   cabinet.php — Личный кабинет: история своих заявок + кнопка «новая заявка».
   ========================================================================== */
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/helpers.php';

$user = require_login();   // только для вошедших

// Заявки текущего пользователя + название услуги (LEFT JOIN — на случай «иной услуги»).
$orders = q(
    'SELECT o.*, s.name AS service_name
     FROM orders o
     LEFT JOIN services s ON s.id = o.service_id
     WHERE o.user_id = ?
     ORDER BY o.created_at DESC',
    [$user['id']]
);

$statuses = app('statuses');

layout_header('Личный кабинет');
?>
<div class="row-between">
    <h1><span class="sec-ico">📋</span>История <?= e(app('entity_label_gen')) ?></h1>
    <a class="btn" href="order.php">+ Новая <?= e(app('entity_label')) ?></a>
</div>
<p class="section-caption">Здесь все ваши заявки и их текущий статус.</p>

<?php if (!$orders): ?>
    <p class="muted">У вас пока нет заявок. Нажмите «Новая <?= e(app('entity_label')) ?>».</p>
<?php else: ?>
<table class="table">
    <thead>
        <tr><th>#</th><th>Услуга</th><th>Адрес</th><th>Дата/время</th><th>Оплата</th><th>Статус</th></tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= e($o['id']) ?></td>
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
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
layout_footer();
