-- ================================================================
-- BASE DE DONNEES: integration_nutrition_ai
-- ================================================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS integration_nutrition_ai;
USE integration_nutrition_ai;

-- ================================================================
-- TABLE: users
-- Stocke les informations des utilisateurs
-- ================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(255) NOT NULL,
    Prenom VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Mot_de_passe VARCHAR(255) NOT NULL,
    Taille_cm INT DEFAULT NULL,
    Poids_kg FLOAT DEFAULT NULL,
    Objectif VARCHAR(255) DEFAULT NULL,
    Niveau_sportif VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- TABLE: post
-- Stocke les articles/posts de blog
-- ================================================================
CREATE TABLE IF NOT EXISTS post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_auteur VARCHAR(255) NOT NULL,
    titre_post VARCHAR(255) NOT NULL,
    contenu_post TEXT NOT NULL,
    fichier VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- TABLE: panier
-- Stocke les articles du panier (cart)
-- ================================================================
CREATE TABLE IF NOT EXISTS panier (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Nom VARCHAR(255) NOT NULL,
    Prix FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- TABLE: commentaire
-- Stocke les commentaires liés aux posts
-- ================================================================
CREATE TABLE IF NOT EXISTS commentaire (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    nom_auteur VARCHAR(255) NOT NULL,
    id_post INT NOT NULL,
    date_commentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commentaire_post FOREIGN KEY (id_post) REFERENCES post(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ================================================================
-- FIN DE LA CREATION DES TABLES
-- ================================================================
