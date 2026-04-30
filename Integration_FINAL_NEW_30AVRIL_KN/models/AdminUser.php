<?php
/**
 * AdminUser Model
 * Handles admin-specific database operations for user management
 */
class AdminUser {
    private $db;
    private $conn;
    private $table = 'users';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get all users with pagination and search
     * @param int $limit Items per page
     * @param int $offset Pagination offset
     * @param string $search Search term
     * @return array Users data
     */
    public function getAllUsers($limit = 10, $offset = 0, $search = '') {
        if ($search) {
            $search = $this->db->escapeString(trim($search));
            $sql = "SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Niveau_sportif, created_at 
                    FROM {$this->table} 
                    WHERE Nom LIKE '%{$search}%' 
                    OR Prenom LIKE '%{$search}%' 
                    OR Email LIKE '%{$search}%'
                    ORDER BY created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        } else {
            $sql = "SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Niveau_sportif, created_at 
                    FROM {$this->table} 
                    ORDER BY created_at DESC
                    LIMIT {$limit} OFFSET {$offset}";
        }

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error fetching users: " . $this->conn->error);
        }

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    /**
     * Get total count of users (with optional search)
     * @param string $search Search term
     * @return int Total users count
     */
    public function getUserCount($search = '') {
        if ($search) {
            $search = $this->db->escapeString(trim($search));
            $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE Nom LIKE '%{$search}%' 
                    OR Prenom LIKE '%{$search}%' 
                    OR Email LIKE '%{$search}%'";
        } else {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        }

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error counting users: " . $this->conn->error);
        }

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id) {
        $id = intval($id);

        $sql = "SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at 
                FROM {$this->table} 
                WHERE id = {$id}";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error fetching user: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }

    /**
     * Delete a user by ID
     * @param int $id User ID
     * @return bool Success status
     */
    public function deleteUser($id) {
        $id = intval($id);

        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error deleting user: " . $this->conn->error);
        }

        return $this->conn->affected_rows > 0;
    }

    /**
     * Get user statistics
     * @return array Statistics
     */
    public function getUserStatistics() {
        $sql = "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week,
                COUNT(CASE WHEN Niveau_sportif IS NOT NULL AND Niveau_sportif != '' THEN 1 END) as active_users
                FROM {$this->table}";

        $result = $this->conn->query($sql);

        if (!$result) {
            throw new Exception("Error fetching statistics: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
