<?php

/**
 * RelationRepository
 * Gère l'accès aux données de la table relations
 */
class RelationRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée une nouvelle relation entre personnages
     * @param int $characterId
     * @param int $targetCharacterId
     * @param string $relationType
     * @param string|null $description
     * @return int L'ID de la nouvelle relation
     * @throws Exception
     */
    public function create(int $characterId, int $targetCharacterId, string $relationType, ?string $description = null): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO relations (character_id, target_character_id, relation_type, description) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$characterId, $targetCharacterId, $relationType, $description]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Trouve toutes les relations d'un personnage
     * @param int $characterId
     * @return array
     */
    public function findByCharacterId(int $characterId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.*, c.firstname as target_firstname 
             FROM relations r 
             INNER JOIN characters c ON r.target_character_id = c.id 
             WHERE r.character_id = ?'
        );
        $statement->execute([$characterId]);
        return $statement->fetchAll();
    }

    /**
     * Trouve toutes les relations d'une partie (via ses personnages)
     * @param int $partyId
     * @return array
     */
    public function findByPartyId(int $partyId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.*, c1.firstname as character_firstname, c2.firstname as target_firstname
             FROM relations r
             INNER JOIN characters c1 ON r.character_id = c1.id
             INNER JOIN characters c2 ON r.target_character_id = c2.id
             WHERE c1.party_id = ?'
        );
        $statement->execute([$partyId]);
        return $statement->fetchAll();
    }

    /**
     * Trouve une relation par ID
     * @param int $relationId
     * @return array|null
     */
    public function findById(int $relationId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM relations WHERE id = ?');
        $statement->execute([$relationId]);
        $relation = $statement->fetch();
        return $relation ?: null;
    }

    /**
     * Supprime une relation
     * @param int $relationId
     * @return bool
     */
    public function delete(int $relationId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM relations WHERE id = ?');
        return $statement->execute([$relationId]);
    }

    /**
     * Met à jour une relation
     * @param int $relationId
     * @param array $data
     * @return bool
     */
    public function update(int $relationId, array $data): bool
    {
        $allowedFields = ['relation_type', 'description'];
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
        
        $values[] = $relationId;
        $sql = 'UPDATE relations SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($values);
    }
}
