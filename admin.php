<?php
/* ============================================================================
   admin.php — Панель администратора.
   Видны все заявки. Смена статуса (Новая → Идёт обучение → Обучение завершено).
   Удобство (Модуль 2): фильтр по статусу, поиск, пагинация, всплывающие сообщения.
   Все запросы — подготовленные (защита от SQL-инъекций). CSRF на смену статуса.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

require_admin();
// Подписи статусов заявки (ключ в базе => текст на экране)
$statuses = ['new' => 'Новая', 'studying' => 'Идёт обучение', 'done' => 'Обучение завершено'];
$per_page = 8;   // сколько заявок показывать на одной странице (пагинация)

// --- Смена статуса (POST) ---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id && isset($statuses[$status])) {
        run('UPDATE orders SET status = ? WHERE id = ?', [$status, $id]);
    }
    // PRG: сохраняем фильтры в редиректе, добавляем флаг для всплывающего сообщения
    $qs = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] . '&saved=1' : '?saved=1';
    header('Location: admin.php' . $qs);
    exit;
}

// --- Фильтры (защищённо, через параметры) -----------------------------------
$f_status = $_GET['status'] ?? '';
$search   = sanitize($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));

$where = [];
$params = [];
if (isset($statuses[$f_status])) { $where[] = 'o.status = ?'; $params[] = $f_status; }
if ($search !== '') {
    $where[] = '(u.fio LIKE ? OR o.course_name LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Всего записей -> число страниц
$total = (int)one("SELECT COUNT(*) c FROM orders o JOIN users u ON u.id = o.user_id $where_sql", $params)['c'];
$pages = max(1, (int)ceil($total / $per_page));
$page  = min($page, $pages);
$offset = ($page - 1) * $per_page;

$orders = q("SELECT o.*, u.fio, u.email, u.phone
             FROM orders o JOIN users u ON u.id = o.user_id
             $where_sql ORDER BY o.created_at DESC
             LIMIT $per_page OFFSET $offset", $params);

// Хелпер ссылки страницы с сохранением фильтров
function page_link(int $p): string {
    $q = $_GET; $q['page'] = $p;
    return 'admin.php?' . http_build_query($q);
}

layout_header('Заявки — администратор', '🛠', 'Меняйте статус заявок. Доступны фильтр, поиск и пагинация.');
?>
<div class="row-between" style="margin-bottom:14px">
    <span class="muted">Найдено: <?= $total ?></span>
    <a href="admin_logout.php">Выйти</a>
</div>

<?php if (isset($_GET['saved'])): ?><div class="toast" data-toast>Статус заявки обновлён</div><?php endif; ?>

<!-- Панель фильтров -->
<form method="get" class="filters">
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Поиск по ФИО или курсу">
    <select name="status">
        <option value="">Все статусы</option>
        <?php foreach ($statuses as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $f_status === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Применить</button>
    <a class="btn-ghost" href="admin.php">Сброс</a>
</form>

<table class="table">
    <thead><tr><th>#</th><th>Заявитель</th><th>Контакты</th><th>Курс</th><th>Дата</th><th>Оплата</th><th>Статус</th><th>Сменить</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="#"><?= e($o['id']) ?></td>
            <td data-label="Заявитель"><?= e($o['fio']) ?></td>
            <td data-label="Контакты"><?= e($o['phone']) ?><br><span class="muted small"><?= e($o['email']) ?></span></td>
            <td data-label="Курс"><?= e($o['course_name']) ?></td>
            <td data-label="Дата"><?= e($o['desired_date']) ?></td>
            <td data-label="Оплата"><?= e($o['payment_type']) ?></td>
            <td data-label="Статус"><span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']] ?? $o['status']) ?></span></td>
            <td data-label="Сменить">
                <form method="post" class="status-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($o['id']) ?>">
                    <select name="status">
                        <?php foreach ($statuses as $k => $label): ?>
                            <option value="<?= e($k) ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">OK</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$orders): ?><tr><td colspan="8" class="muted center">Ничего не найдено</td></tr><?php endif; ?>
    </tbody>
</table>

<?php if ($pages > 1): ?>
<div class="pagination">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="page <?= $p === $page ? 'active' : '' ?>" href="<?= e(page_link($p)) ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php
layout_footer();
