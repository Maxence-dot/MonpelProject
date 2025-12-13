<?php
require_once 'connexionAll.php';
session_start();

$errors = [];
$old = ['email' => '', 'firstname' => '', 'lastname' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password_confirm'] ?? '';
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');

    $old = ['email' => $email, 'firstname' => $firstname, 'lastname' => $lastname];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    if ($password !== $password2) {
        $errors[] = 'Les mots de passe ne correspondent pas';
    }

    if (empty($errors)) {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id FROM session WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Un compte avec cet email existe déjà';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Use PHP timestamp to be compatible with MySQL and SQLite
            $now = date('Y-m-d H:i:s');
            $insert = $pdo->prepare("INSERT INTO session (email, password, firstname, lastname, created_at) VALUES (?,?,?,?,?)");
            try {
                $insert->execute([$email, $hash, $firstname, $lastname, $now]);
                header('Location: login.php?registered=1');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Erreur enregistrement: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
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
                    <input id="firstname" type="text" name="firstname" value="<?= htmlspecialchars($old['firstname']) ?>">
                </div>
                <div class="form-group">
                    <label for="lastname">Nom</label>
                    <input id="lastname" type="text" name="lastname" value="<?= htmlspecialchars($old['lastname']) ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required value="<?= htmlspecialchars($old['email']) ?>">
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