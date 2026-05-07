<?php
class Programme
{
    private PDO $db;
    private array $columnsCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $search = ''): array
    {
        $params = [];
        $where = '';
        if ($search !== '') {
            $where = ' WHERE p.nom LIKE ? OR p.description LIKE ?';
            $params = ["%$search%", "%$search%"];
        }

        if ($this->hasColumn('programmes', 'coach_id')) {
            $stmt = $this->db->prepare('SELECT p.*, c.nom AS coach_nom FROM programmes p LEFT JOIN coaches c ON p.coach_id = c.id' . $where . ' ORDER BY p.id DESC');
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare("SELECT p.*, 'N/A' AS coach_nom FROM programmes p" . $where . ' ORDER BY p.id DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM programmes WHERE id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function create(array $data): bool
    {
        $coachId = $this->normalizeCoachId($data['coach_id'] ?? null);

        if ($this->hasColumn('programmes', 'coach_id') && $this->hasColumn('programmes', 'popularite')) {
            $stmt = $this->db->prepare('INSERT INTO programmes (nom,duree_semaines,jours_semaine,difficulte,description,coach_id,popularite) VALUES (?,?,?,?,?,?,?)');
            return $stmt->execute([
                $data['nom'],
                $data['duree_semaines'],
                $data['jours_semaine'],
                $data['difficulte'],
                $data['description'] ?? null,
                $coachId,
                $data['popularite'] ?? 1,
            ]);
        }

        $stmt = $this->db->prepare('INSERT INTO programmes (nom,duree_semaines,jours_semaine,difficulte,description) VALUES (?,?,?,?,?)');
        return $stmt->execute([
            $data['nom'],
            $data['duree_semaines'],
            $data['jours_semaine'],
            $data['difficulte'],
            $data['description'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $coachId = $this->normalizeCoachId($data['coach_id'] ?? null);

        if ($this->hasColumn('programmes', 'coach_id') && $this->hasColumn('programmes', 'popularite')) {
            $stmt = $this->db->prepare('UPDATE programmes SET nom=?, duree_semaines=?, jours_semaine=?, difficulte=?, description=?, coach_id=?, popularite=? WHERE id=?');
            return $stmt->execute([
                $data['nom'],
                $data['duree_semaines'],
                $data['jours_semaine'],
                $data['difficulte'],
                $data['description'] ?? null,
                $coachId,
                $data['popularite'] ?? 1,
                $id,
            ]);
        }

        $stmt = $this->db->prepare('UPDATE programmes SET nom=?, duree_semaines=?, jours_semaine=?, difficulte=?, description=? WHERE id=?');
        return $stmt->execute([
            $data['nom'],
            $data['duree_semaines'],
            $data['jours_semaine'],
            $data['difficulte'],
            $data['description'] ?? null,
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM programmes WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function recommendedFor(array $user): ?array
    {
        $niveau = $user['niveau'] ?? 'debutant';
        $objectif = strtolower($user['objectif'] ?? '');
        $maxDuree = ((int)($user['age'] ?? 30) > 45) ? 8 : 16;

        $keyword = 'musculation';
        if (str_contains($objectif, 'perte')) {
            $keyword = 'cardio';
        } elseif (str_contains($objectif, 'masse')) {
            $keyword = 'musculation';
        }

        $orderBy = $this->hasColumn('programmes', 'popularite') ? 'popularite DESC' : 'id DESC';
        $stmt = $this->db->prepare("SELECT * FROM programmes WHERE difficulte = ? AND duree_semaines <= ? AND (nom LIKE ? OR description LIKE ?) ORDER BY {$orderBy} LIMIT 1");
        $like = '%' . $keyword . '%';
        $stmt->execute([$niveau, $maxDuree, $like, $like]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function difficultyDistribution(): array
    {
        return $this->db->query("SELECT difficulte, COUNT(*) total FROM programmes GROUP BY difficulte")->fetchAll();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, $this->columnsCache)) {
            return $this->columnsCache[$key];
        }

        $stmt = $this->db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        $exists = (bool)$stmt->fetch();
        $this->columnsCache[$key] = $exists;

        return $exists;
    }

    private function normalizeCoachId(mixed $coachId): ?int
    {
        $id = (int)$coachId;
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT id FROM coaches WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ? $id : null;
    }
}
