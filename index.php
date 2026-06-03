<?php
/* index.php — точка входа. Просто перенаправляет в нужное место. */
require_once __DIR__ . '/auth.php';

if (current_user()) {
    header('Location: cabinet.php');   // вошёл — в личный кабинет
} else {
    header('Location: login.php');     // не вошёл — на страницу входа
}
exit;
