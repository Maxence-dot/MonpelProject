<?php
/**
 * Migration: Ajouter user_id à murder_parties pour l'isolation des données
 */

function up_20251216_add_user_id_to_murder_parties(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        $pdo->exec(<<<'SQL'
ALTER TABLE murder_parties ADD COLUMN user_id INTEGER NOT NULL DEFAULT 0;
CREATE INDEX idx_murder_parties_user_id ON murder_parties(user_id);
SQL
        );
    } else {
        // MySQL
        $pdo->exec(<<<'SQL'
ALTER TABLE murder_parties 
ADD COLUMN user_id INT UNSIGNED NOT NULL,
ADD INDEX idx_user_id (user_id),
ADD CONSTRAINT fk_murder_parties_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
SQL
        );
    }
}

function down_20251216_add_user_id_to_murder_parties(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        // SQLite ne supporte pas DROP COLUMN facilement, nécessite recréation de table
        $pdo->exec(<<<'SQL'
CREATE TABLE murder_parties_new (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  game_type_id INTEGER,
  theme TEXT,
  synopsis TEXT,
  status TEXT DEFAULT 'draft',
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (game_type_id) REFERENCES game_types(id)
);

INSERT INTO murder_parties_new (id, game_type_id, theme, synopsis, status, created_at, updated_at)
SELECT id, game_type_id, theme, synopsis, status, created_at, updated_at FROM murder_parties;

DROP TABLE murder_parties;
ALTER TABLE murder_parties_new RENAME TO murder_parties;
SQL
        );
    } else {
        $pdo->exec(<<<'SQL'
ALTER TABLE murder_parties 
DROP FOREIGN KEY fk_murder_parties_user,
DROP INDEX idx_user_id,
DROP COLUMN user_id;
SQL
        );
    }
}
