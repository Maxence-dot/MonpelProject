<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';
require_once __DIR__ . '/../Controllers/CharacterController.php';
require_once __DIR__ . '/../Repositories/CharacterRepository.php';
require_once __DIR__ . '/../Repositories/PartyRepository.php';

$characterRepo = new CharacterRepository($pdo);
$partyRepo = new PartyRepository($pdo);
$controller = new CharacterController($characterRepo, $partyRepo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'save':
        $controller->savePlayers();
        break;
    case 'update':
        $controller->updateCharacter();
        break;
    case 'delete':
        $controller->deleteCharacter();
        break;
    case 'add':
        $controller->addCharacter();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Action not found']);
        break;
}
