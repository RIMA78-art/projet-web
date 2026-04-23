<?php
/**
 * NutriNova MVC Application Router
 * Main entry point for the application
 */

// Start session
session_start();

// Define base paths from project root
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views/');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers/');
define('MODELS_PATH', ROOT_PATH . '/models/');

// Simple routing system
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Route requests to controllers
if (strpos($action, 'register') === 0 || strpos($action, 'login') === 0) {
    require_once CONTROLLERS_PATH . 'UserController.php';
    $controller = new UserController();
    
    // Extract the specific action (register or login)
    if (strpos($action, 'register') === 0) {
        $controller->register();
    } elseif (strpos($action, 'login') === 0) {
        $controller->login();
    }
} elseif (strpos($action, 'post') !== false || strpos($action, 'posts') !== false) {
    require_once CONTROLLERS_PATH . 'PostController.php';
    $controller = new PostController();
    
    // Map actions to controller methods
    if (strpos($action, 'create_post') === 0) {
        $controller->create();
    } elseif (strpos($action, 'get_all_posts') === 0) {
        $controller->getAll();
    } elseif (strpos($action, 'update_post_') === 0) {
        $id = str_replace('update_post_', '', $action);
        $_POST['id'] = $id;
        $controller->update($id);
    } elseif (strpos($action, 'delete_post_') === 0) {
        $id = str_replace('delete_post_', '', $action);
        $controller->delete($id);
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif (strpos($action, 'product') !== false || strpos($action, 'boutique_product') !== false) {
    require_once CONTROLLERS_PATH . 'ProductController.php';
    $controller = new ProductController();

    if ($action === 'add_product') {
        $controller->addProduct();
    } elseif ($action === 'get_products') {
        $controller->getAllProducts();
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif (strpos($action, 'cart') !== false || strpos($action, 'panier') !== false) {
    require_once CONTROLLERS_PATH . 'CartController.php';
    $controller = new CartController();
    
    // Call appropriate method based on action
    if ($action === 'add_to_cart') {
        $controller->addProduct();
    } elseif ($action === 'get_cart') {
        $controller->getAll();
    } elseif ($action === 'remove_from_cart') {
        $controller->removeProduct();
    } elseif ($action === 'clear_cart') {
        $controller->clear();
    } elseif ($action === 'cart_admin_stats') {
        $controller->getAdminStats();
    } elseif ($action === 'cart_admin_add_product') {
        $controller->addProductAdmin();
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif (strpos($action, 'admin') !== false) {
    require_once CONTROLLERS_PATH . 'AdminUserController.php';
    $controller = new AdminUserController();
    
    // Map admin actions to controller methods
    if (strpos($action, 'admin_list_users') === 0) {
        $controller->listUsers();
    } elseif (strpos($action, 'admin_delete_user') === 0) {
        $controller->deleteUser();
    } elseif (strpos($action, 'admin_get_user_details') === 0) {
        $controller->getUserDetails();
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} else {
    // Default: serve the main view
    header('Content-Type: text/html; charset=utf-8');
    require_once VIEWS_PATH . 'index.html';
}
?>
