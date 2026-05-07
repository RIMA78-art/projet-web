<?php
class SeanceController extends Controller
{
    public function start(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
        $programmeId = (int)($_GET['programme_id'] ?? 0);
        $this->view('front_office/seance', compact('programmeId'));
    }

    public function finish(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
        $seanceModel = new Seance();
        $badgeModel = new Badge();

        $data = [
            'user_id' => (int)$_SESSION['user']['id'],
            'programme_id' => (int)$_POST['programme_id'],
            'duree_effectuee' => max(0, (int)$_POST['duree_effectuee']),
            'calories_brulees' => max(0, (int)$_POST['calories_brulees']),
            'progression' => max(0, min(100, (int)$_POST['progression']))
        ];
        $seanceModel->create($data);

        $history = $seanceModel->byUser((int)$_SESSION['user']['id']);
        $badgeModel->recalculateForUser(
            (int)$_SESSION['user']['id'],
            count($history),
            $seanceModel->caloriesTotalByUser((int)$_SESSION['user']['id']),
            $_SESSION['user']['niveau'] ?? 'debutant'
        );

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Bravo seance terminee'];
        $this->redirect('index.php?route=seance/history');
    }

    public function history(): void
    {
        if (!isset($_SESSION['user'])) $this->redirect('index.php?route=user/login');
        $query = $_GET['q'] ?? '';
        $seances = (new Seance())->byUser((int)$_SESSION['user']['id'], $query);
        $badges = (new Badge())->byUser((int)$_SESSION['user']['id']);
        $this->view('front_office/historique', compact('seances', 'badges', 'query'));
    }
}
