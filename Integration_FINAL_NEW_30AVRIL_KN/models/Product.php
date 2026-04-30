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
        $nom = $this->db->escapeString(trim($data['nom'] ?? ''));
        $prix = floatval($data['prix'] ?? 0);
        $description = $this->db->escapeString(trim($data['description'] ?? ''));
        $rawCategorie = trim($data['categorie'] ?? 'complement');
        $allowedCategories = ['bio', 'complement', 'sport', 'accessoire'];
        if (!in_array($rawCategorie, $allowedCategories, true)) {
            $rawCategorie = 'complement';
        }
        $categorie = $this->db->escapeString($rawCategorie);

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

        $insertSql = "INSERT INTO {$this->table} (nom, prix, description, categorie) VALUES ('{$nom}', {$prix}, '{$description}', '{$categorie}')";
        if ($this->conn->query($insertSql)) {
            return [
                'success' => true,
                'id' => $this->conn->insert_id,
                'message' => 'Product added successfully'
            ];
        }

        return [
            'success' => false,
            'error' => 'Database error: ' . $this->conn->error
        ];
    }

    public function getAllProducts() {
        $sql = "SELECT id, nom, prix, description, categorie, created_at FROM {$this->table} ORDER BY created_at DESC";
        $result = $this->conn->query($sql);

        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }

        return $items;
    }

    private function productNameExists($nomEscaped) {
        $checkSql = "SELECT id FROM {$this->table} WHERE LOWER(nom) = LOWER('{$nomEscaped}') LIMIT 1";
        $result = $this->conn->query($checkSql);
        if (!$result) {
            throw new Exception('Database error while checking product name: ' . $this->conn->error);
        }
        return $result->num_rows > 0;
    }

    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
