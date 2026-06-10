<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser($pdo);

$stmt = $pdo->prepare("
    SELECT ur.created_at, t.title, ur.score, ur.max_score, ur.percentage, ur.successful
    FROM user_results ur
    JOIN tests t ON t.id = ur.test_id
    WHERE ur.user_id = ?
    ORDER BY ur.created_at DESC
");
$stmt->execute([$user['id']]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'results_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['Дата', 'Тест', 'Баллы', 'Макс. балл', 'Процент', 'Статус'], ';');

foreach ($results as $row) {
    fputcsv($output, [
        date('d.m.Y H:i', strtotime($row['created_at'])),
        $row['title'],
        $row['score'],
        $row['max_score'],
        $row['percentage'] . '%',
        $row['successful'] ? 'Успешно' : 'Неуспешно'
    ], ';');
}

fclose($output);
exit;