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
        $meals = $this->getAllMeals();
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
        $ingredients = $this->getAllIngredients();
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
        if (empty($data['type']) || !in_array(strtolower($data['type']), ['petit dÃ©jeuner', 'dÃ©jeuner', 'dÃ®ner'])) {
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
}
?>