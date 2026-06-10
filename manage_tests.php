<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

$pageTitle = 'Управление тестами';

/* =========================================================
   УДАЛЕНИЕ ТЕСТА
========================================================= */
if ($action === 'delete' && $id > 0) {

    $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
    $stmt->execute([$id]);

    setFlash('success', 'Тест удалён.');
    redirect('/knowledge_test_system/admin/manage_tests.php');
}

/* =========================================================
   СОЗДАНИЕ / РЕДАКТИРОВАНИЕ
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $categoryId = $_POST['category_id'] !== ''
        ? (int)$_POST['category_id']
        : null;

    $level = trim($_POST['level'] ?? 'Начальный');

    $timeLimit = max(
        1,
        (int)($_POST['time_limit'] ?? 15)
    );

    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '') {

        setFlash('error', 'Название теста обязательно.');

    } else {

        /* =========================================
           ОБНОВЛЕНИЕ ТЕСТА
        ========================================= */
        if ($action === 'edit' && $id > 0) {

            $stmt = $pdo->prepare("
                UPDATE tests
                SET
                    category_id = ?,
                    title = ?,
                    description = ?,
                    level = ?,
                    time_limit = ?,
                    is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $categoryId,
                $title,
                $description,
                $level,
                $timeLimit,
                $isActive,
                $id
            ]);

            setFlash('success', 'Тест обновлён.');

        } else {

            /* =========================================
               СОЗДАНИЕ ТЕСТА
            ========================================= */
            $stmt = $pdo->prepare("
                INSERT INTO tests (
                    category_id,
                    title,
                    description,
                    level,
                    time_limit,
                    is_active
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $categoryId,
                $title,
                $description,
                $level,
                $timeLimit,
                $isActive
            ]);

            setFlash('success', 'Тест создан.');
        }

        redirect('/knowledge_test_system/admin/manage_tests.php');
    }
}

/* =========================================================
   КАТЕГОРИИ
========================================================= */
$categories = getCategories($pdo);

/* =========================================================
   ПУСТОЙ ШАБЛОН ТЕСТА
========================================================= */
$test = [
    'category_id' => '',
    'title' => '',
    'description' => '',
    'level' => 'Начальный',
    'time_limit' => 15,
    'is_active' => 1
];

/* =========================================================
   ЗАГРУЗКА ТЕСТА ДЛЯ РЕДАКТИРОВАНИЯ
========================================================= */
if ($action === 'edit' && $id > 0) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM tests
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $test = $stmt->fetch() ?: $test;
}

/* =========================================================
   СПИСОК ТЕСТОВ
========================================================= */
$tests = $pdo->query("
    SELECT
        t.*,
        c.name AS category_name,

        (
            SELECT COUNT(*)
            FROM questions q
            WHERE q.test_id = t.id
        ) AS question_count

    FROM tests t

    LEFT JOIN categories c
        ON c.id = t.category_id

    ORDER BY t.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="section-title">
    <h1>Управление тестами</h1>

    <a
        class="btn btn-primary"
        href="/knowledge_test_system/admin/manage_tests.php?action=create"
    >
        Новый тест
    </a>
</div>

<?php if ($action === 'create' || $action === 'edit'): ?>

<div class="card">

    <h3>
        <?= $action === 'edit'
            ? 'Редактирование теста'
            : 'Создание теста'; ?>
    </h3>

    <form method="post" data-validate="true">

        <div class="form-grid">

            <!-- Категория -->
            <div class="form-group">

                <label>Категория</label>

                <select name="category_id">

                    <option value="">
                        Без категории
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= (int)$category['id']; ?>"
                            <?= (string)$test['category_id'] === (string)$category['id']
                                ? 'selected'
                                : ''; ?>
                        >
                            <?= e($category['name']); ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <!-- Уровень -->
            <div class="form-group">

                <label>Уровень сложности</label>

                <select name="level" required>

                    <option value="Начальный"
                        <?= $test['level'] === 'Начальный' ? 'selected' : ''; ?>>
                        Начальный
                    </option>

                    <option value="Базовый"
                        <?= $test['level'] === 'Базовый' ? 'selected' : ''; ?>>
                        Базовый
                    </option>

                    <option value="Средний"
                        <?= $test['level'] === 'Средний' ? 'selected' : ''; ?>>
                        Средний
                    </option>

                    <option value="Продвинутый"
                        <?= $test['level'] === 'Продвинутый' ? 'selected' : ''; ?>>
                        Продвинутый
                    </option>

                </select>
            </div>

            <!-- Лимит -->
            <div class="form-group">

                <label>Лимит времени (мин.)</label>

                <input
                    type="number"
                    name="time_limit"
                    value="<?= e($test['time_limit']); ?>"
                    min="1"
                    required
                >
            </div>

            <!-- Название -->
            <div class="form-group" style="grid-column:1/-1;">

                <label>Название теста</label>

                <input
                    type="text"
                    name="title"
                    value="<?= e($test['title']); ?>"
                    required
                >
            </div>

            <!-- Описание -->
            <div class="form-group" style="grid-column:1/-1;">

                <label>Описание</label>

                <textarea name="description"><?= e($test['description']); ?></textarea>
            </div>

        </div>

        <!-- Активность -->
        <label class="option-item" style="margin:12px 0;">

            <input
                type="checkbox"
                name="is_active"
                <?= !empty($test['is_active']) ? 'checked' : ''; ?>
            >

            <span>Тест активен</span>

        </label>

        <!-- Кнопки -->
        <div class="inline-actions">

            <button class="btn btn-success" type="submit">
                Сохранить
            </button>

            <a
                class="btn btn-light"
                href="/knowledge_test_system/admin/manage_tests.php"
            >
                Отмена
            </a>

        </div>

    </form>
</div>

<?php endif; ?>

<div class="table-wrap" style="margin-top:24px;">

    <table>

        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Категория</th>
            <th>Уровень</th>
            <th>Вопросов</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($tests as $row): ?>

            <tr>

                <td><?= (int)$row['id']; ?></td>

                <td><?= e($row['title']); ?></td>

                <td><?= e($row['category_name'] ?? '—'); ?></td>

                <td>
                    <span class="badge">
                        <?= e($row['level']); ?>
                    </span>
                </td>

                <td><?= (int)$row['question_count']; ?></td>

                <td>
                    <?= $row['is_active']
                        ? 'Активен'
                        : 'Скрыт'; ?>
                </td>

                <td>

                    <div class="inline-actions">

                        <a
                            class="btn btn-light"
                            href="/knowledge_test_system/admin/manage_tests.php?action=edit&id=<?= (int)$row['id']; ?>"
                        >
                            Изменить
                        </a>

                        <a
                            class="btn btn-danger"
                            href="/knowledge_test_system/admin/manage_tests.php?action=delete&id=<?= (int)$row['id']; ?>"
                            onclick="return confirm('Удалить тест?')"
                        >
                            Удалить
                        </a>

                    </div>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>