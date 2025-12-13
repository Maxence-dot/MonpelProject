<?php
require_once 'connexionAll.php';
session_start();

$error = null;
$success = null;
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $old['email'] = $email;
    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM session WHERE email = ?");
        $ok = $stmt->execute([$email]);
        $user = $stmt->fetch();
        // Debugging information shown only on localhost

        if ($user && password_verify($password, $user['password'])) {
            // Connexion OK
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <main class="auth-wrapper">
        <div class="auth-card">
            <h1>Se connecter</h1>
            <p class="lead">Accédez à votre espace Murder Party Générative</p>

            <?php if ($error): ?>
                <div class="msg msg-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="msg msg-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required value="<?= htmlspecialchars($old['email']) ?>">
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