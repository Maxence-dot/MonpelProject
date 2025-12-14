<?php
require_once __DIR__ . '/../../connexionAll.php';
session_start();
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Vérifiez votre email</title>
    <link rel="stylesheet" href="<?= $basePath ?>/style.css">
</head>

<body>
    <main class="auth-wrapper">
        <div class="auth-card">
            <h1>Vérifiez votre email</h1>
            <p>Un email de validation a été envoyé à l'adresse fournie. Cliquez sur le lien dans l'email pour activer votre compte.</p>
            <p>Si vous ne recevez rien, vérifiez votre dossier spam ou essayez de vous inscrire à nouveau.</p>
            <a class="btn" href="<?= $basePath ?>/login.php">Retour à la connexion</a>
        </div>
    </main>
</body>

</html>