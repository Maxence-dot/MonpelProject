<?php
/**
 * EXEMPLE DE ROUTES À AJOUTER AU ROUTEUR
 * 
 * Ce fichier montre comment intégrer les nouvelles routes
 * dans votre fichier router.php existant.
 * 
 * NE PAS EXÉCUTER CE FICHIER DIRECTEMENT !
 * Copiez les routes dans votre router.php
 */

// ====================================
// ROUTES DASHBOARD
// ====================================

$router->addRoute('GET', '/dashboard', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/DashboardController.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $partyRepo = new PartyRepository($pdo);
    $controller = new DashboardController($partyRepo);
    $controller->index();
});

// ====================================
// ROUTES ÉTAPES DE CRÉATION
// ====================================

// Étape 2 : Ajout des joueurs
$router->addRoute('GET', '/party/step2', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->addPlayers();
});

// Étape 3 : Relations et backgrounds
$router->addRoute('GET', '/party/step3', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->manageRelations();
});

// Route optionnelle : continuer une partie depuis le dashboard
$router->addRoute('GET', '/party/continue', function() use ($pdo) {
    // Déterminer l'étape en cours et rediriger
    if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
        header('Location: /dashboard');
        exit;
    }
    
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    
    $partyRepo = new PartyRepository($pdo);
    $charRepo = new CharacterRepository($pdo);
    
    $partyId = (int)$_GET['id'];
    $userId = (int)$_SESSION['user_id'];
    
    $party = $partyRepo->findById($partyId, $userId);
    if (!$party) {
        header('Location: /dashboard');
        exit;
    }
    
    // Déterminer l'étape
    $characterCount = $charRepo->countByPartyId($partyId);
    
    if ($characterCount === 0) {
        // Aucun personnage → Étape 2
        header("Location: /party/step2?party_id=$partyId");
    } else {
        // Des personnages existent → Étape 3
        header("Location: /party/step3?party_id=$partyId");
    }
    exit;
});

// ====================================
// API - GESTION DES PERSONNAGES
// ====================================

// Sauvegarde des personnages (Étape 2)
$router->addRoute('POST', '/api/characters/save', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->savePlayers();
});

// Ajouter un personnage
$router->addRoute('POST', '/api/characters/add', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->addCharacter();
});

// Mettre à jour un personnage
$router->addRoute('POST', '/api/characters/update', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->updateCharacter();
});

// Supprimer un personnage
$router->addRoute('POST', '/api/characters/delete', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->deleteCharacter();
});

// ====================================
// API - GESTION DES RELATIONS
// ====================================

// Créer une relation
$router->addRoute('POST', '/api/relations/create', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/RelationController.php';
    require_once __DIR__ . '/Repositories/RelationRepository.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $relationRepo = new RelationRepository($pdo);
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new RelationController($relationRepo, $charRepo, $partyRepo);
    $controller->create();
});

// Récupérer les relations d'un personnage
$router->addRoute('GET', '/api/relations/get', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/RelationController.php';
    require_once __DIR__ . '/Repositories/RelationRepository.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $relationRepo = new RelationRepository($pdo);
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new RelationController($relationRepo, $charRepo, $partyRepo);
    $controller->getByCharacter();
});

// Supprimer une relation
$router->addRoute('POST', '/api/relations/delete', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/RelationController.php';
    require_once __DIR__ . '/Repositories/RelationRepository.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $relationRepo = new RelationRepository($pdo);
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new RelationController($relationRepo, $charRepo, $partyRepo);
    $controller->delete();
});

// ====================================
// API - FINALISATION
// ====================================

// Finaliser une partie
$router->addRoute('POST', '/api/party/finish', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/PartyController.php';
    require_once __DIR__ . '/Services/ScenarioService.php';
    
    $service = new ScenarioService($pdo);
    $controller = new PartyController($service);
    $controller->finish();
});

// ====================================
// FIN DES ROUTES
// ====================================
