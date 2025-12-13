<?php
// Exemple de configuration pour le projet Murder Party Générative
// Copier ce fichier en `config.local.php` (hors du repo) et remplir
// OU définir les variables d'environnement `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

return [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'murdermaker',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    // Token to trigger web migrations runner; set a long random string in your real config.
    'MIGRATION_TOKEN' => 'changeme_random_token',
];
