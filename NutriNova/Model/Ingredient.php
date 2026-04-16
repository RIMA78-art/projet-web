<?php
/**
 * Modèle Ingredient
 * Gère les opérations CRUD pour les ingrédients
 */
class Ingredient {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Créer un nouvel ingrédient
     * @param array $data Les données de l'ingrédient
     * @return array Résultat avec statut et message
     */
    public function create($data) {
        // Validation des données
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        try {
            $sql = "INSERT INTO ingredients (nom, calories, protein, carb, fat, eco_score) 
                    VALUES (:nom, :calories, :protein, :carb, :fat, :eco_score)";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => floatval($data['calories']),
                ':protein' => floatval($data['protein']),
                ':carb' => floatval($data['carb']),
                ':fat' => floatval($data['fat']),
                ':eco_score' => isset($data['eco_score']) ? trim($data['eco_score']) : null
            ]);
            
            return [
                'success' => true,
                'message' => 'Ingrédient créé avec succès',
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
     * Récupérer tous les ingrédients
     * @return array Liste des ingrédients
     */
    public function getAll() {
        try {
            $sql = "SELECT * FROM ingredients ORDER BY nom ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Récupérer un ingrédient par ID
     * @param int $id ID de l'ingrédient
     * @return array Données de l'ingrédient ou tableau vide
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM ingredients WHERE id_ingredient = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($id)]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Mettre à jour un ingrédient
     * @param int $id ID de l'ingrédient
     * @param array $data Les données à mettre à jour
     * @return array Résultat avec statut et message
     */
    public function update($id, $data) {
        // Validation des données
        $validation = $this->validate($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Vérifier que l'ingrédient existe
        if (!$this->getById($id)) {
            return [
                'success' => false,
                'message' => 'Ingrédient non trouvé'
            ];
        }
        
        try {
            $sql = "UPDATE ingredients 
                    SET nom = :nom, calories = :calories, protein = :protein, 
                        carb = :carb, fat = :fat, eco_score = :eco_score 
                    WHERE id_ingredient = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->execute([
                ':nom' => trim($data['nom']),
                ':calories' => floatval($data['calories']),
                ':protein' => floatval($data['protein']),
                ':carb' => floatval($data['carb']),
                ':fat' => floatval($data['fat']),
                ':eco_score' => isset($data['eco_score']) ? trim($data['eco_score']) : null,
                ':id' => intval($id)
            ]);
            
            return [
                'success' => true,
                'message' => 'Ingrédient mis à jour avec succès'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprimer un ingrédient
     * @param int $id ID de l'ingrédient
     * @return array Résultat avec statut et message
     */
    public function delete($id) {
        try {
            // Vérifier que l'ingrédient existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Ingrédient non trouvé'
                ];
            }
            
            // Supprimer les liaisons avec les repas d'abord
            $sql_delete_links = "DELETE FROM meal_ingredient WHERE id_ingredient = :id";
            $stmt_links = $this->pdo->prepare($sql_delete_links);
            $stmt_links->execute([':id' => intval($id)]);
            
            // Puis supprimer l'ingrédient
            $sql = "DELETE FROM ingredients WHERE id_ingredient = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => intval($id)]);
            
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
     * Valider les données d'un ingrédient
     * @param array $data Les données à valider
     * @return array Résultat de la validation
     */
    private function validate($data) {
        // Vérifier les champs obligatoires
        if (empty($data['nom'])) {
            return ['success' => false, 'message' => 'Le nom de l\'ingrédient est obligatoire'];
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
}
?>
