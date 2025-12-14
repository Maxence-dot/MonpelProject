# 🎭 MonpelProject - Murder Party Maker

Application qui a pour but d'aider à la personnalisation et la création de murder parties et autres animations.

## 🆕 Nouvelles Fonctionnalités (Décembre 2025)

### ✨ Ce qui est nouveau

- **🔒 Sécurité et Isolation** : Chaque utilisateur ne voit que ses propres parties
- **🏠 Dashboard** : Page d'accueil avec la liste des parties en cours
- **🎯 Flux de Création Complet** :
  - Étape 1 : Initialisation (thème + synopsis)
  - Étape 2 : Ajout des joueurs
  - Étape 3 : Relations entre personnages et backgrounds

### 📚 Documentation Complète

**🚀 Démarrage Rapide** : Consultez [INDEX.md](INDEX.md) pour naviguer dans la documentation

**Documents Principaux** :
- 📖 [FEATURES_README.md](FEATURES_README.md) - Vue d'ensemble des fonctionnalités
- 🔧 [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) - Guide d'intégration complet
- 📝 [TECHNICAL_SPECS.md](TECHNICAL_SPECS.md) - Spécifications techniques
- 🗄️ [SQL_REFERENCE.sql](SQL_REFERENCE.sql) - Référence SQL complète

---

## 🚀 Installation

### 1. Cloner le Projet

```bash
git clone https://github.com/votre-compte/MonpelProject.git
cd MonpelProject
```

### 2. Configuration

Copiez le fichier de configuration exemple :

```bash
cp config.sample.php config.php
```

Éditez `config.php` avec vos paramètres de base de données :

```php
return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'murdermaker',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_DSN'  => 'mysql:host=localhost;dbname=murdermaker;charset=utf8mb4',
];
```

### 3. Exécuter les Migrations

```bash
php bin/migrate.php
```

Cela créera toutes les tables nécessaires :
- `game_types` - Types de jeux disponibles
- `murder_parties` - Parties créées (avec `user_id`)
- `characters` - Personnages des parties
- `relations` - Relations entre personnages

### 4. Tester l'Installation

Accédez à :
```
http://localhost:8000/test_implementation.php
```

---

## 🧪 Tests

### Test Automatique

Ouvrez `test_implementation.php` dans votre navigateur pour vérifier :
- ✅ Connexion à la base de données
- ✅ Tables créées correctement
- ✅ Fichiers présents
- ✅ Configuration PHP

### Test Manuel

1. Connectez-vous avec un utilisateur
2. Accédez au Dashboard : `/dashboard`
3. Créez une nouvelle partie
4. Ajoutez des joueurs (Étape 2)
5. Créez des relations (Étape 3)
6. Finalisez la partie

---

## 📁 Structure du Projet

```
MonpelProject/
├── 📄 Documentation
│   ├── INDEX.md                  ← Navigation
│   ├── FEATURES_README.md        ← Fonctionnalités
│   ├── IMPLEMENTATION_GUIDE.md   ← Guide d'intégration
│   ├── TECHNICAL_SPECS.md        ← Spécifications
│   └── SQL_REFERENCE.sql         ← Référence SQL
│
├── 🗄️ Base de Données
│   └── migrations/
│       ├── 20251213_create_game_types_and_murder_parties.php
│       ├── 20251216_add_user_id_to_murder_parties.php
│       └── 20251217_create_characters_and_relations.php
│
├── 🏗️ Backend
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── PartyController.php
│   │   ├── DashboardController.php      ⭐ NOUVEAU
│   │   ├── CharacterController.php      ⭐ NOUVEAU
│   │   └── RelationController.php       ⭐ NOUVEAU
│   │
│   ├── Repositories/
│   │   ├── PartyRepository.php          (sécurisé avec user_id)
│   │   ├── UserRepository.php
│   │   ├── CharacterRepository.php      ⭐ NOUVEAU
│   │   └── RelationRepository.php       ⭐ NOUVEAU
│   │
│   ├── Models/
│   │   ├── Party.php
│   │   ├── User.php
│   │   ├── Character.php                ⭐ NOUVEAU
│   │   └── Relation.php                 ⭐ NOUVEAU
│   │
│   └── Services/
│       └── ScenarioService.php          (mis à jour avec user_id)
│
└── 🎨 Frontend
    ├── views/
    │   ├── dashboard.php                ⭐ NOUVEAU
    │   └── party/
    │       ├── step2_add_players.php    ⭐ NOUVEAU
    │       └── step3_relations.php      ⭐ NOUVEAU
    │
    └── assets/
        ├── css/
        │   └── modal.css                (mis à jour)
        └── js/
            ├── modal.js                 (mis à jour)
            └── step3_relations.js       ⭐ NOUVEAU
```

---

## 🛣️ Routes Principales

### Pages

