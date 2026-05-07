<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../Model/Meal.php';
require_once __DIR__ . '/../Model/Ingredient.php';

/**
 * NutritionController
 * ContrÃ´leur MVC - gÃ¨re le routage et les opÃ©rations BD pour Meal et Ingredient
 */
class NutritionController {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Route les requÃªtes vers les bonnes actions
     */
    public function route() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'home';
        $section = isset($_GET['section']) ? $_GET['section'] : 'meal';
        
        // Actions Front Office
        if ($action === 'home') {
            $this->frontHome();
        } elseif ($action === 'meal-detail') {
            $this->frontMealDetail();
        } elseif ($action === 'top-meals') {
            $this->frontTopMeals();
        } elseif ($action === 'add-rating') {
            $this->addMealRating();
        } elseif ($action === 'nutrition-plan') {
            $this->frontNutritionPlan();
        } elseif ($action === 'pdf-plan-qr') {
            $this->pdfPlanDirect();
        } elseif ($action === 'export-plan-csv') {
            $this->exportNutritionPlanCsv();
        }
        // Actions Back Office - Meals
        elseif ($action === 'admin-meals' && $section === 'meal') {
            $this->adminMealsList();
        } elseif ($action === 'admin-meal-add' && $section === 'meal') {
            $this->adminMealForm('add');
        } elseif ($action === 'admin-meal-edit' && $section === 'meal') {
            $this->adminMealForm('edit');
        } elseif ($action === 'admin-meal-create' && $section === 'meal') {
            $this->adminMealCreate();
        } elseif ($action === 'admin-meal-update' && $section === 'meal') {
            $this->adminMealUpdate();
        } elseif ($action === 'admin-meal-delete' && $section === 'meal') {
            $this->adminMealDelete();
        }
        // Actions Back Office - Ingredients
        elseif ($action === 'admin-ingredients' && $section === 'ingredient') {
            $this->adminIngredientsList();
        } elseif ($action === 'admin-ingredient-add' && $section === 'ingredient') {
            $this->adminIngredientForm('add');
        } elseif ($action === 'admin-ingredient-edit' && $section === 'ingredient') {
            $this->adminIngredientForm('edit');
        } elseif ($action === 'admin-ingredient-create' && $section === 'ingredient') {
            $this->adminIngredientCreate();
        } elseif ($action === 'admin-ingredient-update' && $section === 'ingredient') {
            $this->adminIngredientUpdate();
        } elseif ($action === 'admin-ingredient-delete' && $section === 'ingredient') {
            $this->adminIngredientDelete();
        }
        // Actions de gestion des ingrÃ©dients dans un repas
        elseif ($action === 'ml-model') {
            $this->serveMlModel();
        }
        elseif ($action === 'admin-meal-add-ingredient' && $section === 'meal') {
            $this->adminMealAddIngredient();
        } elseif ($action === 'admin-meal-remove-ingredient' && $section === 'meal') {
            $this->adminMealRemoveIngredient();
        }
        else {
            $this->frontHome();
        }
    }
    
    // ==================== FRONT OFFICE ====================

    private function frontNutritionPlan() {
        $meals = $this->getAllMeals();
        $plan  = null;
        $tdee  = null;
        $error = null;
        $input = [];
        $qr_code_url = null;
        $share_plan_url = null;
        $whatsapp_share_url = null;
        $whatsapp_app_url = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $weight   = (float)($_POST['weight']   ?? 0);
            $height   = (int)  ($_POST['height']   ?? 0);
            $age      = (int)  ($_POST['age']       ?? 0);
            $gender   =        ($_POST['gender']   ?? 'male');
            $activity = (float)($_POST['activity'] ?? 1.55);
            $goal     =        ($_POST['goal']     ?? 'maintain');
            $priorities = $this->sanitizePriorities($_POST['priorities'] ?? []);
            $input    = compact('weight', 'height', 'age', 'gender', 'activity', 'goal', 'priorities');

            if ($weight > 0 && $height > 0 && $age > 0) {
                $plan = $this->buildNutritionPlan($meals, $weight, $height, $age, $gender, $activity, $goal, $priorities);
                $tdee = (float)$plan['tdee'];

                // Générer le code QR qui télécharge le PDF quand scanné
                $qr_params = http_build_query([
                    'action'   => 'pdf-plan-qr',
                    'weight'   => $weight,
                    'height'   => $height,
                    'age'      => $age,
                    'gender'   => $gender,
                    'activity' => $activity,
                    'goal'     => $goal,
                    'priorities' => implode(',', $priorities),
                ]);
                $share_plan_url = APP_URL . '/index.php?' . $qr_params;
                $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($share_plan_url);
                $whatsapp_message = "Découvre mon plan nutritionnel NutriNova : " . $share_plan_url;
                $whatsapp_share_url = 'https://wa.me/?text=' . urlencode($whatsapp_message);
                $whatsapp_app_url = 'whatsapp://send?text=' . urlencode($whatsapp_message);
            } else {
                $error = 'Veuillez remplir tous les champs correctement.';
            }
        }

        include 'View/Front/nutrition-plan.php';
    }

    private function addMealRating(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $meal_id  = (int)($_POST['meal_id'] ?? 0);
        $rating   = (int)($_POST['rating'] ?? 0);
        $comment  = trim($_POST['comment'] ?? '');
        $name     = trim($_POST['name'] ?? 'Anonyme');
        $email    = trim($_POST['email'] ?? '');

        if ($meal_id <= 0 || $rating < 1 || $rating > 5) {
            header('Location: index.php?action=meal-detail&id=' . $meal_id);
            exit;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO meal_ratings (id_meal, rating, comment, visitor_name, visitor_email) 
                 VALUES (:meal_id, :rating, :comment, :name, :email)"
            );
            $stmt->execute([
                ':meal_id' => $meal_id,
                ':rating'  => $rating,
                ':comment' => $comment ?: null,
                ':name'    => $name,
                ':email'   => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null,
            ]);
            $_SESSION['success'] = '✅ Merci pour votre évaluation !';
        } catch (PDOException $e) {
            $_SESSION['error'] = '❌ Erreur lors de l\'ajout de l\'évaluation';
        }

        header('Location: index.php?action=meal-detail&id=' . $meal_id);
        exit;
    }

    private function frontTopMeals(): void {
        $topMeals = $this->getTopMealsByRating();
        include 'View/Front/top-meals.php';
    }

    private function pdfPlanDirect(): void {
        $weight   = (float)($_GET['weight']   ?? 0);
        $height   = (int)  ($_GET['height']   ?? 0);
        $age      = (int)  ($_GET['age']      ?? 0);
        $gender   =        ($_GET['gender']   ?? 'male');
        $activity = (float)($_GET['activity'] ?? 1.55);
        $goal     =        ($_GET['goal']     ?? 'maintain');
        $priorities = $this->sanitizePriorities($_GET['priorities'] ?? '');

        if ($weight <= 0 || $height <= 0 || $age <= 0) {
            header('Location: index.php?action=nutrition-plan');
            exit;
        }

        $meals = $this->getAllMeals();
        $plan = $this->buildNutritionPlan($meals, $weight, $height, $age, $gender, $activity, $goal, $priorities);
        $input = compact('weight', 'height', 'age', 'gender', 'activity', 'goal', 'priorities');
        $share_plan_url = APP_URL . '/index.php?' . http_build_query([
            'action'   => 'pdf-plan-qr',
            'weight'   => $weight,
            'height'   => $height,
            'age'      => $age,
            'gender'   => $gender,
            'activity' => $activity,
            'goal'     => $goal,
            'priorities' => implode(',', $priorities),
        ]);
        $whatsapp_message = 'Découvre mon plan nutritionnel NutriNova : ' . $share_plan_url;
        $whatsapp_share_url = 'https://wa.me/?text=' . urlencode($whatsapp_message);
        $whatsapp_app_url = 'whatsapp://send?text=' . urlencode($whatsapp_message);
        $is_pdf_download = true;
        include 'View/Front/nutrition-plan.php';
    }

    private function exportNutritionPlanCsv(): void {
        $weight   = (float)($_POST['weight']   ?? 0);
        $height   = (int)  ($_POST['height']   ?? 0);
        $age      = (int)  ($_POST['age']       ?? 0);
        $gender   =        ($_POST['gender']   ?? 'male');
        $activity = (float)($_POST['activity'] ?? 1.55);
        $goal     =        ($_POST['goal']     ?? 'maintain');
        $priorities = $this->sanitizePriorities($_POST['priorities'] ?? []);

        if ($weight <= 0 || $height <= 0 || $age <= 0) {
            header('Location: index.php?action=nutrition-plan');
            exit;
        }

        $meals = $this->getAllMeals();
        $fullPlan = $this->buildNutritionPlan($meals, $weight, $height, $age, $gender, $activity, $goal, $priorities);
        $bmr = (float)$fullPlan['bmr'];
        $tdee = (float)$fullPlan['tdee'];

        $plan = [
            'Petit-déjeuner' => [
                'repas'  => $fullPlan['breakfast'],
                'cible'  => (int)$fullPlan['targets']['breakfast'],
            ],
            'Déjeuner' => [
                'repas'  => $fullPlan['lunch'],
                'cible'  => (int)$fullPlan['targets']['lunch'],
            ],
            'Dîner' => [
                'repas'  => $fullPlan['dinner'],
                'cible'  => (int)$fullPlan['targets']['dinner'],
            ],
        ];

        $filename = 'plan-nutritionnel-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 pour Excel

        fputcsv($out, ['Plan Nutritionnel NutriNova - ' . date('d/m/Y')], ';');
        fputcsv($out, ['Profil', $gender === 'male' ? 'Homme' : 'Femme', $weight . ' kg', $height . ' cm', $age . ' ans'], ';');
        fputcsv($out, ['BMR', (int)round($bmr) . ' kcal', 'TDEE / Objectif', (int)round($tdee) . ' kcal'], ';');
        if (!empty($priorities)) {
            $priorityLabels = [
                'maintain_muscle' => 'Maintien musculaire',
                'reduce_sugar_sodium' => 'Réduction sucre/sodium (proxy glucides)',
            ];
            $selectedPriorityLabels = array_map(fn($p) => $priorityLabels[$p] ?? $p, $priorities);
            fputcsv($out, ['Priorités', implode(' + ', $selectedPriorityLabels)], ';');
        }
        fputcsv($out, [], ';');
        fputcsv($out, ['Repas', 'Nom du plat', 'Calories (kcal)', 'Cible (kcal)', 'Protéines (g)', 'Glucides (g)', 'Lipides (g)'], ';');

        foreach ($plan as $slot => $data) {
            $m = $data['repas'];
            if ($m) {
                fputcsv($out, [
                    $slot,
                    $m['nom'],
                    number_format((float)$m['calories'], 1, ',', ''),
                    $data['cible'],
                    number_format((float)$m['protein'], 1, ',', ''),
                    number_format((float)$m['carb'],    1, ',', ''),
                    number_format((float)$m['fat'],     1, ',', ''),
                ], ';');
            } else {
                fputcsv($out, [$slot, 'Aucun repas disponible', '', $data['cible'], '', '', ''], ';');
            }
        }

        fputcsv($out, [], ';');
        $totalCal  = array_sum(array_map(fn($d) => (float)($d['repas']['calories'] ?? 0), $plan));
        $totalProt = array_sum(array_map(fn($d) => (float)($d['repas']['protein']  ?? 0), $plan));
        $totalCarb = array_sum(array_map(fn($d) => (float)($d['repas']['carb']     ?? 0), $plan));
        $totalFat  = array_sum(array_map(fn($d) => (float)($d['repas']['fat']      ?? 0), $plan));
        fputcsv($out, [
            'TOTAL JOURNÉE',
            '',
            number_format($totalCal,  1, ',', ''),
            (int)round($tdee),
            number_format($totalProt, 1, ',', ''),
            number_format($totalCarb, 1, ',', ''),
            number_format($totalFat,  1, ',', ''),
        ], ';');

        fclose($out);
        exit;
    }

    private function sanitizePriorities($rawPriorities): array {
        if (is_string($rawPriorities)) {
            $rawPriorities = $rawPriorities === '' ? [] : explode(',', $rawPriorities);
        }
        if (!is_array($rawPriorities)) {
            return [];
        }

        $allowed = ['maintain_muscle', 'reduce_sugar_sodium'];
        $clean = array_values(array_unique(array_filter(
            array_map('trim', $rawPriorities),
            fn($p) => in_array($p, $allowed, true)
        )));

        return $clean;
    }

    private function buildNutritionPlan(
        array $meals,
        float $weight,
        int $height,
        int $age,
        string $gender,
        float $activity,
        string $goal,
        array $priorities
    ): array {
        if ($gender === 'male') {
            $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }

        $tdee = $bmr * $activity;
        if ($goal === 'loss') {
            $tdee -= 500;
        } elseif ($goal === 'gain') {
            $tdee += 300;
        }

        $macroRatios = ['protein' => 0.25, 'carb' => 0.45, 'fat' => 0.30];
        if (in_array('maintain_muscle', $priorities, true)) {
            $macroRatios['protein'] += 0.10;
            $macroRatios['carb'] -= 0.05;
            $macroRatios['fat'] -= 0.05;
        }
        if (in_array('reduce_sugar_sodium', $priorities, true)) {
            $macroRatios['protein'] += 0.05;
            $macroRatios['carb'] -= 0.10;
            $macroRatios['fat'] += 0.05;
        }

        // Renormalise pour garantir la somme à 100%.
        $ratioSum = max(0.01, array_sum($macroRatios));
        foreach ($macroRatios as $k => $v) {
            $macroRatios[$k] = $v / $ratioSum;
        }

        $dailyMacroTargets = [
            'protein' => (int)round(($tdee * $macroRatios['protein']) / 4),
            'carb'    => (int)round(($tdee * $macroRatios['carb']) / 4),
            'fat'     => (int)round(($tdee * $macroRatios['fat']) / 9),
        ];

        $mealPercents = ['breakfast' => 0.25, 'lunch' => 0.40, 'dinner' => 0.35];
        $targets = [
            'breakfast' => $tdee * $mealPercents['breakfast'],
            'lunch'     => $tdee * $mealPercents['lunch'],
            'dinner'    => $tdee * $mealPercents['dinner'],
        ];

        $slotMacros = [];
        foreach ($mealPercents as $slot => $pct) {
            $slotMacros[$slot] = [
                'protein' => $dailyMacroTargets['protein'] * $pct,
                'carb'    => $dailyMacroTargets['carb'] * $pct,
                'fat'     => $dailyMacroTargets['fat'] * $pct,
            ];
        }

        $breakfastMeals = array_filter($meals, fn($m) => strtolower($m['type']) === 'petit déjeuner');
        $lunchMeals     = array_filter($meals, fn($m) => strtolower($m['type']) === 'déjeuner');
        $dinnerMeals    = array_filter($meals, fn($m) => strtolower($m['type']) === 'dîner');

        return [
            'tdee'      => (int)round($tdee),
            'bmr'       => (int)round($bmr),
            'breakfast' => $this->findBestMealForObjectives($breakfastMeals, $targets['breakfast'], $priorities, $slotMacros['breakfast']),
            'lunch'     => $this->findBestMealForObjectives($lunchMeals, $targets['lunch'], $priorities, $slotMacros['lunch']),
            'dinner'    => $this->findBestMealForObjectives($dinnerMeals, $targets['dinner'], $priorities, $slotMacros['dinner']),
            'targets'   => [
                'breakfast' => (int)round($targets['breakfast']),
                'lunch'     => (int)round($targets['lunch']),
                'dinner'    => (int)round($targets['dinner']),
            ],
            'macro_targets' => $dailyMacroTargets,
            'priorities' => $priorities,
        ];
    }

    private function findBestMealForObjectives(array $meals, float $targetCalories, array $priorities, array $slotMacroTarget): ?array {
        if (empty($meals)) {
            return null;
        }

        // Sans priorité, on garde le comportement historique (plus proche en calories).
        if (empty($priorities)) {
            return $this->findClosestMeal($meals, $targetCalories);
        }

        $best = null;
        $bestScore = PHP_FLOAT_MAX;

        foreach ($meals as $meal) {
            $cal = (float)($meal['calories'] ?? 0);
            $protein = (float)($meal['protein'] ?? 0);
            $carb = (float)($meal['carb'] ?? 0);
            $fat = (float)($meal['fat'] ?? 0);

            $score = abs($cal - $targetCalories);

            if (in_array('maintain_muscle', $priorities, true)) {
                $score += abs($protein - (float)$slotMacroTarget['protein']) * 2.0;
                $proteinDensity = $protein / max(1.0, $cal / 100.0);
                if ($proteinDensity < 8.0) {
                    $score += (8.0 - $proteinDensity) * 18.0;
                }
            }

            if (in_array('reduce_sugar_sodium', $priorities, true)) {
                // Proxy faute de colonnes sucre/sodium: contrôle des glucides rapides et de densité énergétique.
                $score += max(0.0, $carb - ((float)$slotMacroTarget['carb'] * 1.1)) * 4.0;
                $score += max(0.0, $fat - ((float)$slotMacroTarget['fat'] * 1.2)) * 1.8;
                $score += max(0.0, ($cal / 100.0) - 3.2) * 12.0;
            }

            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $meal;
            }
        }

        return $best;
    }

    private function findClosestMeal(array $meals, float $target): ?array {
        if (empty($meals)) return null;
        $best     = null;
        $bestDiff = PHP_INT_MAX;
        foreach ($meals as $meal) {
            $diff = abs((float)$meal['calories'] - $target);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best     = $meal;
            }
        }
        return $best;
    }

    private function frontHome() {
        $meals = $this->getAllMeals();
        include 'View/Front/index.php';
    }
    
    private function frontMealDetail() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $meal = $this->getMealById($id);
        
        if (!$meal) {
            header('Location: index.php');
            exit;
        }
        
        include 'View/Front/meal-detail.php';
    }
    
    // ==================== BACK OFFICE - MEALS ====================
    
    private function adminMealsList() {
        $perPage     = 10;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $totalCount  = $this->countMeals();
        $totalPages  = max(1, (int)ceil($totalCount / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        $meals              = $this->getMealsPaged($perPage, $offset);
        $breakfastCount     = $this->countMealsByType('petit déjeuner');
        $lunchDinnerCount   = $totalCount - $breakfastCount;
        include 'View/Back/meals-list.php';
    }
    
    private function adminMealForm($mode) {
        $meal = null;
        $ingredients = $this->getAllIngredients();
        $meal_ingredients = [];
        
        if ($mode === 'edit') {
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $meal = $this->getMealById($id);
            if (!$meal) {
                $_SESSION['error'] = 'Repas non trouvÃ©';
                header('Location: index.php?action=admin-meals&section=meal');
                exit;
            }
            $meal_ingredients = $meal['ingredients'];
        }
        
        $is_edit = ($mode === 'edit');
        include 'View/Back/meal-form.php';
    }
    
    private function adminMealCreate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meal-add&section=meal');
            exit;
        }
        
        $result = $this->addMeal($_POST, $_FILES['image'] ?? null);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-meals&section=meal');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-meal-add&section=meal');
        }
        exit;
    }
    
    private function adminMealUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meals&section=meal');
            exit;
        }
        
        $id = isset($_POST['id_meal']) ? $_POST['id_meal'] : 0;
        $result = $this->updateMeal($id, $_POST, $_FILES['image'] ?? null);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-meals&section=meal');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $id);
        }
        exit;
    }
    
    private function adminMealDelete() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $result = $this->deleteMeal($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: index.php?action=admin-meals&section=meal');
        exit;
    }
    
    private function adminMealAddIngredient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meals&section=meal');
            exit;
        }
        
        $meal_id = $_POST['meal_id'] ?? 0;
        $ingredient_id = $_POST['ingredient_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        $this->addIngredientToMeal($meal_id, $ingredient_id, $quantity);
        
        header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $meal_id);
        exit;
    }
    
    private function adminMealRemoveIngredient() {
        $meal_id = $_GET['meal_id'] ?? 0;
        $ingredient_id = $_GET['ingredient_id'] ?? 0;
        
        $this->removeIngredientFromMeal($meal_id, $ingredient_id);
        
        header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $meal_id);
        exit;
    }
    
    // ==================== BACK OFFICE - INGREDIENTS ====================
    
    private function adminIngredientsList() {
        $perPage     = 10;
        $currentPage = max(1, (int)($_GET['page'] ?? 1));
        $totalCount  = $this->countIngredients();
        $totalPages  = max(1, (int)ceil($totalCount / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset      = ($currentPage - 1) * $perPage;

        $ingredients = $this->getIngredientsPaged($perPage, $offset);
        include 'View/Back/ingredients-list.php';
    }
    
    private function adminIngredientForm($mode) {
        $ingredient = null;
        
        if ($mode === 'edit') {
            $id = $_GET['id'] ?? 0;
            $ingredient = $this->getIngredientById($id);
            if (!$ingredient) {
                $_SESSION['error'] = 'IngrÃ©dient non trouvÃ©';
                header('Location: index.php?action=admin-ingredients&section=ingredient');
                exit;
            }
        }
        
        $is_edit = ($mode === 'edit');
        include 'View/Back/ingredient-form.php';
    }
    
    private function adminIngredientCreate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-ingredient-add&section=ingredient');
            exit;
        }
        
        $result = $this->addIngredient($_POST);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-ingredients&section=ingredient');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-ingredient-add&section=ingredient');
        }
        exit;
    }
    
    private function adminIngredientUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-ingredients&section=ingredient');
            exit;
        }
        
        $id = $_POST['id_ingredient'] ?? 0;
        $result = $this->updateIngredient($id, $_POST);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-ingredients&section=ingredient');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-ingredient-edit&section=ingredient&id=' . $id);
        }
        exit;
    }
    
    private function adminIngredientDelete() {
        $id = $_GET['id'] ?? 0;
        $result = $this->deleteIngredient($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: index.php?action=admin-ingredients&section=ingredient');
        exit;
    }

    private function serveMlModel() {
        $model_path = 'ml/models/simple_meal_model.json';
        if (!file_exists($model_path)) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Model not found']);
            exit;
        }
        header('Content-Type: application/json');
        header('Cache-Control: max-age=3600, private');
        echo file_get_contents($model_path);
        exit;
    }
    
    // ==================== MEAL CRUD OPERATIONS ====================
    
    public function addMeal(array $data, ?array $file = null): array {
        $validation = $this->validateMeal($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $image_path = null;
        if ($file && $file['tmp_name']) {
            $img_validation = $this->validateImage($file);
            if (!$img_validation['success']) {
                return $img_validation;
            }
            $image_path = $this->uploadImage($file);
        }
        
        try {
            $sql = "INSERT INTO meals (nom, calories, protein, carb, fat, type, image) 
                    VALUES (:nom, :calories, :protein, :carb, :fat, :type, :image)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => (float)$data['calories'],
                ':protein' => (float)$data['protein'],
                ':carb' => (float)$data['carb'],
                ':fat' => (float)$data['fat'],
                ':type' => trim($data['type']),
                ':image' => $image_path
            ]);
            
            return ['success' => true, 'message' => 'Repas crÃ©Ã© avec succÃ¨s', 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la crÃ©ation: ' . $e->getMessage()];
        }
    }
    
    public function updateMeal(int $id, array $data, ?array $file = null): array {
        $validation = $this->validateMeal($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $meal = $this->getMealById($id);
        if (!$meal) {
            return ['success' => false, 'message' => 'Repas non trouvÃ©'];
        }
        
        $image_path = $meal['image'];
        if ($file && $file['tmp_name']) {
            $img_validation = $this->validateImage($file);
            if (!$img_validation['success']) {
                return $img_validation;
            }
            if ($meal['image'] && file_exists($meal['image'])) {
                unlink($meal['image']);
            }
            $image_path = $this->uploadImage($file);
        }
        
        try {
            $sql = "UPDATE meals SET nom = :nom, calories = :calories, protein = :protein, 
                    carb = :carb, fat = :fat, type = :type, image = :image WHERE id_meal = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => (float)$data['calories'],
                ':protein' => (float)$data['protein'],
                ':carb' => (float)$data['carb'],
                ':fat' => (float)$data['fat'],
                ':type' => trim($data['type']),
                ':image' => $image_path,
                ':id' => $id
            ]);
            
            return ['success' => true, 'message' => 'Repas mis Ã  jour avec succÃ¨s'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise Ã  jour: ' . $e->getMessage()];
        }
    }
    
    public function deleteMeal(int $id): array {
        $meal = $this->getMealById($id);
        if (!$meal) {
            return ['success' => false, 'message' => 'Repas non trouvÃ©'];
        }
        
        if ($meal['image'] && file_exists($meal['image'])) {
            unlink($meal['image']);
        }
        
        try {
            $this->pdo->prepare("DELETE FROM meal_ingredient WHERE id_meal = :id")->execute([':id' => $id]);
            $this->pdo->prepare("DELETE FROM meals WHERE id_meal = :id")->execute([':id' => $id]);
            return ['success' => true, 'message' => 'Repas supprimÃ© avec succÃ¨s'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
        }
    }
    
    private function countMeals(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM meals")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function countMealsByType(string $type): int {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM meals WHERE LOWER(type) = :type");
            $stmt->execute([':type' => strtolower($type)]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function getMealsPaged(int $limit, int $offset): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM meals ORDER BY nom ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllMeals(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM meals ORDER BY nom ASC");
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getMealById(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM meals WHERE id_meal = :id");
            $stmt->execute([':id' => $id]);
            $meal = $stmt->fetch();
            
            if ($meal) {
                $stmt = $this->pdo->prepare(
                    "SELECT i.*, mi.quantity FROM ingredients i 
                     INNER JOIN meal_ingredient mi ON i.id_ingredient = mi.id_ingredient 
                     WHERE mi.id_meal = :id ORDER BY i.nom ASC"
                );
                $stmt->execute([':id' => $id]);
                $meal['ingredients'] = $stmt->fetchAll() ?: [];
            }
            
            return $meal;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function addIngredientToMeal(int $meal_id, int $ingredient_id, float $quantity = 1): void {
        try {
            $sql = "INSERT INTO meal_ingredient (id_meal, id_ingredient, quantity) 
                    VALUES (:meal_id, :ingredient_id, :quantity) 
                    ON DUPLICATE KEY UPDATE quantity = :quantity";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':meal_id' => $meal_id, ':ingredient_id' => $ingredient_id, ':quantity' => $quantity]);
            $this->recalculateMealMacros($meal_id);
        } catch (PDOException $e) {}
    }
    
    public function removeIngredientFromMeal(int $meal_id, int $ingredient_id): void {
        try {
            $this->pdo->prepare("DELETE FROM meal_ingredient WHERE id_meal = :meal_id AND id_ingredient = :ingredient_id")
                ->execute([':meal_id' => $meal_id, ':ingredient_id' => $ingredient_id]);
            $this->recalculateMealMacros($meal_id);
        } catch (PDOException $e) {}
    }
    
    private function recalculateMealMacros(int $meal_id): void {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT SUM(i.calories * mi.quantity) as total_calories,
                        SUM(i.protein * mi.quantity) as total_protein,
                        SUM(i.carb * mi.quantity) as total_carb,
                        SUM(i.fat * mi.quantity) as total_fat
                 FROM meal_ingredient mi
                 INNER JOIN ingredients i ON mi.id_ingredient = i.id_ingredient
                 WHERE mi.id_meal = :id"
            );
            $stmt->execute([':id' => $meal_id]);
            $macros = $stmt->fetch();
            
            if ($macros) {
                $stmt = $this->pdo->prepare(
                    "UPDATE meals SET calories = :calories, protein = :protein, carb = :carb, fat = :fat WHERE id_meal = :id"
                );
                $stmt->execute([
                    ':calories' => $macros['total_calories'] ?? 0,
                    ':protein' => $macros['total_protein'] ?? 0,
                    ':carb' => $macros['total_carb'] ?? 0,
                    ':fat' => $macros['total_fat'] ?? 0,
                    ':id' => $meal_id
                ]);
            }
        } catch (PDOException $e) {}
    }
    
    // ==================== INGREDIENT CRUD OPERATIONS ====================
    
    public function addIngredient(array $data): array {
        $validation = $this->validateIngredient($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        try {
            $sql = "INSERT INTO ingredients (nom, calories, protein, carb, fat, eco_score) 
                    VALUES (:nom, :calories, :protein, :carb, :fat, :eco_score)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => (float)$data['calories'],
                ':protein' => (float)$data['protein'],
                ':carb' => (float)$data['carb'],
                ':fat' => (float)$data['fat'],
                ':eco_score' => $data['eco_score'] ?? null
            ]);
            
            return ['success' => true, 'message' => 'IngrÃ©dient crÃ©Ã© avec succÃ¨s', 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la crÃ©ation: ' . $e->getMessage()];
        }
    }
    
    public function updateIngredient(int $id, array $data): array {
        $validation = $this->validateIngredient($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        if (!$this->getIngredientById($id)) {
            return ['success' => false, 'message' => 'IngrÃ©dient non trouvÃ©'];
        }
        
        try {
            $sql = "UPDATE ingredients SET nom = :nom, calories = :calories, protein = :protein, 
                    carb = :carb, fat = :fat, eco_score = :eco_score WHERE id_ingredient = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => (float)$data['calories'],
                ':protein' => (float)$data['protein'],
                ':carb' => (float)$data['carb'],
                ':fat' => (float)$data['fat'],
                ':eco_score' => $data['eco_score'] ?? null,
                ':id' => $id
            ]);
            
            return ['success' => true, 'message' => 'IngrÃ©dient mis Ã  jour avec succÃ¨s'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la mise Ã  jour: ' . $e->getMessage()];
        }
    }
    
    public function deleteIngredient(int $id): array {
        if (!$this->getIngredientById($id)) {
            return ['success' => false, 'message' => 'IngrÃ©dient non trouvÃ©'];
        }
        
        try {
            $this->pdo->prepare("DELETE FROM meal_ingredient WHERE id_ingredient = :id")->execute([':id' => $id]);
            $this->pdo->prepare("DELETE FROM ingredients WHERE id_ingredient = :id")->execute([':id' => $id]);
            return ['success' => true, 'message' => 'IngrÃ©dient supprimÃ© avec succÃ¨s'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la suppression: ' . $e->getMessage()];
        }
    }
    
    private function countIngredients(): int {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    private function getIngredientsPaged(int $limit, int $offset): array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ingredients ORDER BY nom ASC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllIngredients(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM ingredients ORDER BY nom ASC");
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getIngredientById(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE id_ingredient = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    // ==================== VALIDATION & UTILITIES ====================
    
    private function validateMeal(array $data): array {
        if (empty($data['nom'])) {
            return ['success' => false, 'message' => 'Le nom du repas est obligatoire'];
        }
        if (empty($data['type']) || !in_array(strtolower($data['type']), ['petit déjeuner', 'déjeuner', 'dîner'])) {
            return ['success' => false, 'message' => 'Type de repas invalide'];
        }
        if (empty($data['calories']) || !is_numeric($data['calories']) || $data['calories'] < 0) {
            return ['success' => false, 'message' => 'Les calories doivent Ãªtre un nombre positif'];
        }
        if (empty($data['protein']) || !is_numeric($data['protein']) || $data['protein'] < 0) {
            return ['success' => false, 'message' => 'Les protÃ©ines doivent Ãªtre un nombre positif'];
        }
        if (empty($data['carb']) || !is_numeric($data['carb']) || $data['carb'] < 0) {
            return ['success' => false, 'message' => 'Les glucides doivent Ãªtre un nombre positif'];
        }
        if (empty($data['fat']) || !is_numeric($data['fat']) || $data['fat'] < 0) {
            return ['success' => false, 'message' => 'Les lipides doivent Ãªtre un nombre positif'];
        }

        return ['success' => true];
    }
    
    private function validateIngredient(array $data): array {
        if (empty($data['nom'])) {
            return ['success' => false, 'message' => 'Le nom de l\'ingrÃ©dient est obligatoire'];
        }
        if (empty($data['calories']) || !is_numeric($data['calories']) || $data['calories'] < 0) {
            return ['success' => false, 'message' => 'Les calories doivent Ãªtre un nombre positif'];
        }
        if (empty($data['protein']) || !is_numeric($data['protein']) || $data['protein'] < 0) {
            return ['success' => false, 'message' => 'Les protÃ©ines doivent Ãªtre un nombre positif'];
        }
        if (empty($data['carb']) || !is_numeric($data['carb']) || $data['carb'] < 0) {
            return ['success' => false, 'message' => 'Les glucides doivent Ãªtre un nombre positif'];
        }
        if (empty($data['fat']) || !is_numeric($data['fat']) || $data['fat'] < 0) {
            return ['success' => false, 'message' => 'Les lipides doivent Ãªtre un nombre positif'];
        }

        return ['success' => true];
    }

    private function validateImage(array $file): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload'];
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'L\'image est trop volumineuse (max 5MB)'];
        }
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed_types)) {
            return ['success' => false, 'message' => 'Format d\'image non autorisÃ©'];
        }
        return ['success' => true];
    }
    
    private function uploadImage(array $file): ?string {
        $upload_dir = 'uploads/meals/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('meal_') . '.' . $ext;
        $path = $upload_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $path)) {
            return $path;
        }
        return null;
    }

    public function getMealRatings(int $meal_id): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM meal_ratings WHERE id_meal = :meal_id ORDER BY created_at DESC LIMIT 50"
            );
            $stmt->execute([':meal_id' => $meal_id]);
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getMealAverageRating(int $meal_id): array {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM meal_ratings WHERE id_meal = :meal_id"
            );
            $stmt->execute([':meal_id' => $meal_id]);
            return $stmt->fetch() ?: ['avg_rating' => 0, 'total_ratings' => 0];
        } catch (PDOException $e) {
            return ['avg_rating' => 0, 'total_ratings' => 0];
        }
    }

    public function getTopMealsByRating(): array {
        try {
            $stmt = $this->pdo->query(
                "SELECT m.*, 
                        AVG(mr.rating) as avg_rating, 
                        COUNT(mr.id_rating) as total_ratings
                 FROM meals m
                 LEFT JOIN meal_ratings mr ON m.id_meal = mr.id_meal
                 GROUP BY m.id_meal
                 ORDER BY avg_rating DESC, total_ratings DESC
                 LIMIT 12"
            );
            return $stmt->fetchAll() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>