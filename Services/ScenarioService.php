<?php
require_once __DIR__ . '/../Database/DatabaseService.php';

class ScenarioService
{
    private PDO $pdo;
    private DatabaseService $db;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->db = new DatabaseService($pdo);
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
        $stmt = $this->pdo->prepare('SELECT id FROM game_types WHERE id = ?');
        $stmt->execute([$gameTypeId]);
        if (!$stmt->fetch()) {
            throw new InvalidArgumentException('Game type not found');
        }

        $this->pdo->beginTransaction();
        try {
            $insert = $this->pdo->prepare('INSERT INTO murder_parties (game_type_id, theme, synopsis, status) VALUES (?, ?, ?, ?)');
            $insert->execute([$gameTypeId, $theme ?: null, $synopsis ?: null, 'draft']);
            $id = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
