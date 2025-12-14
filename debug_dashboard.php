<?php
session_start();
require_once __DIR__ . '/connexionAll.php';
require_once __DIR__ . '/Repositories/PartyRepository.php';

echo "<h1>Debug Dashboard</h1>";

// Vérifier la session
echo "<h2>Session</h2>";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NON DÉFINI') . "<br>";

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    
    // Vérifier les parties
    echo "<h2>Parties dans la base</h2>";
    $stmt = $pdo->prepare("SELECT id, theme, synopsis, status, user_id, created_at FROM murder_parties WHERE user_id = ?");
    $stmt->execute([$userId]);
    $parties = $stmt->fetchAll();
    
    echo "Nombre de parties: " . count($parties) . "<br><br>";
    
    if (count($parties) > 0) {
        foreach ($parties as $party) {
            echo "ID: {$party['id']}, Thème: {$party['theme']}, Status: {$party['status']}<br>";
        }
    } else {
        echo "Aucune partie trouvée pour l'utilisateur $userId<br>";
        
        // Vérifier toutes les parties
        echo "<h3>Toutes les parties dans la base:</h3>";
        $stmt = $pdo->query("SELECT id, theme, status, user_id FROM murder_parties");
        $allParties = $stmt->fetchAll();
        foreach ($allParties as $p) {
            echo "ID: {$p['id']}, Thème: {$p['theme']}, Status: {$p['status']}, User: {$p['user_id']}<br>";
        }
    }
    
    // Test avec findDraftsWithCharacterCount
    echo "<h2>Test findDraftsWithCharacterCount</h2>";
    $repo = new PartyRepository($pdo);
    $drafts = $repo->findDraftsWithCharacterCount($userId);
    echo "Nombre de drafts: " . count($drafts) . "<br>";
    
    if (count($drafts) > 0) {
        foreach ($drafts as $draft) {
            echo "ID: {$draft['id']}, Thème: {$draft['theme']}, Personnages: {$draft['character_count']}<br>";
        }
    }
}
