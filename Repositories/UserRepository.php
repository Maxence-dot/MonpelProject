<?php

/**
 * UserRepository
 * Gère l'accès aux données de la table users
 */
class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Trouve un utilisateur par email
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $statement->execute([$email]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    /**
     * Vérifie si un email existe déjà
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $statement->execute([$email]);
        return (bool) $statement->fetch();
    }

    /**
     * Crée un nouvel utilisateur
     * @param string $email
     * @param string $hashedPassword
     * @param string|null $firstname
     * @param string|null $lastname
     * @return int L'ID du nouvel utilisateur
     * @throws Exception
     */
    public function create(string $email, string $hashedPassword, ?string $firstname = null, ?string $lastname = null): int
    {
        $now = date('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            "INSERT INTO users (email, password, firstname, lastname, created_at) VALUES (?,?,?,?,?)"
        );
        $statement->execute([$email, $hashedPassword, $firstname, $lastname, $now]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Trouve un utilisateur par ID
     * @param int $userId
     * @return array|null
     */
    public function findById(int $userId): ?array
    {
        $statement = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $statement->execute([$userId]);
        $user = $statement->fetch();
        return $user ?: null;
    }
}
