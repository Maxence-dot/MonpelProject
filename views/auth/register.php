<?php
// Controller moved to views/auth for organization.
require_once __DIR__ . '/../../connexionAll.php';
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
        $stmt = $pdo->prepare("SELECT id FROM session WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Un compte avec cet email existe déjà';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');
            $insert = $pdo->prepare("INSERT INTO session (email, password, firstname, lastname, created_at) VALUES (?,?,?,?,?)");
            try {
                $insert->execute([$email, $hash, $firstname, $lastname, $now]);
                header('Location: /MonpelProject/login.php?registered=1');
                exit;
            } catch (Exception $e) {
                $errors[] = 'Erreur enregistrement: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/register_view.php';
