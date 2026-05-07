<?php
/**
 * Model Meal (Repas)
 * Représente un repas avec ses propriétés
 */
class Meal {
    private ?int $id_meal;
    private ?string $nom;
    private ?float $calories;
    private ?float $protein;
    private ?float $carb;
    private ?float $fat;
    private ?string $type;
    private ?string $image;

    public function __construct(?int $id_meal = null, ?string $nom = null, ?float $calories = null, ?float $protein = null, ?float $carb = null, ?float $fat = null, ?string $type = null, ?string $image = null) {
        $this->id_meal = $id_meal;
        $this->nom = $nom;
        $this->calories = $calories;
        $this->protein = $protein;
        $this->carb = $carb;
        $this->fat = $fat;
        $this->type = $type;
        $this->image = $image;
    }

    public function getIdMeal(): ?int { return $this->id_meal; }
    public function getNom(): ?string { return $this->nom; }
    public function getCalories(): ?float { return $this->calories; }
    public function getProtein(): ?float { return $this->protein; }
    public function getCarb(): ?float { return $this->carb; }
    public function getFat(): ?float { return $this->fat; }
    public function getType(): ?string { return $this->type; }
    public function getImage(): ?string { return $this->image; }

    public function setIdMeal(?int $id_meal): void { $this->id_meal = $id_meal; }
    public function setNom(?string $nom): void { $this->nom = $nom; }
    public function setCalories(?float $calories): void { $this->calories = $calories; }
    public function setProtein(?float $protein): void { $this->protein = $protein; }
    public function setCarb(?float $carb): void { $this->carb = $carb; }
    public function setFat(?float $fat): void { $this->fat = $fat; }
    public function setType(?string $type): void { $this->type = $type; }
    public function setImage(?string $image): void { $this->image = $image; }

    public function show(): void {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Calories</th><th>Protéines</th><th>Glucides</th><th>Lipides</th><th>Type</th><th>Image</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id_meal}</td>";
        echo "<td>{$this->nom}</td>";
        echo "<td>{$this->calories}</td>";
        echo "<td>{$this->protein}</td>";
        echo "<td>{$this->carb}</td>";
        echo "<td>{$this->fat}</td>";
        echo "<td>{$this->type}</td>";
        echo "<td>" . ($this->image ? basename($this->image) : '') . "</td>";
        echo "</tr>";
        echo "</table>";
    }
}
?>
