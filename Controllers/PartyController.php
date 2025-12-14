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
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $userId = (int)$_SESSION['user_id'];
        $gameTypeId = isset($data['game_type_id']) ? (int)$data['game_type_id'] : 0;
        $theme = isset($data['theme']) ? trim($data['theme']) : '';
        $synopsis = isset($data['synopsis']) ? trim($data['synopsis']) : null;

        try {
            $id = $this->service->createInitialParty($userId, $gameTypeId, $theme, $synopsis);
            echo json_encode(['success' => true, 'id' => $id]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Finalise une partie (passe en statut 'completed')
     */
    public function finish()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $partyId = isset($data['party_id']) ? (int)$data['party_id'] : 0;
        $userId = (int)$_SESSION['user_id'];

        try {
            $partyRepo = new PartyRepository($this->service->getPdo());
            $party = $partyRepo->findById($partyId, $userId);
            
            if (!$party) {
                throw new Exception("Partie non trouvée");
            }

            $partyRepo->update($partyId, $userId, ['status' => 'completed']);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
