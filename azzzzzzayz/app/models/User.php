<?php
class User
{
    private PDO $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function create(array $data): bool
    {
        $role = $data['role'] ?? 'utilisateur';
        if ($this->countAll() === 0) {
            // Premier compte du systeme = admin pour demarrer sans blocage.
            $role = 'admin';
        }

        $stmt = $this->db->prepare('INSERT INTO users (nom,email,password,role,age,objectif,niveau) VALUES (?,?,?,?,?,?,?)');
        return $stmt->execute([
            $data['nom'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $role,
            $data['age'],
            $data['objectif'],
            $data['niveau']
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email=? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function activeCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM users WHERE actif = 1")->fetchColumn();
    }

    public function countAll(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
}
