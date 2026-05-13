<?php
require 'modele/config.php';

try {
    $pdo = config::getConnexion();
    $sql = file_get_contents('sql_migration_scoring.sql');
    $pdo->exec($sql);
    echo "Migration executed successfully\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>