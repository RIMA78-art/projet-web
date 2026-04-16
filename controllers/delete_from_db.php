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

    // Create table if it doesn't exist (safe fallback)
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
        if (isset($_POST['id']) && intval($_POST['id']) > 0) {
            $id = intval($_POST['id']);
            $sql = "DELETE FROM panier WHERE id = $id AND user_email = '$user_email'";
        } elseif (isset($_POST['nom']) && isset($_POST['prix'])) {
            $nom = $conn->real_escape_string($_POST['nom']);
            $prix = floatval($_POST['prix']);
            $sql = "DELETE FROM panier WHERE Nom = '$nom' AND Prix = $prix AND user_email = '$user_email' ORDER BY created_at DESC LIMIT 1";
        } else {
            echo json_encode(array('error' => 'Missing delete parameters'));
            exit();
        }

        if ($conn->query($sql) === TRUE) {
            if ($conn->affected_rows > 0) {
                echo json_encode(array('success' => true, 'message' => 'Produit supprimé de la base de données'));
            } else {
                echo json_encode(array('error' => 'Aucun produit correspondant trouvé en base de données'));
            }
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