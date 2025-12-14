# 🔧 Guide de Mise à Jour du Code Existant

Ce document liste toutes les modifications à apporter aux fichiers existants de MonpelProject pour intégrer les nouvelles fonctionnalités.

---

## 1. 📝 Modification de `connexionAll.php`

**Aucune modification nécessaire** - Le fichier fonctionne déjà correctement.

---

## 2. 🛣️ Modification de `router.php`

### Localisation
Votre fichier de routage principal (peut être `index.php` ou `router.php`).

### Modifications à apporter

#### A. Initialiser la session (si pas déjà fait)
```php
// Au début du fichier, après les require
session_start();
```

#### B. Ajouter les nouvelles routes
Copiez toutes les routes du fichier `ROUTER_EXAMPLE.php` dans votre routeur.

**Exemple de structure** :
```php
<?php
require_once __DIR__ . '/connexionAll.php';
session_start();

// Routes existantes...

// ======== NOUVELLES ROUTES À AJOUTER ========

// Dashboard
$router->addRoute('GET', '/dashboard', function() use ($pdo) {
    require_once __DIR__ . '/Controllers/DashboardController.php';
    require_once __DIR__ . '/Repositories/PartyRepository.php';
    
    $partyRepo = new PartyRepository($pdo);
    $controller = new DashboardController($partyRepo);
    $controller->index();
});

// ... (copier toutes les routes du ROUTER_EXAMPLE.php)
```

---

## 3. 🔄 Redirection après Création de Partie (Étape 1)

### Fichier à modifier
Votre fichier JavaScript qui gère la création de partie (probablement dans `views/index.php` ou un fichier JS dédié).

### Modification

**AVANT** :
```javascript
// Après création réussie
if (data.success) {
    alert('Partie créée avec succès !');
    // Ou redirection vers dashboard
}
```

**APRÈS** :
```javascript
// Après création réussie - Redirection automatique vers Étape 2
if (data.success) {
    window.location.href = `/party/step2?party_id=${data.id}`;
}
```

---

## 4. 🏠 Ajout d'un Lien vers le Dashboard

### Dans votre menu de navigation

Ajoutez un lien vers le Dashboard dans votre menu principal :

```html
<!-- Exemple de menu -->
<nav>
    <a href="/dashboard">Mes Parties</a>
    <a href="/party/create">Nouvelle Partie</a>
    <a href="/logout">Déconnexion</a>
</nav>
```

---

## 5. 🔐 Vérification de Session dans les Contrôleurs Existants

### Si vous avez d'autres contrôleurs

Assurez-vous qu'ils vérifient la session :

```php
// Au début de chaque méthode sensible
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}
```

---

## 6. 📊 Mise à Jour de la Page de Connexion

### Fichier : `Controllers/AuthController.php` (ou équivalent)

Après une connexion réussie, redirigez vers le Dashboard :

**AVANT** :
```php
// Après login réussi
$_SESSION['user_id'] = $user['id'];
header('Location: /');
```

**APRÈS** :
```php
// Après login réussi
$_SESSION['user_id'] = $user['id'];
header('Location: /dashboard');
```

---

## 7. 🗄️ Migration de Données Existantes (SI NÉCESSAIRE)

Si vous avez déjà des parties dans la base de données **sans** `user_id`, vous devez les mettre à jour.

### Option 1 : Supprimer les anciennes données (développement)

```sql
-- ATTENTION : Supprime toutes les parties existantes
TRUNCATE TABLE murder_parties;
```

### Option 2 : Attribuer à un utilisateur par défaut

```sql
-- Remplacer 1 par l'ID de votre utilisateur de test
UPDATE murder_parties
SET user_id = 1
WHERE user_id IS NULL OR user_id = 0;
```

### Option 3 : Migration intelligente

Si vous avez un moyen de lier les parties existantes aux utilisateurs :

```sql
-- Exemple : si vous avez une colonne 'created_by' ou similaire
UPDATE murder_parties
SET user_id = created_by
WHERE user_id IS NULL;
```

---

## 8. 🎨 Intégration des Styles Globaux

### Fichier : `style.css`

Assurez-vous que vos styles globaux sont cohérents avec les nouvelles vues.

**Vérifications** :
- Les couleurs des boutons correspondent à votre charte
- Les formulaires ont un style cohérent
- Les cartes du Dashboard sont responsive

**Exemple de personnalisation** :

```css
/* Dans style.css */
.btn-primary {
    background: #votre-couleur-primaire;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```

---

## 9. 🔍 Modification des Requêtes SQL Existantes

### Si vous avez d'autres fichiers qui utilisent `PartyRepository`

**IMPORTANT** : Toutes les méthodes du `PartyRepository` nécessitent maintenant `user_id`.

**AVANT** :
```php
$party = $partyRepo->findById($partyId);
$parties = $partyRepo->findAll();
$partyRepo->update($partyId, ['theme' => 'Nouveau thème']);
```

**APRÈS** :
```php
$userId = $_SESSION['user_id'];

$party = $partyRepo->findById($partyId, $userId);
$parties = $partyRepo->findAll($userId);
$partyRepo->update($partyId, $userId, ['theme' => 'Nouveau thème']);
```

---

## 10. 📱 Configuration du `.htaccess` (Apache)

Si vous utilisez Apache, assurez-vous que votre `.htaccess` redirige correctement les requêtes.

