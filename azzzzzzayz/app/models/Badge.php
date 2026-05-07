<?php
class Badge
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function recalculateForUser(int $userId, int $seances, int $calories, string $niveau): void
    {
        if ($seances >= 1) $this->grant($userId, 'Starter');
        if ($seances >= 5) $this->grant($userId, 'Regular');
        if ($seances >= 10) $this->grant($userId, 'Consistency');
        if ($calories >= 1000) $this->grant($userId, '1000 calories');
        if ($niveau === 'avance') $this->grant($userId, 'Niveau avance');
    }

    private function grant(int $userId, string $label): void
    {
        $table = $this->tableName();
        $stmt = $this->db->prepare("INSERT IGNORE INTO {$table} (user_id,label_badge) VALUES (?,?)");
        $stmt->execute([$userId, $label]);
    }

    public function byUser(int $userId): array
    {
        $table = $this->tableName();
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE user_id = ? ORDER BY obtenu_le DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    private function tableName(): string
    {
        $stmt = $this->db->query("SHOW TABLES LIKE 'user_badges'");
        return $stmt->fetch() ? 'user_badges' : 'badges';
    }
}
