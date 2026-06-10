<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Пользователи';
$users = $pdo->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM user_results ur WHERE ur.user_id = u.id) AS attempts_count
    FROM users u
    ORDER BY u.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="section-title"><h1>Список пользователей</h1></div>
<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>ФИО</th><th>Логин</th><th>Email</th><th>Регистрация</th><th>Попыток</th></tr></thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= (int)$user['id']; ?></td>
                <td><?= e($user['full_name']); ?></td>
                <td><?= e($user['username']); ?></td>
                <td><?= e($user['email']); ?></td>
                <td><?= e(date('d.m.Y H:i', strtotime($user['created_at']))); ?></td>
                <td><?= (int)$user['attempts_count']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
