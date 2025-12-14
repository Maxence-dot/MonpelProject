<?php
// Migration: rename legacy `session` table to `users`, or create `users` if missing
function up_20251215_rename_session_to_users(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        // If `users` already exists, nothing to do (SQLite)
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'") ?: false;
        $hasUsers = false;
        if ($stmt) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $hasUsers = !empty($rows);
        }
        if ($hasUsers) return;

        // If `session` exists, rename it
        $stmt2 = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='session'");
        $rows2 = $stmt2 ? $stmt2->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($rows2)) {
            $pdo->exec("ALTER TABLE session RENAME TO users;");
            return;
        }

        // Otherwise create `users`
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
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
        // MySQL/MariaDB
        // Check existence
        $hasUsers = false;
        $res = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($res && $res->rowCount() > 0) $hasUsers = true;
        if ($hasUsers) return;

        // If session exists, rename it
        $res2 = $pdo->query("SHOW TABLES LIKE 'session'");
        if ($res2 && $res2->rowCount() > 0) {
            $pdo->exec("RENAME TABLE session TO users;");
            return;
        }

        // Create users
        $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  firstname VARCHAR(255) NULL,
  lastname VARCHAR(255) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL
        );
    }
}

function down_20251215_rename_session_to_users(PDO $pdo)
{
    // no-op down (destructive ops avoided)
}
