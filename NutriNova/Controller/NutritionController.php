<?php
/**
 * Contrôleur NutritionController
 * Gère les requêtes et oriente vers les vues appropriées
 */
class NutritionController {
    private $meal;
    private $ingredient;
    
    public function __construct() {
        require_once 'Model/Meal.php';
        require_once 'Model/Ingredient.php';
        
        $this->meal = new Meal();
        $this->ingredient = new Ingredient();
    }
    
    /**
     * Route les requêtes vers les bonnes actions
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
        // Actions de gestion des ingrédients dans un repas
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
    
    /**
     * Afficher la page d'accueil (liste des repas)
     */
    private function frontHome() {
        $meals = $this->meal->getAll();
        include 'View/Front/index.php';
    }
    
    /**
     * Afficher le détail d'un repas
     */
    private function frontMealDetail() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $meal = $this->meal->getById($id);
        
        if (!$meal) {
            header('Location: index.php');
            exit;
        }
        
        include 'View/Front/meal-detail.php';
    }
    
    // ==================== BACK OFFICE - MEALS ====================
    
    /**
     * Afficher la liste des repas (Admin)
     */
    private function adminMealsList() {
        $meals = $this->meal->getAll();
        include 'View/Back/meals-list.php';
    }
    
    /**
     * Afficher le formulaire de création/modification d'un repas
     * @param string $mode 'add' ou 'edit'
     */
    private function adminMealForm($mode) {
        $meal = null;
        $ingredients = $this->ingredient->getAll();
        $meal_ingredients = [];
        
        if ($mode === 'edit') {
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $meal = $this->meal->getById($id);
            if (!$meal) {
                $_SESSION['error'] = 'Repas non trouvé';
                header('Location: index.php?action=admin-meals&section=meal');
                exit;
            }
            $meal_ingredients = $meal['ingredients'];
        }
        
        include 'View/Back/meal-form.php';
    }
    
    /**
     * Créer un nouveau repas
     */
    private function adminMealCreate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meal-add&section=meal');
            exit;
        }
        
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;
        $result = $this->meal->create($_POST, $image);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-meals&section=meal');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-meal-add&section=meal');
        }
        exit;
    }
    
    /**
     * Mettre à jour un repas
     */
    private function adminMealUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meals&section=meal');
            exit;
        }
        
        $id = isset($_POST['id_meal']) ? $_POST['id_meal'] : 0;
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;
        
        $result = $this->meal->update($id, $_POST, $image);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-meals&section=meal');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $id);
        }
        exit;
    }
    
    /**
     * Supprimer un repas
     */
    private function adminMealDelete() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        
        $result = $this->meal->delete($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: index.php?action=admin-meals&section=meal');
        exit;
    }
    
    /**
     * Ajouter un ingrédient à un repas
     */
    private function adminMealAddIngredient() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-meals&section=meal');
            exit;
        }
        
        $meal_id = isset($_POST['meal_id']) ? $_POST['meal_id'] : 0;
        $ingredient_id = isset($_POST['ingredient_id']) ? $_POST['ingredient_id'] : 0;
        $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
        
        $result = $this->meal->addIngredient($meal_id, $ingredient_id, $quantity);
        
        header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $meal_id);
        exit;
    }
    
    /**
     * Supprimer un ingrédient d'un repas
     */
    private function adminMealRemoveIngredient() {
        $meal_id = isset($_GET['meal_id']) ? $_GET['meal_id'] : 0;
        $ingredient_id = isset($_GET['ingredient_id']) ? $_GET['ingredient_id'] : 0;
        
        $this->meal->removeIngredient($meal_id, $ingredient_id);
        
        header('Location: index.php?action=admin-meal-edit&section=meal&id=' . $meal_id);
        exit;
    }
    
    // ==================== BACK OFFICE - INGREDIENTS ====================
    
    /**
     * Afficher la liste des ingrédients (Admin)
     */
    private function adminIngredientsList() {
        $ingredients = $this->ingredient->getAll();
        include 'View/Back/ingredients-list.php';
    }
    
    /**
     * Afficher le formulaire de création/modification d'un ingrédient
     * @param string $mode 'add' ou 'edit'
     */
    private function adminIngredientForm($mode) {
        $ingredient = null;
        
        if ($mode === 'edit') {
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            $ingredient = $this->ingredient->getById($id);
            if (!$ingredient) {
                $_SESSION['error'] = 'Ingrédient non trouvé';
                header('Location: index.php?action=admin-ingredients&section=ingredient');
                exit;
            }
        }
        
        include 'View/Back/ingredient-form.php';
    }
    
    /**
     * Créer un nouvel ingrédient
     */
    private function adminIngredientCreate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-ingredient-add&section=ingredient');
            exit;
        }
        
        $result = $this->ingredient->create($_POST);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-ingredients&section=ingredient');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-ingredient-add&section=ingredient');
        }
        exit;
    }
    
    /**
     * Mettre à jour un ingrédient
     */
    private function adminIngredientUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=admin-ingredients&section=ingredient');
            exit;
        }
        
        $id = isset($_POST['id_ingredient']) ? $_POST['id_ingredient'] : 0;
        
        $result = $this->ingredient->update($id, $_POST);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?action=admin-ingredients&section=ingredient');
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: index.php?action=admin-ingredient-edit&section=ingredient&id=' . $id);
        }
        exit;
    }
    
    /**
     * Supprimer un ingrédient
     */
    private function adminIngredientDelete() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        
        $result = $this->ingredient->delete($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: index.php?action=admin-ingredients&section=ingredient');
        exit;
    }
}
?>
