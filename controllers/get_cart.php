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
        Nom VARCHAR(255) NOT NULL,
        Prix FLOAT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conn->query($createTableSql);

    $sql = "SELECT Nom, Prix FROM panier ORDER BY created_at DESC";
    $result = $conn->query($sql);

    $cart_items = array();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $cart_items[] = $row;
        }
    }

    echo json_encode($cart_items);
    $conn->close();
} catch (Exception $e) {
    echo json_encode(array('error' => 'Exception: ' . $e->getMessage()));
}
?></content>
<parameter name="filePath">c:\Users\Nebouli\Desktop\ESPRIT 2ND YEAR\Semestre 2\Proje\Xamp\htdocs\Integration_FINAL_NEW_KN\get_cart.php