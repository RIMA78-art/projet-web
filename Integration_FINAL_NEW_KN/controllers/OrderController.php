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
                'user_email' => $_POST['user_email'] ?? '',
                'items' => $_POST['items'] ?? null
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

    public function getOrderDetails() {
        header('Content-Type: application/json; charset=utf-8');

        $orderId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Order ID is required'
            ]);
            exit;
        }

        try {
            $orderDetails = $this->orderModel->getOrderDetails($orderId);
            if (!$orderDetails) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Order not found'
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'order' => $orderDetails
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

    public function searchOrders() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        $term = trim($_POST['term'] ?? '');
        if ($term === '') {
            echo json_encode([
                'success' => true,
                'orders' => []
            ]);
            exit;
        }

        try {
            $orders = $this->orderModel->searchOrders($term, 20);
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

    public function deleteOrder() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        $orderId = intval($_POST['id'] ?? 0);
        if ($orderId <= 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Order ID is required'
            ]);
            exit;
        }

        try {
            $deleted = $this->orderModel->deleteOrder($orderId);
            if (!$deleted) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Order not found or could not be deleted'
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'message' => 'Order deleted successfully'
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

    public function getPopularItems() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $limit = intval($_GET['limit'] ?? $_POST['limit'] ?? 10);
            $items = $this->orderModel->getPopularItems($limit);
            echo json_encode([
                'success' => true,
                'items' => $items
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
