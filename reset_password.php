<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Сброс пароля';
$hideHeader = true;

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$resetRow = null;

if ($token !== '') {
    $stmt = $pdo->prepare("
        SELECT pr.*, u.id AS user_id
        FROM password_resets pr
        JOIN users u ON u.id = pr.user_id
        WHERE pr.token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $resetRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$resetRow) {
        setFlash('error', 'Недействительная ссылка для сброса пароля.');
        redirect('/knowledge_test_system/forgot_password.php');
    }

    if (strtotime($resetRow['expires_at']) < time()) {
        $pdo->prepare("DELETE FROM password_resets WHERE id = ?")->execute([$resetRow['id']]);
        setFlash('error', 'Срок действия ссылки истёк.');
        redirect('/knowledge_test_system/forgot_password.php');
    }

    if (mb_strlen($password) < 6) {
        setFlash('error', 'Пароль должен содержать минимум 6 символов.');
        redirect('/knowledge_test_system/reset_password.php?token=' . urlencode($token));
    }

    if ($password !== $passwordConfirm) {
        setFlash('error', 'Пароли не совпадают.');
        redirect('/knowledge_test_system/reset_password.php?token=' . urlencode($token));
    }

    $newHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $resetRow['user_id']]);

    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$resetRow['user_id']]);

    setFlash('success', 'Пароль успешно изменён. Теперь можно войти.');
    redirect('/knowledge_test_system/login.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container auth-page">
    <div class="auth-card">
        <h1>Сброс пароля</h1>

        <?php if (!$resetRow): ?>
            <p class="muted">Ссылка недействительна или не найдена.</p>
            <div class="auth-links">
                <a href="/knowledge_test_system/forgot_password.php">Запросить новую ссылку</a>
            </div>
        <?php elseif (strtotime($resetRow['expires_at']) < time()): ?>
            <p class="muted">Срок действия ссылки истёк.</p>
            <div class="auth-links">
                <a href="/knowledge_test_system/forgot_password.php">Запросить новую ссылку</a>
            </div>
        <?php else: ?>
            <p>Введите новый пароль для вашего аккаунта.</p>

            <form method="post">
                <input type="hidden" name="token" value="<?= e($token); ?>">

                <div class="form-group">
                    <label for="password">Новый пароль</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Подтвердите пароль</label>
                    <input type="password" name="password_confirm" id="password_confirm" required>
                </div>

                <button class="btn btn-primary reset-password-btn" type="submit">Сохранить новый пароль</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>