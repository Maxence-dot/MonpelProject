<?php
require_once __DIR__ . '/../connexionAll.php';
require_once __DIR__ . '/../Database/DatabaseService.php';

header('Content-Type: application/json; charset=utf-8');

$db = new DatabaseService($pdo);
echo json_encode($db->getGameTypes());
