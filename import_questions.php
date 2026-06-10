<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Импорт вопросов из Word';

function readDocxText(string $filePath): string
{
    $zip = new ZipArchive();

    if ($zip->open($filePath) !== true) {
        return '';
    }

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if (!$xml) {
        return '';
    }

    $xml = str_replace('</w:p>', "\n", $xml);
    $xml = strip_tags($xml);
    $xml = html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8');

    return trim($xml);
}

$message = '';
$error = '';

$tests = $pdo->query("SELECT id, title FROM tests ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_questions'])) {
    $testId = (int)($_POST['test_id'] ?? 0);

    if ($testId <= 0) {
        $error = 'Выберите тест.';
    } elseif (!isset($_FILES['docx_file']) || $_FILES['docx_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Загрузите файл Word.';
    } else {
        $file = $_FILES['docx_file'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension !== 'docx') {
            $error = 'Поддерживаются только файлы .docx';
        } else {
            $text = readDocxText($file['tmp_name']);

            if ($text === '') {
                $error = 'Не удалось прочитать файл Word.';
            } else {
                $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $text)), fn($line) => $line !== ''));

                $currentQuestion = null;
                $currentType = 'single';
                $currentAnswers = [];
                $currentTextAnswer = null;
                $importedCount = 0;

                $saveQuestion = function () use ($pdo, $testId, &$currentQuestion, &$currentType, &$currentAnswers, &$currentTextAnswer, &$importedCount) {
                    if (!$currentQuestion) {
                        return;
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO questions (test_id, question_text, question_type)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$testId, $currentQuestion, $currentType]);
                    $questionId = (int)$pdo->lastInsertId();

                    if ($currentType === 'text' && $currentTextAnswer !== null) {
                        $stmtAnswer = $pdo->prepare("
                            INSERT INTO answers (question_id, answer_text, is_correct)
                            VALUES (?, ?, 1)
                        ");
                        $stmtAnswer->execute([$questionId, $currentTextAnswer]);
                    } else {
                        foreach ($currentAnswers as $answer) {
                            $stmtAnswer = $pdo->prepare("
                                INSERT INTO answers (question_id, answer_text, is_correct)
                                VALUES (?, ?, ?)
                            ");
                            $stmtAnswer->execute([
                                $questionId,
                                $answer['text'],
                                $answer['correct'] ? 1 : 0
                            ]);
                        }
                    }

                    $importedCount++;
                    $currentQuestion = null;
                    $currentType = 'single';
                    $currentAnswers = [];
                    $currentTextAnswer = null;
                };

                foreach ($lines as $line) {
                    if (mb_stripos($line, 'ВОПРОС:') === 0) {
                        $saveQuestion();
                        $currentQuestion = trim(mb_substr($line, 7));
                    } elseif (mb_stripos($line, 'ТИП:') === 0) {
                        $currentType = trim(mb_strtolower(mb_substr($line, 4)));
                    } elseif (mb_stripos($line, 'ОТВЕТ:') === 0) {
                        $currentTextAnswer = trim(mb_substr($line, 6));
                    } elseif (preg_match('/^\d+\)\s*(.+)$/u', $line, $matches)) {
                        $answerText = trim($matches[1]);
                        $isCorrect = false;

                        if (mb_substr($answerText, -1) === '*') {
                            $isCorrect = true;
                            $answerText = trim(mb_substr($answerText, 0, -1));
                        }

                        $currentAnswers[] = [
                            'text' => $answerText,
                            'correct' => $isCorrect
                        ];
                    }
                }

                $saveQuestion();

                $message = "Импорт завершён. Добавлено вопросов: {$importedCount}.";
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="section-title">
        <h2>Импорт вопросов из Word</h2>
        <a class="btn btn-light" href="/knowledge_test_system/admin/admin_dashboard.php">Назад</a>
    </div>

    <div class="card">
        <?php if ($message): ?>
            <div class="success-message"><?= e($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message"><?= e($error); ?></div>
        <?php endif; ?>

        <p class="muted">
            Поддерживается только формат <strong>.docx</strong>.
            Используйте шаблон: ВОПРОС, ТИП, ответы или ОТВЕТ для текстового вопроса.
        </p>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="test_id">Выберите тест</label>
                <select name="test_id" id="test_id" required>
                    <option value="">-- Выберите тест --</option>
                    <?php foreach ($tests as $test): ?>
                        <option value="<?= (int)$test['id']; ?>"><?= e($test['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="docx_file">Файл Word (.docx)</label>
                <input type="file" name="docx_file" id="docx_file" accept=".docx" required>
            </div>

            <button type="submit" name="import_questions" class="btn btn-primary">Импортировать</button>
        </form>
    </div>
</div>

