<?php
/**
 * Product Controller
 * Handles boutique products CRUD needed for front office rendering
 */
class ProductController {
    private $productModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Product.php';
        $this->productModel = new Product();
    }

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
                'prix' => $_POST['prix'] ?? '',
                'description' => $_POST['description'] ?? '',
                'categorie' => $_POST['categorie'] ?? 'complement'
            ];

            $response = $this->productModel->addProduct($data);
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

    public function getAllProducts() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $products = $this->productModel->getAllProducts();
            echo json_encode([
                'success' => true,
                'products' => $products
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
