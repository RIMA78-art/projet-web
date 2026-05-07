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
            $searchTerm = '%' . $search . '%';
            $stmt = $this->conn->prepare("SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Niveau_sportif, created_at 
                    FROM {$this->table} 
                    WHERE Nom LIKE ? 
                    OR Prenom LIKE ? 
                    OR Email LIKE ?
                    ORDER BY created_at DESC
                    LIMIT ? OFFSET ?");
            $stmt->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
        } else {
            $stmt = $this->conn->prepare("SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Niveau_sportif, created_at 
                    FROM {$this->table} 
                    ORDER BY created_at DESC
                    LIMIT ? OFFSET ?");
            $stmt->bind_param('ii', $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();

        return $users;
    }

    /**
     * Get total count of users (with optional search)
     * @param string $search Search term
     * @return int Total users count
     */
    public function getUserCount($search = '') {
        if ($search) {
            $searchTerm = '%' . $search . '%';
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM {$this->table} 
                    WHERE Nom LIKE ? 
                    OR Prenom LIKE ? 
                    OR Email LIKE ?");
            $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM {$this->table}");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }

    /**
     * Get user by ID
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id) {
        $id = intval($id);

        $stmt = $this->conn->prepare("SELECT id, Nom, Prenom, Email, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at 
                FROM {$this->table} 
                WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    /**
     * Delete a user by ID
     * @param int $id User ID
     * @return bool Success status
     */
    public function deleteUser($id) {
        $id = intval($id);

        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected > 0;
    }

    /**
     * Get user statistics
     * @return array Statistics
     */
    public function getUserStatistics() {
        $stmt = $this->conn->prepare("SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week,
                COUNT(CASE WHEN Niveau_sportif IS NOT NULL AND Niveau_sportif != '' THEN 1 END) as active_users
                FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->get_result();

        $stats = $result->fetch_assoc();
        $stmt->close();
        return $stats;
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
