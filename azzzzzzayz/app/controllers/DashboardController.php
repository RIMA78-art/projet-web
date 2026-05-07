<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $programmeModel = new Programme();
        $coachModel = new Coach();
        $userModel = new User();
        $seanceModel = new Seance();

        $programmes = $programmeModel->all();
        $difficulty = $programmeModel->difficultyDistribution();
        $weekly = $seanceModel->sessionsByWeek();
        $trend = $seanceModel->caloriesTrend();

        $stats = [
            'programmes' => count($programmes),
            'coachs' => count($coachModel->all()),
            'usersActifs' => $userModel->activeCount(),
            'seancesTerminees' => $seanceModel->completedCount(),
            'seancesSemaine' => $seanceModel->sessionsThisWeek(),
            'seancesMois' => $seanceModel->sessionsThisMonth(),
            'caloriesTotal' => $seanceModel->caloriesTotal(),
            'caloriesAvg' => $seanceModel->caloriesAvg(),
            'completionRate' => $seanceModel->completionRate(),
            'topProgrammes' => array_slice($programmes, 0, 5),
        ];

        $recommended = $programmeModel->recommendedFor($_SESSION['user']);

        $chart = [
            'difficultyLabels' => array_map(fn($r) => $r['difficulte'], $difficulty),
            'difficultyValues' => array_map(fn($r) => (int)$r['total'], $difficulty),
            'weeklyLabels' => array_reverse(array_map(fn($r) => 'S' . $r['week_key'], $weekly)),
            'weeklyValues' => array_reverse(array_map(fn($r) => (int)$r['total'], $weekly)),
            'caloriesLabels' => array_reverse(array_map(fn($r) => $r['jour'], $trend)),
            'caloriesValues' => array_reverse(array_map(fn($r) => (int)$r['total'], $trend)),
        ];

        $this->view('back_office/dashboard', compact('stats', 'recommended', 'chart'));
    }

    private function requireAuth(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?route=user/login');
        }
    }
}
