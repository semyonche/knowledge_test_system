<?php
require_once __DIR__ . '/functions.php';

if (!isUserLoggedIn()) {
    setFlash('error', 'Сначала войдите в систему.');
    redirect('/knowledge_test_system/login.php');
}
