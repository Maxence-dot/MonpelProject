<?php

/*************************************************
 *  CONFIGURATION ENVIRONNEMENT
 *************************************************/

// Load configuration (may come from environment or config.local.php outside the repo)
$cfg = require __DIR__ . '/config.php';

// Detect local env to enable error display (keeps previous behavior)
$isLocalhost = in_array($_SERVER['SERVER_NAME'] ?? '', [
    'localhost',
    '127.0.0.1'
]);
if ($isLocalhost) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$dbHost = $cfg['DB_HOST'];
$dbName = $cfg['DB_NAME'];
$dbUser = $cfg['DB_USER'];
$dbPass = $cfg['DB_PASS'];

/*************************************************
 *  CONNEXION PDO
 *************************************************/

try {
    $pdo = new PDO(
        $cfg['DB_DSN'],
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    if ($isLocalhost) {
        die('<pre>Erreur BDD : ' . $e->getMessage() . '</pre>');
    }
    die('Erreur de connexion à la base de données');
}
