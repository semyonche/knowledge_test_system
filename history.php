<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser($pdo);
$pageTitle = 'История прохождения';

$stmt = $pdo->prepare("
    SELECT ur.*, t.title
    FROM user_results ur
    JOIN tests t ON t.id = ur.test_id
    WHERE ur.user_id = ?
    ORDER BY ur.created_at DESC
");
$stmt->execute([$user['id']]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container narrow-container">
    <div class="section-title">
        <h2>История прохождения тестов</h2>
        <div class="inline-actions">
            <a class="btn history-action-btn" href="/knowledge_test_system/export_results.php">Экспорт в Excel</a>
            <a class="btn history-action-btn" href="/knowledge_test_system/profile.php">Назад</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Тест</th>
                    <th>Баллы</th>
                    <th>Процент</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results)): ?>
                    <?php foreach ($results as $item): ?>
                        <tr>
                            <td><?= e($item['title']); ?></td>
                            <td><?= (int)$item['score']; ?>/<?= (int)$item['max_score']; ?></td>
                            <td><?= e($item['percentage']); ?>%</td>
                            <td><?= $item['successful'] ? 'Успешно' : 'Неуспешно'; ?></td>
                            <td><?= e(date('d.m.Y H:i', strtotime($item['created_at']))); ?></td>
                            <td>
                                <a class="btn btn-light" href="/knowledge_test_system/result.php?id=<?= (int)$item['id']; ?>">
                                    Открыть
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="muted" style="text-align:center;">История прохождения пока пуста.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>