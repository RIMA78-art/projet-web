<?php
class Seance
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO seances (user_id,programme_id,duree_effectuee,calories_brulees,progression) VALUES (?,?,?,?,?)');
        return $stmt->execute([$data['user_id'],$data['programme_id'],$data['duree_effectuee'],$data['calories_brulees'],$data['progression']]);
    }

    public function byUser(int $userId, string $q=''): array
    {
        $sql = 'SELECT s.*, p.nom as programme_nom FROM seances s LEFT JOIN programmes p ON s.programme_id = p.id WHERE s.user_id = ?';
        $params = [$userId];
        if ($q !== '') {
            $sql .= ' AND p.nom LIKE ?';
            $params[] = "%$q%";
        }
        $sql .= ' ORDER BY s.date_seance DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function completedCount(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM seances')->fetchColumn();
    }

    public function caloriesTotalByUser(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(calories_brulees),0) FROM seances WHERE user_id=?');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function caloriesTotal(): int
    {
        return (int)$this->db->query('SELECT COALESCE(SUM(calories_brulees),0) FROM seances')->fetchColumn();
    }

    public function caloriesAvg(): int
    {
        return (int)$this->db->query('SELECT COALESCE(AVG(calories_brulees),0) FROM seances')->fetchColumn();
    }

    public function completionRate(): int
    {
        return (int)$this->db->query('SELECT COALESCE(AVG(progression),0) FROM seances')->fetchColumn();
    }

    public function sessionsByWeek(): array
    {
        return $this->db->query('SELECT YEARWEEK(date_seance, 1) week_key, COUNT(*) total FROM seances GROUP BY YEARWEEK(date_seance, 1) ORDER BY week_key DESC LIMIT 8')->fetchAll();
    }

    public function caloriesTrend(): array
    {
        return $this->db->query('SELECT DATE(date_seance) jour, SUM(calories_brulees) total FROM seances GROUP BY DATE(date_seance) ORDER BY jour DESC LIMIT 10')->fetchAll();
    }

    public function sessionsThisWeek(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM seances WHERE YEARWEEK(date_seance, 1) = YEARWEEK(CURRENT_DATE(), 1)')->fetchColumn();
    }

    public function sessionsThisMonth(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM seances WHERE YEAR(date_seance) = YEAR(CURRENT_DATE()) AND MONTH(date_seance) = MONTH(CURRENT_DATE())')->fetchColumn();
    }
}
