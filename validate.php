<?php
require_once __DIR__ . '/connexionAll.php';

$token = $_GET['token'] ?? null;

function render_page($title, $message, $link = null, $linkText = 'Retour')
{
?>
    <!doctype html>
    <html lang="fr">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="/style.css">
    </head>

    <body>
        <main class="center">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <?php if ($link): ?>
                <p><a href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($linkText); ?></a></p>
            <?php endif; ?>
        </main>
    </body>

    </html><?php
            exit;
        }

        if (!$token) {
            render_page('Token manquant', 'Le lien de validation est incomplet. Vérifiez le courriel et réessayez.');
        }

        try {
            $stmt = $pdo->prepare('SELECT id, email_valid FROM users WHERE email_token = :token LIMIT 1');
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            render_page('Erreur', 'Une erreur interne est survenue.');
        }

        if (!$user) {
            render_page('Token invalide', 'Ce lien de validation est invalide ou expiré.');
        }

        if ($user['email_valid']) {
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            $loginUrl = $basePath . '/login.php';
            render_page('Compte déjà validé', 'Votre compte a déjà été validé. Vous pouvez vous connecter.', $loginUrl, 'Se connecter');
        }

        try {
            $stmt = $pdo->prepare('UPDATE users SET email_valid = 1, email_token = NULL, token_created_at = NULL WHERE id = :id');
            $stmt->execute(['id' => $user['id']]);
        } catch (Exception $e) {
            render_page('Erreur', 'Impossible de valider le compte pour le moment.');
        }

        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $loginUrl = $basePath . '/login.php';
        render_page('Email validé', 'Merci — votre adresse e-mail a été validée. Vous pouvez maintenant vous connecter.', $loginUrl, 'Aller à la connexion');
