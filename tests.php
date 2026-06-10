<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Список тестов';

/* =========================================================
   ПОЛУЧЕНИЕ ФИЛЬТРОВ
========================================================= */

$search  = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$level    = trim($_GET['level'] ?? '');

/* =========================================================
   SQL ЗАПРОС С ФИЛЬТРАЦИЕЙ
========================================================= */

$sql = "
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

    WHERE t.is_active = 1
";

$params = [];

/* Поиск по названию */
if ($search !== '') {
    $sql .= " AND t.title LIKE ?";
    $params[] = "%{$search}%";
}

/* Фильтр по категории */
if ($category !== '') {
    $sql .= " AND c.name = ?";
    $params[] = $category;
}

/* Фильтр по уровню */
if ($level !== '') {
    $sql .= " AND t.level = ?";
    $params[] = $level;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tests = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">

    <!-- =====================================================
         ЗАГОЛОВОК
    ====================================================== -->

    <div class="section-title">
        <h2>Доступные тесты</h2>
    </div>

    <!-- =====================================================
         ФИЛЬТРЫ И ПОИСК
    ====================================================== -->

    <div class="card" style="margin-bottom: 24px;">

        <form method="GET" class="form-grid">

            <!-- Поиск -->
            <div class="form-group">
                <label>Поиск теста</label>

                <input
                    type="text"
                    name="search"
                    placeholder="Введите название..."
                    value="<?= e($search); ?>"
                >
            </div>

            <!-- Категории -->
            <div class="form-group">
                <label>Категория</label>

                <select name="category">

                    <option value="">Все категории</option>

                    <option value="Программирование"
                        <?= $category === 'Программирование' ? 'selected' : ''; ?>>
                        Программирование
                    </option>

                    <option value="Веб-разработка"
                        <?= $category === 'Веб-разработка' ? 'selected' : ''; ?>>
                        Веб-разработка
                    </option>

                    <option value="Базы данных"
                        <?= $category === 'Базы данных' ? 'selected' : ''; ?>>
                        Базы данных
                    </option>

                    <option value="Кибербезопасность"
                        <?= $category === 'Кибербезопасность' ? 'selected' : ''; ?>>
                        Кибербезопасность
                    </option>

                    <option value="Алгоритмы"
                        <?= $category === 'Алгоритмы' ? 'selected' : ''; ?>>
                        Алгоритмы
                    </option>

                    <option value="Сети"
                        <?= $category === 'Сети' ? 'selected' : ''; ?>>
                        Сети
                    </option>

                </select>
            </div>

            <!-- Уровни -->
            <div class="form-group">
                <label>Уровень</label>

                <select name="level">

                    <option value="">Все уровни</option>

                    <option value="Начальный"
                        <?= $level === 'Начальный' ? 'selected' : ''; ?>>
                        Начальный
                    </option>

                    <option value="Базовый"
                        <?= $level === 'Базовый' ? 'selected' : ''; ?>>
                        Базовый
                    </option>

                    <option value="Средний"
                        <?= $level === 'Средний' ? 'selected' : ''; ?>>
                        Средний
                    </option>

                    <option value="Продвинутый"
                        <?= $level === 'Продвинутый' ? 'selected' : ''; ?>>
                        Продвинутый
                    </option>

                </select>
            </div>

            <!-- Кнопка -->
            <div class="form-group">
                <label>&nbsp;</label>

                <button type="submit" class="btn btn-primary">
                    Найти
                </button>
            </div>

        </form>
    </div>

    <!-- =====================================================
         СПИСОК ТЕСТОВ
    ====================================================== -->

    <div class="cards">

        <?php if (empty($tests)): ?>

            <div class="card">
                <h3>Тесты не найдены</h3>
                <p class="muted">
                    Попробуйте изменить параметры поиска.
                </p>
            </div>

        <?php endif; ?>

        <?php foreach ($tests as $test): ?>

            <div class="card">

                <!-- Категория -->
                <span class="badge">
                    <?= e($test['category_name'] ?? 'Без категории'); ?>
                </span>

                <!-- Уровень -->
                <?php if (!empty($test['level'])): ?>
                    <p style="margin-top:12px;">
                        <strong>Уровень:</strong>
                        <?= e($test['level']); ?>
                    </p>
                <?php endif; ?>

                <!-- Название -->
                <h3><?= e($test['title']); ?></h3>

                <!-- Описание -->
                <p class="muted">
                    <?= e($test['description']); ?>
                </p>

                <!-- Количество вопросов -->
                <p>
                    <strong>Вопросов:</strong>
                    <?= (int)$test['question_count']; ?>
                </p>

                <!-- Время -->
                <p>
                    <strong>Лимит времени:</strong>
                    <?= (int)$test['time_limit']; ?> мин.
                </p>

                <!-- Кнопка -->
                <a
                    class="btn btn-primary"
                    href="/knowledge_test_system/test.php?id=<?= (int)$test['id']; ?>"
                >
                    Начать
                </a>

            </div>

        <?php endforeach; ?>

    </div>
</div>

