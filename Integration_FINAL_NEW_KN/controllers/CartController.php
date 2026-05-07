<?php
/**
 * Cart Controller
 * Handles cart operations (add, get, remove items)
 */
class CartController {
    private $cartModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Cart.php';
        $this->cartModel = new Cart();
    }

    /**
     * Add a product to the cart
     * @return void
     */
    public function addProduct() {
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
                'user_email' => $_POST['email'] ?? '',
                'nom' => $_POST['nom'] ?? '',
                'prix' => $_POST['prix'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            $response = $this->cartModel->addProduct($data);
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

    /**
     * Get all cart items
     * @return void
     */
    public function getAll() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $user_email = $_GET['email'] ?? '';
            $items = $this->cartModel->getAll($user_email);
            echo json_encode($items);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Remove a product from the cart by ID
     * @return void
     */
    public function removeProduct() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $user_email = $_POST['email'] ?? '';
            // Try to remove by ID first
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);
                $response = $this->cartModel->removeProduct($id, $user_email);
            } 
            // Fallback to remove by name and price
            elseif (isset($_POST['nom']) && isset($_POST['prix'])) {
                $nom = $_POST['nom'];
                $prix = $_POST['prix'];
                $response = $this->cartModel->removeByNameAndPrice($nom, $prix, $user_email);
            } 
            else {
                $response = [
                    'success' => false,
                    'error' => 'Product ID or name and price are required'
                ];
            }

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

    /**
     * Clear entire cart
     * @return void
     */
    public function clear() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = $this->cartModel->clear();
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

    /**
     * Get admin shop statistics and recent purchases from orders
     * @return void
     */
    public function getAdminStats() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            require_once __DIR__ . '/../models/Order.php';
            $orderModel = new Order();
            
            // Get order statistics (revenue, count, recent orders)
            $stats = $orderModel->getStats(8);
            
            // Get recent purchases from order items
            $recentPurchases = $orderModel->getRecentPurchaseItems(8);

            echo json_encode([
                'success' => true,
                'revenue' => $stats['total_profit'],
                'orders' => $stats['total_orders'],
                'recent_purchases' => $recentPurchases
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

    /**
     * Add a product from backoffice form
     * @return void
     */
    public function addProductAdmin() {
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
                'user_email' => $_POST['email'] ?? 'admin@backoffice.local',
                'nom' => $_POST['nom'] ?? '',
                'prix' => $_POST['prix'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];

            $response = $this->cartModel->addProduct($data);
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
}

?>
