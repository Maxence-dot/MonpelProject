<?php

/**
 * PartyRepository
 * Gère l'accès aux données de la table murder_parties
 */
class PartyRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée une nouvelle partie
     * @param int $gameTypeId
     * @param string|null $theme
     * @param string|null $synopsis
     * @param string $status
     * @return int L'ID de la nouvelle partie
     * @throws Exception
     */
    public function create(int $gameTypeId, ?string $theme = null, ?string $synopsis = null, string $status = 'draft'): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO murder_parties (game_type_id, theme, synopsis, status) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$gameTypeId, $theme, $synopsis, $status]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Trouve une partie par ID
     * @param int $partyId
     * @return array|null
     */
    public function findById(int $partyId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM murder_parties WHERE id = ?');
        $statement->execute([$partyId]);
        $party = $statement->fetch();
        return $party ?: null;
    }

    /**
     * Met à jour une partie
     * @param int $partyId
     * @param array $data Tableau associatif des champs à mettre à jour
     * @return bool
     */
    public function update(int $partyId, array $data): bool
    {
        $allowedFields = ['game_type_id', 'theme', 'synopsis', 'status'];
        $fields = [];
        $values = [];
        
        foreach ($data as $fieldName => $fieldValue) {
            if (in_array($fieldName, $allowedFields)) {
                $fields[] = "$fieldName = ?";
                $values[] = $fieldValue;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $partyId;
        $sql = 'UPDATE murder_parties SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($values);
    }

    /**
     * Vérifie si un game_type existe
     * @param int $gameTypeId
     * @return bool
     */
    public function gameTypeExists(int $gameTypeId): bool
    {
        $statement = $this->pdo->prepare('SELECT id FROM game_types WHERE id = ?');
        $statement->execute([$gameTypeId]);
        return (bool) $statement->fetch();
    }

    /**
     * Liste toutes les parties (avec pagination optionnelle)
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT * FROM murder_parties ORDER BY created_at DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$limit, $offset]);
        } else {
            $statement = $this->pdo->query($sql);
        }
        return $statement->fetchAll();
    }
}
