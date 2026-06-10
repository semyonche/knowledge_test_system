<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser($pdo);
$resultId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT ur.*, t.title, t.description
    FROM user_results ur
    JOIN tests t ON t.id = ur.test_id
    WHERE ur.id = ? AND ur.user_id = ?
");
$stmt->execute([$resultId, $user['id']]);
$result = $stmt->fetch();

if (!$result) {
    setFlash('error', 'Результат не найден.');
    redirect('/knowledge_test_system/history.php');
}

$detailsStmt = $pdo->prepare("
    SELECT ua.*, q.question_text
    FROM user_answers ua
    JOIN questions q ON q.id = ua.question_id
    WHERE ua.result_id = ?
");
$detailsStmt->execute([$resultId]);
$details = $detailsStmt->fetchAll();

$pageTitle = 'Результат теста';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container center-box">
    <div class="card">
        <span class="badge"><?= $result['successful'] ? 'Тест пройден' : 'Тест не пройден'; ?></span>
        <h2><?= e($result['title']); ?></h2>
        <p class="muted"><?= e($result['description']); ?></p>

        <div class="cards">
            <div class="stat-card"><h3><?= (int)$result['score']; ?>/<?= (int)$result['max_score']; ?></h3><p class="muted">Набранные баллы</p></div>
            <div class="stat-card"><h3><?= e($result['percentage']); ?>%</h3><p class="muted">Процент правильных ответов</p></div>
            <div class="stat-card"><h3><?= e(date('d.m.Y H:i', strtotime($result['created_at']))); ?></h3><p class="muted">Дата прохождения</p></div>
        </div>

        <div class="section-title" style="margin-top:24px;"><h2>Детализация ответов</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Вопрос</th><th>Ответ пользователя</th><th>Статус</th></tr></thead>
                <tbody>
                <?php foreach ($details as $item): ?>
                    <tr>
                        <td><?= e($item['question_text']); ?></td>
                        <td><?= e($item['user_answer_text']); ?></td>
                        <td><?= $item['is_correct'] ? 'Верно' : 'Ошибка'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="inline-actions" style="margin-top:18px;">
            <a class="btn btn-primary" href="/knowledge_test_system/test.php?id=<?= (int)$result['test_id']; ?>">Пройти снова</a>
            <a class="btn btn-light" href="/knowledge_test_system/history.php">К истории</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
