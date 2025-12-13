<?php
require_once __DIR__ . '/../Services/ScenarioService.php';

class PartyController
{
    private ScenarioService $service;

    public function __construct(ScenarioService $service)
    {
        $this->service = $service;
    }

    public function create()
    {
        header('Content-Type: application/json; charset=utf-8');
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $gameTypeId = isset($data['game_type_id']) ? (int)$data['game_type_id'] : 0;
        $theme = isset($data['theme']) ? trim($data['theme']) : '';
        $synopsis = isset($data['synopsis']) ? trim($data['synopsis']) : null;

        try {
            $id = $this->service->createInitialParty($gameTypeId, $theme, $synopsis);
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
