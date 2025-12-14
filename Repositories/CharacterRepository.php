<?php

/**
 * CharacterRepository
 * Gère l'accès aux données de la table characters
 */
class CharacterRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée un nouveau personnage
     * @param int $partyId
     * @param string $firstname
     * @param string|null $background
     * @return int L'ID du nouveau personnage
     * @throws Exception
     */
    public function create(int $partyId, string $firstname, ?string $background = null): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO characters (party_id, firstname, background) VALUES (?, ?, ?)'
        );
        $statement->execute([$partyId, $firstname, $background]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Trouve tous les personnages d'une partie
     * @param int $partyId
     * @return array
     */
    public function findByPartyId(int $partyId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM characters WHERE party_id = ? ORDER BY id ASC');
        $statement->execute([$partyId]);
        return $statement->fetchAll();
    }

    /**
     * Trouve un personnage par ID
     * @param int $characterId
     * @return array|null
     */
    public function findById(int $characterId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM characters WHERE id = ?');
        $statement->execute([$characterId]);
        $character = $statement->fetch();
        return $character ?: null;
    }

    /**
     * Met à jour un personnage
     * @param int $characterId
     * @param array $data
     * @return bool
     */
    public function update(int $characterId, array $data): bool
    {
        $allowedFields = ['firstname', 'background'];
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
        
        $values[] = $characterId;
        $sql = 'UPDATE characters SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($values);
    }

    /**
     * Supprime un personnage
     * @param int $characterId
     * @return bool
     */
    public function delete(int $characterId): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM characters WHERE id = ?');
        return $statement->execute([$characterId]);
    }

    /**
     * Compte le nombre de personnages dans une partie
     * @param int $partyId
     * @return int
     */
    public function countByPartyId(int $partyId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM characters WHERE party_id = ?');
        $statement->execute([$partyId]);
        return (int) $statement->fetchColumn();
    }

    /**
     * Vérifie qu'un personnage appartient à une partie spécifique
     * @param int $characterId
     * @param int $partyId
     * @return bool
     */
    public function belongsToParty(int $characterId, int $partyId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM characters WHERE id = ? AND party_id = ?');
        $statement->execute([$characterId, $partyId]);
        return (bool) $statement->fetch();
    }
}
