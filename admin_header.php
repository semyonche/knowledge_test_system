<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Админ-панель'; ?></title>
    <link rel="stylesheet" href="/knowledge_test_system/assets/css/style.css">
    <link rel="stylesheet" href="/knowledge_test_system/assets/css/admin.css">
    <script defer src="/knowledge_test_system/assets/js/main.js"></script>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a class="brand brand-admin" href="/knowledge_test_system/admin/admin_dashboard.php">APCQuiz Admin</a>
        <nav class="admin-nav">
            <a href="/knowledge_test_system/admin/admin_dashboard.php">Dashboard</a>
            <a href="/knowledge_test_system/admin/manage_tests.php">Тесты</a>
            <a href="/knowledge_test_system/admin/manage_questions.php">Вопросы</a>
            <a href="/knowledge_test_system/admin/manage_users.php">Пользователи</a>
            <a href="/knowledge_test_system/admin/manage_results.php">Результаты</a>
            <a href="/knowledge_test_system/logout.php?type=admin">Выход</a>
        </nav>
    </aside>
    <div class="admin-content">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']); ?>">
                <?= e($flash['message']); ?>
            </div>
        <?php endif; ?>
