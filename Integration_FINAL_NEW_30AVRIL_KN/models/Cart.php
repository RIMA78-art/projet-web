<?php
/**
 * Cart Model
 * Handles all cart-related database operations
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
            user_email VARCHAR(255),
            Nom VARCHAR(255) NOT NULL,
            Prix FLOAT NOT NULL,
            Description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating cart table: " . $this->conn->error);
        }

        $checkSql = "SHOW COLUMNS FROM {$this->table} LIKE 'user_email'";
        $checkResult = $this->conn->query($checkSql);
        if ($checkResult && $checkResult->num_rows === 0) {
            $alterSql = "ALTER TABLE {$this->table} ADD COLUMN user_email VARCHAR(255) AFTER id";
            if (!$this->conn->query($alterSql)) {
                throw new Exception("Error altering cart table: " . $this->conn->error);
            }
        }

        $checkDescriptionSql = "SHOW COLUMNS FROM {$this->table} LIKE 'Description'";
        $checkDescriptionResult = $this->conn->query($checkDescriptionSql);
        if ($checkDescriptionResult && $checkDescriptionResult->num_rows === 0) {
            $alterDescriptionSql = "ALTER TABLE {$this->table} ADD COLUMN Description TEXT NULL AFTER Prix";
            if (!$this->conn->query($alterDescriptionSql)) {
                throw new Exception("Error altering cart table for description: " . $this->conn->error);
            }
        }
    }

    /**
     * Add a product to the cart
     * @param array $data Product data
     * @return array Response
     */
    public function addProduct($data) {
        $userEmail = isset($data['user_email']) ? $this->db->escapeString(trim($data['user_email'])) : '';
        $nom = $this->db->escapeString(trim($data['nom']));
        $prix = floatval($data['prix']);
        $description = isset($data['description']) ? $this->db->escapeString(trim($data['description'])) : '';

        // Validate required fields
        if (empty($nom) || $prix < 0) {
            return [
                'success' => false,
                'error' => 'Product name and valid price are required',
                'code' => 'INVALID_DATA'
            ];
        }

        // Insert product to cart
        $insert_sql = "INSERT INTO {$this->table} (user_email, Nom, Prix, Description) VALUES ('" . $userEmail . "', '" . $nom . "', " . $prix . ", '" . $description . "')";

        if ($this->conn->query($insert_sql)) {
            return [
                'success' => true,
                'message' => 'Product added to cart',
                'id' => $this->conn->insert_id
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get all products in the cart
     * @param string|null $email
     * @return array Cart items
     */
    public function getAll($email = null) {
        $sql = "SELECT id, user_email, Nom, Prix, Description, created_at FROM {$this->table}";
        if (!empty($email)) {
            $email = $this->db->escapeString(trim($email));
            $sql .= " WHERE user_email = '" . $email . "'";
        }
        $sql .= " ORDER BY created_at DESC";

        $result = $this->conn->query($sql);
        
        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    /**
     * Get a cart item by ID
     * @param int $id
     * @return array Item data or null
     */
    public function getById($id) {
        $id = intval($id);
        $sql = "SELECT * FROM {$this->table} WHERE id = " . $id;
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    /**
     * Remove a product from the cart
     * @param int $id
     * @return array Response
     */
    public function removeProduct($id, $email = '') {
        $id = intval($id);
        $email = $this->db->escapeString(trim($email));

        if (!$id) {
            return [
                'success' => false,
                'error' => 'Product ID is required',
                'code' => 'INVALID_ID'
            ];
        }

        // Delete product from cart
        $delete_sql = "DELETE FROM {$this->table} WHERE id = " . $id;
        if (!empty($email)) {
            $delete_sql .= " AND user_email = '" . $email . "'";
        }

        if ($this->conn->query($delete_sql)) {
            if ($this->conn->affected_rows > 0) {
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
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Remove product from cart by name and price
     * @param string $nom
     * @param float $prix
     * @param string $email
     * @return array Response
     */
    public function removeByNameAndPrice($nom, $prix, $email = '') {
        $nom = $this->db->escapeString(trim($nom));
        $prix = floatval($prix);
        $email = $this->db->escapeString(trim($email));

        $delete_sql = "DELETE FROM {$this->table} WHERE Nom = '" . $nom . "' AND Prix = " . $prix;
        if (!empty($email)) {
            $delete_sql .= " AND user_email = '" . $email . "'";
        }
        $delete_sql .= " LIMIT 1";

        if ($this->conn->query($delete_sql)) {
            if ($this->conn->affected_rows > 0) {
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
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Clear entire cart
     * @return array Response
     */
    public function clear() {
        $delete_sql = "DELETE FROM {$this->table}";

        if ($this->conn->query($delete_sql)) {
            return [
                'success' => true,
                'message' => 'Cart cleared successfully'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get global shop statistics for admin dashboard
     * @return array Global stats
     */
    public function getGlobalStats() {
        $sql = "SELECT SUM(Prix) as total_revenue, COUNT(*) as total_orders FROM {$this->table}";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return [
                'total_revenue' => $row['total_revenue'] ?? 0,
                'total_orders' => $row['total_orders'] ?? 0
            ];
        }
        return [
            'total_revenue' => 0,
            'total_orders' => 0
        ];
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
