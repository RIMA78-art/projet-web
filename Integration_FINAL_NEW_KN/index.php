<?php
/**
 * NutriNova MVC Application Router
 * Main entry point for the application
 */

// Suppress error display, log instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Ensure uploads/images directory exists
$uploadsDir = __DIR__ . '/uploads/images';
if (!is_dir($uploadsDir)) {
    @mkdir($uploadsDir, 0755, true);
}

// Start session
session_start();

// Define base paths
define('BASE_PATH', dirname(__FILE__));
define('VIEWS_PATH', BASE_PATH . '/views/');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers/');
define('MODELS_PATH', BASE_PATH . '/models/');

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
    } elseif (strpos($action, 'admin_list_posts') === 0) {
        $controller->listPosts();
    } elseif (strpos($action, 'admin_edit_post') === 0) {
        $controller->editPost();
    } elseif (strpos($action, 'admin_update_post') === 0) {
        $controller->updatePost();
    } elseif (strpos($action, 'admin_delete_post') === 0) {
        $controller->deletePost();
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif (strpos($action, 'comment') !== false || strpos($action, 'commentaire') !== false) {
    require_once CONTROLLERS_PATH . 'CommentaireController.php';
    $controller = new CommentaireController();
    
    // Map actions to controller methods
    if (strpos($action, 'create_comment') === 0) {
        $controller->create();
    } elseif (strpos($action, 'get_comments_by_post') === 0) {
        $controller->getByPost();
    } elseif (strpos($action, 'get_comment_count') === 0) {
        $controller->getCountByPost();
    } elseif (strpos($action, 'update_comment_') === 0) {
        $id = str_replace('update_comment_', '', $action);
        $_POST['id'] = $id;
        $controller->update($id);
    } elseif (strpos($action, 'delete_comment_') === 0) {
        $id = str_replace('delete_comment_', '', $action);
        $controller->delete($id);
    } elseif (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif (strpos($action, 'post') !== false || strpos($action, 'posts') !== false || strpos($action, 'contributor') !== false) {
    require_once CONTROLLERS_PATH . 'PostController.php';
    $controller = new PostController();
    
    // Map actions to controller methods
    if (strpos($action, 'create_post') === 0) {
        $controller->create();
    } elseif (strpos($action, 'get_all_posts') === 0) {
        $controller->getAll();
    } elseif (strpos($action, 'get_top_contributors') === 0) {
        $controller->getTopContributors();
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
} elseif (strpos($action, 'cart') !== false || strpos($action, 'panier') !== false) {
    require_once CONTROLLERS_PATH . 'CartController.php';
    $controller = new CartController();
    
    // Call appropriate method based on action
    if (method_exists($controller, $action)) {
        $controller->$action();
    }
} elseif ($action === 'backoffice') {
    // Serve back office dashboard
    header('Content-Type: text/html; charset=utf-8');
    require_once VIEWS_PATH . 'back_office/index2.php';
} else {
    // Default: serve the main view
    header('Content-Type: text/html; charset=utf-8');
    require_once VIEWS_PATH . 'index.html';
}
?>
