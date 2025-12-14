<?php
// Web migration runner (protected by token in config.php -> MIGRATION_TOKEN)
require_once __DIR__ . '/../connexionAll.php';

$cfg = $cfg ?? (file_exists(__DIR__ . '/../config.php') ? require __DIR__ . '/../config.php' : []);
$token = $cfg['MIGRATION_TOKEN'] ?? getenv('MIGRATION_TOKEN') ?? null;

// Basic HTML header
function h($s) { return htmlspecialchars($s, ENT_QUOTES); }

if (!$token) {
    http_response_code(403);
    echo "<h1>Migration runner</h1><p>MIGRATION_TOKEN not configured. Set it in config.php and try again.</p>";
    exit;
}

$provided = $_REQUEST['token'] ?? null;
if (!hash_equals((string)$token, (string)$provided)) {
    http_response_code(403);
    echo "<h1>Migration runner</h1><p>Invalid token.</p>";
    exit;
}

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

$appliedStmt = $pdo->query('SELECT name FROM migrations');
$applied = [];
foreach ($appliedStmt->fetchAll(PDO::FETCH_ASSOC) as $r) $applied[$r['name']] = true;

if (isset($_POST['run'])) {
    $results = [];
    foreach ($migrations as $file) {
        $name = basename($file);
        if (isset($applied[$name])) {
            $results[] = ['name' => $name, 'status' => 'already'];
            continue;
        }
          include $file;
          $base = pathinfo($file, PATHINFO_FILENAME);
          $candidate = 'up_' . preg_replace('/[^A-Za-z0-9_]/', '_', $base);
          if (function_exists($candidate)) {
            $upFn = $candidate;
          } elseif (function_exists('up')) {
            $upFn = 'up';
          } else {
            $results[] = ['name' => $name, 'status' => 'error', 'message' => 'No up(PDO) function'];
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
          $results[] = ['name' => $name, 'status' => 'applied'];
        } catch (Exception $e) {
          if ($pdo->inTransaction()) {
            $pdo->rollBack();
          }
          $results[] = ['name' => $name, 'status' => 'failed', 'message' => $e->getMessage()];
        }
    }
}

// Render HTML
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Runner de migrations</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .applied { color: green; }
    .error { color: red; }
    .pending { color: orange; }
    table { border-collapse: collapse; width:100%; max-width: 900px; }
    th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
    button { padding: 10px 12px; font-size: 16px; border-radius: 6px; cursor:pointer; }
  </style>
</head>
<body>
  <h1>Runner de migrations</h1>
  <p>Recherche des fichiers de migration dans <strong>migrations/</strong>.</p>

  <form method="post" action="<?php echo h($_SERVER['REQUEST_URI']); ?>">
    <input type="hidden" name="token" value="<?php echo h($provided); ?>" />
    <table>
      <thead><tr><th>Fichier</th><th>Statut</th><th>Détails</th></tr></thead>
      <tbody>
        <?php foreach ($migrations as $file):
            $name = basename($file);
            $isApplied = isset($applied[$name]);
            $status = $isApplied ? 'applied' : 'pending';
        ?>
          <tr>
            <td><?php echo h($name); ?></td>
            <td class="<?php echo ($isApplied ? 'applied' : 'pending'); ?>"><?php echo ($isApplied ? 'Déjà appliquée' : 'En attente'); ?></td>
            <td><?php
                if (isset($results)) {
                    foreach ($results as $r) {
                        if ($r['name'] === $name) {
                            echo h($r['status']);
                            if (!empty($r['message'])) echo ': ' . h($r['message']);
                        }
                    }
                }
            ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>
      <button type="submit" name="run">Exécuter les migrations (token fourni)</button>
    </p>
  </form>
  <p>Important : protégez cette URL (paramètre <code>token</code> requis). Mettez à jour <code>config.php</code> ou votre variable d'environnement <code>MIGRATION_TOKEN</code>.</p>
</body>
</html>
