<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('/knowledge_test_system/admin/admin_dashboard.php');
}

$pageTitle = 'Вход администратора';
$hideHeader = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1");
    $stmt->execute([$login, $login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = (int)$admin['id'];
        setFlash('success', 'Вы вошли в панель администратора.');
        redirect('/knowledge_test_system/admin/admin_dashboard.php');
    } else {
        setFlash('error', 'Неверные данные администратора.');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <h1>Вход администратора</h1>
        <p>Отдельная авторизация для управления системой.</p>

        <form method="post" data-validate="true">
            <div class="form-group">
                <label for="login">Логин или email</label>
                <input type="text" id="login" name="login" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button class="btn btn-primary admin-login-btn" type="submit">Войти</button>
        </form>

        <div class="auth-links">
            <a href="/knowledge_test_system/login.php">Вход пользователя</a>
        </div>
    </div>
</div>

