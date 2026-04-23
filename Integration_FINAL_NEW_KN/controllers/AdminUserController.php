<?php
/**
 * AdminUser Controller
 * Handles admin user management operations
 */
class AdminUserController {
    private $adminUserModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/AdminUser.php';
        $this->adminUserModel = new AdminUser();
    }

    /**
     * Display list of all users (Admin Dashboard)
     * @return void
     */
    public function listUsers() {
        try {
            // Get pagination parameters
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Get search parameter
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            // Fetch users
            $users = $this->adminUserModel->getAllUsers($limit, $offset, $search);
            $totalUsers = $this->adminUserModel->getUserCount($search);
            $totalPages = ceil($totalUsers / $limit);

            // Get statistics
            $stats = $this->adminUserModel->getUserStatistics();

            // Pass data to view
            require_once __DIR__ . '/../views/admin/users.php';

        } catch (Exception $e) {
            // Log error and display error message
            error_log("AdminUserController::listUsers - " . $e->getMessage());
            $error = "Erreur lors du chargement des utilisateurs: " . $e->getMessage();
            require_once __DIR__ . '/../views/admin/error.php';
        }
    }

    /**
     * Delete a user (AJAX request)
     * @return void
     */
    public function deleteUser() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'error' => 'User ID is required'
                ]);
                exit;
            }

            // Delete user
            $deleted = $this->adminUserModel->deleteUser($id);

            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'User not found'
                ]);
            }

        } catch (Exception $e) {
            error_log("AdminUserController::deleteUser - " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error deleting user: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Get user details (AJAX request)
     * @return void
     */
    public function getUserDetails() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($id <= 0) {
                echo json_encode([
                    'success' => false,
                    'error' => 'User ID is required'
                ]);
                exit;
            }

            // Fetch user
            $user = $this->adminUserModel->getUserById($id);

            if ($user) {
                echo json_encode([
                    'success' => true,
                    'user' => $user
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'User not found'
                ]);
            }

        } catch (Exception $e) {
            error_log("AdminUserController::getUserDetails - " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error fetching user: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Display list of all posts (Admin Dashboard)
     * @return void
     */
    public function listPosts() {
        try {
            // Get pagination parameters
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            // Load Post model
            require_once __DIR__ . '/../models/Post.php';
            $postModel = new Post();

            // Fetch posts
            $posts = $postModel->getAll(1000); // Get all posts for pagination
            
            // Paginate posts in PHP
            $totalPosts = count($posts);
            $totalPages = ceil($totalPosts / $limit);
            $posts = array_slice($posts, $offset, $limit);

            // Pass data to view
            require_once __DIR__ . '/../views/admin/posts.php';

        } catch (Exception $e) {
            // Log error and display error message
            error_log("AdminUserController::listPosts - " . $e->getMessage());
            $error = "Error loading posts: " . $e->getMessage();
            require_once __DIR__ . '/../views/admin/error.php';
        }
    }

    /**
     * Show post edit form
     * @return void
     */
    public function editPost() {
        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

            if ($id <= 0) {
                throw new Exception('Post ID is required');
            }

            // Load Post model
            require_once __DIR__ . '/../models/Post.php';
            $postModel = new Post();

            // Fetch post
            $post = $postModel->getById($id);

            if (!$post) {
                throw new Exception('Post not found');
            }

            // Pass data to view
            require_once __DIR__ . '/../views/admin/edit_post.php';

        } catch (Exception $e) {
            // Log error and display error message
            error_log("AdminUserController::editPost - " . $e->getMessage());
            $error = "Error: " . $e->getMessage();
            require_once __DIR__ . '/../views/admin/error.php';
        }
    }

    /**
     * Update a post (AJAX request)
     * @return void
     */
    /**
     * Update a post (AJAX request)
     * @return void
     */
    public function updatePost() {
        // Clear all output buffers to ensure clean JSON response
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $titre_post = isset($_POST['titre_post']) ? $_POST['titre_post'] : '';
        $contenu_post = isset($_POST['contenu_post']) ? $_POST['contenu_post'] : '';

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Post ID is required']);
            exit;
        }

        try {
            // Load Post model
            require_once __DIR__ . '/../models/Post.php';
            $postModel = new Post();

            // Update post
            $response = $postModel->update($id, [
                'titre_post' => $titre_post,
                'contenu_post' => $contenu_post
            ]);

            if ($response['success']) {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            
            echo json_encode($response);

        } catch (Exception $e) {
            error_log("AdminUserController::updatePost - " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }

        exit;
    }

    /**
     * Delete a post (AJAX request)
     * @return void
     */
    public function deletePost() {
        // Start by clearing all output buffers first
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // NOW set headers (after buffers are cleared)
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Check request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'Invalid request method']));
        }

        // Get and validate ID
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            http_response_code(400);
            die(json_encode(['success' => false, 'error' => 'Post ID is required']));
        }

        // Load models
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Post.php';
        
        $postModel = new Post();
        $response = $postModel->delete($id);

        // Send response with appropriate status code
        http_response_code($response['success'] ? 200 : 400);
        die(json_encode($response));
    }
}
?>
