-- ========================================
-- SQL COMPLET - Murder Party Maker
-- Isolation des données et gestion des relations
-- ========================================

-- ----------------------------------------
-- 1. AJOUT DE user_id À murder_parties
-- ----------------------------------------
-- Cette modification assure l'isolation des données par utilisateur

ALTER TABLE murder_parties 
ADD COLUMN user_id INT UNSIGNED NOT NULL,
ADD INDEX idx_user_id (user_id),
ADD CONSTRAINT fk_murder_parties_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- ----------------------------------------
-- 2. TABLE characters (Personnages)
-- ----------------------------------------
-- Stocke les personnages de chaque partie

CREATE TABLE IF NOT EXISTS characters (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  party_id INT UNSIGNED NOT NULL,
  firstname VARCHAR(255) NOT NULL,
  background TEXT COMMENT 'Histoire du personnage',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_party_id (party_id),
  CONSTRAINT fk_characters_party 
    FOREIGN KEY (party_id) REFERENCES murder_parties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------
-- 3. TABLE relations (Relations entre personnages)
-- ----------------------------------------
-- Stocke les relations entre les personnages (Amant, Rival, etc.)

CREATE TABLE IF NOT EXISTS relations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  character_id INT UNSIGNED NOT NULL COMMENT 'Personnage source',
  target_character_id INT UNSIGNED NOT NULL COMMENT 'Personnage cible',
  relation_type VARCHAR(100) NOT NULL COMMENT 'Type: Amant, Rival, Frère, etc.',
  description TEXT COMMENT 'Description détaillée de la relation',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_character_id (character_id),
  INDEX idx_target_character_id (target_character_id),
  CONSTRAINT fk_relations_character 
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
  CONSTRAINT fk_relations_target 
    FOREIGN KEY (target_character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- REQUÊTES UTILES (EXEMPLES)
-- ========================================

-- ----------------------------------------
-- Dashboard : Récupérer les parties en cours avec le nombre de joueurs
-- ----------------------------------------
SELECT 
    mp.id,
    mp.theme,
    mp.synopsis,
    mp.status,
    mp.created_at,
    mp.updated_at,
    COUNT(c.id) as character_count
FROM murder_parties mp
LEFT JOIN characters c ON mp.id = c.party_id
WHERE mp.user_id = :user_id 
  AND mp.status = 'draft'
GROUP BY mp.id
ORDER BY mp.updated_at DESC;

-- ----------------------------------------
-- Récupérer tous les personnages d'une partie (SÉCURISÉ)
-- ----------------------------------------
SELECT c.*
FROM characters c
INNER JOIN murder_parties mp ON c.party_id = mp.id
WHERE mp.id = :party_id 
  AND mp.user_id = :user_id
ORDER BY c.id ASC;

-- ----------------------------------------
-- Récupérer les relations d'un personnage avec les noms
-- ----------------------------------------
SELECT 
    r.id,
    r.character_id,
    r.target_character_id,
    r.relation_type,
    r.description,
    c_target.firstname as target_firstname
FROM relations r
INNER JOIN characters c_target ON r.target_character_id = c_target.id
WHERE r.character_id = :character_id;

-- ----------------------------------------
-- Récupérer toutes les relations d'une partie (SÉCURISÉ)
-- ----------------------------------------
SELECT 
    r.id,
    r.character_id,
    r.target_character_id,
    r.relation_type,
    r.description,
    c1.firstname as character_firstname,
    c2.firstname as target_firstname
FROM relations r
INNER JOIN characters c1 ON r.character_id = c1.id
INNER JOIN characters c2 ON r.target_character_id = c2.id
INNER JOIN murder_parties mp ON c1.party_id = mp.id
WHERE mp.id = :party_id 
  AND mp.user_id = :user_id;

-- ----------------------------------------
-- Vérifier qu'une partie appartient à un utilisateur
-- ----------------------------------------
SELECT COUNT(*) as has_access
FROM murder_parties
WHERE id = :party_id 
  AND user_id = :user_id;

-- ----------------------------------------
-- Créer un personnage (INSERT)
-- ----------------------------------------
INSERT INTO characters (party_id, firstname, background)
VALUES (:party_id, :firstname, :background);

-- ----------------------------------------
-- Créer une relation (INSERT)
-- ----------------------------------------
INSERT INTO relations (character_id, target_character_id, relation_type, description)
VALUES (:character_id, :target_character_id, :relation_type, :description);

-- ----------------------------------------
-- Mettre à jour le background d'un personnage
-- ----------------------------------------
UPDATE characters
SET background = :background,
    updated_at = CURRENT_TIMESTAMP
WHERE id = :character_id;

-- ----------------------------------------
-- Supprimer un personnage (supprime aussi ses relations en cascade)
-- ----------------------------------------
DELETE FROM characters
WHERE id = :character_id;

-- ----------------------------------------
-- Finaliser une partie (changer le statut)
-- ----------------------------------------
UPDATE murder_parties
SET status = 'completed',
    updated_at = CURRENT_TIMESTAMP
WHERE id = :party_id 
  AND user_id = :user_id;

-- ========================================
-- REQUÊTES DE VÉRIFICATION ET DEBUG
-- ========================================

-- Compter le nombre de parties par utilisateur
SELECT 
    user_id, 
    COUNT(*) as party_count
FROM murder_parties
GROUP BY user_id;

-- Compter le nombre de personnages par partie
SELECT 
    party_id,
    COUNT(*) as character_count
FROM characters
GROUP BY party_id;

-- Compter le nombre de relations par personnage
SELECT 
    character_id,
    COUNT(*) as relation_count
FROM relations
GROUP BY character_id;

-- Trouver les parties orphelines (sans user_id valide)
SELECT mp.id, mp.theme
FROM murder_parties mp
LEFT JOIN users u ON mp.user_id = u.id
WHERE u.id IS NULL;

-- Trouver les personnages sans partie (ne devrait jamais arriver avec FK)
SELECT c.id, c.firstname
FROM characters c
LEFT JOIN murder_parties mp ON c.party_id = mp.id
WHERE mp.id IS NULL;

-- ========================================
-- REQUÊTES D'ADMINISTRATION
-- ========================================

-- Supprimer toutes les parties d'un utilisateur
DELETE FROM murder_parties
WHERE user_id = :user_id;

-- Supprimer tous les personnages d'une partie
DELETE FROM characters
WHERE party_id = :party_id;

-- Supprimer toutes les relations d'un personnage
DELETE FROM relations
WHERE character_id = :character_id
   OR target_character_id = :character_id;

-- ========================================
-- STATISTIQUES
-- ========================================

-- Statistiques globales
SELECT 
    (SELECT COUNT(*) FROM murder_parties) as total_parties,
    (SELECT COUNT(*) FROM murder_parties WHERE status = 'draft') as draft_parties,
    (SELECT COUNT(*) FROM murder_parties WHERE status = 'completed') as completed_parties,
    (SELECT COUNT(*) FROM characters) as total_characters,
    (SELECT COUNT(*) FROM relations) as total_relations,
    (SELECT COUNT(DISTINCT user_id) FROM murder_parties) as active_users;

-- Statistiques par utilisateur
SELECT 
    u.id,
    u.email,
    COUNT(DISTINCT mp.id) as party_count,
    COUNT(c.id) as character_count,
    COUNT(r.id) as relation_count
FROM users u
LEFT JOIN murder_parties mp ON u.id = mp.user_id
LEFT JOIN characters c ON mp.id = c.party_id
LEFT JOIN relations r ON c.id = r.character_id
GROUP BY u.id, u.email
ORDER BY party_count DESC;

-- ========================================
-- FIN DU FICHIER SQL
-- ========================================
