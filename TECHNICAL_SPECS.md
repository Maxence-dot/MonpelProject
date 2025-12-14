# Spécifications Techniques - Murder Party Maker

## 📋 Résumé de l'Implémentation

Cette implémentation ajoute trois fonctionnalités majeures à MonpelProject :

1. **Sécurité et Isolation des Données** : Chaque utilisateur ne voit que ses propres parties
2. **Dashboard** : Vue d'ensemble des parties en cours de création
3. **Flux de Création Complet** : Étapes 2 et 3 avec gestion des personnages et relations

---

## 🗄️ 1. Modifications de la Base de Données

### Tables Modifiées

#### `murder_parties`
- **Ajout** : Colonne `user_id` (Foreign Key vers `users`)
- **Index** : `idx_user_id`
- **Contrainte** : Suppression en cascade si l'utilisateur est supprimé

### Tables Créées

#### `characters`
```
- id (PK)
- party_id (FK → murder_parties.id)
- firstname (VARCHAR 255)
- background (TEXT)
- created_at (DATETIME)
- updated_at (DATETIME)
```

#### `relations`
```
- id (PK)
- character_id (FK → characters.id)
- target_character_id (FK → characters.id)
- relation_type (VARCHAR 100)
- description (TEXT)
- created_at (DATETIME)
```

---

## 🏗️ 2. Architecture Backend

### Nouveaux Repositories

| Repository | Responsabilité |
|-----------|----------------|
| `CharacterRepository` | CRUD des personnages |
| `RelationRepository` | CRUD des relations |

### Repositories Modifiés

| Repository | Changements |
|-----------|-------------|
| `PartyRepository` | Toutes les méthodes incluent maintenant `user_id` |

### Nouveaux Contrôleurs

| Contrôleur | Routes Gérées |
|-----------|---------------|
| `DashboardController` | `/dashboard` |
| `CharacterController` | `/party/step2`, `/party/step3`, API characters |
| `RelationController` | API relations |

### Contrôleurs Modifiés

| Contrôleur | Changements |
|-----------|-------------|
| `PartyController` | Ajout de `user_id`, méthode `finish()` |
| `ScenarioService` | Paramètre `user_id` dans `createInitialParty()` |

---

## 🎨 3. Frontend et Vues

### Nouvelles Vues

| Vue | Description |
|-----|-------------|
| `views/dashboard.php` | Liste des parties en cours avec nombre de joueurs |
| `views/party/step2_add_players.php` | Formulaire dynamique d'ajout de joueurs |
| `views/party/step3_relations.php` | Interface CRUD complète pour personnages et relations |

### Composants JavaScript

| Fichier | Fonctionnalités |
|---------|----------------|
| `assets/js/step3_relations.js` | - Gestion des cartes personnages<br>- Édition inline des backgrounds<br>- Modale de création de relations<br>- Suppression AJAX |
| `assets/js/modal.js` | Gestion générique des modales (mise à jour) |

### Styles CSS

| Fichier | Contenu |
|---------|---------|
| `assets/css/modal.css` | Styles pour les modales de relations (mise à jour) |

---

## 🔐 4. Sécurité Implémentée

### Principe de Sécurisation

Toutes les opérations suivent ce pattern :

```php
// 1. Vérifier la session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

// 2. Récupérer l'ID utilisateur
$userId = (int)$_SESSION['user_id'];

// 3. Vérifier l'appartenance
$party = $partyRepo->findById($partyId, $userId);
if (!$party) {
    http_response_code(404);
    exit;
}
```

### Points de Contrôle

| Contrôle | Niveau |
|----------|--------|
| Session utilisateur | ✅ Tous les contrôleurs |
| Appartenance de la partie | ✅ Toutes les requêtes |
| Validation des IDs | ✅ Casting en `int` |
| Échappement XSS | ✅ `escapeHtml()` en JS |
| Protection CSRF | ⚠️ À implémenter (recommandé) |

---

## 🔄 5. Flux de Création Détaillé

### Diagramme de Flux

```
┌─────────────┐
│ Étape 1     │
│ (Existante) │  POST /api/party/create
│ Thème +     │  { game_type_id, theme, synopsis }
│ Synopsis    │
└──────┬──────┘
       │
       │ Redirection automatique
       ▼
┌─────────────┐
│ Étape 2     │  GET /party/step2?party_id=X
│             │
│ Ajout des   │  Formulaire dynamique
│ Joueurs     │  - Champs prénom
│             │  - Boutons +/- joueurs
└──────┬──────┘
       │
       │ POST /api/characters/save
       │ { party_id, players: [{firstname}] }
       ▼
┌─────────────┐
│ Étape 3     │  GET /party/step3?party_id=X
│             │
│ Relations + │  Interface CRUD
│ Backgrounds │  - Édition backgrounds
│             │  - Modale relations
│             │  - Ajout/Suppression personnages
└──────┬──────┘
       │
       │ POST /api/party/finish
       │ { party_id }
       ▼
┌─────────────┐
│ Dashboard   │  GET /dashboard
│             │
│ Liste des   │  Affiche parties + nombre joueurs
│ Parties     │
└─────────────┘
```

### États d'une Partie

| État | Description |
|------|-------------|
| `draft` | En cours de création (visible au Dashboard) |
| `completed` | Création terminée |

---

## 📡 6. API REST

### Endpoints Personnages

| Méthode | Route | Body | Réponse |
|---------|-------|------|---------|
| POST | `/api/characters/save` | `{ party_id, players: [{firstname}] }` | `{ success, party_id }` |
| POST | `/api/characters/add` | `{ party_id, firstname }` | `{ success, character: {...} }` |
| POST | `/api/characters/update` | `{ character_id, firstname?, background? }` | `{ success }` |
| POST | `/api/characters/delete` | `{ character_id }` | `{ success }` |

