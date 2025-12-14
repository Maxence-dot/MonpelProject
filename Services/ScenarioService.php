<?php
require_once __DIR__ . '/../Database/DatabaseService.php';
require_once __DIR__ . '/../Repositories/PartyRepository.php';

class ScenarioService
{
    private PDO $pdo;
    private DatabaseService $db;
    private PartyRepository $partyRepo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->db = new DatabaseService($pdo);
        $this->partyRepo = new PartyRepository($pdo);
    }

    /**
     * Crée une nouvelle partie initiale et retourne son ID
     * @param int $gameTypeId
     * @param string $theme
     * @param string|null $synopsis
     * @return int
     * @throws Exception
     */
    public function createInitialParty(int $gameTypeId, string $theme, ?string $synopsis): int
    {
        // Validation simple
        if ($gameTypeId <= 0) {
            throw new InvalidArgumentException('Invalid game type id');
        }
        if (strlen($theme) > 255) {
            throw new InvalidArgumentException('Theme too long');
        }

        // Ensure game type exists
        if (!$this->partyRepo->gameTypeExists($gameTypeId)) {
            throw new InvalidArgumentException('Game type not found');
        }

        $this->pdo->beginTransaction();
        try {
            $id = $this->partyRepo->create($gameTypeId, $theme ?: null, $synopsis ?: null, 'draft');
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
