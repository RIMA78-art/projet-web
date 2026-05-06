-- ════════════════════════════════════════════════════════════════════════════
-- MIGRATION SQL — NutriNova User Scoring & Status Badge
-- Ajouter les colonnes nécessaires pour le système de scoring
-- ════════════════════════════════════════════════════════════════════════════

-- Ajouter last_login (dernière connexion de l'utilisateur)
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;

-- Ajouter last_weight_update (dernière mise à jour du poids)
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS last_weight_update DATETIME DEFAULT NULL;

-- Mettre à jour last_weight_update lors de la création (maintenant)
UPDATE utilisateur SET last_weight_update = date_inscription WHERE last_weight_update IS NULL;

-- Index pour les recherches rapides sur les dates
CREATE INDEX IF NOT EXISTS idx_last_login ON utilisateur(last_login);
CREATE INDEX IF NOT EXISTS idx_last_weight_update ON utilisateur(last_weight_update);

-- Vérification : afficher la structure finale
DESCRIBE utilisateur;
