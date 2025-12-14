# MonpelProject
Application qui a pour but d'aider à la personnalisation et la création de murder party et autre animation

## Migrations

Pour initialiser les tables (dev):

```bash
php bin/migrate.php
```

Web runner de migrations
-----------------------
Vous pouvez exécuter les migrations depuis une URL sur le serveur (utile si vous n'avez pas accès au terminal). Configurez un token sécurisé dans `config.php` (MIGRATION_TOKEN) puis appelez :

```
https://votre-domaine.tld/migrate.php?token=VOTRE_TOKEN
```

La page affiche les migrations et un bouton "Exécuter les migrations".

Cela créera les tables `game_types` et `murder_parties` et insérera le type "Murder Party" par défaut.

Documentation & Onboarding
-------------------------
Si vous débutez sur le projet, consultez le guide d'onboarding : [ONBOARDING.md](ONBOARDING.md) — il explique le fonctionnement, la structure et les premières actions pour contribuer.
