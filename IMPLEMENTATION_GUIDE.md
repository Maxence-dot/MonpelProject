# Guide d'Implémentation - Murder Party Maker (MonpelProject)

## 📋 Vue d'Ensemble

Ce guide explique comment intégrer les nouvelles fonctionnalités de gestion des utilisateurs, du dashboard et du flux de création de parties (Étapes 2 et 3).

## 🔐 1. Migrations de Base de Données

### Exécuter les migrations

Les migrations suivantes doivent être exécutées dans l'ordre :

```bash
php bin/migrate.php
```

Ou manuellement :

1. **Migration 20251216** : Ajoute `user_id` à la table `murder_parties`
2. **Migration 20251217** : Crée les tables `characters` et `relations`

### Schéma SQL (Résumé)

```sql
-- Ajout de user_id à murder_parties
ALTER TABLE murder_parties 
ADD COLUMN user_id INT UNSIGNED NOT NULL,
ADD INDEX idx_user_id (user_id),
ADD CONSTRAINT fk_murder_parties_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Table characters
CREATE TABLE characters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  party_id INT UNSIGNED NOT NULL,
  firstname VARCHAR(255) NOT NULL,
  background TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_party_id (party_id),
  CONSTRAINT fk_characters_party 
    FOREIGN KEY (party_id) REFERENCES murder_parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table relations
CREATE TABLE relations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  character_id INT UNSIGNED NOT NULL,
  target_character_id INT UNSIGNED NOT NULL,
  relation_type VARCHAR(100) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_character_id (character_id),
  INDEX idx_target_character_id (target_character_id),
  CONSTRAINT fk_relations_character 
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
  CONSTRAINT fk_relations_target 
    FOREIGN KEY (target_character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 🛣️ 2. Routage (à ajouter dans router.php)

Ajoutez les routes suivantes à votre fichier `router.php` :

```php
// Dashboard
$router->addRoute('GET', '/dashboard', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/DashboardController.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $partyRepo = new PartyRepository($pdo);
    $controller = new DashboardController($partyRepo);
    $controller->index();
});

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

// API : Sauvegarde des personnages
$router->addRoute('POST', '/api/characters/save', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->savePlayers();
});

// API : Ajouter un personnage
$router->addRoute('POST', '/api/characters/add', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->addCharacter();
});

// API : Mettre à jour un personnage
$router->addRoute('POST', '/api/characters/update', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->updateCharacter();
});

// API : Supprimer un personnage
$router->addRoute('POST', '/api/characters/delete', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/CharacterController.php';
    require_once __DIR__ . '/Repositories/CharacterRepository.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $charRepo = new CharacterRepository($pdo);
    $partyRepo = new PartyRepository($pdo);
    $controller = new CharacterController($charRepo, $partyRepo);
    $controller->deleteCharacter();
});

// API : Créer une relation
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

// API : Récupérer les relations d'un personnage
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

// API : Supprimer une relation
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

// API : Finaliser une partie
$router->addRoute('POST', '/api/party/finish', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/PartyController.php';
    require_once __DIR__ . '/Services/ScenarioService.php';
    
    $service = new ScenarioService($pdo);
    $controller = new PartyController($service);
    $controller->finish();
});
```

## 🔒 3. Sécurité et Isolation des Données

### Principe de Base

Toutes les requêtes SQL incluent maintenant une clause `WHERE user_id = :current_user_id` pour garantir que :
- Un utilisateur ne peut voir que ses propres parties
- Un utilisateur ne peut modifier/supprimer que ses propres données

### Vérification de Session

Tous les contrôleurs vérifient la présence de `$_SESSION['user_id']` :

```php
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}
```

### Méthodes Sécurisées du PartyRepository

```php
// AVANT
$party = $partyRepository->findById($partyId);

