<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /MonpelProject/login');
    exit;
}

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$basePath = str_replace('/admin', '', $basePath);

// Lister les migrations disponibles
$migrationsDir = __DIR__ . '/../migrations';
$migrations = [];

if (is_dir($migrationsDir)) {
    $files = scandir($migrationsDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $migrations[] = $file;
        }
    }
}

sort($migrations);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Migrations - Admin</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
    <style>
        .admin-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .migration-list {
            list-style: none;
            padding: 0;
        }
        .migration-item {
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: #f6f7fb;
            border-radius: 4px;
            border-left: 4px solid #7b61ff;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #7b61ff;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Liste des Migrations</h1>
    </header>

    <div class="admin-container">
        <a href="<?= $basePath ?>/admin/" class="back-link">← Retour à l'admin</a>

        <h2>Migrations disponibles (<?= count($migrations) ?>)</h2>

        <?php if (empty($migrations)): ?>
            <p>Aucune migration trouvée dans le dossier migrations/</p>
        <?php else: ?>
            <ul class="migration-list">
                <?php foreach ($migrations as $migration): ?>
                    <li class="migration-item">
                        <strong><?= htmlspecialchars($migration) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <a href="<?= $basePath ?>/admin/migrate.php" class="btn-create">Exécuter les migrations</a>
        </div>
    </div>

    <footer>
        <a href="<?= $basePath ?>/contact.html">Contact</a>
    </footer>
</body>
</html>
