<?php
require_once __DIR__ . '/config.php';

class NutritionMeal {

    // ── Tous les repas ────────────────────────────────────────────
    public static function tousLesRepas() {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT * FROM meals ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    // ── Repas paginés ─────────────────────────────────────────────
    public static function repasPagines($limit, $offset) {
        $pdo = config::getConnexion();
        $limit = intval($limit);
        $offset = intval($offset);
        $stmt = $pdo->query("SELECT * FROM meals ORDER BY nom ASC LIMIT $limit OFFSET $offset");
        return $stmt->fetchAll();
    }

    // ── Compteur ──────────────────────────────────────────────────
    public static function compterRepas() {
        $pdo = config::getConnexion();
        return (int)$pdo->query("SELECT COUNT(*) FROM meals")->fetchColumn();
    }

    public static function compterParType($type) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM meals WHERE LOWER(type) = :type");
        $stmt->execute([':type' => strtolower($type)]);
        return (int)$stmt->fetchColumn();
    }

    // ── Détail d'un repas ─────────────────────────────────────────
    public static function parId($id) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM meals WHERE id_meal = ?");
        $stmt->execute([intval($id)]);
        $meal = $stmt->fetch();
        if (!$meal) return null;

        $stmt2 = $pdo->prepare(
            "SELECT i.*, mi.quantity FROM ingredients i
             INNER JOIN meal_ingredient mi ON i.id_ingredient = mi.id_ingredient
             WHERE mi.id_meal = ? ORDER BY i.nom ASC"
        );
        $stmt2->execute([intval($id)]);
        $meal['ingredients'] = $stmt2->fetchAll();

        // Ratings
        $stmt3 = $pdo->prepare(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM meal_ratings WHERE id_meal = ?"
        );
        $stmt3->execute([intval($id)]);
        $meal['rating_info'] = $stmt3->fetch() ?: ['avg_rating' => 0, 'total_ratings' => 0];

        $stmt4 = $pdo->prepare(
            "SELECT * FROM meal_ratings WHERE id_meal = ? ORDER BY created_at DESC LIMIT 50"
        );
        $stmt4->execute([intval($id)]);
        $meal['ratings'] = $stmt4->fetchAll();

