-- ============================================================
-- Migration : Tables Nutrition pour NUTRINOVA_MVC
-- Tables : meals, ingredients, meal_ingredient, meal_ratings
-- ============================================================
USE `nutrinova`;

-- Table: meals
CREATE TABLE IF NOT EXISTS `meals` (
    `id_meal` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `calories` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `protein` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `carb` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `fat` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `type` ENUM('petit déjeuner','déjeuner','dîner') NOT NULL,
    `image` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_type` (`type`),
    INDEX `idx_nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ingredients
CREATE TABLE IF NOT EXISTS `ingredients` (
    `id_ingredient` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(255) NOT NULL,
    `calories` DECIMAL(10,2) NOT NULL,
    `protein` DECIMAL(10,2) NOT NULL,
    `carb` DECIMAL(10,2) NOT NULL,
    `fat` DECIMAL(10,2) NOT NULL,
    `eco_score` VARCHAR(10) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nom` (`nom`),
    INDEX `idx_eco_score` (`eco_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: meal_ingredient (N:N)
CREATE TABLE IF NOT EXISTS `meal_ingredient` (
    `id_meal` INT NOT NULL,
    `id_ingredient` INT NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_meal`, `id_ingredient`),
    FOREIGN KEY (`id_meal`) REFERENCES `meals`(`id_meal`) ON DELETE CASCADE,
    FOREIGN KEY (`id_ingredient`) REFERENCES `ingredients`(`id_ingredient`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: meal_ratings
CREATE TABLE IF NOT EXISTS `meal_ratings` (
    `id_rating` INT AUTO_INCREMENT PRIMARY KEY,
    `id_meal` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    `comment` TEXT DEFAULT NULL,
    `visitor_name` VARCHAR(100) DEFAULT 'Anonyme',
    `visitor_email` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_meal`) REFERENCES `meals`(`id_meal`) ON DELETE CASCADE,
    INDEX `idx_meal_rating` (`id_meal`, `rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter la colonne image si elle n'existe pas
-- (IF NOT EXISTS n'est pas supporté pour ALTER TABLE en MariaDB, donc on ignore l'erreur)
-- ALTER TABLE meals ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT NULL AFTER type;
