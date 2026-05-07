<?php
/**
 * Model Ingredient
 * Représente un ingrédient avec ses propriétés
 */
class Ingredient {
    private ?int $id_ingredient;
    private ?string $nom;
    private ?float $calories;
    private ?float $protein;
    private ?float $carb;
    private ?float $fat;
    private ?string $eco_score;

    // Constructor
    public function __construct(?int $id_ingredient = null, ?string $nom = null, ?float $calories = null, ?float $protein = null, ?float $carb = null, ?float $fat = null, ?string $eco_score = null) {
        $this->id_ingredient = $id_ingredient;
        $this->nom = $nom;
        $this->calories = $calories;
        $this->protein = $protein;
        $this->carb = $carb;
        $this->fat = $fat;
        $this->eco_score = $eco_score;
    }

    // Getters
    public function getIdIngredient(): ?int {
        return $this->id_ingredient;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function getCalories(): ?float {
        return $this->calories;
    }

    public function getProtein(): ?float {
        return $this->protein;
    }

    public function getCarb(): ?float {
        return $this->carb;
    }

    public function getFat(): ?float {
        return $this->fat;
    }

    public function getEcoScore(): ?string {
        return $this->eco_score;
    }

    // Setters
    public function setIdIngredient(?int $id_ingredient): void {
        $this->id_ingredient = $id_ingredient;
    }

    public function setNom(?string $nom): void {
        $this->nom = $nom;
    }

    public function setCalories(?float $calories): void {
        $this->calories = $calories;
    }

    public function setProtein(?float $protein): void {
        $this->protein = $protein;
    }

    public function setCarb(?float $carb): void {
        $this->carb = $carb;
    }

    public function setFat(?float $fat): void {
        $this->fat = $fat;
    }

    public function setEcoScore(?string $eco_score): void {
        $this->eco_score = $eco_score;
    }

    /**
     * Afficher l'ingrédient sous forme de tableau HTML
     */
    public function show(): void {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Calories</th><th>Protéines</th><th>Glucides</th><th>Lipides</th><th>Eco Score</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_ingredient}</td>";
        echo "<td>{$this->nom}</td>";
        echo "<td>{$this->calories}</td>";
        echo "<td>{$this->protein}</td>";
        echo "<td>{$this->carb}</td>";
        echo "<td>{$this->fat}</td>";
        echo "<td>{$this->eco_score}</td>";
        echo "</tr>";
        echo "</table>";
    }
}
?>
