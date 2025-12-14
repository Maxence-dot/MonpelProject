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
     * @param int $userId
     * @param int $gameTypeId
     * @param string|null $theme
     * @param string|null $synopsis
     * @param string $status
     * @return int L'ID de la nouvelle partie
     * @throws Exception
     */
    public function create(int $userId, int $gameTypeId, ?string $theme = null, ?string $synopsis = null, string $status = 'draft'): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO murder_parties (user_id, game_type_id, theme, synopsis, status) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute([$userId, $gameTypeId, $theme, $synopsis, $status]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Trouve une partie par ID pour un utilisateur spécifique (sécurisé)
     * @param int $partyId
     * @param int $userId
     * @return array|null
     */
    public function findById(int $partyId, int $userId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM murder_parties WHERE id = ? AND user_id = ?');
        $statement->execute([$partyId, $userId]);
        $party = $statement->fetch();
        return $party ?: null;
    }

    /**
     * Met à jour une partie (sécurisé)
     * @param int $partyId
     * @param int $userId
     * @param array $data Tableau associatif des champs à mettre à jour
     * @return bool
     */
    public function update(int $partyId, int $userId, array $data): bool
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
        $values[] = $userId;
        $sql = 'UPDATE murder_parties SET ' . implode(', ', $fields) . ' WHERE id = ? AND user_id = ?';
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
     * Liste toutes les parties d'un utilisateur (avec pagination optionnelle)
     * @param int $userId
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function findAll(int $userId, ?int $limit = null, int $offset = 0): array
    {
        $sql = 'SELECT * FROM murder_parties WHERE user_id = ? ORDER BY created_at DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$userId, $limit, $offset]);
        } else {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([$userId]);
        }
        return $statement->fetchAll();
    }

    /**
     * Liste les parties en brouillon (draft) avec le nombre de personnages
     * @param int $userId
     * @return array
     */
    public function findDraftsWithCharacterCount(int $userId): array
    {
        $sql = <<<'SQL'
SELECT 
    mp.*,
    COUNT(c.id) as character_count
FROM murder_parties mp
LEFT JOIN characters c ON mp.id = c.party_id
WHERE mp.user_id = ?
GROUP BY mp.id
ORDER BY mp.updated_at DESC
SQL;
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    /**
     * Supprime une partie (sécurisé)
     * @param int $partyId
     * @param int $userId
     * @return bool
     */
    public function delete(int $partyId, int $userId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM murder_parties WHERE id = ? AND user_id = ?');
        return $statement->execute([$partyId, $userId]);
    }
}
