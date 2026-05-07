<?php
class CoachController extends Controller
{
    private Coach $model;
    public function __construct() { $this->model = new Coach(); }

    public function front(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
        $search = trim($_GET['q'] ?? '');
        $coaches = $this->model->all($search);
        $this->view('front_office/coaches', compact('coaches', 'search'));
    }

    public function index(): void
    {
        $this->guardAdmin();
        $search = trim($_GET['q'] ?? '');
        $coaches = $this->model->all($search);
        $this->view('back_office/coaches', compact('coaches', 'search'));
    }

    public function store(): void
    {
        $this->guardAdmin();
        $this->model->create($_POST);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Coach ajoute'];
        $this->redirect('index.php?route=coach/index');
    }

    public function update(): void
    {
        $this->guardAdmin();
        $this->model->update((int)$_POST['id'], $_POST);
        $_SESSION['flash'] = ['type' => 'primary', 'message' => 'Coach modifie'];
        $this->redirect('index.php?route=coach/index');
    }

    public function delete(): void
    {
        $this->guardAdmin();
        $this->model->delete((int)$_GET['id']);
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Coach supprime'];
        $this->redirect('index.php?route=coach/index');
    }

    private function guardAdmin(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?route=user/login');
        }
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Acces reserve a l admin'];
            $this->redirect('index.php?route=dashboard/index');
        }
    }
}
