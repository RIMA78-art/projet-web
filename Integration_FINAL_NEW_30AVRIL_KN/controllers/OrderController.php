<?php
/**
 * Order Controller
 * Handles order creation and listing.
 */
class OrderController {
    private $orderModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Order.php';
        $this->orderModel = new Order();
    }

    public function createOrder() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $data = [
                'customer_name' => $_POST['customer_name'] ?? '',
                'address' => $_POST['address'] ?? '',
                'telephone' => $_POST['telephone'] ?? '',
                'total_price' => $_POST['total_price'] ?? '',
                'user_email' => $_POST['user_email'] ?? ''
            ];

            $response = $this->orderModel->createOrder($data);
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getAllOrders() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $orders = $this->orderModel->getAllOrders();
            echo json_encode([
                'success' => true,
                'orders' => $orders
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getAdminStats() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $stats = $this->orderModel->getStats(10);
            echo json_encode([
                'success' => true,
                'profit' => $stats['total_profit'],
                'orders' => $stats['total_orders'],
                'recent_orders' => $stats['recent_orders']
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
?>
