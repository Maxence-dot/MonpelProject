# ⚡ Quick Reference - MonpelProject

Guide de référence rapide pour les développeurs pressés.

---

## 🚀 Installation en 3 Commandes

```bash
# 1. Configuration
cp config.sample.php config.php
# Éditez config.php avec vos paramètres DB

# 2. Migrations
php bin/migrate.php

# 3. Test
# Ouvrir http://localhost:8000/test_implementation.php
```

---

## 🗄️ Base de Données

### Tables Principales

```sql
murder_parties (user_id*, theme, synopsis, status)
  └─ characters (party_id*, firstname, background)
      └─ relations (character_id*, target_character_id*, relation_type)
```

**Important** : `user_id` obligatoire pour l'isolation !

---

## 🛣️ Routes à Ajouter

```php
// Dashboard
GET /dashboard → DashboardController::index()

// Étapes
GET /party/step2 → CharacterController::addPlayers()
GET /party/step3 → CharacterController::manageRelations()

// API Characters
POST /api/characters/save
POST /api/characters/add
POST /api/characters/update
POST /api/characters/delete

// API Relations
POST /api/relations/create
GET  /api/relations/get
POST /api/relations/delete

// Finalisation
POST /api/party/finish
```

---

## 🔐 Pattern de Sécurité

```php
// 1. Vérifier session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

// 2. Récupérer userId
$userId = (int)$_SESSION['user_id'];

// 3. Filtrer par user_id
$party = $partyRepo->findById($partyId, $userId);
if (!$party) {
    http_response_code(404);
    exit;
}
```

---

## 📁 Fichiers Modifiés

### PartyRepository.php
```php
// AVANT
create($gameTypeId, $theme, $synopsis)
findById($partyId)
update($partyId, $data)

// APRÈS
create($userId, $gameTypeId, $theme, $synopsis)
findById($partyId, $userId)
update($partyId, $userId, $data)
```

### ScenarioService.php
```php
// AVANT
createInitialParty($gameTypeId, $theme, $synopsis)

// APRÈS
createInitialParty($userId, $gameTypeId, $theme, $synopsis)
```

---

## 🎨 Flux Utilisateur

```
Login → Dashboard → [Nouvelle Partie]
  → Étape 1 (Thème)
  → Étape 2 (Joueurs)    ⭐ NOUVEAU
  → Étape 3 (Relations)  ⭐ NOUVEAU
  → Dashboard
```

---

## 🧪 Test Rapide

```bash
# Test complet
php -S localhost:8000
# Ouvrir http://localhost:8000/test_implementation.php
```

**✅ Vert = OK | ❌ Rouge = Problème**

---

## 📊 SQL Rapide

```sql
-- Parties d'un utilisateur
SELECT * FROM murder_parties 
WHERE user_id = :user_id AND status = 'draft';

-- Personnages d'une partie
SELECT * FROM characters 
WHERE party_id = :party_id;

-- Relations d'un personnage
SELECT r.*, c.firstname as target_name
FROM relations r
JOIN characters c ON r.target_character_id = c.id
WHERE r.character_id = :character_id;
```

---

## 🐛 Debug Express

| Problème | Solution |
|----------|----------|
| user_id not found | `$_SESSION['user_id']` non défini |
| Table doesn't exist | `php bin/migrate.php` |
| Partie non trouvée | Vérifier user_id |
| Modale bloquée | Vérifier `modal.js` chargé |

---

## 📚 Documentation

| Besoin | Document |
|--------|----------|
| Vue d'ensemble | [SUMMARY.md](SUMMARY.md) |
| Installation | [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) |
| SQL | [SQL_REFERENCE.sql](SQL_REFERENCE.sql) |
| Tout comprendre | [INDEX.md](INDEX.md) |

---

## 💡 Snippets Utiles

### Créer une Relation (JS)

```javascript
await fetch('/api/relations/create', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        character_id: 1,
        target_character_id: 2,
        relation_type: 'Amant'
    })
});
```

### Récupérer Partie Sécurisée (PHP)

```php
$party = $partyRepo->findById($partyId, $_SESSION['user_id']);
if (!$party) throw new Exception("Non autorisé");
```

---

## ✅ Checklist Go-Live

- [ ] Migrations exécutées
- [ ] Routes configurées
- [ ] test_implementation.php = 100% vert
- [ ] Flux complet testé
- [ ] Isolation testée (2 users)

---

## 📞 Aide Rapide

```
Erreur session     → MIGRATION_CHECKLIST.md #13
Erreur SQL         → SQL_REFERENCE.sql
Erreur routes      → ROUTER_EXAMPLE.php
Erreur code        → TECHNICAL_SPECS.md
```

---

**⚡ C'est parti ! → `php bin/migrate.php`**
