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
        $userEmail = isset($data['user_email']) ? trim($data['user_email']) : '';
        $nom = trim($data['nom']);
        $prix = floatval($data['prix']);
        $description = isset($data['description']) ? trim($data['description']) : '';

        // Validate required fields
        if (empty($nom) || $prix < 0) {
            return [
                'success' => false,
                'error' => 'Product name and valid price are required',
                'code' => 'INVALID_DATA'
            ];
        }

        // Insert product to cart
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (user_email, Nom, Prix, Description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssds', $userEmail, $nom, $prix, $description);

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return [
                'success' => true,
                'message' => 'Product added to cart',
                'id' => $id
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => 'Database error: ' . $error,
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
        if (!empty($email)) {
            $stmt = $this->conn->prepare("SELECT id, user_email, Nom, Prix, Description, created_at FROM {$this->table} WHERE user_email = ? ORDER BY created_at DESC");
            $stmt->bind_param('s', $email);
        } else {
            $stmt = $this->conn->prepare("SELECT id, user_email, Nom, Prix, Description, created_at FROM {$this->table} ORDER BY created_at DESC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    /**
     * Get a cart item by ID
     * @param int $id
     * @return array Item data or null
     */
    public function getById($id) {
        $id = intval($id);
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item;
    }

    /**
     * Remove a product from the cart
     * @param int $id
     * @return array Response
     */
    public function removeProduct($id, $email = '') {
        $id = intval($id);
        $email = trim($email);

        if (!$id) {
            return [
                'success' => false,
                'error' => 'Product ID is required',
                'code' => 'INVALID_ID'
            ];
        }

        // Delete product from cart
        if (!empty($email)) {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ? AND user_email = ?");
            $stmt->bind_param('is', $id, $email);
        } else {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            $stmt->bind_param('i', $id);
        }

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
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
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => 'Database error: ' . $error,
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
        $nom = trim($nom);
        $prix = floatval($prix);
        $email = trim($email);

        if (!empty($email)) {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE Nom = ? AND Prix = ? AND user_email = ? LIMIT 1");
            $stmt->bind_param('sds', $nom, $prix, $email);
        } else {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE Nom = ? AND Prix = ? LIMIT 1");
            $stmt->bind_param('sd', $nom, $prix);
        }

        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
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
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => 'Database error: ' . $error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Clear entire cart
     * @return array Response
     */
    public function clear() {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table}");

        if ($stmt->execute()) {
            $stmt->close();
            return [
                'success' => true,
                'message' => 'Cart cleared successfully'
            ];
        } else {
            $error = $stmt->error;
            $stmt->close();
            return [
                'success' => false,
                'error' => 'Database error: ' . $error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get global shop statistics for admin dashboard
     * @return array Global stats
     */
    public function getGlobalStats() {
        $stmt = $this->conn->prepare("SELECT SUM(Prix) as total_revenue, COUNT(*) as total_orders FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return [
                'total_revenue' => $row['total_revenue'] ?? 0,
                'total_orders' => $row['total_orders'] ?? 0
            ];
        }
        $stmt->close();
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
