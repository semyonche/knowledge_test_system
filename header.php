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
    <title><?= isset($pageTitle) ? e($pageTitle) : 'APCQuiz'; ?></title>
    <link rel="stylesheet" href="/knowledge_test_system/assets/css/style.css">
    <link rel="stylesheet" href="/knowledge_test_system/assets/css/auth.css">
    <script>
        (function () {
            const savedTheme = localStorage.getItem('site-theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark-theme');
            }
        })();
    </script>
    <script defer src="/knowledge_test_system/assets/js/main.js"></script>
    <script defer src="/knowledge_test_system/assets/js/theme.js"></script>
</head>
<body>

<?php if (empty($hideHeader)): ?>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="/knowledge_test_system/index.php">APCQuiz</a>

        <nav class="top-nav">
            <a href="/knowledge_test_system/index.php">Главная</a>
            <a href="/knowledge_test_system/tests.php">Тесты</a>

            <?php if (isUserLoggedIn()): ?>
                <a href="/knowledge_test_system/history.php">История</a>
                <a href="/knowledge_test_system/profile.php">Профиль</a>
                <a href="/knowledge_test_system/logout.php?type=user" class="nav-action-link">Выход</a>
            <?php else: ?>
                <a href="/knowledge_test_system/login.php" class="header-login-link">Вход</a>
                <a href="/knowledge_test_system/register.php" class="btn btn-primary header-register-btn">Регистрация</a>
            <?php endif; ?>

            <button type="button" class="theme-toggle-btn" id="themeToggleBtn">🌙 Тема</button>
        </nav>

        <button class="menu-toggle" type="button" aria-label="Открыть меню">☰</button>
    </div>
</header>
<?php endif; ?>

<?php if (empty($hideHeader) && $flash): ?>
    <div class="container">
        <div class="alert alert-<?= e($flash['type']); ?>">
            <?= e($flash['message']); ?>
        </div>
    </div>
<?php endif; ?>

<main class="page-main">