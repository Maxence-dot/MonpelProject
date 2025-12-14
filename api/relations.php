<?php
session_start();
require_once __DIR__ . '/../connexionAll.php';
require_once __DIR__ . '/../Controllers/RelationController.php';
require_once __DIR__ . '/../Repositories/RelationRepository.php';
require_once __DIR__ . '/../Repositories/CharacterRepository.php';
require_once __DIR__ . '/../Repositories/PartyRepository.php';

$relationRepo = new RelationRepository($pdo);
$characterRepo = new CharacterRepository($pdo);
$partyRepo = new PartyRepository($pdo);
$controller = new RelationController($relationRepo, $characterRepo, $partyRepo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'get':
        $controller->getByCharacter();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Action not found']);
        break;
}
