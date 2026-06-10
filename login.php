<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isUserLoggedIn()) {
    redirect('/knowledge_test_system/index.php');
}

$pageTitle = 'Вход';
$hideHeader = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        setFlash('success', 'Добро пожаловать, ' . $user['full_name'] . '!');
        redirect('/knowledge_test_system/index.php');
    } else {
        setFlash('error', 'Неверный логин или пароль.');
    }
}


require_once __DIR__ . '/includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <h1>Вход в систему</h1>
        <p>Введите логин или email и пароль.</p>

        <form method="post" data-validate="true">
            <div class="form-group">
                <label for="login">Логин или email</label>
                <input type="text" name="login" id="login" required>
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button class="btn btn-primary login-submit-btn" type="submit">Войти</button>
        </form>

        <div class="auth-links">
            <a href="/knowledge_test_system/forgot_password.php">Забыли пароль?</a>
            <a href="/knowledge_test_system/register.php">Создать аккаунт</a>
            <a href="/knowledge_test_system/admin_login.php">Панель администратора</a>
        </div>
    </div>
</div>


