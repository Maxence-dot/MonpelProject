<?php
// Script de test pour vérifier les game_types
require_once __DIR__ . '/connexionAll.php';

echo "=== Test de la table game_types ===\n\n";

try {
    // Compter les game_types
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM game_types');
    $count = $stmt->fetch()['count'];
    echo "Nombre de game_types dans la base: $count\n\n";
    
    // Lister les game_types
    $stmt = $pdo->query('SELECT id, name, description FROM game_types ORDER BY id');
    $gameTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($gameTypes)) {
        echo "❌ PROBLÈME: Aucun type de jeu trouvé!\n";
        echo "Solution: Exécutez les migrations avec 'php bin/migrate.php'\n";
    } else {
        echo "✅ Types de jeu trouvés:\n";
        foreach ($gameTypes as $gt) {
            echo "  - ID: {$gt['id']}, Nom: {$gt['name']}\n";
        }
    }
    
    echo "\n=== Test de l'API game_types ===\n\n";
    
    // Simuler l'appel API
    require_once __DIR__ . '/Database/DatabaseService.php';
    $db = new DatabaseService($pdo);
    $result = $db->getGameTypes();
    
    echo "Réponse JSON de l'API:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}
