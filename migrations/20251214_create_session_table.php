<?php
// Migration to create `session` table used by login/register pages
function up_20251214_create_session_table(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS session (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  password TEXT NOT NULL,
  firstname TEXT,
  lastname TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);
SQL
        );
    } else {
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS session (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firstname VARCHAR(255) NULL,
  lastname VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
        );
    }
}

function down_20251214_create_session_table(PDO $pdo)
{
    $pdo->exec("DROP TABLE IF EXISTS session;");
}
