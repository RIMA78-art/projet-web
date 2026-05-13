<?php
require 'modele/config.php';

try {
    $pdo = config::getConnexion();

    // Vérifier les tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";

    // Vérifier la structure de la table utilisateur
    $stmt = $pdo->query('DESCRIBE utilisateur');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nColumns in utilisateur table:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']}: {$column['Type']}\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>