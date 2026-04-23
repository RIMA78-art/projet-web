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
}
?>
