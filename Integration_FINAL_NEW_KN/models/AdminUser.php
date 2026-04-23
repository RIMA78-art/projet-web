<?php
/**
 * AdminUser Model
 * Handles admin-specific database operations for user management using PDO
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
     * Get all users with pagination and search using PDO prepared statements
     * @param int $limit Items per page
     * @param int $offset Pagination offset
     * @param string $search Search term
     * @return array Users data
     */
    public function getAllUsers($limit = 10, $offset = 0, $search = '') {
        try {
            if ($search) {
                $search_term = '%' . $search . '%';
                $sql = "SELECT id, Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at 
                        FROM {$this->table} 
                        WHERE Nom LIKE :search
                        OR Prenom LIKE :search
                        OR Email LIKE :search
                        ORDER BY created_at DESC
                        LIMIT :limit OFFSET :offset";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':search', $search_term);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            } else {
                $sql = "SELECT id, Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at 
                        FROM {$this->table} 
                        ORDER BY created_at DESC
                        LIMIT :limit OFFSET :offset";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }

            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $users ?: [];
        } catch (PDOException $e) {
            error_log("AdminUser::getAllUsers error: " . $e->getMessage());
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }

    /**
     * Get total count of users (with optional search) using PDO
     * @param string $search Search term
     * @return int Total users count
     */
    public function getUserCount($search = '') {
        try {
            if ($search) {
                $search_term = '%' . $search . '%';
                $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                        WHERE Nom LIKE :search
                        OR Prenom LIKE :search
                        OR Email LIKE :search";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':search', $search_term);
            } else {
                $sql = "SELECT COUNT(*) as total FROM {$this->table}";
                $stmt = $this->conn->prepare($sql);
            }

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("AdminUser::getUserCount error: " . $e->getMessage());
            throw new Exception("Error counting users: " . $e->getMessage());
        }
    }

    /**
     * Get user by ID using PDO
     * @param int $id User ID
     * @return array|null User data or null
     */
    public function getUserById($id) {
        $id = intval($id);

        try {
            $sql = "SELECT id, Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif, created_at 
                    FROM {$this->table} 
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("AdminUser::getUserById error: " . $e->getMessage());
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }

    /**
     * Delete a user by ID using PDO
     * @param int $id User ID
     * @return bool Success status
     */
    public function deleteUser($id) {
        $id = intval($id);

        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("AdminUser::deleteUser error: " . $e->getMessage());
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }

    /**
     * Get user statistics using PDO
     * @return array Statistics
     */
    public function getUserStatistics() {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week,
                    COUNT(CASE WHEN Niveau_sportif IS NOT NULL AND Niveau_sportif != '' THEN 1 END) as active_users
                    FROM {$this->table}";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("AdminUser::getUserStatistics error: " . $e->getMessage());
            throw new Exception("Error fetching statistics: " . $e->getMessage());
        }
    }

    /**
     * Close database connection
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
