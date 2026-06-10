<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

$pageTitle = 'Забыли пароль';
$hideHeader = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Введите корректный email.');
        redirect('/knowledge_test_system/forgot_password.php');
    }

    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        setFlash('error', 'Пользователь с таким email не найден.');
        redirect('/knowledge_test_system/forgot_password.php');
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + 3600);

    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, email, token, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $user['email'], $token, $expiresAt]);

    $resetLink = 'http://localhost/knowledge_test_system/reset_password.php?token=' . $token;

    if (sendResetEmail($user['email'], $resetLink)) {
        setFlash('success', 'Письмо со ссылкой для сброса пароля отправлено.');
    } else {
        setFlash('error', 'Не удалось отправить письмо. Проверьте настройки Gmail SMTP.');
    }

    redirect('/knowledge_test_system/forgot_password.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <h1>Забыли пароль?</h1>
        <p>Введите email, который использовался при регистрации.</p>

        <form method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <button class="btn btn-primary forgot-password-btn" type="submit">Отправить ссылку</button>
        </form>

        <div class="auth-links">
            <a href="/knowledge_test_system/login.php">Вернуться ко входу</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>