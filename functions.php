<?php
// Общие функции проекта.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Безопасный вывод текста.
 */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Перенаправление.
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Проверка авторизации пользователя.
 */
function isUserLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Проверка авторизации администратора.
 */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Получение текущего пользователя.
 */
function currentUser(PDO $pdo): ?array
{
    if (!isUserLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Получение текущего администратора.
 */
function currentAdmin(PDO $pdo): ?array
{
    if (!isAdminLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Flash-сообщение.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Нормализация строки.
 */
function normalizeAnswer(string $text): string
{
    $text = trim(mb_strtolower($text));
    $text = preg_replace('/\s+/u', ' ', $text);
    return $text;
}

/**
 * Получение пользовательской статистики.
 */
function getUserStats(PDO $pdo, int $userId): array
{
    $stats = [
        'tests_passed' => 0,
        'attempts_count' => 0,
        'avg_score' => 0,
        'best_result' => 0,
        'last_test' => 'Нет данных',
        'success_rate' => 0,
    ];

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT test_id) AS tests_passed,
            COUNT(*) AS attempts_count,
            COALESCE(AVG(percentage), 0) AS avg_score,
            COALESCE(MAX(percentage), 0) AS best_result,
            COALESCE(AVG(successful) * 100, 0) AS success_rate
        FROM user_results
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if ($row) {
        $stats['tests_passed'] = (int)$row['tests_passed'];
        $stats['attempts_count'] = (int)$row['attempts_count'];
        $stats['avg_score'] = round((float)$row['avg_score'], 2);
        $stats['best_result'] = round((float)$row['best_result'], 2);
        $stats['success_rate'] = round((float)$row['success_rate'], 2);
    }

    $stmt = $pdo->prepare("
        SELECT t.title
        FROM user_results ur
        JOIN tests t ON t.id = ur.test_id
        WHERE ur.user_id = ?
        ORDER BY ur.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $last = $stmt->fetch();
    if ($last) {
        $stats['last_test'] = $last['title'];
    }

    return $stats;
}

/**
 * Получение всех категорий.
 */
function getCategories(PDO $pdo): array
{
    return $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}
