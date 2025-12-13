<?php
// Migration: create game_types and murder_parties (driver-aware)
function up(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS game_types (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  description TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS murder_parties (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  game_type_id INTEGER,
  theme TEXT,
  synopsis TEXT,
  status TEXT DEFAULT 'draft',
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (game_type_id) REFERENCES game_types(id)
);

INSERT OR IGNORE INTO game_types (id, name, description) VALUES (1, 'Murder Party', 'Jeu d''enquête à rôles');
SQL
        );
    } else {
        // Assume MySQL compatible
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS game_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS murder_parties (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  game_type_id INT UNSIGNED NULL,
  theme VARCHAR(255) NULL,
  synopsis TEXT NULL,
  status VARCHAR(50) DEFAULT 'draft',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_murder_parties_game_type FOREIGN KEY (game_type_id) REFERENCES game_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO game_types (name, description)
SELECT 'Murder Party', 'Jeu d''enquête à rôles'
WHERE NOT EXISTS (SELECT 1 FROM game_types WHERE name = 'Murder Party');
SQL
        );
    }
}

function down(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec("DROP TABLE IF EXISTS murder_parties;");
        $pdo->exec("DROP TABLE IF EXISTS game_types;");
    } else {
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec("DROP TABLE IF EXISTS murder_parties;");
        $pdo->exec("DROP TABLE IF EXISTS game_types;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    }
}

// End of migration file
