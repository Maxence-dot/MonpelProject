<?php
// Simple router: map public paths to implementation files under views/ or root handlers
// Keeps a safe allowlist of routes to avoid arbitrary file includes.

// Load database connection
require_once __DIR__ . '/connexionAll.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$req = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $req), '/');

// Build routes dynamically by scanning views/
$routes = [];
$viewsDir = __DIR__ . '/views';
if (is_dir($viewsDir)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        if (strtolower($file->getExtension()) !== 'php') continue;
        $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($viewsDir) + 1)); // e.g. auth/login.php
        $basename = basename($rel); // login.php
        $noExt = preg_replace('/\.php$/i', '', $basename); // login

        // map basename (login.php) -> views/auth/login.php
        if (!isset($routes[$basename])) $routes[$basename] = 'views/' . $rel;
        // map basename without extension (login) -> views/auth/login.php
        if (!isset($routes[$noExt])) $routes[$noExt] = 'views/' . $rel;
        // map path relative (auth/login.php) -> views/auth/login.php
        if (!isset($routes[$rel])) $routes[$rel] = 'views/' . $rel;
        // map path relative without ext (auth/login) -> views/auth/login.php
        $relNoExt = preg_replace('/\.php$/i', '', $rel);
        if (!isset($routes[$relNoExt])) $routes[$relNoExt] = 'views/' . $rel;

        // special: index -> root
        if (strtolower($basename) === 'index.php') {
            if (!isset($routes[''])) $routes[''] = 'views/' . $rel;
            if (!isset($routes['index'])) $routes['index'] = 'views/' . $rel;
            if (!isset($routes['index.php'])) $routes['index.php'] = 'views/' . $rel;
        }
    }
}

// Add explicit non-view routes
$routes['migrate.php'] = 'admin/migrate.php';
$routes['admin/migrate.php'] = 'admin/migrate.php';

// Serve static files from assets/ folder (CSS, JS, images, etc.)
if (preg_match('#^assets/(.+)$#', $path, $m)) {
    $assetFile = __DIR__ . '/assets/' . $m[1];
    if (file_exists($assetFile)) {
        // Set appropriate content type based on file extension
        $ext = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ];
        if (isset($contentTypes[$ext])) {
            header('Content-Type: ' . $contentTypes[$ext]);
        }
        readfile($assetFile);
        exit;
    }
}

// API routes - map api/* to api/ folder
if (preg_match('#^api/([^/]+)$#', $path, $m)) {
    $apiFile = __DIR__ . '/api/' . $m[1];
    if (file_exists($apiFile)) {
        require $apiFile;
        exit;
    }
}

$pathKey = $path === '' ? '' : $path;

// Controller mappings (prefer controllers over raw views when present)
$controllerMap = [
    '' => ['file' => 'Controllers/DashboardController.php', 'method' => 'index'],
    'dashboard' => ['file' => 'Controllers/DashboardController.php', 'method' => 'index'],
    'dashboard.php' => ['file' => 'Controllers/DashboardController.php', 'method' => 'index'],
    'party/step2' => ['file' => 'Controllers/CharacterController.php', 'method' => 'addPlayers'],
    'party/step3' => ['file' => 'Controllers/CharacterController.php', 'method' => 'manageRelations'],
    'login' => ['file' => 'Controllers/AuthController.php', 'method' => 'login'],
    'login.php' => ['file' => 'Controllers/AuthController.php', 'method' => 'login'],
    'register' => ['file' => 'Controllers/AuthController.php', 'method' => 'register'],
    'register.php' => ['file' => 'Controllers/AuthController.php', 'method' => 'register'],
    'logout' => ['file' => 'Controllers/AuthController.php', 'method' => 'logout'],
    'logout.php' => ['file' => 'Controllers/AuthController.php', 'method' => 'logout'],
];

// If a controller mapping exists for the requested path, dispatch it
if (isset($controllerMap[$pathKey])) {
    $c = $controllerMap[$pathKey];
    $controllerFile = __DIR__ . '/' . $c['file'];
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $method = $c['method'];
        
        // Extract class name from file path
        $className = basename($c['file'], '.php');
        
        // Special handling based on controller type
        if ($className === 'DashboardController') {
            require_once __DIR__ . '/Repositories/PartyRepository.php';
            $partyRepo = new PartyRepository($pdo);
            $controller = new DashboardController($partyRepo);
            $controller->$method();
            exit;
        } elseif ($className === 'CharacterController') {
            require_once __DIR__ . '/Repositories/PartyRepository.php';
            require_once __DIR__ . '/Repositories/CharacterRepository.php';
            $partyRepo = new PartyRepository($pdo);
            $characterRepo = new CharacterRepository($pdo);
            $controller = new CharacterController($characterRepo, $partyRepo);
            $controller->$method();
            exit;
        } elseif ($className === 'AuthController') {
            // AuthController uses static methods
            if (is_callable(['\\' . $className, $method])) {
                ('\\' . $className)::$method();
                exit;
            }
        }
    }
}

// $pathKey already normalized above

if (isset($routes[$pathKey])) {
    $target = __DIR__ . '/' . $routes[$pathKey];
    if (file_exists($target)) {
        include $target;
        exit;
    }
}

// No match
http_response_code(404);
echo "404 Not Found";
exit;
