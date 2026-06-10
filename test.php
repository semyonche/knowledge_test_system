<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser($pdo);
$testId = (int)($_GET['id'] ?? $_POST['test_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM tests WHERE id = ? AND is_active = 1");
$stmt->execute([$testId]);
$test = $stmt->fetch();

if (!$test) {
    setFlash('error', 'Тест не найден.');
    redirect('/knowledge_test_system/tests.php');
}

$qStmt = $pdo->prepare("SELECT * FROM questions WHERE test_id = ? ORDER BY id");
$qStmt->execute([$testId]);
$questions = $qStmt->fetchAll();

foreach ($questions as &$question) {
    if ($question['question_type'] !== 'text') {
        $aStmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id");
        $aStmt->execute([$question['id']]);
        $question['answers'] = $aStmt->fetchAll();
    } else {
        $question['answers'] = [];
    }
}
unset($question);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answersRaw = $_POST['answers'] ?? [];
    $score = 0;
    $maxScore = count($questions);

    $insertResult = $pdo->prepare("
        INSERT INTO user_results (user_id, test_id, score, max_score, percentage, successful)
        VALUES (?, ?, 0, ?, 0, 0)
    ");
    $insertResult->execute([$user['id'], $testId, $maxScore]);
    $resultId = (int)$pdo->lastInsertId();

    $insertUserAnswer = $pdo->prepare("
        INSERT INTO user_answers (result_id, question_id, user_answer_text, is_correct)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($questions as $question) {
        $qid = (int)$question['id'];
        $isCorrect = 0;
        $userAnswerText = '';

        if ($question['question_type'] === 'single') {
            $selected = isset($answersRaw[$qid]) ? (int)$answersRaw[$qid] : 0;
            $userAnswerText = (string)$selected;

            $stmt = $pdo->prepare("SELECT id FROM answers WHERE question_id = ? AND is_correct = 1 LIMIT 1");
            $stmt->execute([$qid]);
            $correct = $stmt->fetch();
            $isCorrect = ($correct && (int)$correct['id'] === $selected) ? 1 : 0;
        } elseif ($question['question_type'] === 'multiple') {
            $selected = $answersRaw[$qid] ?? [];
            if (!is_array($selected)) {
                $selected = [];
            }
            $selected = array_map('intval', $selected);
            sort($selected);
            $userAnswerText = implode(',', $selected);

            $stmt = $pdo->prepare("SELECT id FROM answers WHERE question_id = ? AND is_correct = 1 ORDER BY id");
            $stmt->execute([$qid]);
            $correctIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));
            sort($correctIds);

            $isCorrect = ($selected === $correctIds) ? 1 : 0;
        } else {
            $text = trim($answersRaw[$qid] ?? '');
            $userAnswerText = $text;
            $isCorrect = normalizeAnswer($text) === normalizeAnswer((string)$question['correct_text_answer']) ? 1 : 0;
        }

        if ($isCorrect) {
            $score++;
        }

        $insertUserAnswer->execute([$resultId, $qid, $userAnswerText, $isCorrect]);
    }

    $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;
    $successful = $percentage >= 60 ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE user_results SET score = ?, percentage = ?, successful = ? WHERE id = ?");
    $stmt->execute([$score, $percentage, $successful, $resultId]);

    redirect('/knowledge_test_system/result.php?id=' . $resultId);
}

$pageTitle = 'Прохождение теста';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container center-box">
    <div class="card">
        <div class="section-title">
            <div>
                <h2><?= e($test['title']); ?></h2>
                <p class="muted"><?= e($test['description']); ?></p>
            </div>
            <div class="badge">Осталось: <span id="timer" data-seconds="<?= (int)$test['time_limit'] * 60; ?>">00:00</span></div>
        </div>

        <form method="post" id="testForm">
            <input type="hidden" name="test_id" value="<?= (int)$testId; ?>">
            <?php foreach ($questions as $index => $question): ?>
                <div class="test-question">
                    <h3>Вопрос <?= $index + 1; ?>.</h3>
                    <p><?= e($question['question_text']); ?></p>

                    <?php if ($question['question_type'] === 'single'): ?>
                        <div class="option-list">
                            <?php foreach ($question['answers'] as $answer): ?>
                                <label class="option-item">
                                    <input type="radio" name="answers[<?= (int)$question['id']; ?>]" value="<?= (int)$answer['id']; ?>">
                                    <span><?= e($answer['answer_text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($question['question_type'] === 'multiple'): ?>
                        <div class="option-list">
                            <?php foreach ($question['answers'] as $answer): ?>
                                <label class="option-item">
                                    <input type="checkbox" name="answers[<?= (int)$question['id']; ?>][]" value="<?= (int)$answer['id']; ?>">
                                    <span><?= e($answer['answer_text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <input type="text" name="answers[<?= (int)$question['id']; ?>]" placeholder="Введите ваш ответ">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <button class="btn btn-success" type="submit">Завершить тест</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
