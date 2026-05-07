<?php
class Coach
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function all(string $search = ''): array
    {
        if ($search === '') {
            return $this->db->query('SELECT * FROM coaches ORDER BY id DESC')->fetchAll();
        }
        $stmt = $this->db->prepare('SELECT * FROM coaches WHERE nom LIKE ? OR email LIKE ? OR specialite LIKE ? ORDER BY id DESC');
        $like = "%$search%";
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM coaches WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO coaches (nom,email,telephone,specialite) VALUES (?,?,?,?)');
        return $stmt->execute([$data['nom'],$data['email'],$data['telephone'],$data['specialite']]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE coaches SET nom=?, email=?, telephone=?, specialite=? WHERE id=?');
        return $stmt->execute([$data['nom'],$data['email'],$data['telephone'],$data['specialite'],$id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM coaches WHERE id=?');
        return $stmt->execute([$id]);
    }
}
