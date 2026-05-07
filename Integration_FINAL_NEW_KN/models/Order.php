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

    private $itemsTable = 'order_items';

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

        $itemsSql = "CREATE TABLE IF NOT EXISTS {$this->itemsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_order_items_order_id FOREIGN KEY (order_id) REFERENCES {$this->table}(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$this->conn->query($itemsSql)) {
            throw new Exception('Error creating order_items table: ' . $this->conn->error);
        }
    }

    public function createOrder($data) {
        $customerName = trim($data['customer_name'] ?? '');
        $address = trim($data['address'] ?? '');
        $telephone = preg_replace('/\D+/', '', (string)($data['telephone'] ?? ''));
        $totalPrice = floatval($data['total_price'] ?? 0);
        $userEmail = trim($data['user_email'] ?? '');
        $itemsPayload = $data['items'] ?? null;

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

        // Check if customer name already exists
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE customer_name = ? LIMIT 1");
        $stmt->bind_param('s', $customerName);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result && $result->num_rows > 0) {
            return [
                'success' => false,
                'error' => 'This name already exists in orders'
            ];
        }

        $items = [];
        if (!empty($itemsPayload)) {
            if (is_string($itemsPayload)) {
                $decoded = json_decode($itemsPayload, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $items = $decoded;
                }
            } elseif (is_array($itemsPayload)) {
                $items = $itemsPayload;
            }
        }

        if (empty($items) && $userEmail !== '') {
            $items = $this->getCartItemsByUserEmail($userEmail);
        }

        if (empty($items)) {
            return [
                'success' => false,
                'error' => 'Order items are required'
            ];
        }

        $this->conn->begin_transaction();

        try {
            $stmt = $this->conn->prepare("INSERT INTO {$this->table} (customer_name, address, telephone, total_price, user_email)
                          VALUES (?, ?, ?, ?, ?)");
            $emailValue = $userEmail === '' ? null : $userEmail;
            $stmt->bind_param('sssds', $customerName, $address, $telephone, $totalPrice, $emailValue);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            if (!$this->saveOrderItems($orderId, $items)) {
                throw new Exception('Unable to save order items');
            }

            $this->conn->commit();
            return [
                'success' => true,
                'id' => $orderId,
                'message' => 'Order confirmed successfully'
            ];
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function saveOrderItems($orderId, $items) {
        if (empty($items) || !is_array($items)) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO {$this->itemsTable} (order_id, product_name, quantity, unit_price, total_price)
                              VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $productName = trim($item['product_name'] ?? $item['Nom'] ?? '');
            $quantity = max(1, intval($item['quantity'] ?? 1));
            $unitPrice = floatval($item['unit_price'] ?? $item['Prix'] ?? 0);
            $totalPrice = floatval($item['total_price'] ?? ($unitPrice * $quantity));

            if ($productName === '' || $unitPrice <= 0 || $quantity < 1) {
                return false;
            }

            $stmt->bind_param('isidd', $orderId, $productName, $quantity, $unitPrice, $totalPrice);
            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }
        }

        $stmt->close();
        return true;
    }

    private function getCartItemsByUserEmail($email) {
        $email = trim($email);
        if ($email === '') {
            return [];
        }

        $stmt = $this->conn->prepare("SELECT Nom AS product_name, Prix AS unit_price, COUNT(*) AS quantity, SUM(Prix) AS total_price
                FROM panier
                WHERE user_email = ?
                GROUP BY Nom, Prix
                ORDER BY created_at DESC");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'product_name' => $row['product_name'],
                'quantity' => intval($row['quantity']),
                'unit_price' => floatval($row['unit_price']),
                'total_price' => floatval($row['total_price'])
            ];
        }
        $stmt->close();
        return $items;
    }

    public function getAllOrders() {
        $stmt = $this->conn->prepare("SELECT id, customer_name, address, telephone, total_price, user_email, created_at
                FROM {$this->table}
                ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    public function searchOrders($term, $limit = 20) {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $searchTerm = '%' . $term . '%';
        $limit = max(1, intval($limit));

        $stmt = $this->conn->prepare("SELECT id, customer_name, address, telephone, total_price, user_email, created_at
                FROM {$this->table}
                WHERE customer_name LIKE ?
                ORDER BY created_at DESC
                LIMIT ?");
        $stmt->bind_param('si', $searchTerm, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();

        return $orders;
    }

    public function getOrderDetails($orderId) {
        $orderId = intval($orderId);
        if ($orderId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT id, customer_name, address, telephone, total_price, user_email, created_at FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $order = $result->fetch_assoc();
        $stmt->close();
        if (!$order) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT product_name, quantity, unit_price, total_price FROM {$this->itemsTable} WHERE order_id = ? ORDER BY id ASC");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $orderItems = [];
        while ($item = $result->fetch_assoc()) {
            $orderItems[] = $item;
        }
        $stmt->close();

        $order['items'] = $orderItems;
        return $order;
    }

    public function deleteOrder($orderId) {
        $orderId = intval($orderId);
        if ($orderId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        return $deleted;
    }

    public function getStats($limit = 8) {
        $limit = max(1, intval($limit));
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(total_price), 0) AS total_profit, COUNT(*) AS total_orders FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->get_result();
        $totals = $result->fetch_assoc();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT id, customer_name, telephone, total_price, created_at
                      FROM {$this->table}
                      ORDER BY created_at DESC
                      LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $recentOrders = [];
        while ($row = $result->fetch_assoc()) {
            $recentOrders[] = $row;
        }
        $stmt->close();

        return [
            'total_profit' => floatval($totals['total_profit'] ?? 0),
            'total_orders' => intval($totals['total_orders'] ?? 0),
            'recent_orders' => $recentOrders
        ];
    }

    public function getPopularItems($limit = 3) {
        $limit = max(1, intval($limit));
        $stmt = $this->conn->prepare("SELECT 
                    oi.product_name,
                    SUM(oi.quantity) AS total_quantity,
                    COUNT(DISTINCT o.customer_name) AS unique_customers,
                    ROUND(SUM(oi.total_price), 2) AS total_revenue
                FROM {$this->itemsTable} oi
                INNER JOIN {$this->table} o ON oi.order_id = o.id
                GROUP BY oi.product_name
                ORDER BY total_quantity DESC
                LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'product_name' => $row['product_name'],
                'total_quantity' => intval($row['total_quantity']),
                'unique_customers' => intval($row['unique_customers']),
                'total_revenue' => floatval($row['total_revenue'])
            ];
        }
        $stmt->close();

        return $items;
    }

    public function getRecentPurchaseItems($limit = 8) {
        $limit = max(1, intval($limit));
        $stmt = $this->conn->prepare("SELECT 
                    oi.id,
                    oi.product_name AS Nom,
                    oi.unit_price AS Prix,
                    oi.quantity,
                    oi.total_price,
                    o.user_email,
                    o.customer_name,
                    o.created_at,
                    oi.order_id
                FROM {$this->itemsTable} oi
                INNER JOIN {$this->table} o ON oi.order_id = o.id
                ORDER BY oi.created_at DESC
                LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'Nom' => $row['Nom'],
                'Prix' => floatval($row['Prix']),
                'quantity' => intval($row['quantity']),
                'total_price' => floatval($row['total_price']),
                'user_email' => $row['user_email'] ?? 'Guest',
                'customer_name' => $row['customer_name'],
                'created_at' => $row['created_at'],
                'order_id' => $row['order_id']
            ];
        }
        $stmt->close();

        return $items;
    }

    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
