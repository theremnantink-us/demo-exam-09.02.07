<?php
/* admin_logout.php — выход администратора. */
require_once __DIR__ . '/auth.php';
logout_admin();
header('Location: admin_login.php');
exit;
