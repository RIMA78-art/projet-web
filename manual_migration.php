<?php
require 'modele/config.php';

try {
    $pdo = config::getConnexion();

    // Ajouter last_login
    $pdo->exec('ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL');
    echo "last_login column added\n";

    // Ajouter last_weight_update
    $pdo->exec('ALTER TABLE utilisateur ADD COLUMN IF NOT EXISTS last_weight_update DATETIME DEFAULT NULL');
    echo "last_weight_update column added\n";

    // Mettre à jour les valeurs existantes
    $pdo->exec('UPDATE utilisateur SET last_weight_update = date_inscription WHERE last_weight_update IS NULL');
    echo "Existing records updated\n";

    // Créer les index
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_last_login ON utilisateur(last_login)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_last_weight_update ON utilisateur(last_weight_update)');
    echo "Indexes created\n";

    echo "Migration completed successfully\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>