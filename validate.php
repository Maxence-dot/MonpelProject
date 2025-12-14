<?php
require_once __DIR__ . '/connexionAll.php';
session_start();

$token = $_GET['token'] ?? null;
if (!$token) {
    echo "Token manquant";
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email_token = ? LIMIT 1');
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user) {
    echo "Token invalide ou expiré.";
    exit;
}

$update = $pdo->prepare('UPDATE users SET email_valid = 1, email_token = NULL, token_created_at = NULL WHERE id = ?');
$update->execute([$user['id']]);

// Redirect to login with message
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $basePath . '/login.php?verified=1');
exit;
