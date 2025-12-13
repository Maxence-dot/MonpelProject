<?php
class DatabaseService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retourne la liste des types de jeu disponibles
     * @return array
     */
    public function getGameTypes(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, description FROM game_types ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
