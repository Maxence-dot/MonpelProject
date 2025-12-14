<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';
require_once __DIR__ . '/../Controllers/PartyController.php';
require_once __DIR__ . '/../Services/ScenarioService.php';

$service = new ScenarioService($pdo);
$controller = new PartyController($service);

$action = $_GET['action'] ?? 'create';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'finish':
        $controller->finish();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Action not found']);
        break;
}
