<?php
/**
 * Point d'entrée principal de l'application NutriNova
 * Initialise la session et route les requêtes
 */

session_start();

// Inclure la configuration de la base de données
require_once 'config/Database.php';

// Inclure le contrôleur
require_once 'Controller/NutritionController.php';

// Créer une instance du contrôleur et router la requête
$controller = new NutritionController();
$controller->route();
?>
