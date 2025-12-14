<?php
/**
 * Migration: Ajouter la contrainte FK user_id sur murder_parties après création de users
 */

function up_20251218_add_fk_murder_parties_user(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver !== 'sqlite') {
        // Vérifier si la contrainte existe déjà
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_NAME = 'murder_parties' 
            AND CONSTRAINT_NAME = 'fk_murder_parties_user'
            AND TABLE_SCHEMA = DATABASE()
        ");
        
        $constraintExists = $stmt->rowCount() > 0;
        
        if (!$constraintExists) {
            // Vérifier que la table users existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $usersExists = $stmt->rowCount() > 0;
            
            if ($usersExists) {
                $pdo->exec(<<<'SQL'
ALTER TABLE murder_parties 
ADD CONSTRAINT fk_murder_parties_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
SQL
                );
            }
        }
    }
}

function down_20251218_add_fk_murder_parties_user(PDO $pdo)
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    
    if ($driver !== 'sqlite') {
        $pdo->exec(<<<'SQL'
ALTER TABLE murder_parties 
DROP FOREIGN KEY IF EXISTS fk_murder_parties_user;
SQL
        );
    }
}
