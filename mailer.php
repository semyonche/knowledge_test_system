<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

function sendResetEmail(string $toEmail, string $resetLink): bool
{
    $mailConfig = require __DIR__ . '/../config/mail.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)$mailConfig['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Сброс пароля';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
                <h2>Сброс пароля</h2>
                <p>Вы запросили восстановление пароля для аккаунта в системе QuizCore.</p>
                <p>Нажмите на ссылку ниже, чтобы задать новый пароль:</p>
                <p>
                    <a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" 
                       style="display:inline-block;padding:12px 18px;background:#4f46e5;color:#ffffff;text-decoration:none;border-radius:10px;">
                        Сбросить пароль
                    </a>
                </p>
                <p>Если кнопка не работает, откройте эту ссылку вручную:</p>
                <p>' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '</p>
                <p>Если вы не отправляли запрос, просто проигнорируйте это письмо.</p>
            </div>
        ';
        $mail->AltBody = "Сброс пароля\n\nПерейдите по ссылке:\n" . $resetLink;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}