### Fichier : `.htaccess` (à la racine du projet)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Rediriger tout vers index.php (ou router.php)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

---

## 11. 🧪 Fichier de Test (Optionnel)

Créez un fichier de test pour vérifier que tout fonctionne.

### Fichier : `test_implementation.php`

```php
<?php
require_once __DIR__ . '/connexionAll.php';

echo "<h1>Test d'Implémentation - MonpelProject</h1>";

// Test 1 : Connexion à la DB
try {
    $pdo->query("SELECT 1");
    echo "✅ Connexion DB : OK<br>";
} catch (Exception $e) {
    echo "❌ Connexion DB : ERREUR - " . $e->getMessage() . "<br>";
}

// Test 2 : Table murder_parties avec user_id
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM murder_parties LIKE 'user_id'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Colonne user_id : OK<br>";
    } else {
        echo "❌ Colonne user_id : MANQUANTE - Exécuter la migration<br>";
    }
} catch (Exception $e) {
    echo "❌ Colonne user_id : ERREUR - " . $e->getMessage() . "<br>";
}

// Test 3 : Table characters
try {
    $pdo->query("SELECT 1 FROM characters LIMIT 1");
    echo "✅ Table characters : OK<br>";
} catch (Exception $e) {
    echo "❌ Table characters : MANQUANTE - Exécuter la migration<br>";
}

// Test 4 : Table relations
try {
    $pdo->query("SELECT 1 FROM relations LIMIT 1");
    echo "✅ Table relations : OK<br>";
} catch (Exception $e) {
    echo "❌ Table relations : MANQUANTE - Exécuter la migration<br>";
}

// Test 5 : Session
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessions PHP : OK<br>";
} else {
    echo "❌ Sessions PHP : ERREUR<br>";
}

// Test 6 : Fichiers créés
$files = [
    'Controllers/DashboardController.php',
    'Controllers/CharacterController.php',
    'Controllers/RelationController.php',
    'Repositories/CharacterRepository.php',
    'Repositories/RelationRepository.php',
    'views/dashboard.php',
    'views/party/step2_add_players.php',
    'views/party/step3_relations.php',
    'assets/js/step3_relations.js'
];

echo "<h2>Fichiers créés :</h2>";
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file : MANQUANT<br>";
    }
}
```

**Usage** :
```
http://localhost:8000/test_implementation.php
```

---

## 12. 📋 Checklist de Modifications

### À Faire (cochez au fur et à mesure)

- [ ] **Migrations exécutées** (`php bin/migrate.php`)
- [ ] **Routes ajoutées** dans router.php
- [ ] **Session initialisée** (`session_start()`)
- [ ] **Redirection Étape 1 → Étape 2** configurée
- [ ] **Lien Dashboard** ajouté au menu
- [ ] **Redirection après login** vers Dashboard
- [ ] **Migration des données existantes** (si nécessaire)
- [ ] **Styles personnalisés** (si souhaité)
- [ ] **`.htaccess` vérifié** (Apache)
- [ ] **Test d'implémentation** exécuté

---

## 13. 🚨 Erreurs Courantes et Solutions

### Erreur : "Call to undefined method PartyRepository::findById()"

**Cause** : Vous utilisez l'ancienne signature de méthode.

**Solution** : Ajoutez le paramètre `$userId` :
```php
$party = $partyRepo->findById($partyId, $userId);
```

---

### Erreur : "Table 'characters' doesn't exist"

**Cause** : Migrations non exécutées.

**Solution** :
```bash
php bin/migrate.php
```

---

### Erreur : "Undefined index: user_id"

**Cause** : Session non initialisée ou utilisateur non connecté.

**Solution** :
1. Vérifiez que `session_start()` est appelé
2. Vérifiez que l'utilisateur est bien connecté
3. Ajoutez une vérification :
```php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
```

---

### Erreur : "Cannot modify header information"

**Cause** : Du contenu a été envoyé avant un `header()`.

**Solution** :
- Assurez-vous qu'il n'y a pas d'espace ou de BOM avant `<?php`
- Utilisez `ob_start()` au début du fichier

---

## 14. 🔄 Mise à Jour Continue

### Après chaque modification

1. **Testez** la fonctionnalité modifiée
2. **Vérifiez** les logs d'erreurs
3. **Committez** vos changements (Git)
4. **Documentez** les modifications spécifiques

---

## 15. 🎯 Validation Finale

Avant de considérer l'intégration terminée :

1. ✅ Créez un nouvel utilisateur
2. ✅ Créez une partie de A à Z
3. ✅ Vérifiez qu'un autre utilisateur ne peut pas y accéder
4. ✅ Testez toutes les fonctionnalités CRUD
5. ✅ Vérifiez la responsivité mobile
6. ✅ Testez avec des données de production (si applicable)

---

## 📞 Besoin d'Aide ?

Si vous rencontrez des problèmes lors de l'intégration :

1. Consultez **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)**
2. Vérifiez les logs PHP (`tail -f /var/log/apache2/error.log`)
3. Utilisez `test_implementation.php` pour diagnostiquer
4. Consultez **[SQL_REFERENCE.sql](SQL_REFERENCE.sql)** pour les requêtes

---

**Bonne intégration ! 🚀**

*Document mis à jour le 2025-12-14*
