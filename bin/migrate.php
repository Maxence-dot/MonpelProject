<?php
// Simple migration runner for this small project.
require_once __DIR__ . '/../connexionAll.php';

// Ensure migrations table (driver-aware)
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );"
    );
} else {
    // MySQL / MariaDB compatible
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE,
            applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    );
}

$migrations = glob(__DIR__ . '/../migrations/*.php');
sort($migrations);

// Track failures so we can attempt all migrations and summarize at the end
$failed = false;
$failures = [];

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
    $base = pathinfo($file, PATHINFO_FILENAME);
    $candidate = 'up_' . preg_replace('/[^A-Za-z0-9_]/', '_', $base);
    if (function_exists($candidate)) {
        $upFn = $candidate;
    } elseif (function_exists('up')) {
        $upFn = 'up';
    } else {
        echo "Migration $name does not expose up(PDO)\n";
        continue;
    }
    try {
        $pdo->beginTransaction();
        $upFn($pdo);
        // Some DB engines (MySQL/MariaDB) may perform implicit commits on DDL,
        // which means the transaction can be closed by the time we call commit().
        // Check before committing to avoid "There is no active transaction".
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
        $insert = $pdo->prepare('INSERT INTO migrations (name) VALUES (?)');
        $insert->execute([$name]);
        echo "Applied $name\n";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "Failed $name: " . $e->getMessage() . "\n";
        $failed = true;
        $failures[] = ['name' => $name, 'error' => $e->getMessage()];
        // continue to next migration instead of exiting
        continue;
    }
}

if ($failed) {
    echo "Migrations finished with errors:\n";
    foreach ($failures as $f) {
        echo " - {$f['name']}: {$f['error']}\n";
    }
    exit(1);
}

echo "Migrations complete.\n";
