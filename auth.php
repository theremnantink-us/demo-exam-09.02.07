<?php
/* ============================================================================
   auth.php — сессии и доступ. Подключай ПЕРВЫМ на каждой странице.
   Использует подсистему безопасности (security.php): безопасная сессия,
   защитные заголовки, проверка пароля по хешу.
   ========================================================================== */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

security_boot();   // безопасный старт сессии + защитные HTTP-заголовки

// --- Пользователь -----------------------------------------------------------
function login_user(int $id): void {
    session_regenerate_id(true);     // защита от фиксации сессии
    $_SESSION['user_id'] = $id;
}
function logout_user(): void { unset($_SESSION['user_id']); }

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    return one('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
}
function require_login(): array {
    $u = current_user();
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}

// Проверка логина/пароля пользователя. Возвращает пользователя или null.
function authenticate_user(string $login, string $password): ?array {
    $u = one('SELECT * FROM users WHERE login = ?', [$login]);
    if ($u && verify_password($password, $u['password'])) return $u;
    return null;
}

// --- Администратор ----------------------------------------------------------
function login_admin(): void { session_regenerate_id(true); $_SESSION['is_admin'] = true; }
function logout_admin(): void { unset($_SESSION['is_admin']); }
function is_admin(): bool { return !empty($_SESSION['is_admin']); }
function require_admin(): void {
    if (!is_admin()) { header('Location: admin_login.php'); exit; }
}
