<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /MonpelProject/login');
    exit;
}

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$basePath = str_replace('/admin', '', $basePath);

// Récupérer les statistiques de la base
$stats = [];

try {
    // Nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $stmt->fetch()['count'];

    // Nombre de parties
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM murder_parties");
    $stats['parties'] = $stmt->fetch()['count'];

    // Nombre de parties par statut
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM murder_parties GROUP BY status");
    $stats['parties_by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Nombre de personnages
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM characters");
    $stats['characters'] = $stmt->fetch()['count'];

    // Nombre de relations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM relations");
    $stats['relations'] = $stmt->fetch()['count'];

    // Nombre de types de jeu
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM game_types");
    $stats['game_types'] = $stmt->fetch()['count'];

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de la Base de Données - Admin</title>
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-card {
            background: #f6f7fb;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border-top: 4px solid #7b61ff;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #7b61ff;
        }
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
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
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>État de la Base de Données</h1>
    </header>

    <div class="admin-container">
        <a href="<?= $basePath ?>/admin/" class="back-link">← Retour à l'admin</a>

        <h2>Statistiques</h2>

        <?php if (isset($error)): ?>
            <div class="error">
                <strong>Erreur:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['users'] ?></div>
                    <div class="stat-label">Utilisateurs</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $stats['parties'] ?></div>
                    <div class="stat-label">Parties</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $stats['characters'] ?></div>
                    <div class="stat-label">Personnages</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $stats['relations'] ?></div>
                    <div class="stat-label">Relations</div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"><?= $stats['game_types'] ?></div>
                    <div class="stat-label">Types de Jeu</div>
                </div>
            </div>

            <?php if (!empty($stats['parties_by_status'])): ?>
                <h3 style="margin-top: 2rem;">Parties par statut</h3>
                <div class="stats-grid">
                    <?php foreach ($stats['parties_by_status'] as $status => $count): ?>
                        <div class="stat-card">
                            <div class="stat-number"><?= $count ?></div>
                            <div class="stat-label"><?= htmlspecialchars(ucfirst($status)) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        <a href="<?= $basePath ?>/contact.html">Contact</a>
    </footer>
</body>
</html>
