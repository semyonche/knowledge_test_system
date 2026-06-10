<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser($pdo);
$pageTitle = 'Профиль';

$avatarMessage = '';
$avatarError = '';

// ===== ЗАГРУЗКА АВАТАРКИ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (!isset($_SESSION['user_id'])) {
        die('Доступ запрещён.');
    }

    $userId = (int) $_SESSION['user_id'];

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        $avatarError = 'Выберите изображение.';
    } else {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $avatarError = 'Ошибка при загрузке файла.';
        } else {
            $maxSize = 2 * 1024 * 1024;
            $allowedMime = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if ($file['size'] > $maxSize) {
                $avatarError = 'Файл слишком большой. Максимум 2 МБ.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!array_key_exists($mimeType, $allowedMime)) {
                    $avatarError = 'Разрешены только JPG, PNG и WEBP.';
                } else {
                    $extension = $allowedMime[$mimeType];

                    $uploadDir = __DIR__ . '/uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $stmtOld = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                    $stmtOld->execute([$userId]);
                    $oldUser = $stmtOld->fetch(PDO::FETCH_ASSOC);

                    $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
                    $targetPath = $uploadDir . $newFileName;
                    $dbPath = 'uploads/avatars/' . $newFileName;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $stmtUpdate = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $saved = $stmtUpdate->execute([$dbPath, $userId]);

                        if ($saved) {
                            if (!empty($oldUser['avatar'])) {
                                $oldFile = __DIR__ . '/' . ltrim($oldUser['avatar'], '/');
                                if (file_exists($oldFile) && is_file($oldFile)) {
                                    @unlink($oldFile);
                                }
                            }

                            $avatarMessage = 'Аватар успешно обновлён.';
                            $user = currentUser($pdo);
                        } else {
                            $avatarError = 'Не удалось сохранить путь к аватару в базе.';
                        }
                    } else {
                        $avatarError = 'Не удалось сохранить файл.';
                    }
                }
            }
        }
    }
}

// ===== УДАЛЕНИЕ АККАУНТА =====


// ===== ОБНОВЛЕНИЕ ПРОФИЛЯ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Проверьте корректность данных.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$email, $user['id']]);

        if ($stmt->fetch()) {
            setFlash('error', 'Этот email уже используется.');
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$fullName, $email, $user['id']]);

            setFlash('success', 'Профиль обновлен.');
            redirect('/knowledge_test_system/profile.php');
        }
    }
}

// ===== ПУТЬ К АВАТАРКЕ =====
$avatarPath = '/knowledge_test_system/assets/img/default-avatar.svg';

if (!empty($user['avatar'])) {
    $storedAvatar = ltrim($user['avatar'], '/');
    $fullAvatarFile = __DIR__ . '/' . $storedAvatar;

    if (file_exists($fullAvatarFile) && is_file($fullAvatarFile)) {
        $avatarPath = '/knowledge_test_system/' . $storedAvatar;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container center-box">
    <div class="card">
        <div class="section-title">
            <h2>Профиль пользователя</h2>
            
        </div>

        <div class="profile-avatar-card">
            <div class="avatar-wrapper">
                <img
                    src="<?= e($avatarPath); ?>"
                    alt="Аватар пользователя"
                    class="profile-avatar"
                    id="avatarPreview"
                >
            </div>

            <div class="avatar-info">
                <h3>Фотография профиля</h3>
                <p>Загрузите JPG, PNG или WEBP. Максимальный размер: 2 МБ.</p>

                <?php if (!empty($avatarMessage)): ?>
                    <div class="success-message"><?= e($avatarMessage); ?></div>
                <?php endif; ?>

                <?php if (!empty($avatarError)): ?>
                    <div class="error-message"><?= e($avatarError); ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <input
                            type="file"
                            name="avatar"
                            id="avatarInput"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            required
                        >
                    </div>
                    <button type="submit" name="upload_avatar" class="btn btn-primary upload-avatar-btn">
                      Изменить аватар
                </button>
                </form>
            </div>
        </div>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">

        <form method="post" data-validate="true">
            <div class="form-grid">
                <div class="form-group">
                    <label>ФИО</label>
                    <input type="text" name="full_name" value="<?= e($user['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" value="<?= e($user['username']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Дата регистрации</label>
                    <input type="text" value="<?= e(date('d.m.Y H:i', strtotime($user['created_at']))); ?>" disabled>
                </div>
            </div>

            <button class="btn btn-primary save-profile-btn" type="submit" name="save_profile">Сохранить изменения</button>
        </form>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #e2e8f0;">
        
<script>
document.addEventListener('DOMContentLoaded', function () {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Разрешены только JPG, PNG и WEBP.');
                avatarInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                avatarPreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>

