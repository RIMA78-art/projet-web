-- 002_optional_seed_nutrinova.sql
USE gest_sportt;

INSERT INTO coaches (nom,email,telephone,specialite)
VALUES
('Coach Lina','lina@nutrinova.local','0600000001','Nutrition sportive'),
('Coach Mehdi','mehdi@nutrinova.local','0600000002','Cardio performance')
ON DUPLICATE KEY UPDATE nom=VALUES(nom);

INSERT INTO programmes (nom,duree_semaines,jours_semaine,difficulte,description,popularite)
VALUES
('Cardio Lean Start',6,4,'debutant','Programme cardio pour perte de poids',8),
('Mass Build Pro',10,5,'intermediaire','Programme musculation pour prise de masse',9),
('Elite Conditioning',12,6,'avance','Conditionnement avance haute intensite',7);

