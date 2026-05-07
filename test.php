<?php
/**
 * Script de test et de diagnostique pour NutriNova
 * Accédez via: http://localhost/NutriNova/test.php
 */

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<meta charset='utf-8'>";
echo "<title>Test NutriNova</title>";
echo "<style>";
echo "body { font-family: Arial; margin: 20px; background: #f5f5f5; }";
echo ".container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: 0 auto; }";
echo "h1 { color: #006e1c; }";
echo ".success { color: #22c55e; padding: 10px; background: #f0fdf4; border-left: 4px solid #22c55e; margin: 10px 0; }";
echo ".error { color: #dc2626; padding: 10px; background: #fef2f2; border-left: 4px solid #dc2626; margin: 10px 0; }";
echo ".warning { color: #f59e0b; padding: 10px; background: #fffbeb; border-left: 4px solid #f59e0b; margin: 10px 0; }";
echo ".info { color: #3b82f6; padding: 10px; background: #eff6ff; border-left: 4px solid #3b82f6; margin: 10px 0; }";
echo "table { width: 100%; border-collapse: collapse; margin: 10px 0; }";
echo "th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }";
echo "th { background: #f3f4f6; font-weight: bold; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔍 Test Diagnostique - NutriNova</h1>";

// ============================================================
// 1. Test PHP Version
// ============================================================
echo "<h2>1️⃣ Version PHP</h2>";
$php_version = phpversion();
if (version_compare($php_version, '7.4', '>=')) {
    echo "<div class='success'>✅ PHP $php_version (requis: 7.4+)</div>";
} else {
    echo "<div class='error'>❌ PHP $php_version est trop ancien (requis: 7.4+)</div>";
}

// ============================================================
// 2. Test Extension PDO
// ============================================================
echo "<h2>2️⃣ Extensions PHP</h2>";
$extensions = [
    'PDO' => extension_loaded('pdo'),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'MySQLi' => extension_loaded('mysqli'),
];

foreach ($extensions as $name => $loaded) {
    if ($loaded) {
        echo "<div class='success'>✅ $name est activé</div>";
    } else {
        echo "<div class='warning'>⚠️ $name n'est pas activé</div>";
    }
}

// ============================================================
// 3. Test Fichiers
// ============================================================
echo "<h2>3️⃣ Structure des Fichiers</h2>";
$files = [
    'config/Database.php' => 'config/Database.php',
    'Model/Meal.php' => 'Model/Meal.php',
    'Model/Ingredient.php' => 'Model/Ingredient.php',
    'Controller/NutritionController.php' => 'Controller/NutritionController.php',
    'View/Front/index.php' => 'View/Front/index.php',
    'View/Back/meals-list.php' => 'View/Back/meals-list.php',
    'nutrinova.sql' => 'nutrinova.sql',
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<div class='success'>✅ $name existe</div>";
    } else {
        echo "<div class='error'>❌ $name est manquant</div>";
    }
}

// ============================================================
// 4. Test Connexion Base de Données
// ============================================================
echo "<h2>4️⃣ Connexion Base de Données</h2>";

try {
    require_once 'config/Database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<div class='success'>✅ Connexion PDO établie</div>";
    
    // Tester les tables
    echo "<h3>Vérification des tables</h3>";
    $tables = ['meals', 'ingredients', 'meal_ingredient'];
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if ($stmt->rowCount() > 0) {
            // Compter les lignes
            $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM $table");
            $count_stmt->execute();
            $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "<div class='success'>✅ Table '$table' existe ($count lignes)</div>";
        } else {
            echo "<div class='warning'>⚠️ Table '$table' n'existe pas</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Erreur de connexion: " . $e->getMessage() . "</div>";
    echo "<div class='info'>";
    echo "📝 Pour configurer la connexion, éditez config/Database.php<br>";
    echo "Vérifiez les paramètres:<br>";
    echo "- host (localhost)<br>";
    echo "- db_name (nutrinova)<br>";
    echo "- user (root)<br>";
    echo "- password (vide par défaut)<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
}

// ============================================================
// 5. Informations Système
// ============================================================
echo "<h2>5️⃣ Informations Système</h2>";
echo "<table>";
echo "<tr><th>Paramètre</th><th>Valeur</th></tr>";
echo "<tr><td>OS</td><td>" . php_uname() . "</td></tr>";
echo "<tr><td>Serveur Web</td><td>" . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A') . "</td></tr>";
echo "<tr><td>Dossier Courant</td><td>" . getcwd() . "</td></tr>";
echo "<tr><td>Répertoire Document Root</td><td>" . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'N/A') . "</td></tr>";
echo "</table>";

// ============================================================
// 6. Next Steps
// ============================================================
echo "<h2>6️⃣ Prochaines Étapes</h2>";
echo "<div class='info'>";
echo "<strong>Tout est OK? 🎉</strong><br>";
echo "1. Accédez à l'application: <a href='index.php' style='color: #006e1c;'>Lancer NutriNova</a><br>";
echo "2. Front Office: <a href='index.php' style='color: #006e1c;'>http://localhost/NutriNova/</a><br>";
echo "3. Back Office: <a href='index.php?action=admin-meals&section=meal' style='color: #006e1c;'>Admin</a><br>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
