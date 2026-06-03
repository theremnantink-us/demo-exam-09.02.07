<?php
/* ============================================================================
   auth.php — работа с сессиями: вход/выход пользователя и администратора.
   ----------------------------------------------------------------------------
   Две независимые «роли» в одной сессии:
     $_SESSION['user_id']  — id вошедшего пользователя (клиента)
     $_SESSION['is_admin'] — true, если вошёл администратор
   Подключай этот файл первым на каждой странице (require_once 'auth.php';).
   ========================================================================== */

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Пользователь -----------------------------------------------------------
function login_user(int $id): void { $_SESSION['user_id'] = $id; }
function logout_user(): void        { unset($_SESSION['user_id']); }

// Текущий пользователь (массив из БД) или null.
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    return one('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
}

// Защита страницы: пускаем только вошедших, иначе — на страницу входа.
function require_login(): array {
    $u = current_user();
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}

// --- Администратор ----------------------------------------------------------
function login_admin(): void  { $_SESSION['is_admin'] = true; }
function logout_admin(): void { unset($_SESSION['is_admin']); }
function is_admin(): bool      { return !empty($_SESSION['is_admin']); }

// Защита админ-страницы.
function require_admin(): void {
    if (!is_admin()) { header('Location: admin_login.php'); exit; }
}
