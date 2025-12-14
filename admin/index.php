<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: /MonpelProject/login');
    exit;
}

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$basePath = str_replace('/admin', '', $basePath);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Murder Party Maker</title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .admin-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .admin-section h2 {
            margin-top: 0;
            color: #7b61ff;
            border-bottom: 2px solid #7b61ff;
            padding-bottom: 0.5rem;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .admin-card {
            background: #f6f7fb;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #7b61ff;
            transition: all 0.3s ease;
        }
        .admin-card:hover {
            background: #eef0f8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(123, 97, 255, 0.2);
        }
        .admin-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1rem;
            color: #333;
        }
        .admin-card p {
            margin: 0 0 1rem 0;
            font-size: 0.9rem;
            color: #666;
        }
        .admin-link {
            display: inline-block;
            background: #7b61ff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .admin-link:hover {
            background: #684fe0;
        }
        .admin-link.danger {
            background: #f44336;
        }
        .admin-link.danger:hover {
            background: #d32f2f;
        }
        .admin-link.success {
            background: #4CAF50;
        }
        .admin-link.success:hover {
            background: #45a049;
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
        <h1>Administration</h1>
        <a href="<?= $basePath ?>/" class="btn-create">← Retour au Dashboard</a>
    </header>

    <div class="admin-container">
        <a href="<?= $basePath ?>/" class="back-link">← Retour au dashboard</a>

        <!-- Section Migrations -->
        <div class="admin-section">
            <h2>🗄️ Migrations de la base de données</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Exécuter les migrations</h3>
                    <p>Lance toutes les migrations en attente pour mettre à jour la base de données.</p>
                    <a href="<?= $basePath ?>/admin/migrate.php" class="admin-link success">Lancer les migrations</a>
                </div>
                <div class="admin-card">
                    <h3>Migrations disponibles</h3>
                    <p>Voir la liste de toutes les migrations disponibles.</p>
                    <a href="<?= $basePath ?>/admin/list_migrations.php" class="admin-link">Voir les migrations</a>
                </div>
            </div>
        </div>

        <!-- Section Tests & Debug -->
        <div class="admin-section">
            <h2>🧪 Tests et Diagnostics</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>Test Types de Jeu</h3>
                    <p>Vérifie que les types de jeu sont correctement configurés dans la base.</p>
                    <a href="<?= $basePath ?>/tests/test_game_types.php" class="admin-link">Exécuter le test</a>
                </div>
                <div class="admin-card">
                    <h3>Test d'Implémentation</h3>
                    <p>Vérifie que tous les fichiers nécessaires sont présents et bien configurés.</p>
                    <a href="<?= $basePath ?>/tests/test_implementation.php" class="admin-link">Exécuter le test</a>
                </div>
                <div class="admin-card">
                    <h3>Debug Dashboard</h3>
                    <p>Affiche les informations de debug du dashboard et de la session utilisateur.</p>
                    <a href="<?= $basePath ?>/tests/debug_dashboard.php" class="admin-link">Voir le debug</a>
                </div>
                <div class="admin-card">
                    <h3>Validation</h3>
                    <p>Script de validation générique pour tester les fonctionnalités.</p>
                    <a href="<?= $basePath ?>/tests/validate.php" class="admin-link">Valider</a>
                </div>
            </div>
        </div>

        <!-- Section Documentation -->
        <div class="admin-section">
            <h2>📚 Documentation</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>README</h3>
                    <p>Documentation principale du projet.</p>
                    <a href="<?= $basePath ?>/docs/README.md" class="admin-link" target="_blank">Ouvrir</a>
                </div>
                <div class="admin-card">
                    <h3>Guide d'implémentation</h3>
                    <p>Guide détaillé pour implémenter les fonctionnalités.</p>
                    <a href="<?= $basePath ?>/docs/IMPLEMENTATION_GUIDE.md" class="admin-link" target="_blank">Ouvrir</a>
                </div>
                <div class="admin-card">
                    <h3>Spécifications Techniques</h3>
                    <p>Architecture et spécifications du projet.</p>
                    <a href="<?= $basePath ?>/docs/TECHNICAL_SPECS.md" class="admin-link" target="_blank">Ouvrir</a>
                </div>
                <div class="admin-card">
                    <h3>Référence SQL</h3>
                    <p>Schéma de base de données et requêtes de référence.</p>
                    <a href="<?= $basePath ?>/docs/SQL_REFERENCE.sql" class="admin-link" target="_blank">Ouvrir</a>
                </div>
            </div>
        </div>

        <!-- Section Base de données -->
        <div class="admin-section">
            <h2>💾 Base de données</h2>
            <div class="admin-grid">
                <div class="admin-card">
                    <h3>État de la base</h3>
                    <p>Voir les statistiques et l'état actuel de la base de données.</p>
                    <a href="<?= $basePath ?>/admin/db_status.php" class="admin-link">Voir l'état</a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <a href="<?= $basePath ?>/contact.html">Contact</a>
    </footer>
</body>
</html>
