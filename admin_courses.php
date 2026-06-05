<?php
/* ============================================================================
   admin_courses.php — Управление курсами (полный CRUD).
   Create  — форма «Добавить курс».
   Read    — список всех курсов.
   Update  — редактирование названия и активности прямо в строке.
   Delete  — удаление курса.
   Все запросы подготовленные (защита от SQL-инъекций), формы с CSRF-токеном.
   ========================================================================== */
require_once __DIR__ . '/layout.php';

require_admin();

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {                          // CREATE
        $name = sanitize($_POST['name'] ?? '');
        if ($name === '') {
            $flash = 'Введите название курса';
        } else {
            run('INSERT INTO courses (name, is_active) VALUES (?, 1)', [$name]);
            header('Location: admin_courses.php?msg=created'); exit;
        }
    } elseif ($action === 'update') {                    // UPDATE
        $id     = (int)($_POST['id'] ?? 0);
        $name   = sanitize($_POST['name'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        if ($id && $name !== '') {
            run('UPDATE courses SET name = ?, is_active = ? WHERE id = ?', [$name, $active, $id]);
        }
        header('Location: admin_courses.php?msg=updated'); exit;
    } elseif ($action === 'delete') {                    // DELETE
        $id = (int)($_POST['id'] ?? 0);
        if ($id) run('DELETE FROM courses WHERE id = ?', [$id]);
        header('Location: admin_courses.php?msg=deleted'); exit;
    }
}

$courses = q('SELECT * FROM courses ORDER BY id');

layout_header('Управление курсами', '📚', 'Добавляйте, редактируйте и удаляйте курсы (CRUD).');
?>
<!-- Вкладки админки -->
<div class="admin-tabs">
    <a href="admin.php">Заявки</a>
    <a href="admin_courses.php" class="active">Курсы</a>
    <a href="admin_logout.php" class="right">Выйти</a>
</div>

<?php
$msg = $_GET['msg'] ?? '';
$msgs = ['created' => 'Курс добавлен', 'updated' => 'Курс обновлён', 'deleted' => 'Курс удалён'];
if (isset($msgs[$msg])): ?><div class="toast" data-toast><?= e($msgs[$msg]) ?></div><?php endif; ?>
<?php if ($flash): ?><div class="alert"><?= e($flash) ?></div><?php endif; ?>

<!-- CREATE: добавить новый курс -->
<form method="post" class="filters">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <input type="text" name="name" placeholder="Название нового курса" required style="min-width:280px">
    <button type="submit">+ Добавить курс</button>
</form>

<!-- READ + UPDATE + DELETE: список курсов -->
<table class="table">
    <thead><tr><th>#</th><th>Название</th><th>Активен</th><th>Действия</th></tr></thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
        <tr>
            <td data-label="#"><?= e($c['id']) ?></td>
            <!-- редактирование строки: одна форма на UPDATE и DELETE -->
            <td data-label="Название" colspan="3">
                <form method="post" class="course-row">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                    <input type="text" name="name" value="<?= e($c['name']) ?>" required>
                    <label class="inline"><input type="checkbox" name="is_active" <?= $c['is_active'] ? 'checked' : '' ?>> активен</label>
                    <button type="submit" name="action" value="update">Сохранить</button>
                    <button type="submit" name="action" value="delete" class="btn-danger"
                            onclick="return confirm('Удалить курс «<?= e($c['name']) ?>»?')">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$courses): ?><tr><td colspan="4" class="muted center">Курсов пока нет — добавьте первый.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php
layout_footer();
