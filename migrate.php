<?php
// Convenience redirect to the admin web migration runner.
// Usage: /migrate.php?token=YOUR_TOKEN

if (!file_exists(__DIR__ . '/admin/migrate.php')) {
    http_response_code(404);
    echo "Migration runner not found (admin/migrate.php missing).";
    exit;
}

$token = isset($_GET['token']) ? urlencode($_GET['token']) : '';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$location = $basePath . '/admin/migrate.php' . ($token !== '' ? '?token=' . $token : '');
header('Location: ' . $location);
exit;
