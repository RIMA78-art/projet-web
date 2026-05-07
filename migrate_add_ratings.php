<?php
/**
 * Migration : Ajouter la table meal_ratings
 * Exécutez ce fichier une seule fois : http://localhost/NutriNova/migrate_add_ratings.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    // Créer la table meal_ratings
    $sql = "
        CREATE TABLE IF NOT EXISTS `meal_ratings` (
            `id_rating` INT AUTO_INCREMENT PRIMARY KEY,
            `id_meal` INT NOT NULL,
            `rating` INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            `comment` TEXT NULL,
            `visitor_name` VARCHAR(100) NULL,
            `visitor_email` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`id_meal`) REFERENCES `meals`(`id_meal`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX `idx_meal` (`id_meal`),
            INDEX `idx_rating` (`rating`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    echo "✅ Table meal_ratings créée avec succès !<br>";
    echo "🎉 Les évaluations sont maintenant activées.<br><br>";
    echo "➡️ <a href='index.php'>Retour à l'accueil</a>";

} catch (PDOException $e) {
    echo "❌ Erreur : " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "Vérifiez votre connexion à la base de données.";
}
?>
