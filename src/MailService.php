<?php

// If Composer autoload exists, load it so PHPMailer (and other libs) are available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * MailService
 * - SMTP via PHPMailer si configuré
 * - Fallback vers mail() sinon
 */
class MailService
{
    public function __construct()
    {
        // no-op
    }

    /**
     * Construit le lien de validation en tenant compte de APP_URL et du chemin de base
     */
    private function buildValidationLink(string $token): string
    {
        $appUrl = getenv('APP_URL') ?: null;

        // If APP_URL provided, use it; but if it has no path component and we can infer a base path, append it
        if ($appUrl) {
            $parts = parse_url($appUrl);
            $hasPath = isset($parts['path']) && rtrim($parts['path'], '/') !== '';
            if (!$hasPath && php_sapi_name() !== 'cli' && isset($_SERVER['SCRIPT_NAME'])) {
                $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                if ($basePath && $basePath !== '.') {
                    $appUrl = rtrim($appUrl, '/') . $basePath;
                }
            }
        } else {
            // infer from current request when possible
            if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $appUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
                $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                if ($basePath && $basePath !== '.') {
                    $appUrl = rtrim($appUrl, '/') . $basePath;
                }
            } else {
                $appUrl = 'http://localhost';
            }
        }

        return rtrim($appUrl, '/') . '/validate.php?token=' . urlencode($token);
    }

    /**
     * Envoie l'email de validation utilisateur
     */
    public function sendValidationEmail(string $to, string $token): bool
    {
        $host = getenv('MAIL_HOST');

        if (!empty($host)) {
            return $this->sendViaSMTP($to, $token);
        }

        return $this->sendViaMailFunction($to, $token);
    }

    /**
     * Envoi via SMTP (PHPMailer)
     */
    private function sendViaSMTP(string $to, string $token): bool
    {
        $link = $this->buildValidationLink($token);

        $mail = new PHPMailer(true);

        try {
            // SMTP config
            $mail->isSMTP();
            $mail->Host       = getenv('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = getenv('MAIL_USERNAME');
            $mail->Password   = getenv('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)(getenv('MAIL_PORT') ?: 587);

            // Charset
            $mail->CharSet = 'UTF-8';

            // From
            $fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@localhost';
            $fromName  = getenv('MAIL_FROM_NAME') ?: 'Murder Party Générative';
            $mail->setFrom($fromEmail, $fromName);

            // Recipient
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Validez votre adresse email - Murder Party Générative';
            $mail->Body    = $this->getValidationEmailHtml($link);

            // Send
            $mail->send();
            error_log('[MAIL_SUCCESS] SMTP email sent to ' . $to);
            return true;
        } catch (Exception $e) {
            error_log('[MAIL_ERROR] SMTP failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Envoi via mail() PHP (fallback)
     */
    private function sendViaMailFunction(string $to, string $token): bool
    {
        $link = $this->buildValidationLink($token);

        $subject = 'Validez votre adresse email - Murder Party Générative';
        $htmlBody = $this->getValidationEmailHtml($link);

        $fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@localhost';
        $fromName  = getenv('MAIL_FROM_NAME') ?: 'Murder Party Générative';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";

        $ok = mail($to, $subject, $htmlBody, $headers);

        if ($ok) {
            error_log('[MAIL_SUCCESS] mail() email sent to ' . $to);
        } else {
            error_log('[MAIL_ERROR] mail() failed for ' . $to);
        }

        return (bool)$ok;
    }

    /**
     * Template HTML de l'email de validation
     */
    private function getValidationEmailHtml(string $link): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Validation email</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
    .container { max-width: 600px; margin: auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
    .header { background: #7b61ff; color: white; padding: 20px; text-align: center; }
    .content { padding: 20px; color: #333; }
    .button {
      display: inline-block;
      background: #7b61ff;
      color: #ffffff;
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      margin: 20px 0;
    }
    .footer { font-size: 12px; color: #777; text-align: center; padding: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Murder Party Générative</h1>
    </div>
    <div class="content">
      <p>Bonjour,</p>
      <p>Merci de créer un compte sur <strong>Murder Party Générative</strong>.</p>
      <p>Pour activer votre compte, cliquez sur le bouton ci-dessous :</p>
      <p style="text-align:center;">
        <a href="{$link}" class="button">Valider mon email</a>
      </p>
      <p>Ou copiez-collez ce lien dans votre navigateur :</p>
      <p><a href="{$link}">{$link}</a></p>
      <p>Si vous n'avez pas demandé cette action, ignorez cet email.</p>
      <p>Cordialement,<br>L'équipe Murder Party Générative</p>
    </div>
    <div class="footer">
      © 2025 Murder Party Générative
    </div>
  </div>
</body>
</html>
HTML;
    }
}
