<?php
/**
 * Commentaire Model
 * Handles all comment-related database operations using PDO
 */
class Commentaire {
    private $db;
    private $conn;
    private $table = 'commentaire';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        
        // Try to create table, but don't fail if it already exists
        try {
            $this->createTable();
        } catch (Exception $e) {
            // Log but don't throw - table might already exist
            error_log("Commentaire::createTable warning: " . $e->getMessage());
        }
    }

    /**
     * Create commentaire table if it doesn't exist
     */
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
            contenu TEXT NOT NULL,
            nom_auteur VARCHAR(255) NOT NULL,
            id_post INT NOT NULL,
            date_commentaire TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_commentaire_post FOREIGN KEY (id_post) REFERENCES post(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            // Don't throw, just log
            error_log("Commentaire table creation warning: " . $e->getMessage());
        }
    }

    /**
     * Create a new comment using PDO prepared statements
     * @param array $data Comment data
     * @return array Response
     */
    public function create($data) {
        $nom_auteur = trim($data['nom_auteur'] ?? '');
        $contenu = trim($data['contenu'] ?? '');
        $id_post = intval($data['id_post'] ?? 0);

        // Validate required fields
        if (empty($nom_auteur)) {
            return [
                'success' => false,
                'error' => 'Author name is required',
                'code' => 'MISSING_AUTHOR'
            ];
        }

        if (empty($contenu)) {
            return [
                'success' => false,
                'error' => 'Comment content is required',
                'code' => 'MISSING_CONTENT'
            ];
        }

        if (!$id_post) {
            return [
                'success' => false,
                'error' => 'Post ID is required',
                'code' => 'MISSING_POST_ID'
            ];
        }

        // Validate field lengths
        if (strlen($nom_auteur) > 255) {
            return [
                'success' => false,
                'error' => 'Author name must not exceed 255 characters',
                'code' => 'AUTHOR_TOO_LONG'
            ];
        }

        if (strlen($contenu) > 5000) {
            return [
                'success' => false,
                'error' => 'Comment must not exceed 5000 characters',
                'code' => 'CONTENT_TOO_LONG'
            ];
        }

        if (strlen($contenu) < 2) {
            return [
                'success' => false,
                'error' => 'Comment must be at least 2 characters',
                'code' => 'CONTENT_TOO_SHORT'
            ];
        }

        // Verify that the post exists
        $post_check_sql = "SELECT id FROM post WHERE id = :id_post LIMIT 1";
        try {
            $stmt = $this->conn->prepare($post_check_sql);
            $stmt->execute([':id_post' => $id_post]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                return [
                    'success' => false,
                    'error' => 'Post not found',
                    'code' => 'POST_NOT_FOUND'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }

        // Insert comment using prepared statement (secure against SQL injection)
        $insert_sql = "INSERT INTO {$this->table} (nom_auteur, contenu, id_post) 
                       VALUES (:nom_auteur, :contenu, :id_post)";

        try {
            $stmt = $this->conn->prepare($insert_sql);
            $stmt->execute([
                ':nom_auteur' => $nom_auteur,
                ':contenu' => $contenu,
                ':id_post' => $id_post
            ]);

            return [
                'success' => true,
                'message' => 'Comment created successfully',
                'id' => $this->conn->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get all comments for a specific post ordered by date (newest first)
     * @param int $id_post
     * @param int $limit
     * @return array Comments
     */
    public function getByPost($id_post, $limit = 100) {
        $id_post = intval($id_post);
        $limit = intval($limit);
        $sql = "SELECT id_commentaire, nom_auteur, contenu, date_commentaire, id_post 
                FROM {$this->table} 
                WHERE id_post = :id_post 
                ORDER BY date_commentaire DESC 
                LIMIT :limit";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_post', $id_post, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $comments ?: [];
        } catch (PDOException $e) {
            error_log("Commentaire::getByPost error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a comment by ID using PDO
     * @param int $id
     * @return array|null Comment data or null
     */
    public function getById($id) {
        $id = intval($id);
        $sql = "SELECT * FROM {$this->table} WHERE id_commentaire = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Commentaire::getById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a comment using PDO prepared statements
     * @param int $id
     * @param array $data Comment data
     * @return array Response
     */
    public function update($id, $data) {
        $id = intval($id);
        $contenu = trim($data['contenu'] ?? '');

        // Validate required field
        if (empty($contenu)) {
            return [
                'success' => false,
                'error' => 'Comment content is required',
                'code' => 'MISSING_CONTENT'
            ];
        }

        // Validate field lengths
        if (strlen($contenu) > 5000) {
            return [
                'success' => false,
                'error' => 'Comment must not exceed 5000 characters',
                'code' => 'CONTENT_TOO_LONG'
            ];
        }

        if (strlen($contenu) < 2) {
            return [
                'success' => false,
                'error' => 'Comment must be at least 2 characters',
                'code' => 'CONTENT_TOO_SHORT'
            ];
        }

        // Update comment using prepared statement
        $update_sql = "UPDATE {$this->table} SET contenu = :contenu WHERE id_commentaire = :id";

        try {
            $stmt = $this->conn->prepare($update_sql);
            $stmt->execute([
                ':contenu' => $contenu,
                ':id' => $id
            ]);

            return [
                'success' => true,
                'message' => 'Comment updated successfully'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Delete a comment using PDO prepared statements
     * @param int $id
     * @return array Response
     */
    public function delete($id) {
        $id = intval($id);

        if (!$id) {
            return [
                'success' => false,
                'error' => 'Comment ID is required',
                'code' => 'INVALID_ID'
            ];
        }

        // Delete comment using prepared statement
        $delete_sql = "DELETE FROM {$this->table} WHERE id_commentaire = :id";

        try {
            $stmt = $this->conn->prepare($delete_sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Comment deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Comment not found',
                    'code' => 'NOT_FOUND'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Count comments for a specific post
     * @param int $id_post
     * @return int Count of comments
     */
    public function countByPost($id_post) {
        $id_post = intval($id_post);
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_post = :id_post";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id_post' => $id_post]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return intval($result['count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Commentaire::countByPost error: " . $e->getMessage());
            return 0;
        }
    }
}
?>
