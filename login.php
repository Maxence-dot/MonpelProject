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
<?php include __DIR__ . '/views/auth/login_view.php'; ?>

