<?php
/**
 * Order Model
 * Persists checkout orders.
 */
class Order {
    private $db;
    private $conn;
    private $table = 'orders';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createTable();
    }

    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            address TEXT NOT NULL,
            telephone VARCHAR(8) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            user_email VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception('Error creating orders table: ' . $this->conn->error);
        }
    }

    public function createOrder($data) {
        $customerName = $this->db->escapeString(trim($data['customer_name'] ?? ''));
        $address = $this->db->escapeString(trim($data['address'] ?? ''));
        $telephone = preg_replace('/\D+/', '', (string)($data['telephone'] ?? ''));
        $totalPrice = floatval($data['total_price'] ?? 0);
        $userEmail = $this->db->escapeString(trim($data['user_email'] ?? ''));

        if ($customerName === '' || $address === '' || $totalPrice <= 0) {
            return [
                'success' => false,
                'error' => 'Name, address and valid total price are required'
            ];
        }

        if (!preg_match('/^\d{8}$/', $telephone)) {
            return [
                'success' => false,
                'error' => 'Telephone must contain exactly 8 digits'
            ];
        }

        $checkSql = "SELECT id FROM {$this->table} WHERE customer_name = '{$customerName}' LIMIT 1";
        $checkResult = $this->conn->query($checkSql);
        if ($checkResult && $checkResult->num_rows > 0) {
            return [
                'success' => false,
                'error' => 'Ce nom existe déjà dans les commandes'
            ];
        }

        $emailSql = $userEmail === '' ? 'NULL' : "'" . $userEmail . "'";
        $insertSql = "INSERT INTO {$this->table} (customer_name, address, telephone, total_price, user_email)
                      VALUES ('{$customerName}', '{$address}', '{$telephone}', {$totalPrice}, {$emailSql})";

        if ($this->conn->query($insertSql)) {
            return [
                'success' => true,
                'id' => $this->conn->insert_id,
                'message' => 'Order confirmed successfully'
            ];
        }

        return [
            'success' => false,
            'error' => 'Database error: ' . $this->conn->error
        ];
    }

    public function getAllOrders() {
        $sql = "SELECT id, customer_name, address, telephone, total_price, user_email, created_at
                FROM {$this->table}
                ORDER BY created_at DESC";
        $result = $this->conn->query($sql);

        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }

    public function getStats($limit = 8) {
        $limit = max(1, intval($limit));
        $sumSql = "SELECT COALESCE(SUM(total_price), 0) AS total_profit, COUNT(*) AS total_orders FROM {$this->table}";
        $sumResult = $this->conn->query($sumSql);
        if (!$sumResult) {
            throw new Exception('Database error while loading order totals: ' . $this->conn->error);
        }
        $totals = $sumResult->fetch_assoc();

        $recentSql = "SELECT id, customer_name, telephone, total_price, created_at
                      FROM {$this->table}
                      ORDER BY created_at DESC
                      LIMIT {$limit}";
        $recentResult = $this->conn->query($recentSql);
        if (!$recentResult) {
            throw new Exception('Database error while loading recent orders: ' . $this->conn->error);
        }

        $recentOrders = [];
        while ($row = $recentResult->fetch_assoc()) {
            $recentOrders[] = $row;
        }

        return [
            'total_profit' => floatval($totals['total_profit'] ?? 0),
            'total_orders' => intval($totals['total_orders'] ?? 0),
            'recent_orders' => $recentOrders
        ];
    }

    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
