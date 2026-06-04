<?php
/* ============================================================================
   cabinet.php — Личный кабинет (страница просмотра заявок).
   Пользователь видит свои заявки и их статус. Отзыв можно оставить ТОЛЬКО по
   заявке со статусом «Обучение завершено» (требование Модуля 2). CSRF-защита.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

$user = require_login();
$statuses = app('statuses');
$review_status = app('review_allowed_status');   // 'done'
$flash = '';

// --- Отправка отзыва --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review') {
    csrf_require();
    $order_id = (int)($_POST['order_id'] ?? 0);
    $rating   = (int)($_POST['rating'] ?? 5);
    $text     = sanitize($_POST['text'] ?? '');

    // Заявка должна принадлежать пользователю и быть завершённой.
    $order = one('SELECT * FROM orders WHERE id = ? AND user_id = ?', [$order_id, $user['id']]);
    if (!$order || $order['status'] !== $review_status) {
        $flash = 'Отзыв доступен только после завершения обучения.';
    } elseif ($text === '') {
        $flash = 'Напишите текст отзыва.';
    } elseif (one('SELECT id FROM reviews WHERE order_id = ?', [$order_id])) {
        $flash = 'Вы уже оставили отзыв по этой заявке.';
    } else {
        if ($rating < 1 || $rating > 5) $rating = 5;
        run('INSERT INTO reviews (user_id,order_id,course_name,rating,text) VALUES (?,?,?,?,?)',
            [$user['id'], $order_id, $order['course_name'], $rating, $text]);
        header('Location: cabinet.php?reviewed=1');
        exit;
    }
}

$orders = q('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC', [$user['id']]);
// id заявок, по которым отзыв уже оставлен
$reviewed = array_column(q('SELECT order_id FROM reviews WHERE user_id = ?', [$user['id']]), 'order_id');

layout_header('Мои ' . app('entity_label_gen'), '📋', 'Ваши заявки и их статус. Отзыв — после завершения обучения.');
?>
<div class="row-between" style="margin-bottom:18px">
    <span class="muted">Всего заявок: <?= count($orders) ?></span>
    <a class="btn" href="order.php">+ Новая <?= e(app('entity_label')) ?></a>
</div>

<?php if (isset($_GET['ok'])): ?><div class="toast" data-toast>Заявка успешно отправлена!</div><?php endif; ?>
<?php if (isset($_GET['reviewed'])): ?><div class="toast" data-toast>Спасибо за отзыв!</div><?php endif; ?>
<?php if ($flash): ?><div class="alert"><?= e($flash) ?></div><?php endif; ?>

<?php
// есть ли завершённый курс без отзыва — подскажем оставить отзыв
$can_review = false;
foreach ($orders as $o) { if ($o['status'] === $review_status && !in_array($o['id'], $reviewed)) { $can_review = true; break; } }
?>
<?php if ($can_review): ?>
    <div class="hint" id="review">★ У вас есть завершённый курс — в столбце «Отзыв» нажмите «Оставить отзыв».</div>
<?php endif; ?>

<?php if (!$orders): ?>
    <p class="muted">У вас пока нет заявок. Нажмите «Новая <?= e(app('entity_label')) ?>».</p>
<?php else: ?>
<table class="table" id="orders">
    <thead><tr><th>#</th><th>Курс</th><th>Дата начала</th><th>Оплата</th><th>Статус</th><th>Отзыв</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="#"><?= e($o['id']) ?></td>
            <td data-label="Курс"><?= e($o['course_name']) ?></td>
            <td data-label="Дата начала"><?= e($o['desired_date']) ?></td>
            <td data-label="Оплата"><?= e($o['payment_type']) ?></td>
            <td data-label="Статус"><span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']] ?? $o['status']) ?></span></td>
            <td data-label="Отзыв">
                <?php if ($o['status'] !== $review_status): ?>
                    <span class="muted small">после обучения</span>
                <?php elseif (in_array($o['id'], $reviewed)): ?>
                    <span class="muted small">✓ оставлен</span>
                <?php else: ?>
                    <details class="review-box">
                        <summary class="btn-mini">★ Оставить отзыв</summary>
                        <form method="post" class="review-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="review">
                            <input type="hidden" name="order_id" value="<?= e($o['id']) ?>">
                            <p class="muted small">Оцените качество обучения по курсу «<?= e($o['course_name']) ?>».</p>
                            <label class="inline">Оценка
                                <select name="rating">
                                    <option value="5">5 — отлично</option>
                                    <option value="4">4 — хорошо</option>
                                    <option value="3">3 — нормально</option>
                                    <option value="2">2 — плохо</option>
                                    <option value="1">1 — очень плохо</option>
                                </select>
                            </label>
                            <textarea name="text" rows="3" placeholder="Что понравилось, что можно улучшить?" required></textarea>
                            <button type="submit" class="btn-mini">Отправить отзыв</button>
                        </form>
                    </details>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
layout_footer();
