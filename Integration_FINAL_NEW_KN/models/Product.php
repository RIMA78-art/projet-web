<?php
/**
 * Product Model
 * Handles boutique product catalog operations
 */
class Product {
    private $db;
    private $conn;
    private $table = 'boutique_products';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createTable();
    }

    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(255) NOT NULL,
            prix FLOAT NOT NULL,
            description TEXT NULL,
            categorie VARCHAR(50) NOT NULL DEFAULT 'complement',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating products table: " . $this->conn->error);
        }
    }

    public function addProduct($data) {
        $nom = trim($data['nom'] ?? '');
        $prix = floatval($data['prix'] ?? 0);
        $description = trim($data['description'] ?? '');
        $rawCategorie = trim($data['categorie'] ?? 'complement');
        $allowedCategories = ['bio', 'complement', 'sport', 'accessoire'];
        if (!in_array($rawCategorie, $allowedCategories, true)) {
            $rawCategorie = 'complement';
        }
        $categorie = $rawCategorie;

        if (empty($nom) || $prix <= 0) {
            return [
                'success' => false,
                'error' => 'Product name and valid price are required'
            ];
        }
        if ($prix > 200) {
            return [
                'success' => false,
                'error' => 'Price cannot exceed 200'
            ];
        }

        if ($this->productNameExists($nom)) {
            return [
                'success' => false,
                'error' => 'A product with this name already exists'
            ];
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (nom, prix, description, categorie) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sdss', $nom, $prix, $description, $categorie);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Product added successfully'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => 'Database error: ' . $error
            ];
        }
    }

    public function getAllProducts() {
        $stmt = $this->conn->prepare("SELECT id, nom, prix, description, categorie, created_at FROM {$this->table} ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();

        return $items;
    }

    private function productNameExists($nom) {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE LOWER(nom) = LOWER(?) LIMIT 1");
        $stmt->bind_param('s', $nom);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