### Endpoints Relations

| Méthode | Route | Body/Query | Réponse |
|---------|-------|------------|---------|
| POST | `/api/relations/create` | `{ character_id, target_character_id, relation_type, description? }` | `{ success, relation: {...} }` |
| GET | `/api/relations/get` | `?character_id=X` | `{ success, relations: [...] }` |
| POST | `/api/relations/delete` | `{ relation_id }` | `{ success }` |

### Endpoint Finalisation

| Méthode | Route | Body | Réponse |
|---------|-------|------|---------|
| POST | `/api/party/finish` | `{ party_id }` | `{ success }` |

---

## 🎯 7. Fonctionnalités Clés

### Dashboard

- [x] Affichage des parties en statut `draft` uniquement
- [x] Comptage des personnages via `COUNT(*)`
- [x] Tri par date de dernière modification
- [x] Clic sur carte = redirection vers étape en cours

### Étape 2 : Ajout des Joueurs

- [x] Génération dynamique des champs (JavaScript)
- [x] Validation : minimum 2 joueurs
- [x] Boutons +/- pour ajouter/retirer des joueurs
- [x] Pré-remplissage si personnages existants
- [x] Sauvegarde AJAX avant redirection

### Étape 3 : Relations et Backgrounds

- [x] Grid responsive de cartes personnages
- [x] Édition inline des prénoms
- [x] Textarea pour backgrounds (auto-save on blur)
- [x] Bouton "+" pour ajouter une relation
- [x] Modale avec dropdown de personnages cibles
- [x] Affichage des relations sous chaque personnage
- [x] Suppression de personnages (avec confirmation)
- [x] Suppression de relations
- [x] Bouton "Terminer la Création" (finalise la partie)

---

## ⚙️ 8. Configuration Requise

### Prérequis

- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.2+
- PDO activé
- Sessions PHP configurées

### Extensions PHP

- `pdo`
- `pdo_mysql`
- `json`

### Serveur Web

- Apache avec `mod_rewrite` (ou équivalent Nginx)
- Configuration `.htaccess` pour le routage

---

## 🧪 9. Tests Recommandés

### Tests Fonctionnels

- [ ] Créer une partie de A à Z
- [ ] Ajouter 5 joueurs
- [ ] Créer 3 relations différentes
- [ ] Éditer un background
- [ ] Supprimer un personnage
- [ ] Finaliser la partie
- [ ] Vérifier l'affichage au Dashboard

### Tests de Sécurité

- [ ] Tenter d'accéder à une partie d'un autre utilisateur
- [ ] Tenter de créer une relation sans authentification
- [ ] Vérifier l'isolation des données (2 utilisateurs)
- [ ] Tester les injections SQL (via PDO prepared statements)
- [ ] Tester les injections XSS (échappement HTML)

### Tests de Performance

- [ ] Dashboard avec 50 parties
- [ ] Partie avec 20 personnages
- [ ] 100 relations dans une partie
- [ ] Temps de réponse des API < 200ms

---

## 🐛 10. Points d'Attention et Limitations

### Limitations Connues

1. **Pas de protection CSRF** : À implémenter avec des tokens
2. **Pas de validation des types de relations** : Tout texte accepté
3. **Pas de limite au nombre de relations** : Pourrait causer des problèmes de performance
4. **Pas de système de brouillons multiples** : Une seule étape sauvegardée

### Améliorations Possibles

- [ ] Pagination du Dashboard (si > 50 parties)
- [ ] Recherche/filtres au Dashboard
- [ ] Export PDF des fiches personnages
- [ ] Historique des modifications
- [ ] Relations bidirectionnelles automatiques
- [ ] Suggestions de types de relations (dropdown prédéfini)
- [ ] Upload d'avatars pour les personnages
- [ ] Graphe visuel des relations

---

## 📚 11. Références de Code

### Exemples d'Utilisation

#### Récupérer une partie de manière sécurisée

```php
$userId = $_SESSION['user_id'];
$partyId = (int)$_GET['party_id'];

$party = $partyRepository->findById($partyId, $userId);
if (!$party) {
    throw new Exception("Partie non trouvée");
}
```

#### Créer une relation en JavaScript

```javascript
const response = await fetch('/api/relations/create', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        character_id: 1,
        target_character_id: 2,
        relation_type: 'Amant',
        description: 'Relation secrète depuis 2 ans'
    })
});
```

---

## 📞 12. Support et Documentation

### Fichiers de Documentation

| Fichier | Contenu |
|---------|---------|
| `IMPLEMENTATION_GUIDE.md` | Guide d'intégration complet |
| `SQL_REFERENCE.sql` | Toutes les requêtes SQL utiles |
| `TECHNICAL_SPECS.md` | Ce fichier (spécifications) |

### Fichiers de Migration

| Fichier | Description |
|---------|-------------|
| `20251216_add_user_id_to_murder_parties.php` | Ajout isolation utilisateur |
| `20251217_create_characters_and_relations.php` | Création tables personnages/relations |

---

## ✅ Checklist de Déploiement

- [ ] Migrations exécutées
- [ ] Routes configurées dans `router.php`
- [ ] Sessions testées
- [ ] Tests de sécurité passés
- [ ] Documentation lue par l'équipe
- [ ] Backup de la base de données effectué
- [ ] Logs d'erreurs configurés
- [ ] Tests de performance OK

---

**Version** : 1.0  
**Date** : 2025-12-14  
**Auteur** : GitHub Copilot  
**Projet** : MonpelProject - Murder Party Maker
