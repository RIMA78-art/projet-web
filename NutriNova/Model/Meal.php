<?php
/**
 * Modèle Meal (Repas)
 * Gère les opérations CRUD pour les repas avec ingrédients
 */
class Meal {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer un nouveau repas
     * @param array $data Les données du repas
     * @param array $image Données du fichier image ($_FILES['image'])
     * @return array Résultat avec statut et message
     */
    public function create($data, $image = null) {
        // Validation des données
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Gérer l'upload d'image
        $image_path = null;
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $image_validation = $this->validateImage($image);
            if (!$image_validation['success']) {
                return $image_validation;
            }
            $image_path = $this->uploadImage($image);
        }
        
        try {
            $sql = "INSERT INTO meals (nom, calories, protein, carb, fat, type, image) 
                    VALUES (:nom, :calories, :protein, :carb, :fat, :type, :image)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => floatval($data['calories']),
                ':protein' => floatval($data['protein']),
                ':carb' => floatval($data['carb']),
                ':fat' => floatval($data['fat']),
                ':type' => trim($data['type']),
                ':image' => $image_path
            ]);
            
            return [
                'success' => true,
                'message' => 'Repas créé avec succès',
                'id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupérer tous les repas
     * @return array Liste des repas
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM meals ORDER BY nom ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Récupérer un repas par ID avec ses ingrédients
     * @param int $id ID du repas
     * @return array Données du repas avec ingrédients
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM meals WHERE id_meal = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($id)]);
            $meal = $stmt->fetch();
            
            if ($meal) {
                $meal['ingredients'] = $this->getIngredients($id);
            }
            
            return $meal;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Récupérer les ingrédients d'un repas
     * @param int $meal_id ID du repas
     * @return array Liste des ingrédients du repas
     */
    public function getIngredients($meal_id) {
        try {
            $sql = "SELECT i.*, mi.quantity FROM ingredients i 
                    INNER JOIN meal_ingredient mi ON i.id_ingredient = mi.id_ingredient 
                    WHERE mi.id_meal = :id 
                    ORDER BY i.nom ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($meal_id)]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Mettre à jour un repas
     * @param int $id ID du repas
     * @param array $data Les données à mettre à jour
     * @param array $image Données du fichier image ($_FILES['image'])
     * @return array Résultat avec statut et message
     */
    public function update($id, $data, $image = null) {
        // Validation des données
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Vérifier que le repas existe
        $meal = $this->getById($id);
        if (!$meal) {
            return [
                'success' => false,
                'message' => 'Repas non trouvé'
            ];
        }
        
        // Gérer l'upload d'image
        $image_path = $meal['image']; // Garder l'ancienne image par défaut
        if ($image && isset($image['tmp_name']) && !empty($image['tmp_name'])) {
            $image_validation = $this->validateImage($image);
            if (!$image_validation['success']) {
                return $image_validation;
            }
            // Supprimer l'ancienne image si elle existe
            if ($meal['image'] && file_exists($meal['image'])) {
                unlink($meal['image']);
            }
            $image_path = $this->uploadImage($image);
        }
        
        try {
            $sql = "UPDATE meals 
                    SET nom = :nom, calories = :calories, protein = :protein, 
                        carb = :carb, fat = :fat, type = :type, image = :image 
                    WHERE id_meal = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => floatval($data['calories']),
                ':protein' => floatval($data['protein']),
                ':carb' => floatval($data['carb']),
                ':fat' => floatval($data['fat']),
                ':type' => trim($data['type']),
                ':image' => $image_path,
                ':id' => intval($id)
            ]);
            
            return [
                'success' => true,
                'message' => 'Repas mis à jour avec succès'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un repas
     * @param int $id ID du repas
     * @return array Résultat avec statut et message
     */
    public function delete($id) {
        try {
            // Vérifier que le repas existe
            $meal = $this->getById($id);
            if (!$meal) {
                return [
                    'success' => false,
                    'message' => 'Repas non trouvé'
                ];
            }
            
            // Supprimer l'image si elle existe
            if ($meal['image'] && file_exists($meal['image'])) {
                unlink($meal['image']);
            }
            
            // Supprimer les liaisons avec les ingrédients d'abord
            $sql_delete_links = "DELETE FROM meal_ingredient WHERE id_meal = :id";
            $stmt_links = $this->pdo->prepare($sql_delete_links);
            $stmt_links->execute([':id' => intval($id)]);
            
            // Puis supprimer le repas
            $sql = "DELETE FROM meals WHERE id_meal = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($id)]);
            
            return [
                'success' => true,
                'message' => 'Repas supprimé avec succès'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ajouter un ingrédient à un repas
     * @param int $meal_id ID du repas
     * @param int $ingredient_id ID de l'ingrédient
     * @param float $quantity Quantité de l'ingrédient
     * @return array Résultat avec statut et message
     */
    public function addIngredient($meal_id, $ingredient_id, $quantity = 1) {
        try {
            $sql = "INSERT INTO meal_ingredient (id_meal, id_ingredient, quantity) 
                    VALUES (:meal_id, :ingredient_id, :quantity)
                    ON DUPLICATE KEY UPDATE quantity = :quantity";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':meal_id' => intval($meal_id),
                ':ingredient_id' => intval($ingredient_id),
                ':quantity' => floatval($quantity)
            ]);
            
            // Recalculer les macros du repas
            $this->recalculateMacros($meal_id);
            
            return [
                'success' => true,
                'message' => 'Ingrédient ajouté avec succès'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un ingrédient d'un repas
     * @param int $meal_id ID du repas
     * @param int $ingredient_id ID de l'ingrédient
     * @return array Résultat avec statut et message
     */
    public function removeIngredient($meal_id, $ingredient_id) {
        try {
            $sql = "DELETE FROM meal_ingredient 
                    WHERE id_meal = :meal_id AND id_ingredient = :ingredient_id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':meal_id' => intval($meal_id),
                ':ingredient_id' => intval($ingredient_id)
            ]);
            
            // Recalculer les macros du repas
            $this->recalculateMacros($meal_id);
            
            return [
                'success' => true,
                'message' => 'Ingrédient supprimé avec succès'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Recalculer les macros (calories, protéines, etc.) d'un repas basé sur ses ingrédients
     * @param int $meal_id ID du repas
     */
    public function recalculateMacros($meal_id) {
        try {
            $sql = "SELECT 
                    SUM(i.calories * mi.quantity) as total_calories,
                    SUM(i.protein * mi.quantity) as total_protein,
                    SUM(i.carb * mi.quantity) as total_carb,
                    SUM(i.fat * mi.quantity) as total_fat
                    FROM meal_ingredient mi
                    INNER JOIN ingredients i ON mi.id_ingredient = i.id_ingredient
                    WHERE mi.id_meal = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($meal_id)]);
            $macros = $stmt->fetch();
            
            if ($macros) {
                $sql_update = "UPDATE meals 
                              SET calories = :calories, 
                                  protein = :protein, 
                                  carb = :carb, 
                                  fat = :fat 
                              WHERE id_meal = :id";
                
                $stmt_update = $this->pdo->prepare($sql_update);
                $stmt_update->execute([
                    ':calories' => $macros['total_calories'] ?? 0,
                    ':protein' => $macros['total_protein'] ?? 0,
                    ':carb' => $macros['total_carb'] ?? 0,
                    ':fat' => $macros['total_fat'] ?? 0,
                    ':id' => intval($meal_id)
                ]);
            }
        } catch (PDOException $e) {
            // Erreur silencieuse, les macros ne sont pas critique
        }
    }
    
    /**
     * Récupérer les repas par type
     * @param string $type Type de repas (petit déjeuner, déjeuner, dîner)
     * @return array Liste des repas du type spécifié
     */
    public function getByType($type) {
        try {
            $sql = "SELECT * FROM meals WHERE type = :type ORDER BY nom ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':type' => trim($type)]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Valider les données d'un repas
     * @param array $data Les données à valider
     * @return array Résultat de la validation
     */
    private function validate($data) {
        // Vérifier les champs obligatoires
        if (empty($data['nom'])) {
            return ['success' => false, 'message' => 'Le nom du repas est obligatoire'];
        }
        
        if (empty($data['type'])) {
            return ['success' => false, 'message' => 'Le type de repas est obligatoire'];
        }
        
        $types_valides = ['petit déjeuner', 'déjeuner', 'dîner'];
        if (!in_array(strtolower($data['type']), $types_valides)) {
            return ['success' => false, 'message' => 'Type de repas invalide'];
        }
        
        if (empty($data['calories']) || !is_numeric($data['calories']) || $data['calories'] < 0) {
            return ['success' => false, 'message' => 'Les calories doivent être un nombre positif'];
        }
        
        if (empty($data['protein']) || !is_numeric($data['protein']) || $data['protein'] < 0) {
            return ['success' => false, 'message' => 'Les protéines doivent être un nombre positif'];
        }
        
        if (empty($data['carb']) || !is_numeric($data['carb']) || $data['carb'] < 0) {
            return ['success' => false, 'message' => 'Les glucides doivent être un nombre positif'];
        }
        
        if (empty($data['fat']) || !is_numeric($data['fat']) || $data['fat'] < 0) {
            return ['success' => false, 'message' => 'Les lipides doivent être un nombre positif'];
        }
        
        // Vérifier la longueur du nom
        if (strlen($data['nom']) > 255) {
            return ['success' => false, 'message' => 'Le nom ne peut pas dépasser 255 caractères'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Valider une image uploadée
     * @param array $file Données du fichier ($_FILES['image'])
     * @return array Résultat de la validation
     */
    private function validateImage($file) {
        // Vérifier que le fichier a été uploadé sans erreur
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
        }
        
        // Vérifier la taille (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            return ['success' => false, 'message' => 'L\'image est trop voluminense (max 5MB)'];
        }
        
        // Vérifier le type MIME
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            return ['success' => false, 'message' => 'Format d\'image non autorisé (JPEG, PNG, GIF, WebP)'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Uploader une image
     * @param array $file Données du fichier ($_FILES['image'])
     * @return string Chemin relatif du fichier uploadé
     */
    private function uploadImage($file) {
        // Créer le dossier s'il n'existe pas
        $upload_dir = 'uploads/meals/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Générer un nom de fichier unique
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_name = uniqid('meal_') . '.' . $file_extension;
        $upload_path = $upload_dir . $unique_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return $upload_path; // Retourner le chemin complet, pas seulement le nom
        }
        
        return null;
    }
}
?>