        return $meal;
    }

    // ── Ajouter un repas ──────────────────────────────────────────
    public static function ajouter($data, $file = null) {
        $nom = trim($data['nom'] ?? '');
        $type = trim($data['type'] ?? '');
        $calories = floatval($data['calories'] ?? 0);
        $protein = floatval($data['protein'] ?? 0);
        $carb = floatval($data['carb'] ?? 0);
        $fat = floatval($data['fat'] ?? 0);

        if ($nom === '') return ['success' => false, 'error' => 'Le nom du repas est obligatoire'];
        if (!in_array(strtolower($type), ['petit déjeuner', 'déjeuner', 'dîner']))
            return ['success' => false, 'error' => 'Type de repas invalide'];
        if ($calories < 0 || $protein < 0 || $carb < 0 || $fat < 0)
            return ['success' => false, 'error' => 'Les valeurs nutritionnelles doivent être positives'];

        $image_path = null;
        if ($file && !empty($file['tmp_name'])) {
            $imgRes = self::uploadImage($file);
            if (!$imgRes['success']) return $imgRes;
            $image_path = $imgRes['path'];
        }

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO meals (nom, calories, protein, carb, fat, type, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $calories, $protein, $carb, $fat, $type, $image_path]);
        return ['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Repas créé avec succès'];
    }

    // ── Modifier un repas ─────────────────────────────────────────
    public static function modifier($id, $data, $file = null) {
        $id = intval($id);
        $nom = trim($data['nom'] ?? '');
        $type = trim($data['type'] ?? '');
        $calories = floatval($data['calories'] ?? 0);
        $protein = floatval($data['protein'] ?? 0);
        $carb = floatval($data['carb'] ?? 0);
        $fat = floatval($data['fat'] ?? 0);

        if ($nom === '') return ['success' => false, 'error' => 'Le nom du repas est obligatoire'];
        if (!in_array(strtolower($type), ['petit déjeuner', 'déjeuner', 'dîner']))
            return ['success' => false, 'error' => 'Type de repas invalide'];

        $pdo = config::getConnexion();
        $existing = $pdo->prepare("SELECT image FROM meals WHERE id_meal = ?");
        $existing->execute([$id]);
        $old = $existing->fetch();
        if (!$old) return ['success' => false, 'error' => 'Repas non trouvé'];

        $image_path = $old['image'];
        if ($file && !empty($file['tmp_name'])) {
            $imgRes = self::uploadImage($file);
            if (!$imgRes['success']) return $imgRes;
            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                @unlink(__DIR__ . '/../' . $image_path);
            }
            $image_path = $imgRes['path'];
        }

        $stmt = $pdo->prepare("UPDATE meals SET nom=?, calories=?, protein=?, carb=?, fat=?, type=?, image=? WHERE id_meal=?");
        $stmt->execute([$nom, $calories, $protein, $carb, $fat, $type, $image_path, $id]);
        return ['success' => true, 'message' => 'Repas mis à jour'];
    }

    // ── Supprimer un repas ────────────────────────────────────────
    public static function supprimer($id) {
        $pdo = config::getConnexion();
        $id = intval($id);
        $stmt = $pdo->prepare("SELECT image FROM meals WHERE id_meal = ?");
        $stmt->execute([$id]);
        $meal = $stmt->fetch();
        if (!$meal) return ['success' => false, 'error' => 'Repas non trouvé'];

        if ($meal['image'] && file_exists(__DIR__ . '/../' . $meal['image'])) {
            @unlink(__DIR__ . '/../' . $meal['image']);
        }
        $pdo->prepare("DELETE FROM meal_ingredient WHERE id_meal = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM meal_ratings WHERE id_meal = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM meals WHERE id_meal = ?")->execute([$id]);
        return ['success' => true, 'message' => 'Repas supprimé'];
    }

    // ── Gestion des ingrédients dans un repas ─────────────────────
    public static function ajouterIngredient($mealId, $ingredientId, $quantity) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO meal_ingredient (id_meal, id_ingredient, quantity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = ?"
        );
        $stmt->execute([intval($mealId), intval($ingredientId), floatval($quantity), floatval($quantity)]);
        self::recalculerMacros(intval($mealId));
        return ['success' => true, 'message' => 'Ingrédient ajouté au repas'];
    }

    public static function retirerIngredient($mealId, $ingredientId) {
        $pdo = config::getConnexion();
        $pdo->prepare("DELETE FROM meal_ingredient WHERE id_meal = ? AND id_ingredient = ?")
            ->execute([intval($mealId), intval($ingredientId)]);
        self::recalculerMacros(intval($mealId));
        return ['success' => true, 'message' => 'Ingrédient retiré'];
    }

    private static function recalculerMacros($mealId) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT SUM(i.calories * mi.quantity) as total_calories,
                    SUM(i.protein * mi.quantity) as total_protein,
                    SUM(i.carb * mi.quantity) as total_carb,
                    SUM(i.fat * mi.quantity) as total_fat
             FROM meal_ingredient mi
             INNER JOIN ingredients i ON mi.id_ingredient = i.id_ingredient
             WHERE mi.id_meal = ?"
        );
        $stmt->execute([$mealId]);
        $macros = $stmt->fetch();
        if ($macros && $macros['total_calories'] !== null) {
            $pdo->prepare("UPDATE meals SET calories=?, protein=?, carb=?, fat=? WHERE id_meal=?")
                ->execute([$macros['total_calories'], $macros['total_protein'], $macros['total_carb'], $macros['total_fat'], $mealId]);
        }
    }

    // ── Ratings ───────────────────────────────────────────────────
    public static function ajouterRating($mealId, $rating, $comment, $name, $email) {
        if ($rating < 1 || $rating > 5) return ['success' => false, 'error' => 'Note invalide (1-5)'];
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO meal_ratings (id_meal, rating, comment, visitor_name, visitor_email) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            intval($mealId),
            intval($rating),
            $comment ?: null,
            $name ?: 'Anonyme',
            filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null
        ]);
        return ['success' => true, 'message' => 'Merci pour votre évaluation !'];
    }

    // ── Top repas par rating ──────────────────────────────────────
    public static function topRepas($limit = 12) {
        $pdo = config::getConnexion();
        $limit = intval($limit);
        $stmt = $pdo->query(
            "SELECT m.*, AVG(mr.rating) as avg_rating, COUNT(mr.id_rating) as total_ratings
             FROM meals m LEFT JOIN meal_ratings mr ON m.id_meal = mr.id_meal
             GROUP BY m.id_meal ORDER BY avg_rating DESC, total_ratings DESC LIMIT $limit"
        );
        return $stmt->fetchAll();
    }

    // ── Plan nutritionnel (TDEE + sélection repas) ────────────────
    public static function genererPlan($weight, $height, $age, $gender, $activity, $goal, $priorities = []) {
        $meals = self::tousLesRepas();

        // BMR Mifflin-St Jeor
        if ($gender === 'male') {
            $bmr = 88.362 + (13.397 * $weight) + (4.799 * $height) - (5.677 * $age);
        } else {
            $bmr = 447.593 + (9.247 * $weight) + (3.098 * $height) - (4.330 * $age);
        }
        $tdee = $bmr * $activity;
        if ($goal === 'loss') $tdee -= 500;
        elseif ($goal === 'gain') $tdee += 300;

        // Macro ratios
        $macroRatios = ['protein' => 0.25, 'carb' => 0.45, 'fat' => 0.30];
        if (in_array('maintain_muscle', $priorities)) {
            $macroRatios['protein'] += 0.10; $macroRatios['carb'] -= 0.05; $macroRatios['fat'] -= 0.05;
        }
        if (in_array('reduce_sugar_sodium', $priorities)) {
            $macroRatios['protein'] += 0.05; $macroRatios['carb'] -= 0.10; $macroRatios['fat'] += 0.05;
        }
        $ratioSum = max(0.01, array_sum($macroRatios));
        foreach ($macroRatios as $k => $v) $macroRatios[$k] = $v / $ratioSum;

        $dailyMacroTargets = [
            'protein' => (int)round(($tdee * $macroRatios['protein']) / 4),
            'carb'    => (int)round(($tdee * $macroRatios['carb']) / 4),
            'fat'     => (int)round(($tdee * $macroRatios['fat']) / 9),
        ];

        $mealPercents = ['breakfast' => 0.25, 'lunch' => 0.40, 'dinner' => 0.35];
        $targets = [];
        $slotMacros = [];
        foreach ($mealPercents as $slot => $pct) {
            $targets[$slot] = $tdee * $pct;
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
            'breakfast' => self::findBestMeal($breakfastMeals, $targets['breakfast'], $priorities, $slotMacros['breakfast']),
            'lunch'     => self::findBestMeal($lunchMeals, $targets['lunch'], $priorities, $slotMacros['lunch']),
            'dinner'    => self::findBestMeal($dinnerMeals, $targets['dinner'], $priorities, $slotMacros['dinner']),
            'targets'   => [
                'breakfast' => (int)round($targets['breakfast']),
                'lunch'     => (int)round($targets['lunch']),
                'dinner'    => (int)round($targets['dinner']),
            ],
            'macro_targets' => $dailyMacroTargets,
            'priorities' => $priorities,
        ];
    }

    private static function findBestMeal($meals, $targetCalories, $priorities, $slotMacroTarget) {
        if (empty($meals)) return null;
        if (empty($priorities)) {
            $best = null; $bestDiff = PHP_INT_MAX;
            foreach ($meals as $m) {
                $diff = abs((float)$m['calories'] - $targetCalories);
                if ($diff < $bestDiff) { $bestDiff = $diff; $best = $m; }
            }
            return $best;
        }
        $best = null; $bestScore = PHP_FLOAT_MAX;
        foreach ($meals as $meal) {
            $cal = (float)($meal['calories'] ?? 0);
            $protein = (float)($meal['protein'] ?? 0);
            $carb = (float)($meal['carb'] ?? 0);
            $fat = (float)($meal['fat'] ?? 0);
            $score = abs($cal - $targetCalories);
            if (in_array('maintain_muscle', $priorities)) {
                $score += abs($protein - (float)$slotMacroTarget['protein']) * 2.0;
                $pd = $protein / max(1.0, $cal / 100.0);
                if ($pd < 8.0) $score += (8.0 - $pd) * 18.0;
            }
            if (in_array('reduce_sugar_sodium', $priorities)) {
                $score += max(0.0, $carb - ((float)$slotMacroTarget['carb'] * 1.1)) * 4.0;
                $score += max(0.0, $fat - ((float)$slotMacroTarget['fat'] * 1.2)) * 1.8;
                $score += max(0.0, ($cal / 100.0) - 3.2) * 12.0;
            }
            if ($score < $bestScore) { $bestScore = $score; $best = $meal; }
        }
        return $best;
    }

    // ── Stats pour admin ──────────────────────────────────────────
    public static function getStats() {
        $pdo = config::getConnexion();
        $total = (int)$pdo->query("SELECT COUNT(*) FROM meals")->fetchColumn();
        $breakfast = (int)$pdo->query("SELECT COUNT(*) FROM meals WHERE LOWER(type)='petit déjeuner'")->fetchColumn();
        $totalIngredients = (int)$pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
        $avgRating = $pdo->query("SELECT COALESCE(AVG(rating),0) FROM meal_ratings")->fetchColumn();
        return [
            'total_meals' => $total,
            'breakfast_count' => $breakfast,
            'lunch_dinner_count' => $total - $breakfast,
            'total_ingredients' => $totalIngredients,
            'avg_rating' => round((float)$avgRating, 1),
        ];
    }

    // ── Upload image ──────────────────────────────────────────────
    private static function uploadImage($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) return ['success' => false, 'error' => 'Erreur upload'];
        if ($file['size'] > 5 * 1024 * 1024) return ['success' => false, 'error' => 'Image trop volumineuse (max 5MB)'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed)) return ['success' => false, 'error' => 'Format image non autorisé'];

        $dir = __DIR__ . '/../uploads/meals/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('meal_') . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            return ['success' => true, 'path' => 'uploads/meals/' . $filename];
        }
        return ['success' => false, 'error' => 'Échec de l\'upload'];
    }

    // ── Servir le modèle ML ───────────────────────────────────────
    public static function getMLModel() {
        $path = __DIR__ . '/../ml/models/simple_meal_model.json';
        if (!file_exists($path)) return null;
        return file_get_contents($path);
    }
}
