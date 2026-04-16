<?php
header('Content-Type: application/json');

try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "integration_nutrition_ai";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo json_encode(array('error' => 'Connection failed: ' . $conn->connect_error));
        exit();
    }

    $conn->set_charset("utf8mb4");

    // Create table if it doesn't exist
    $createTableSql = "CREATE TABLE IF NOT EXISTS panier (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255),
        Nom VARCHAR(255) NOT NULL,
        Prix FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conn->query($createTableSql);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user_email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
        $nom = isset($_POST['nom']) ? $conn->real_escape_string($_POST['nom']) : '';
        $prix = isset($_POST['prix']) ? floatval($_POST['prix']) : 0;

        if (empty($nom) || $prix <= 0) {
            echo json_encode(array('error' => 'Invalid product data'));
            exit();
        }

        $sql = "INSERT INTO panier (user_email, Nom, Prix) VALUES ('$user_email', '$nom', $prix)";

        if ($conn->query($sql) === TRUE) {
            $insertId = $conn->insert_id;
            echo json_encode(array('success' => true, 'message' => 'Produit sauvegardé en base de données', 'id' => $insertId));
        } else {
            echo json_encode(array('error' => 'Database error: ' . $conn->error));
        }
    } else {
        echo json_encode(array('error' => 'Invalid request method'));
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode(array('error' => 'Exception: ' . $e->getMessage()));
}
?>
