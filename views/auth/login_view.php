<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>

<body>

    <main class="auth-wrapper">
        <div class="auth-card">
            <h1>Se connecter</h1>
            <p class="lead">Accédez à votre espace Murder Party Générative</p>

            <?php if ($error): ?>
                <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Se connecter</button>
                    <a class="small-link" href="register.php">Pas de compte ? Créez-en un</a>
                </div>
            </form>
        </div>
    </main>

</body>

</html>
