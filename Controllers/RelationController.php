<?php
require_once __DIR__ . '/../Repositories/RelationRepository.php';
require_once __DIR__ . '/../Repositories/CharacterRepository.php';
require_once __DIR__ . '/../Repositories/PartyRepository.php';

/**
 * RelationController
 * Gère les relations entre personnages
 */
class RelationController
{
    private RelationRepository $relationRepository;
    private CharacterRepository $characterRepository;
    private PartyRepository $partyRepository;

    public function __construct(
        RelationRepository $relationRepository, 
        CharacterRepository $characterRepository,
        PartyRepository $partyRepository
    ) {
        $this->relationRepository = $relationRepository;
        $this->characterRepository = $characterRepository;
        $this->partyRepository = $partyRepository;
    }

    /**
     * Crée une nouvelle relation (API)
     */
    public function create()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $characterId = isset($data['character_id']) ? (int)$data['character_id'] : 0;
        $targetCharacterId = isset($data['target_character_id']) ? (int)$data['target_character_id'] : 0;
        $relationType = trim($data['relation_type'] ?? '');
        $description = trim($data['description'] ?? '');
        $userId = (int)$_SESSION['user_id'];

        try {
            // Vérifier que les personnages existent
            $character = $this->characterRepository->findById($characterId);
            $targetCharacter = $this->characterRepository->findById($targetCharacterId);
            
            if (!$character || !$targetCharacter) {
                throw new Exception("Personnage non trouvé");
            }

            // Vérifier que les deux personnages appartiennent à la même partie
            if ($character['party_id'] !== $targetCharacter['party_id']) {
                throw new Exception("Les personnages doivent appartenir à la même partie");
            }

            // Vérifier que la partie appartient à l'utilisateur
            $party = $this->partyRepository->findById($character['party_id'], $userId);
            if (!$party) {
                throw new Exception("Accès refusé");
            }

            if (empty($relationType)) {
                throw new Exception("Le type de relation est requis");
            }

            $relationId = $this->relationRepository->create($characterId, $targetCharacterId, $relationType, $description ?: null);
            $relation = $this->relationRepository->findById($relationId);

            echo json_encode(['success' => true, 'relation' => $relation]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Récupère les relations d'un personnage (API)
     */
    public function getByCharacter()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $characterId = isset($_GET['character_id']) ? (int)$_GET['character_id'] : 0;
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

            $relations = $this->relationRepository->findByCharacterId($characterId);

            echo json_encode(['success' => true, 'relations' => $relations]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Supprime une relation (API)
     */
    public function delete()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $relationId = isset($data['relation_id']) ? (int)$data['relation_id'] : 0;
        $userId = (int)$_SESSION['user_id'];

        try {
            $relation = $this->relationRepository->findById($relationId);
            if (!$relation) {
                throw new Exception("Relation non trouvée");
            }

            // Vérifier que le personnage source appartient à l'utilisateur
            $character = $this->characterRepository->findById($relation['character_id']);
            $party = $this->partyRepository->findById($character['party_id'], $userId);
            if (!$party) {
                throw new Exception("Accès refusé");
            }

            $this->relationRepository->delete($relationId);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
