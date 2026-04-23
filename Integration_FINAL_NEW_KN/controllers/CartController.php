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
                'nom' => $_POST['nom'] ?? '',
                'prix' => $_POST['prix'] ?? ''
            ];

            $response = $this->cartModel->addProduct($data);
            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all cart items
     * @return void
     */
    public function getAll() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $items = $this->cartModel->getAll();
            echo json_encode($items);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
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
            // Try to remove by ID first
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']);
                $response = $this->cartModel->removeProduct($id);
            } 
            // Fallback to remove by name and price
            elseif (isset($_POST['nom']) && isset($_POST['prix'])) {
                $nom = $_POST['nom'];
                $prix = $_POST['prix'];
                $response = $this->cartModel->removeByNameAndPrice($nom, $prix);
            } 
            else {
                $response = [
                    'success' => false,
                    'error' => 'Product ID or name and price are required'
                ];
            }

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
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
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }
}

// Router for this controller
if ($_GET['action'] ?? '' === 'add_to_cart') {
    $controller = new CartController();
    $controller->addProduct();
} elseif ($_GET['action'] ?? '' === 'get_cart') {
    $controller = new CartController();
    $controller->getAll();
} elseif ($_GET['action'] ?? '' === 'remove_from_cart') {
    $controller = new CartController();
    $controller->removeProduct();
} elseif ($_GET['action'] ?? '' === 'clear_cart') {
    $controller = new CartController();
    $controller->clear();
}
?>
