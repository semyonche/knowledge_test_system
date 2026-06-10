<?php
require_once __DIR__ . '/functions.php';

if (!isAdminLoggedIn()) {
    setFlash('error', 'Сначала войдите в панель администратора.');
    redirect('/knowledge_test_system/admin_login.php');
}
