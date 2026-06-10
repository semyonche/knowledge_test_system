<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (isUserLoggedIn()) {
    redirect('/knowledge_test_system/dashboard.php');
}

$pageTitle = 'Регистрация';
$hideHeader = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password_confirm'] ?? '';

    if ($fullName === '' || $username === '' || $email === '' || $password === '') {
        setFlash('error', 'Заполните все обязательные поля.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Введите корректный email.');
    } elseif (mb_strlen($password) < 6) {
        setFlash('error', 'Пароль должен содержать минимум 6 символов.');
    } elseif ($password !== $password2) {
        setFlash('error', 'Пароли не совпадают.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            setFlash('error', 'Пользователь с таким email или логином уже существует.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$fullName, $username, $email, $hash]);
            setFlash('success', 'Регистрация успешно завершена. Теперь войдите в систему.');
            redirect('/knowledge_test_system/login.php');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container auth-page">
    <div class="auth-card">
        <h1>Регистрация</h1>
        <p>Создайте учетную запись для прохождения тестов и просмотра личной статистики.</p>
        <form method="post" data-validate="true">
            <div class="form-group">
                <label>ФИО</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Повторите пароль</label>
                <input type="password" name="password_confirm" required>
            </div>
            <button class="btn btn-primary register-submit-btn" type="submit">Зарегистрироваться</button>
        </form>
        <div class="auth-links">
            <a href="/knowledge_test_system/login.php">Уже есть аккаунт?</a>
            <a href="/knowledge_test_system/admin_login.php">Вход для администратора</a>
        </div>
    </div>
</div>

