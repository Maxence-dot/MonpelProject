<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
</head>

<body>
    <main class="auth-wrapper">
        <div class="auth-card">
            <h1>Créer un compte</h1>
            <p class="lead">Inscrivez-vous pour gérer vos parties</p>

            <?php if (!empty($errors)): ?>
                <div class="msg msg-error">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="firstname">Prénom</label>
                    <input id="firstname" type="text" name="firstname" value="<?= htmlspecialchars($old['firstname'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input id="lastname" type="text" name="lastname" value="<?= htmlspecialchars($old['lastname'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmez le mot de passe</label>
                    <input id="password_confirm" type="password" name="password_confirm" required>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">S'inscrire</button>
                    <a class="small-link" href="login.php">Déjà inscrit ? Se connecter</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>
