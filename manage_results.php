<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Результаты';
$userFilter = trim($_GET['user'] ?? '');
$testFilter = trim($_GET['test'] ?? '');
$dateFilter = trim($_GET['date'] ?? '');

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM user_results WHERE id = ?");
    $stmt->execute([$deleteId]);
    setFlash('success', 'Результат удален.');
    redirect('/knowledge_test_system/admin/manage_results.php');
}

$sql = "
    SELECT ur.*, u.full_name, t.title
    FROM user_results ur
    JOIN users u ON u.id = ur.user_id
    JOIN tests t ON t.id = ur.test_id
    WHERE 1=1
";
$params = [];

if ($userFilter !== '') {
    $sql .= " AND u.full_name LIKE ?";
    $params[] = "%$userFilter%";
}
if ($testFilter !== '') {
    $sql .= " AND t.title LIKE ?";
    $params[] = "%$testFilter%";
}
if ($dateFilter !== '') {
    $sql .= " AND DATE(ur.created_at) = ?";
    $params[] = $dateFilter;
}

$sql .= " ORDER BY ur.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="section-title"><h1>Результаты пользователей</h1></div>

<div class="card">
    <form method="get">
        <div class="form-grid">
            <div class="form-group">
                <label>Пользователь</label>
                <input type="text" name="user" value="<?= e($userFilter); ?>">
            </div>
            <div class="form-group">
                <label>Тест</label>
                <input type="text" name="test" value="<?= e($testFilter); ?>">
            </div>
            <div class="form-group">
                <label>Дата</label>
                <input type="date" name="date" value="<?= e($dateFilter); ?>">
            </div>
        </div>
        <div class="inline-actions results-filter-actions">
    <button class="btn btn-primary" type="submit">Фильтровать</button>
    <a class="btn btn-light" href="/knowledge_test_system/admin/manage_results.php">Сбросить</a>
</div>
<div class="table-wrap" style="margin-top:24px;">
    <table>
        <thead><tr><th>ID</th><th>Пользователь</th><th>Тест</th><th>Баллы</th><th>Процент</th><th>Статус</th><th>Дата</th><th>Действие</th></tr></thead>
        <tbody>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?= (int)$row['id']; ?></td>
                <td><?= e($row['full_name']); ?></td>
                <td><?= e($row['title']); ?></td>
                <td><?= (int)$row['score']; ?>/<?= (int)$row['max_score']; ?></td>
                <td><?= e($row['percentage']); ?>%</td>
                <td><?= $row['successful'] ? 'Успешно' : 'Неуспешно'; ?></td>
                <td><?= e(date('d.m.Y H:i', strtotime($row['created_at']))); ?></td>
                <td><a class="btn btn-danger" href="/knowledge_test_system/admin/manage_results.php?delete=<?= (int)$row['id']; ?>" onclick="return confirm('Удалить результат?')">Удалить</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