| Route | Description |
|-------|-------------|
| `/` | Page d'accueil |
| `/login` | Connexion |
| `/register` | Inscription |
| `/dashboard` | Tableau de bord (parties en cours) ⭐ |
| `/party/step2?party_id=X` | Ajout des joueurs ⭐ |
| `/party/step3?party_id=X` | Relations et backgrounds ⭐ |

### API

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/party/create` | POST | Créer une partie (Étape 1) |
| `/api/characters/save` | POST | Sauvegarder les joueurs (Étape 2) ⭐ |
| `/api/characters/add` | POST | Ajouter un personnage ⭐ |
| `/api/characters/update` | POST | Mettre à jour un personnage ⭐ |
| `/api/characters/delete` | POST | Supprimer un personnage ⭐ |
| `/api/relations/create` | POST | Créer une relation ⭐ |
| `/api/relations/get` | GET | Récupérer les relations ⭐ |
| `/api/relations/delete` | POST | Supprimer une relation ⭐ |
| `/api/party/finish` | POST | Finaliser une partie ⭐ |

---

## 🔐 Sécurité

### Isolation des Données

Toutes les requêtes incluent une vérification `user_id` :

```php
// Exemple de requête sécurisée
$party = $partyRepository->findById($partyId, $userId);
```

**Règle** : Un utilisateur ne peut **jamais** accéder aux parties d'un autre utilisateur.

### Sessions

Vérification de session sur toutes les routes sensibles :

```php
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
```

---

## 🌐 Déploiement

### Production (InfinityFree)

Le déploiement se fait automatiquement via GitHub Actions :

```yaml
# .github/workflows/deploy.yml
- uses: SamKirkland/FTP-Deploy-Action@4.3.0
  with:
    server: ${{ secrets.FTP_HOST }}
    username: ${{ secrets.FTP_USERNAME }}
    password: ${{ secrets.FTP_PASSWORD }}
```

**Secrets requis** :
- `FTP_HOST`
- `FTP_USERNAME`
- `FTP_PASSWORD`

### Migration Web

Pour exécuter les migrations sur le serveur (sans accès terminal) :

```
https://votre-domaine.tld/migrate.php?token=VOTRE_TOKEN
```

Configurez le token dans `config.php` :

```php
'MIGRATION_TOKEN' => 'votre-token-securise'
```

---

## 📚 Documentation & Onboarding

### Pour les Nouveaux Contributeurs

Si vous débutez sur le projet :

1. **Lisez** [ONBOARDING.md](ONBOARDING.md) - Guide général du projet
2. **Consultez** [INDEX.md](INDEX.md) - Navigation dans la documentation
3. **Parcourez** [FEATURES_README.md](FEATURES_README.md) - Nouvelles fonctionnalités

### Pour l'Intégration

Suivez le guide complet : [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)

---

## 🐛 Dépannage

### Erreurs Courantes

| Erreur | Solution |
|--------|----------|
| "user_id not found" | Vérifiez que `$_SESSION['user_id']` est défini |
| "Table doesn't exist" | Exécutez `php bin/migrate.php` |
| "Partie non trouvée" | Vérifiez que la partie appartient à l'utilisateur |
| Modale ne s'ouvre pas | Vérifiez que `modal.js` est chargé |

**Plus d'aide** : [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) - Section 13

---

## 🤝 Contribution

Les contributions sont les bienvenues !

1. Fork le projet
2. Créez une branche (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

---

## 📊 Statistiques du Projet

- **Lignes de Code PHP** : ~3000
- **Lignes de Code JS** : ~500
- **Tables DB** : 5
- **Routes API** : 15+
- **Fonctionnalités** : Création de parties, gestion des personnages, relations, isolation des données

---

## 📝 Licence

Ce projet est sous licence MIT.

---

## 👥 Auteurs

- **Équipe MonpelProject** - Développement initial
- **GitHub Copilot** - Implémentation des nouvelles fonctionnalités (Décembre 2025)

---

## 🎯 Roadmap

### ✅ Fait (v1.0 - Décembre 2025)

- [x] Isolation des données par utilisateur
- [x] Dashboard avec liste des parties
- [x] Flux de création complet (3 étapes)
- [x] Gestion des personnages
- [x] Relations entre personnages
- [x] Documentation complète

### 🚧 En Cours

- [ ] Export PDF des fiches personnages
- [ ] Graphe visuel des relations
- [ ] Upload d'avatars

### 📋 Prévu

- [ ] Mode collaboratif
- [ ] Templates de parties prédéfinis
- [ ] Générateur IA de backgrounds
- [ ] Application mobile

---

## 📞 Support

Pour toute question ou problème :

1. Consultez la [documentation](INDEX.md)
2. Ouvrez une [issue](https://github.com/votre-compte/MonpelProject/issues)
3. Contactez l'équipe

---

**🎭 Créez des Murder Parties inoubliables ! 🔍**

*Dernière mise à jour : Décembre 2025*
