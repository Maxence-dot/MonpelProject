<?php
// Minimal .env loader for development. Parses KEY=VALUE lines.
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) return;
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (!strpos($line, '=')) continue;
    list($key, $val) = explode('=', $line, 2);
    $key = trim($key);
    $val = trim($val);
    // remove surrounding quotes
    $val = preg_replace('/^"(.*)"$/', '$1', $val);
    $val = preg_replace("/^'(.*)'$/", '$1', $val);
    putenv($key . '=' . $val);
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
}
