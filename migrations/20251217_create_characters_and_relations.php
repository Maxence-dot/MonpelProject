<?php
/**
 * Migration: Créer les tables characters et relations pour le jeu
 */

function up_20251217_create_characters_and_relations(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS characters (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  party_id INTEGER NOT NULL,
  firstname TEXT NOT NULL,
  background TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (party_id) REFERENCES murder_parties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS relations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  character_id INTEGER NOT NULL,
  target_character_id INTEGER NOT NULL,
  relation_type TEXT NOT NULL,
  description TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
  FOREIGN KEY (target_character_id) REFERENCES characters(id) ON DELETE CASCADE
);

CREATE INDEX idx_characters_party_id ON characters(party_id);
CREATE INDEX idx_relations_character_id ON relations(character_id);
CREATE INDEX idx_relations_target_character_id ON relations(target_character_id);
SQL
        );
    } else {
        // MySQL
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS characters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  party_id INT UNSIGNED NOT NULL,
  firstname VARCHAR(255) NOT NULL,
  background TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_party_id (party_id),
  CONSTRAINT fk_characters_party FOREIGN KEY (party_id) REFERENCES murder_parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS relations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  character_id INT UNSIGNED NOT NULL,
  target_character_id INT UNSIGNED NOT NULL,
  relation_type VARCHAR(100) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_character_id (character_id),
  INDEX idx_target_character_id (target_character_id),
  CONSTRAINT fk_relations_character FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
  CONSTRAINT fk_relations_target FOREIGN KEY (target_character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
        );
    }
}

function down_20251217_create_characters_and_relations(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver === 'sqlite') {
        $pdo->exec("DROP TABLE IF EXISTS relations;");
        $pdo->exec("DROP TABLE IF EXISTS characters;");
    } else {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("DROP TABLE IF EXISTS relations;");
        $pdo->exec("DROP TABLE IF EXISTS characters;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }
}
