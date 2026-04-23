<?php

declare(strict_types=1);

class ProgrammeModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = require __DIR__ . '/../config/database.php';
    }

    public function getAllProgrammes(array $filters = []): array
    {
        $sql = 'SELECT * FROM programme';
        $conditions = [];
        $params = [];

        if (!empty($filters['type'])) {
            $conditions[] = 'type = :type';
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['niveau'])) {
            $conditions[] = 'niveau = :niveau';
            $params[':niveau'] = $filters['niveau'];
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY id_programme DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getProgrammeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM programme WHERE id_programme = :id');
        $stmt->execute([':id' => $id]);
        $programme = $stmt->fetch();

        return $programme === false ? null : $programme;
    }

    public function addProgramme(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO programme (nom, type, duree, niveau, description, calories)
            VALUES (:nom, :type, :duree, :niveau, :description, :calories)'
        );

        return $stmt->execute([
            ':nom' => $data['nom'],
            ':type' => $data['type'],
            ':duree' => $data['duree'],
            ':niveau' => $data['niveau'],
            ':description' => $data['description'],
            ':calories' => $data['calories'],
        ]);
    }

    public function updateProgramme(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE programme SET
                nom = :nom,
                type = :type,
                duree = :duree,
                niveau = :niveau,
                description = :description,
                calories = :calories
            WHERE id_programme = :id'
        );

        return $stmt->execute([
            ':nom' => $data['nom'],
            ':type' => $data['type'],
            ':duree' => $data['duree'],
            ':niveau' => $data['niveau'],
            ':description' => $data['description'],
            ':calories' => $data['calories'],
            ':id' => $id,
        ]);
    }

    public function deleteProgramme(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM programme WHERE id_programme = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function getUniqueTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT type FROM programme ORDER BY type ASC');
        return array_column($stmt->fetchAll(), 'type');
    }

    public function getUniqueNiveaux(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT niveau FROM programme ORDER BY niveau ASC');
        return array_column($stmt->fetchAll(), 'niveau');
    }
}
