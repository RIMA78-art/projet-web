<?php
/**
 * Cart Model
 * Handles all cart-related database operations using PDO
 */
class Cart {
    private $db;
    private $conn;
    private $table = 'panier';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createTable();
    }

    /**
     * Create cart (panier) table if it doesn't exist
     */
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Nom VARCHAR(255) NOT NULL,
            Prix FLOAT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Error creating cart table: " . $e->getMessage());
        }
    }

    /**
     * Add a product to the cart using PDO
     * @param array $data Product data
     * @return array Response
     */
    public function addProduct($data) {
        $nom = trim($data['nom'] ?? '');
        $prix = floatval($data['prix'] ?? 0);

        // Validate required fields
        if (empty($nom) || $prix < 0) {
            return [
                'success' => false,
                'error' => 'Product name and valid price are required',
                'code' => 'INVALID_DATA'
            ];
        }

        try {
            // Insert product to cart using prepared statement
            $insert_sql = "INSERT INTO {$this->table} (Nom, Prix) VALUES (:nom, :prix)";

            $stmt = $this->conn->prepare($insert_sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prix' => $prix
            ]);

            return [
                'success' => true,
                'message' => 'Product added to cart',
                'id' => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get all products in the cart using PDO
     * @return array Cart items
     */
    public function getAll() {
        try {
            $sql = "SELECT id, Nom, Prix, created_at FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $items ?: [];
        } catch (PDOException $e) {
            error_log("Cart::getAll error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a cart item by ID using PDO
     * @param int $id
     * @return array|null Item data or null
     */
    public function getById($id) {
        $id = intval($id);
        
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Cart::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Remove a product from the cart using PDO
     * @param int $id
     * @return array Response
     */
    public function removeProduct($id) {
        $id = intval($id);

        if (!$id) {
            return [
                'success' => false,
                'error' => 'Product ID is required',
                'code' => 'INVALID_ID'
            ];
        }

        try {
            // Delete product from cart using prepared statement
            $delete_sql = "DELETE FROM {$this->table} WHERE id = :id";

            $stmt = $this->conn->prepare($delete_sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Product removed from cart'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Product not found in cart',
                    'code' => 'NOT_FOUND'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Remove product from cart by name and price using PDO
     * @param string $nom
     * @param float $prix
     * @return array Response
     */
    public function removeByNameAndPrice($nom, $prix) {
        $nom = trim($nom);
        $prix = floatval($prix);

        try {
            $delete_sql = "DELETE FROM {$this->table} WHERE Nom = :nom AND Prix = :prix LIMIT 1";

            $stmt = $this->conn->prepare($delete_sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prix' => $prix
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Product removed from cart'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Product not found in cart',
                    'code' => 'NOT_FOUND'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Clear entire cart using PDO
     * @return array Response
     */
    public function clear() {
        try {
            $delete_sql = "DELETE FROM {$this->table}";

            $stmt = $this->conn->prepare($delete_sql);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Cart cleared successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
