<?php
require_once __DIR__ . '/../Repositories/CharacterRepository.php';
require_once __DIR__ . '/../Repositories/PartyRepository.php';

/**
 * CharacterController
 * Gère la création et la modification des personnages
 */
class CharacterController
{
    private CharacterRepository $characterRepository;
    private PartyRepository $partyRepository;

    public function __construct(CharacterRepository $characterRepository, PartyRepository $partyRepository)
    {
        $this->characterRepository = $characterRepository;
        $this->partyRepository = $partyRepository;
    }

    /**
     * Affiche le formulaire d'ajout des personnages (Étape 2)
     */
    public function addPlayers()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $partyId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['party_id']) ? (int)$_GET['party_id'] : 0);
        $userId = (int)$_SESSION['user_id'];

        // Vérifier que la partie appartient à l'utilisateur
        $party = $this->partyRepository->findById($partyId, $userId);
        if (!$party) {
            http_response_code(404);
            echo "Partie non trouvée";
            exit;
        }

        // Charger les personnages existants
        $characters = $this->characterRepository->findByPartyId($partyId);

        require_once __DIR__ . '/../views/party/step2_add_players.php';
    }

    /**
     * Sauvegarde les personnages (API)
     */
    public function savePlayers()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $partyId = isset($data['party_id']) ? (int)$data['party_id'] : 0;
        $players = isset($data['players']) ? $data['players'] : [];
        $userId = (int)$_SESSION['user_id'];

        try {
            // Vérifier que la partie appartient à l'utilisateur
            $party = $this->partyRepository->findById($partyId, $userId);
            if (!$party) {
                throw new Exception("Partie non trouvée");
            }

            // Supprimer les personnages existants pour cette partie
            $existingCharacters = $this->characterRepository->findByPartyId($partyId);
            foreach ($existingCharacters as $char) {
                $this->characterRepository->delete($char['id']);
            }

            // Créer les nouveaux personnages
            foreach ($players as $player) {
                $firstname = trim($player['firstname'] ?? '');
                if (!empty($firstname)) {
                    $this->characterRepository->create($partyId, $firstname);
                }
            }

            echo json_encode(['success' => true, 'party_id' => $partyId]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Affiche l'interface de gestion des relations et backgrounds (Étape 3)
     */
    public function manageRelations()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $partyId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['party_id']) ? (int)$_GET['party_id'] : 0);
        $userId = (int)$_SESSION['user_id'];

        // Vérifier que la partie appartient à l'utilisateur
        $party = $this->partyRepository->findById($partyId, $userId);
        if (!$party) {
            http_response_code(404);
            echo "Partie non trouvée";
            exit;
        }

        // Charger les personnages
        $characters = $this->characterRepository->findByPartyId($partyId);

        require_once __DIR__ . '/../views/party/step3_relations.php';
    }

    /**
     * Ajoute un nouveau personnage (API)
     */
    public function addCharacter()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $partyId = isset($data['party_id']) ? (int)$data['party_id'] : 0;
        $firstname = trim($data['firstname'] ?? '');
        $userId = (int)$_SESSION['user_id'];

        try {
            // Vérifier que la partie appartient à l'utilisateur
            $party = $this->partyRepository->findById($partyId, $userId);
            if (!$party) {
                throw new Exception("Partie non trouvée");
            }

            if (empty($firstname)) {
                throw new Exception("Le prénom est requis");
            }

            $characterId = $this->characterRepository->create($partyId, $firstname);
            $character = $this->characterRepository->findById($characterId);

            echo json_encode(['success' => true, 'character' => $character]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Met à jour un personnage (API)
     */
    public function updateCharacter()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $characterId = isset($data['character_id']) ? (int)$data['character_id'] : 0;
        $userId = (int)$_SESSION['user_id'];

        try {
            $character = $this->characterRepository->findById($characterId);
            if (!$character) {
                throw new Exception("Personnage non trouvé");
            }

            // Vérifier que la partie appartient à l'utilisateur
            $party = $this->partyRepository->findById($character['party_id'], $userId);
            if (!$party) {
                throw new Exception("Accès refusé");
            }

            $updateData = [];
            if (isset($data['firstname'])) {
                $updateData['firstname'] = trim($data['firstname']);
            }
            if (isset($data['background'])) {
                $updateData['background'] = trim($data['background']);
            }

            $this->characterRepository->update($characterId, $updateData);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Supprime un personnage (API)
     */
    public function deleteCharacter()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $characterId = isset($data['character_id']) ? (int)$data['character_id'] : 0;
        $userId = (int)$_SESSION['user_id'];

        try {
            $character = $this->characterRepository->findById($characterId);
            if (!$character) {
                throw new Exception("Personnage non trouvé");
            }

            // Vérifier que la partie appartient à l'utilisateur
            $party = $this->partyRepository->findById($character['party_id'], $userId);
            if (!$party) {
                throw new Exception("Accès refusé");
            }

            $this->characterRepository->delete($characterId);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
