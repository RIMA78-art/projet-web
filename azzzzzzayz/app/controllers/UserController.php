<?php
class UserController extends Controller
{
    private User $model;
    public function __construct() { $this->model = new User(); }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = $this->model->findByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Bienvenue ' . $user['nom']];
                $this->redirect('index.php?route=dashboard/index');
            }
            $error = 'Identifiants invalides';
            $this->view('front_office/login', compact('error'));
            return;
        }
        $this->view('front_office/login');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->create($_POST);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Inscription reussie, connectez-vous'];
            $this->redirect('index.php?route=user/login');
        }
        $this->view('front_office/register');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('index.php?route=user/login');
    }
}
