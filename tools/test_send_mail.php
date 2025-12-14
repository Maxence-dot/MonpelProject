<?php
require_once __DIR__ . '/../load_env.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/MailService.php';

$to = $argv[1] ?? getenv('MAIL_TO_TEST') ?? 'test@example.com';
$token = bin2hex(random_bytes(8));

$mail = new MailService();
$ok = $mail->sendValidationEmail($to, $token);

echo "sendValidationEmail result: " . ($ok ? "OK" : "FAILED") . PHP_EOL;