// APRÈS (sécurisé)
$party = $partyRepository->findById($partyId, $userId);
```

## 📂 4. Structure des Fichiers Créés

```
MonpelProject/
├── migrations/
│   ├── 20251216_add_user_id_to_murder_parties.php  ✅ Nouveau
│   └── 20251217_create_characters_and_relations.php ✅ Nouveau
├── Models/
│   ├── Character.php                                ✅ Nouveau
│   └── Relation.php                                 ✅ Nouveau
├── Repositories/
│   ├── PartyRepository.php                          ✏️ Modifié (sécurisé)
│   ├── CharacterRepository.php                      ✅ Nouveau
│   └── RelationRepository.php                       ✅ Nouveau
├── Controllers/
│   ├── PartyController.php                          ✏️ Modifié (sécurisé)
│   ├── DashboardController.php                      ✅ Nouveau
│   ├── CharacterController.php                      ✅ Nouveau
│   └── RelationController.php                       ✅ Nouveau
├── Services/
│   └── ScenarioService.php                          ✏️ Modifié (user_id)
├── views/
│   ├── dashboard.php                                ✅ Nouveau
│   └── party/
│       ├── step2_add_players.php                    ✅ Nouveau
│       └── step3_relations.php                      ✅ Nouveau
└── assets/
    ├── css/
    │   └── modal.css                                ✏️ Modifié
    └── js/
        ├── modal.js                                 ✏️ Modifié
        └── step3_relations.js                       ✅ Nouveau
```

## 🎯 5. Flux de Création Complet

### Étape 1 : Initialisation (Existante)
- Route : `/party/create` (POST)
- Crée une `murder_party` avec statut `draft`
- **IMPORTANT** : Maintenant nécessite `user_id`

### Étape 2 : Ajout des Joueurs
- Route : `/party/step2?party_id=X` (GET)
- Vue : `views/party/step2_add_players.php`
- API : `/api/characters/save` (POST)
- Crée les personnages dans la table `characters`

### Étape 3 : Relations et Backgrounds
- Route : `/party/step3?party_id=X` (GET)
- Vue : `views/party/step3_relations.php`
- Interface CRUD complète :
  - Édition des backgrounds
  - Ajout/suppression de personnages
  - Création de relations via modale

### Finalisation
- API : `/api/party/finish` (POST)
- Change le statut de `draft` à `completed`
- Redirige vers le dashboard

## 🖥️ 6. Dashboard

Le tableau de bord affiche :
- Toutes les parties en statut `draft` de l'utilisateur connecté
- Le nombre de personnages par partie (via un `COUNT`)
- Clic sur une partie → redirection vers la dernière étape

## 🧪 7. Tests de Sécurité

Pour vérifier l'isolation des données :

1. Créer deux utilisateurs différents
2. Créer une partie avec l'utilisateur 1
3. Se connecter avec l'utilisateur 2
4. Tenter d'accéder à `/party/step2?party_id=X` (ID de la partie de l'utilisateur 1)
5. **Résultat attendu** : Erreur 404 "Partie non trouvée"

## 📝 8. Notes Importantes

### Session Utilisateur
Assurez-vous que votre système d'authentification définit bien `$_SESSION['user_id']` lors de la connexion.

### Redirection Automatique
Après l'Étape 1, redirigez automatiquement vers l'Étape 2 :

```php
// Dans PartyController::create()
echo json_encode(['success' => true, 'id' => $id, 'redirect' => "/party/step2?party_id=$id"]);
```

### Validation Côté Client
Le JavaScript dans `step3_relations.js` inclut des validations :
- Impossible de créer une relation d'un personnage vers lui-même
- Validation des champs requis

## 🚀 9. Mise en Production

Avant de déployer :

1. ✅ Exécuter les migrations sur la base de production
2. ✅ Vérifier que toutes les routes sont configurées
3. ✅ Tester le flux complet de création
4. ✅ Vérifier l'isolation des données entre utilisateurs
5. ✅ S'assurer que les sessions fonctionnent correctement

## 🐛 10. Débogage Courant

### Erreur "user_id not found"
➡️ Vérifier que `$_SESSION['user_id']` est défini

### Erreur "Partie non trouvée"
➡️ Vérifier que la partie appartient bien à l'utilisateur connecté

### Relations ne s'affichent pas
➡️ Ouvrir la console développeur et vérifier les appels API

### Modale ne s'ouvre pas
➡️ Vérifier que `modal.js` et `step3_relations.js` sont bien chargés

---

## ✅ Checklist d'Implémentation

- [ ] Migrations exécutées
- [ ] Routes ajoutées au routeur
- [ ] Session utilisateur fonctionnelle
- [ ] Tests de sécurité réalisés
- [ ] Flux complet testé
- [ ] UI responsive vérifiée
- [ ] Documentation mise à jour

---

**Développé pour MonpelProject - Murder Party Maker**
