<?php
require_once __DIR__ . '/config.php';

class NutritionIngredient {

    public static function tousLesIngredients() {
        $pdo = config::getConnexion();
        return $pdo->query("SELECT * FROM ingredients ORDER BY nom ASC")->fetchAll();
    }

    public static function ingredientsPagines($limit, $offset) {
        $pdo = config::getConnexion();
        $limit = intval($limit); $offset = intval($offset);
        return $pdo->query("SELECT * FROM ingredients ORDER BY nom ASC LIMIT $limit OFFSET $offset")->fetchAll();
    }

    public static function compter() {
        $pdo = config::getConnexion();
        return (int)$pdo->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();
    }

    public static function parId($id) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE id_ingredient = ?");
        $stmt->execute([intval($id)]);
        return $stmt->fetch() ?: null;
    }

    public static function ajouter($data) {
        $nom = trim($data['nom'] ?? '');
        $calories = floatval($data['calories'] ?? 0);
        $protein = floatval($data['protein'] ?? 0);
        $carb = floatval($data['carb'] ?? 0);
        $fat = floatval($data['fat'] ?? 0);
        $eco = trim($data['eco_score'] ?? '');

        if ($nom === '') return ['success' => false, 'error' => 'Le nom est obligatoire'];
        if ($calories < 0 || $protein < 0 || $carb < 0 || $fat < 0)
            return ['success' => false, 'error' => 'Les valeurs doivent être positives'];

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO ingredients (nom, calories, protein, carb, fat, eco_score) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$nom, $calories, $protein, $carb, $fat, $eco ?: null]);
        return ['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Ingrédient créé'];
    }

    public static function modifier($id, $data) {
        $id = intval($id);
        $nom = trim($data['nom'] ?? '');
        if ($nom === '') return ['success' => false, 'error' => 'Le nom est obligatoire'];

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE ingredients SET nom=?, calories=?, protein=?, carb=?, fat=?, eco_score=? WHERE id_ingredient=?");
        $stmt->execute([
            $nom, floatval($data['calories'] ?? 0), floatval($data['protein'] ?? 0),
            floatval($data['carb'] ?? 0), floatval($data['fat'] ?? 0),
            trim($data['eco_score'] ?? '') ?: null, $id
        ]);
        return $stmt->rowCount() >= 0 ? ['success' => true, 'message' => 'Ingrédient mis à jour'] : ['success' => false, 'error' => 'Non trouvé'];
    }

    public static function supprimer($id) {
        $pdo = config::getConnexion();
        $id = intval($id);
        $pdo->prepare("DELETE FROM meal_ingredient WHERE id_ingredient = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM ingredients WHERE id_ingredient = ?")->execute([$id]);
        return ['success' => true, 'message' => 'Ingrédient supprimé'];
    }
}
