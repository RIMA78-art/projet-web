<?php
/**
 * User Controller
 * Handles user registration, login, and authentication
 */
class UserController {
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/User.php';
        $this->userModel = new User();
    }

    /**
     * Handle user registration
     * @return void
     */
    public function register() {
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
                'prenom' => $_POST['prenom'] ?? '',
                'email' => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
                'taille' => $_POST['taille'] ?? '',
                'poids' => $_POST['poids'] ?? '',
                'objectif' => $_POST['objectif'] ?? '',
                'niveau_sportif' => $_POST['niveau_sportif'] ?? ''
            ];

            $response = $this->userModel->register($data);
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
     * Handle user login
     * @return void
     */
    public function login() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $email = $_POST['email'] ?? '';
            $password = $_POST['mot_de_passe'] ?? '';

            $response = $this->userModel->login($email, $password);
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
     * Get user profile
     * @param int $id
     * @return void
     */
    public function getProfile($id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $user = $this->userModel->getUserById($id);
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
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }
}

// Router for this controller
if ($_GET['action'] ?? '' === 'register') {
    $controller = new UserController();
    $controller->register();
} elseif ($_GET['action'] ?? '' === 'login') {
    $controller = new UserController();
    $controller->login();
} elseif (isset($_GET['action']) && strpos($_GET['action'], 'profile_') === 0) {
    $id = intval(str_replace('profile_', '', $_GET['action']));
    $controller = new UserController();
    $controller->getProfile($id);
}
?>
