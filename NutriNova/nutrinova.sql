-- ============================================================
-- Script de création de la base de données NutriNova
-- Architecture MVC avec PDO


-- Créer la base de données
CREATE DATABASE IF NOT EXISTS `nutrinova` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nutrinova`;

-- ============================================================
-- Table: Meals (Repas)
-- ============================================================
CREATE TABLE IF NOT EXISTS `meals` (
    `id_meal` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `calories` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `protein` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `carb` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `fat` DECIMAL(10, 2) NOT NULL DEFAULT 0,
    `type` ENUM('petit déjeuner', 'déjeuner', 'dîner') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: Ingredients
-- ============================================================
CREATE TABLE IF NOT EXISTS `ingredients` (
    `id_ingredient` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `calories` DECIMAL(10, 2) NOT NULL,
    `protein` DECIMAL(10, 2) NOT NULL,
    `carb` DECIMAL(10, 2) NOT NULL,
    `fat` DECIMAL(10, 2) NOT NULL,
    `eco_score` VARCHAR(10) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nom` (`nom`),
    INDEX `idx_eco_score` (`eco_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: Meal_Ingredient (Relation N:N)
-- ============================================================
CREATE TABLE IF NOT EXISTS `meal_ingredient` (
    `id_meal` INT NOT NULL,
    `id_ingredient` INT NOT NULL,
    `quantity` DECIMAL(10, 2) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_meal`, `id_ingredient`),
    FOREIGN KEY (`id_meal`) REFERENCES `meals`(`id_meal`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`id_ingredient`) REFERENCES `ingredients`(`id_ingredient`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_ingredient` (`id_ingredient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Ingrédients
INSERT INTO `ingredients` (`nom`, `calories`, `protein`, `carb`, `fat`, `eco_score`) VALUES
('Poitrine de poulet', 165, 31, 0, 3.6, 'A'),
('Riz blanc', 130, 2.7, 28, 0.3, 'B'),
('Brocoli', 34, 2.8, 7, 0.4, 'A'),
('Œuf entier', 155, 13, 1.1, 11, 'A'),
('Lait demi-écrémé', 49, 3.3, 4.8, 1.5, 'B'),
('Yaourt nature', 59, 3.5, 4.7, 0.4, 'B'),
('Amande', 579, 21, 22, 50, 'B'),
('Pomme', 52, 0.3, 14, 0.2, 'A'),
('Saumon', 208, 22, 0, 13, 'A'),
('Tomate', 18, 0.9, 3.9, 0.2, 'A');

-- Repas
INSERT INTO `meals` (`nom`, `calories`, `protein`, `carb`, `fat`, `type`) VALUES
('Omelette Protéinée', 310, 26, 2.1, 14.6, 'petit déjeuner'),
('Poitrine de Poulet Grillée', 295, 34, 28, 3.9, 'déjeuner'),
('Saumon Rôti', 416, 35, 0, 26, 'dîner'),
('Yaourt Muesli', 200, 8, 25, 4, 'petit déjeuner');

-- Relations Meal_Ingredient
INSERT INTO `meal_ingredient` (`id_meal`, `id_ingredient`, `quantity`) VALUES
(1, 4, 2),      -- Omelette: 2 œufs
(1, 5, 0.5),    -- Omelette: 0.5 L de lait
(2, 1, 1),      -- Poulet: 1 unité
(2, 2, 1),      -- Riz: 1 unité
(2, 3, 1),      -- Brocoli: 1 unité
(3, 9, 1),      -- Saumon: 1 unité
(4, 6, 1),      -- Yaourt: 1 unité
(4, 8, 1);      -- Pomme: 1 unité

-- ============================================================
-- INDEX SUPPLÉMENTAIRES POUR PERFORMANCE
-- ============================================================
CREATE INDEX idx_meals_type_calories ON meals(`type`, `calories`);
CREATE INDEX idx_ingredients_nom ON ingredients(`nom`)
ALTER TABLE `meals` ADD COLUMN `image` VARCHAR(255) NULL AFTER `type`;
