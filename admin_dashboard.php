<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Дашборд';
$admin = currentAdmin($pdo);

$counts = [
    'users' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'tests' => (int)$pdo->query("SELECT COUNT(*) FROM tests")->fetchColumn(),
    'questions' => (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
    'attempts' => (int)$pdo->query("SELECT COUNT(*) FROM user_results")->fetchColumn(),
];

$latest = $pdo->query("
    SELECT ur.*, u.full_name, t.title
    FROM user_results ur
    JOIN users u ON u.id = ur.user_id
    JOIN tests t ON t.id = ur.test_id
    ORDER BY ur.created_at DESC
    LIMIT 8
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="section-title">
    <div>
        <h1>Панель администратора</h1>
        <p class="muted">Здравствуйте, <?= e($admin['full_name']); ?>. Здесь собрана сводка по системе.</p>
    </div>
    <div class="inline-actions">
        <a class="btn btn-primary" href="/knowledge_test_system/admin/manage_tests.php?action=create">Добавить тест</a>
        <a class="btn btn-light" href="/knowledge_test_system/admin/manage_questions.php?action=create">Добавить вопрос</a>
        <a class="btn btn-light" href="/knowledge_test_system/admin/import_questions.php">Импорт из Word</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card"><h3><?= $counts['users']; ?></h3><p class="muted">Пользователей</p></div>
    <div class="stat-card"><h3><?= $counts['tests']; ?></h3><p class="muted">Тестов</p></div>
    <div class="stat-card"><h3><?= $counts['questions']; ?></h3><p class="muted">Вопросов</p></div>
    <div class="stat-card"><h3><?= $counts['attempts']; ?></h3><p class="muted">Попыток прохождения</p></div>
</div>

<div class="cards">
    <div class="card">
        <h3>Быстрые действия</h3>
        <div class="inline-actions">
            <a class="btn btn-primary" href="/knowledge_test_system/admin/manage_tests.php">Управление тестами</a>
            <a class="btn btn-light" href="/knowledge_test_system/admin/manage_users.php">Пользователи</a>
            <a class="btn btn-light" href="/knowledge_test_system/admin/manage_results.php">Результаты</a>
            <a class="btn btn-light" href="/knowledge_test_system/admin/import_questions.php">Импорт из Word</a>
        </div>
    </div>
</div>

<div class="section-title" style="margin-top:24px;">
    <h2>Последние результаты пользователей</h2>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Тест</th>
                <th>Баллы</th>
                <th>Процент</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($latest as $row): ?>
                <tr>
                    <td><?= e($row['full_name']); ?></td>
                    <td><?= e($row['title']); ?></td>
                    <td><?= (int)$row['score']; ?>/<?= (int)$row['max_score']; ?></td>
                    <td><?= e($row['percentage']); ?>%</td>
                    <td><?= e(date('d.m.Y H:i', strtotime($row['created_at']))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>