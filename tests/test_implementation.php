<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'Implémentation - MonpelProject</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 0.5rem;
        }
        .test-section {
            background: white;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .test-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .test-item.success {
            border-left-color: #4CAF50;
            background: #f1f8f4;
        }
        .test-item.error {
            border-left-color: #f44336;
            background: #fef1f1;
        }
        .test-item.warning {
            border-left-color: #ff9800;
            background: #fff8f1;
        }
        .icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        .details {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .summary h2 {
            margin: 0 0 0.5rem 0;
        }
        .stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }
        .stat {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        code {
            background: #f5f5f5;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <h1>🧪 Test d'Implémentation - MonpelProject</h1>

    <?php
    require_once __DIR__ . '/connexionAll.php';

    $results = [
        'success' => 0,
        'error' => 0,
        'warning' => 0
    ];

    function testResult($title, $passed, $details = '', $isWarning = false) {
        global $results;
        
        if ($passed) {
            $results['success']++;
            $class = 'success';
            $icon = '✅';
        } else {
            if ($isWarning) {
                $results['warning']++;
                $class = 'warning';
                $icon = '⚠️';
            } else {
                $results['error']++;
                $class = 'error';
                $icon = '❌';
            }
        }
        
        echo "<div class='test-item $class'>";
        echo "<span class='icon'>$icon</span>";
        echo "<div>";
        echo "<strong>$title</strong>";
        if ($details) {
            echo "<div class='details'>$details</div>";
        }
        echo "</div>";
        echo "</div>";
    }
    ?>

    <div class="test-section">
        <h2>🗄️ Base de Données</h2>
        
        <?php
        // Test 1: Connexion DB
        try {
            $pdo->query("SELECT 1");
            testResult("Connexion à la base de données", true, "PDO connecté avec succès");
        } catch (Exception $e) {
            testResult("Connexion à la base de données", false, "Erreur: " . $e->getMessage());
        }

        // Test 2: Table murder_parties avec user_id
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM murder_parties LIKE 'user_id'");
            $hasUserIdColumn = $stmt->rowCount() > 0;
            testResult(
                "Colonne user_id dans murder_parties",
                $hasUserIdColumn,
                $hasUserIdColumn ? "Migration 20251216 appliquée" : "Exécutez: php bin/migrate.php"
            );
        } catch (Exception $e) {
            testResult("Colonne user_id dans murder_parties", false, "Erreur: " . $e->getMessage());
        }

        // Test 3: Table characters
        try {
            $pdo->query("SELECT 1 FROM characters LIMIT 1");
            testResult("Table characters", true, "Table créée avec succès");
        } catch (Exception $e) {
            testResult("Table characters", false, "Migration 20251217 non appliquée. Exécutez: php bin/migrate.php");
        }

        // Test 4: Table relations
        try {
            $pdo->query("SELECT 1 FROM relations LIMIT 1");
            testResult("Table relations", true, "Table créée avec succès");
        } catch (Exception $e) {
            testResult("Table relations", false, "Migration 20251217 non appliquée");
        }

        // Test 5: Foreign keys
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as fk_count 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND TABLE_NAME IN ('murder_parties', 'characters', 'relations')
            ");
            $fkCount = $stmt->fetch()['fk_count'];
            testResult(
                "Foreign Keys configurées",
                $fkCount >= 4,
                "Trouvé $fkCount foreign keys (minimum requis: 4)"
            );
        } catch (Exception $e) {
            testResult("Foreign Keys", false, "Impossible de vérifier", true);
        }
        ?>
    </div>

    <div class="test-section">
        <h2>📁 Fichiers et Structure</h2>
        
        <?php
        $requiredFiles = [
            'Controllers/DashboardController.php' => 'Contrôleur Dashboard',
            'Controllers/CharacterController.php' => 'Contrôleur Characters',
            'Controllers/RelationController.php' => 'Contrôleur Relations',
            'Repositories/CharacterRepository.php' => 'Repository Characters',
            'Repositories/RelationRepository.php' => 'Repository Relations',
            'Models/Character.php' => 'Modèle Character',
            'Models/Relation.php' => 'Modèle Relation',
            'views/dashboard.php' => 'Vue Dashboard',
            'views/party/step2_add_players.php' => 'Vue Étape 2',
            'views/party/step3_relations.php' => 'Vue Étape 3',
            'assets/js/step3_relations.js' => 'JavaScript Étape 3',
            'migrations/20251216_add_user_id_to_murder_parties.php' => 'Migration user_id',
            'migrations/20251217_create_characters_and_relations.php' => 'Migration tables'
        ];

        foreach ($requiredFiles as $file => $description) {
            $exists = file_exists(__DIR__ . '/' . $file);
            testResult(
                $description,
                $exists,
                $exists ? "Fichier: <code>$file</code>" : "Fichier manquant: <code>$file</code>"
            );
        }
        ?>
    </div>

    <div class="test-section">
        <h2>🔧 Configuration PHP</h2>
        
        <?php
        // Test Sessions
        $sessionActive = session_status() === PHP_SESSION_ACTIVE || @session_start();
        testResult(
            "Sessions PHP activées",
            $sessionActive,
            $sessionActive ? "session_status() = " . session_status() : "Sessions non disponibles"
        );

        // Test PDO
        $pdoAvailable = class_exists('PDO');
        testResult(
            "Extension PDO",
            $pdoAvailable,
            $pdoAvailable ? "PDO disponible" : "PDO non installé"
        );

        // Test JSON
        $jsonAvailable = function_exists('json_encode');
        testResult(
            "Extension JSON",
            $jsonAvailable,
            $jsonAvailable ? "JSON disponible" : "JSON non disponible"
        );

        // Version PHP
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare(PHP_VERSION, '7.4.0', '>=');
        testResult(
            "Version PHP",
            $phpOk,
            "Version actuelle: PHP $phpVersion (minimum: 7.4)"
        );
        ?>
    </div>

    <div class="test-section">
        <h2>📊 Statistiques de la Base</h2>
        
        <?php
        try {
            // Nombre de parties
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM murder_parties");
            $partyCount = $stmt->fetch()['count'];
            testResult(
                "Parties créées",
                true,
                "$partyCount partie(s) dans la base",
                true
            );

            // Nombre de personnages
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM characters");
            $charCount = $stmt->fetch()['count'];
            testResult(
                "Personnages créés",
                true,
                "$charCount personnage(s) dans la base",
                true
            );

            // Nombre de relations
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM relations");
            $relCount = $stmt->fetch()['count'];
            testResult(
                "Relations créées",
                true,
                "$relCount relation(s) dans la base",
                true
            );

            // Parties avec user_id
            if ($hasUserIdColumn ?? false) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM murder_parties WHERE user_id > 0");
                $partiesWithUser = $stmt->fetch()['count'];
                testResult(
                    "Parties avec user_id",
                    $partiesWithUser > 0 || $partyCount === 0,
                    "$partiesWithUser/$partyCount partie(s) ont un user_id",
                    true
                );
            }
        } catch (Exception $e) {
            testResult("Statistiques", false, "Erreur: " . $e->getMessage(), true);
        }
        ?>
    </div>

    <div class="test-section">
        <h2>📚 Documentation</h2>
        
        <?php
        $docFiles = [
            'INDEX.md' => 'Index de la documentation',
            'SUMMARY.md' => 'Résumé visuel',
            'FEATURES_README.md' => 'Documentation principale',
            'IMPLEMENTATION_GUIDE.md' => 'Guide d\'implémentation',
            'TECHNICAL_SPECS.md' => 'Spécifications techniques',
            'MIGRATION_CHECKLIST.md' => 'Checklist de migration',
            'SQL_REFERENCE.sql' => 'Référence SQL',
            'ROUTER_EXAMPLE.php' => 'Exemple de routes'
        ];

        foreach ($docFiles as $file => $description) {
            $exists = file_exists(__DIR__ . '/' . $file);
            testResult(
                $description,
                $exists,
                $exists ? "<code>$file</code>" : "Manquant: <code>$file</code>",
                !$exists
            );
        }
        ?>
    </div>

    <div class="summary">
        <h2>📊 Résumé des Tests</h2>
        <div class="stats">
            <div>
                <div class="stat"><?= $results['success'] ?></div>
                <div class="stat-label">✅ Réussis</div>
            </div>
            <div>
                <div class="stat"><?= $results['error'] ?></div>
                <div class="stat-label">❌ Échecs</div>
            </div>
            <div>
                <div class="stat"><?= $results['warning'] ?></div>
                <div class="stat-label">⚠️ Avertissements</div>
            </div>
            <div>
                <div class="stat"><?= $results['success'] + $results['error'] + $results['warning'] ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
        
        <?php
        $total = $results['success'] + $results['error'] + $results['warning'];
        $percentage = $total > 0 ? round(($results['success'] / $total) * 100) : 0;
        ?>
        
        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.3);">
            <?php if ($results['error'] === 0 && $results['warning'] <= 3): ?>
                <strong>🎉 Excellent !</strong> Votre implémentation est prête à <?= $percentage ?>%.
            <?php elseif ($results['error'] === 0): ?>
                <strong>✅ Bon travail !</strong> Quelques avertissements mineurs mais tout fonctionne.
            <?php elseif ($results['error'] <= 3): ?>
                <strong>⚠️ Presque prêt !</strong> Corrigez les <?= $results['error'] ?> erreur(s) pour finaliser.
            <?php else: ?>
                <strong>❌ Action requise !</strong> <?= $results['error'] ?> erreur(s) à corriger.
                <br>Consultez <a href="IMPLEMENTATION_GUIDE.md" style="color: white;">IMPLEMENTATION_GUIDE.md</a>
            <?php endif; ?>
        </div>
    </div>

    <div style="text-align: center; margin-top: 2rem; color: #999;">
        <p>📚 Pour plus d'informations, consultez <a href="INDEX.md">INDEX.md</a></p>
        <p><small>Test généré le <?= date('Y-m-d H:i:s') ?></small></p>
    </div>
</body>
</html>
