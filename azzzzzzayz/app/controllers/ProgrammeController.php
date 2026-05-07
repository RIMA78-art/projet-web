<?php
class ProgrammeController extends Controller
{
    private Programme $model;
    public function __construct() { $this->model = new Programme(); }

    public function front(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
        $search = trim($_GET['q'] ?? '');
        $programmes = $this->model->all($search);
        $this->view('front_office/programmes', compact('programmes', 'search'));
    }

    public function index(): void
    {
        $this->guardAdmin();
        $search = trim($_GET['q'] ?? '');
        $programmes = $this->model->all($search);
        $coaches = (new Coach())->all();
        $this->view('back_office/programmes', compact('programmes', 'coaches', 'search'));
    }

    public function store(): void
    {
        $this->guardAdmin();
        $this->model->create($_POST);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Programme ajoute'];
        $this->redirect('index.php?route=programme/index');
    }

    public function update(): void
    {
        $this->guardAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $this->model->update($id, $_POST);
        $_SESSION['flash'] = ['type' => 'primary', 'message' => 'Programme modifie'];
        $this->redirect('index.php?route=programme/index');
    }

    public function delete(): void
    {
        $this->guardAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Programme supprime'];
        $this->redirect('index.php?route=programme/index');
    }

    public function exportPdf(): void
    {
        $this->guardAuth();
        $id = (int)($_GET['id'] ?? 0);
        $programme = $this->model->find($id);
        if (!$programme) exit('Programme introuvable');

        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        $logoPath = __DIR__ . '/../../public/assets/images/nutri-nova-logo.png';
        $logoTag = file_exists($logoPath)
            ? '<img src="' . $logoPath . '" style="height:48px" alt="Nutri Nova">'
            : '<h2 style="margin:0;color:#16a34a">Nutri Nova</h2>';

        $html = '<div style="font-family: DejaVu Sans, sans-serif;">'
            . '<div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #16a34a;padding-bottom:8px;">'
            . $logoTag
            . '<div style="text-align:right;"><strong>Programme PDF</strong><br><small>Smart Sport & Nutrition Platform</small></div></div>'
            . '<h1 style="color:#0f172a">' . htmlspecialchars($programme['nom']) . '</h1>'
            . '<p><strong>Difficulte:</strong> ' . htmlspecialchars($programme['difficulte']) . '</p>'
            . '<p><strong>Duree:</strong> ' . (int)$programme['duree_semaines'] . ' semaines</p>'
            . '<p><strong>Description:</strong><br>' . nl2br(htmlspecialchars($programme['description'] ?? '')) . '</p>'
            . '<hr><small>Nutri Nova - genere le ' . date('d/m/Y H:i') . '</small></div>';

        if (!file_exists($autoloadPath)) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Export Programme</title></head><body>';
            echo $html;
            echo '<p style="margin-top:16px;color:#b45309;"><strong>Info:</strong> DomPDF non installe. Installez-le avec <code>composer require dompdf/dompdf</code> pour un vrai PDF.</p>';
            echo '</body></html>';
            return;
        }

        require_once $autoloadPath;
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('programme_' . $id . '.pdf');
    }

    private function guardAuth(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
    }

    private function guardAdmin(): void
    {
        $this->guardAuth();
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Acces reserve a l admin'];
            $this->redirect('index.php?route=dashboard/index');
        }
    }
}
