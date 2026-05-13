-- ════════════════════════════════════════════════════════════════════════════
-- MIGRATION SQL — NutriNova Reset Password + Google OAuth
-- Exécuter dans phpMyAdmin ou MySQL CLI une seule fois
-- ════════════════════════════════════════════════════════════════════════════

-- Ajouter reset_token (stocke le token de réinitialisation)
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS reset_token   VARCHAR(64) DEFAULT NULL;

-- Ajouter reset_expires (date d'expiration du token, 1 heure)
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS reset_expires DATETIME    DEFAULT NULL;

-- Ajouter google_id (identifiant Google OAuth)
ALTER TABLE utilisateur
    ADD COLUMN IF NOT EXISTS google_id     VARCHAR(64) DEFAULT NULL;

-- Index pour les recherches rapides
CREATE INDEX IF NOT EXISTS idx_reset_token ON utilisateur(reset_token);
CREATE INDEX IF NOT EXISTS idx_google_id   ON utilisateur(google_id);

-- Vérification : afficher la structure finale
DESCRIBE utilisateur;
