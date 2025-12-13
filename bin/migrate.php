<?php
// Simple migration runner for this small project.
require_once __DIR__ . '/../connexionAll.php';

// Ensure migrations table
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );"
);

$migrations = glob(__DIR__ . '/../migrations/*.php');
sort($migrations);

foreach ($migrations as $file) {
    $name = basename($file);
    $stmt = $pdo->prepare('SELECT 1 FROM migrations WHERE name = ?');
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo "$name already applied\n";
        continue;
    }

    echo "Applying $name...\n";
    include $file;
    if (!function_exists('up')) {
        echo "Migration $name does not expose up(PDO)\n";
        continue;
    }
    try {
        $pdo->beginTransaction();
        up($pdo);
        $pdo->commit();
        $insert = $pdo->prepare('INSERT INTO migrations (name) VALUES (?)');
        $insert->execute([$name]);
        echo "Applied $name\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Failed $name: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Migrations complete.\n";
