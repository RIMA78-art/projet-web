-- Dump de création de la base de données pour l'application de gestion de programmes sportifs

CREATE DATABASE IF NOT EXISTS `gestion_sport` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gestion_sport`;

CREATE TABLE IF NOT EXISTS `programme` (
  `id_programme` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `duree` INT NOT NULL,
  `niveau` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `calories` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
