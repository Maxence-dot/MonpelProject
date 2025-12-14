<?php
// Controller moved to views/auth for organization.
require_once __DIR__ . '/../../connexionAll.php';
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

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            header('Location: /MonpelProject/index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}

include __DIR__ . '/login_view.php';
