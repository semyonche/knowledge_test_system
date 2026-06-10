<?php
require_once __DIR__ . '/includes/functions.php';

$type = $_GET['type'] ?? 'user';

if ($type === 'admin') {
    unset($_SESSION['admin_id']);
    setFlash('success', 'Вы вышли из панели администратора.');
    redirect('/knowledge_test_system/admin_login.php');
}

unset($_SESSION['user_id']);
setFlash('success', 'Вы вышли из системы.');
redirect('/knowledge_test_system/login.php');
