<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$pageTitle = 'Управление вопросами';

if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Вопрос удален.');
    redirect('/knowledge_test_system/admin/manage_questions.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testId = (int)($_POST['test_id'] ?? 0);
    $questionText = trim($_POST['question_text'] ?? '');
    $questionType = $_POST['question_type'] ?? 'single';
    $correctTextAnswer = trim($_POST['correct_text_answer'] ?? '');
    $optionTexts = $_POST['option_text'] ?? [];
    $correctOptions = $_POST['correct_option'] ?? [];

    if ($testId <= 0 || $questionText === '') {
        setFlash('error', 'Заполните обязательные поля.');
    } else {
        if ($action === 'edit' && $id > 0) {
            $stmt = $pdo->prepare("UPDATE questions SET test_id = ?, question_text = ?, question_type = ?, correct_text_answer = ? WHERE id = ?");
            $stmt->execute([$testId, $questionText, $questionType, $questionType === 'text' ? $correctTextAnswer : null, $id]);

            $pdo->prepare("DELETE FROM answers WHERE question_id = ?")->execute([$id]);
            $questionId = $id;
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions (test_id, question_text, question_type, correct_text_answer) VALUES (?, ?, ?, ?)");
            $stmt->execute([$testId, $questionText, $questionType, $questionType === 'text' ? $correctTextAnswer : null]);
            $questionId = (int)$pdo->lastInsertId();
        }

        if ($questionType !== 'text') {
            $insertAnswer = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
            foreach ($optionTexts as $index => $text) {
                $text = trim($text);
                if ($text === '') {
                    continue;
                }
                $isCorrect = in_array((string)$index, array_map('strval', (array)$correctOptions), true) ? 1 : 0;
                $insertAnswer->execute([$questionId, $text, $isCorrect]);
            }
        }

        setFlash('success', $action === 'edit' ? 'Вопрос обновлен.' : 'Вопрос добавлен.');
        redirect('/knowledge_test_system/admin/manage_questions.php');
    }
}

$tests = $pdo->query("SELECT id, title FROM tests ORDER BY title")->fetchAll();
$question = ['test_id' => '', 'question_text' => '', 'question_type' => 'single', 'correct_text_answer' => ''];
$options = [];

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    $question = $stmt->fetch() ?: $question;

    $stmt = $pdo->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id");
    $stmt->execute([$id]);
    $options = $stmt->fetchAll();
}

$questions = $pdo->query("
    SELECT q.*, t.title AS test_title,
           (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answer_count
    FROM questions q
    JOIN tests t ON t.id = q.test_id
    ORDER BY q.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>
<div class="section-title">
    <h1>Управление вопросами</h1>
    <a class="btn btn-primary" href="/knowledge_test_system/admin/manage_questions.php?action=create">Новый вопрос</a>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>
    <div class="card">
        <h3><?= $action === 'edit' ? 'Редактирование вопроса' : 'Добавление вопроса'; ?></h3>
        <form method="post" data-validate="true">
            <div class="form-grid">
                <div class="form-group">
                    <label>Тест</label>
                    <select name="test_id" required>
                        <option value="">Выберите тест</option>
                        <?php foreach ($tests as $test): ?>
                            <option value="<?= (int)$test['id']; ?>" <?= (string)$question['test_id'] === (string)$test['id'] ? 'selected' : ''; ?>>
                                <?= e($test['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Тип вопроса</label>
                    <select name="question_type" required>
                        <option value="single" <?= $question['question_type'] === 'single' ? 'selected' : ''; ?>>Один правильный ответ</option>
                        <option value="multiple" <?= $question['question_type'] === 'multiple' ? 'selected' : ''; ?>>Несколько правильных ответов</option>
                        <option value="text" <?= $question['question_type'] === 'text' ? 'selected' : ''; ?>>Текстовый ответ</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Текст вопроса</label>
                    <textarea name="question_text" required><?= e($question['question_text']); ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Правильный текстовый ответ</label>
                    <input type="text" name="correct_text_answer" value="<?= e($question['correct_text_answer']); ?>" placeholder="Заполняется для типа text">
                </div>
            </div>

            <h4>Варианты ответов для single/multiple</h4>
            <?php
            $renderOptions = $options ?: [
                ['answer_text' => '', 'is_correct' => 0],
                ['answer_text' => '', 'is_correct' => 0],
                ['answer_text' => '', 'is_correct' => 0],
                ['answer_text' => '', 'is_correct' => 0],
            ];
            foreach ($renderOptions as $i => $opt):
            ?>
                <div class="form-grid" style="margin-bottom:10px;">
                    <div class="form-group">
                        <label>Вариант <?= $i + 1; ?></label>
                        <input type="text" name="option_text[<?= $i; ?>]" value="<?= e($opt['answer_text']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Правильный</label>
                        <input type="checkbox" name="correct_option[]" value="<?= $i; ?>" <?= !empty($opt['is_correct']) ? 'checked' : ''; ?>>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="inline-actions">
                <button class="btn btn-success" type="submit">Сохранить</button>
                <a class="btn btn-light" href="/knowledge_test_system/admin/manage_questions.php">Отмена</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="table-wrap" style="margin-top:24px;">
    <table>
        <thead><tr><th>ID</th><th>Тест</th><th>Тип</th><th>Вопрос</th><th>Ответов</th><th>Действия</th></tr></thead>
        <tbody>
        <?php foreach ($questions as $row): ?>
            <tr>
                <td><?= (int)$row['id']; ?></td>
                <td><?= e($row['test_title']); ?></td>
                <td><?= e($row['question_type']); ?></td>
                <td><?= e($row['question_text']); ?></td>
                <td><?= (int)$row['answer_count']; ?></td>
                <td>
                    <div class="inline-actions">
                        <a class="btn btn-light" href="/knowledge_test_system/admin/manage_questions.php?action=edit&id=<?= (int)$row['id']; ?>">Изменить</a>
                        <a class="btn btn-danger" href="/knowledge_test_system/admin/manage_questions.php?action=delete&id=<?= (int)$row['id']; ?>" onclick="return confirm('Удалить вопрос?')">Удалить</a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
