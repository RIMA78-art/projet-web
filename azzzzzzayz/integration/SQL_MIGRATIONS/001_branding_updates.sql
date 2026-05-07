-- 001_branding_updates.sql
USE gest_sportt;

ALTER TABLE programmes ADD COLUMN IF NOT EXISTS coach_id INT NULL;
ALTER TABLE programmes ADD COLUMN IF NOT EXISTS popularite INT DEFAULT 1;

CREATE TABLE IF NOT EXISTS user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label_badge VARCHAR(150) NOT NULL,
    obtenu_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_badge (user_id, label_badge),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_programmes_nom ON programmes(nom);
CREATE INDEX idx_coaches_nom ON coaches(nom);
CREATE INDEX idx_seances_date ON seances(date_seance);

