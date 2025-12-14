# MonpelProject — Guide d'onboarding (débutants)

Ce document explique simplement comment le projet est organisé, comment le lancer en local et comment contribuer.

## Objectif
Murder Party Générative : générer des scénarios de murder party (rôles, secrets, documents) en s'appuyant sur une API d'IA.

## Prérequis
- PHP 8.x installé (ou XAMPP sur Windows)
- Composer (optionnel mais recommandé)
- Git

## Démarrage rapide (développeur)
1. Clonez le repo et placez-vous dans le dossier du projet.

2. Créez un fichier de configuration local (exemple déjà fourni)

- Copiez `config.sample.php` vers `config.local.php` ou créez `config.local.php` et définissez `DB_DSN`.
- Pour un test rapide local, vous pouvez utiliser SQLite :

```php
<?php
return [
  'DB_DSN' => 'sqlite:' . __DIR__ . '/dev.sqlite',
  'MIGRATION_TOKEN' => 'dev-local-token',
];
```

3. Exécutez les migrations (créera les tables dans la DB configurée) :

```bash
php bin/migrate.php
```

Sur Windows avec XAMPP, lancez Apache/PHP ou utilisez serveur PHP intégré :

```bash
php -S localhost:8000
# puis ouvrez http://localhost:8000/MonpelProject/
```

Si vous préférez, vous pouvez exécuter la page web de migration via :

```
http://localhost/MonpelProject/admin/migrate.php?token=VOTRE_TOKEN
```

## Architecture & fichiers importants
Voici ce que contient chaque répertoire et comment il fonctionne aujourd'hui :

- **`index.php`** : point d'entrée public (front controller) — inclut `router.php` qui décide quelle vue ou quel contrôleur appeler.
- **`router.php`** : routeur simple qui scanne `views/` et mappe des URLs conviviales vers des vues (`views/..._view.php`) ou, pour certaines routes, appelle un `Controller` (ex: `Controllers/AuthController.php`).
- **`Controllers/`** : handlers HTTP légers — reçoivent la requête, valident les données, appellent un `Service` si nécessaire, puis incluent une vue ou renvoient du JSON. Ex: `AuthController` gère `login/register/logout`.
- **`Services/`** : logique métier — orchestrent l'accès à la base et les appels externes (IA), et exposent des méthodes réutilisables pour les contrôleurs.
- **`Models/`** : objets de données simples (POPO) représentant les enregistrements (ex: `Party`, `Player`, `User`). Utilisés pour transporter des données entre couches.
- **`Database/`** : utilitaires de connexion (ex: `DatabaseService.php`) et configuration PDO via `connexionAll.php`.
- **`migrations/`** : fichiers de migration (nommés par date) contenant des fonctions `up_*` (et parfois `down_*`) qui exécutent des changements de schéma. Le runner `bin/migrate.php` applique ces fichiers et écrit l'historique dans la table `migrations`.
- **`views/`** : templates HTML (souvent nommés `*_view.php`) — ils affichent l'UI et s'attendent à ce que le contrôleur ait préparé les variables nécessaires.
- **`api/`** : endpoints JSON pour l'interface (ex: `/api/party.php`) — ils consomment `Services` et renvoient des réponses JSON pour le front-end.
- **`assets/`** : fichiers statiques (CSS, JS, icônes) servis directement par le serveur.
- **`bin/`** : scripts utilitaires exécutables en CLI (ex: `bin/migrate.php`).
- **`config.php` / `config.local.php`** : configuration de l'application. `config.local.php` est git-ignored et sert pour les secrets et la configuration locale (ex: `DB_DSN`, `MIGRATION_TOKEN`).

**Flux d'une requête typique**
1. Le navigateur demande une URL → `index.php` → `router.php`.
2. `router.php` identifie la route et appelle soit un `Controller` (ex: auth), soit inclut directement une vue (`views/..._view.php`).
3. Le `Controller` (si présent) valide l'entrée, appelle un `Service` qui interagit avec la base via `PDO` (ou un `Repository` si présent) et retourne des données.
4. Le `Controller` rend la `view` ou renvoie du JSON.

**Fichiers importants**
- `connexionAll.php` : initialisation de la configuration et création de `$pdo`.
- `bin/migrate.php` : lance les migrations dans `migrations/`.
- `router.php` : front controller et mapping dynamique des vues.

(Le reste du document explique le démarrage, la sécurité et les bonnes pratiques.)

## Bonnes pratiques pour contribuer
1. **Branch**: créez une branche courte (`feature/ajout-service-ia`).
2. **Ajouter une migration**: ajouter un fichier dans `migrations/` `YYYYMMDD_description.php` avec une fonction `up_...` qui accepte `PDO $pdo`.
3. **Séparer le code**: mettre la logique SQL dans `Repositories/` (pas directement dans les controllers). Créer `Services/` pour la logique métier.
4. **Tests**: ajouter des tests unitaires (PHPUnit) pour `Services` en mockant les dépendances externes (IA, DB).

## Intégration IA (conseil rapide)
- Créer une interface `IAGenerator` (ex: `src/IAGenerator.php`) avec méthodes claires (`generateTheme`, `generateCharacterSheet`, ...).
- Implémenter `OpenAIAGenerator` et `MockIAGenerator` pour le dev local.
- Stocker les prompts et les réponses brutes (avec meta: modèle, tokens) dans `generated_documents` pour audit.

## Sécurité & Secrets
- Ne stockez PAS vos clés API dans le repo.
- Utilisez `config.local.php` ou variables d'environnement.
- Protégez le runner de migration avec `MIGRATION_TOKEN`.
- Ajoutez CSRF tokens sur les formulaires POST.

## Tests & CI suggérés
- Linter: PHPStan / PHPCS (PSR-12)
- Tests: PHPUnit (unit + intégration)
- CI: exécutez tests et migrations sur une DB de test (SQLite ou MySQL de CI)

## Prochaines étapes recommandées
- Mettre en place Composer + autoload PSR‑4
- Introduire `Repositories/` et extraire toutes les requêtes SQL
- Implémenter `IAGenerator` + tests
- Ajouter un GUIDELINES/CONTRIBUTING simple pour les nouveaux contributeurs

---
Si vous voulez, je peux :
- créer un `CONTRIBUTING.md` basé sur ce document, ou
- commencer par ajouter Composer + PSR‑4 et renommer quelques classes pour namespaces.

Dites-moi quelle option vous préférez et je m'en occupe.