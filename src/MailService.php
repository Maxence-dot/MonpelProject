<?php

/**
 * MailService - Envoi d'emails simplement via mail()
 */
class MailService
{
    public function __construct()
    {
        // no-op
    }

    /**
     * Envoie l'email de validation
     */
    public function sendValidationEmail(string $to, string $token): bool
    {
        $appUrl = getenv('APP_URL') ?: 'http://localhost';
        $link = rtrim($appUrl, '/') . '/validate.php?token=' . urlencode($token);

        $subject = 'Validez votre adresse email - Murder Party Générative';
        $htmlBody = $this->getValidationEmailHtml($link);

        $fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@localhost';
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Murder Party Générative';

        // Headers pour HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $fromName . " <" . $fromEmail . ">" . "\r\n";

        // Envoyer
        $ok = mail($to, $subject, $htmlBody, $headers);

        if (!$ok) {
            error_log('[MAIL_ERROR] mail() failed for ' . $to);
        } else {
            error_log('[MAIL_SUCCESS] Email sent to ' . $to);
        }

        return (bool)$ok;
    }

    private function getValidationEmailHtml(string $link): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: #7b61ff; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
    .content { background: #f9f9f9; padding: 20px; border: 1px solid #eee; border-radius: 0 0 8px 8px; }
    .button { display: inline-block; background: #7b61ff; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin: 20px 0; }
    .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Murder Party Générative</h1>
    </div>
    <div class="content">
      <p>Bonjour,</p>
      <p>Merci de créer un compte sur Murder Party Générative!</p>
      <p>Pour activer votre compte, cliquez sur le bouton ci-dessous:</p>
      <center>
        <a href="{$link}" class="button">Valider mon email</a>
      </center>
      <p>Ou copiez-collez ce lien:<br/>
      <a href="{$link}">{$link}</a></p>
      <p>Si vous n'avez pas demandé cela, ignorez ce message.</p>
      <p>Cordialement,<br/>L'équipe Murder Party Générative</p>
    </div>
    <div class="footer">
      <p>© 2025 Murder Party Générative</p>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
