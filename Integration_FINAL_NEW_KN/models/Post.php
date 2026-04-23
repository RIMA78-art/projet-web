<?php
/**
 * Post Model
 * Handles all post-related database operations
 */
class Post {
    private $db;
    private $conn;
    private $table = 'post';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createTable();
    }

    /**
     * Create posts table if it doesn't exist
     */
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nom_auteur VARCHAR(255) NOT NULL,
            titre_post VARCHAR(255) NOT NULL,
            contenu_post TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        if (!$this->conn->query($sql)) {
            throw new Exception("Error creating posts table: " . $this->conn->error);
        }
    }

    /**
     * Create a new post
     * @param array $data Post data
     * @return array Response
     */
    public function create($data) {
        $nom_auteur = $this->db->escapeString(trim($data['nom_auteur']));
        $titre_post = $this->db->escapeString(trim($data['titre_post']));
        $contenu_post = $this->db->escapeString(trim($data['contenu_post']));

        // Validate required fields
        if (empty($nom_auteur) || empty($titre_post) || empty($contenu_post)) {
            return [
                'success' => false,
                'error' => 'All fields are required',
                'code' => 'MISSING_FIELDS'
            ];
        }

        // Validate field lengths
        if (strlen($titre_post) > 255) {
            return [
                'success' => false,
                'error' => 'Title must not exceed 255 characters',
                'code' => 'TITLE_TOO_LONG'
            ];
        }

        if (strlen($contenu_post) > 10000) {
            return [
                'success' => false,
                'error' => 'Content must not exceed 10000 characters',
                'code' => 'CONTENT_TOO_LONG'
            ];
        }

        // Insert post
        $insert_sql = "INSERT INTO {$this->table} (nom_auteur, titre_post, contenu_post) 
                       VALUES ('" . $nom_auteur . "', '" . $titre_post . "', '" . $contenu_post . "')";

        if ($this->conn->query($insert_sql)) {
            return [
                'success' => true,
                'message' => 'Post created successfully',
                'id' => $this->conn->insert_id
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Get all posts ordered by date
     * @param int $limit
     * @return array Posts
     */
    public function getAll($limit = 50) {
        $limit = intval($limit);
        $sql = "SELECT id, nom_auteur, titre_post, contenu_post, created_at FROM {$this->table} ORDER BY created_at DESC LIMIT " . $limit;
        $result = $this->conn->query($sql);
        
        $posts = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }

    /**
     * Get a post by ID
     * @param int $id
     * @return array Post data or null
     */
    public function getById($id) {
        $id = intval($id);
        $sql = "SELECT * FROM {$this->table} WHERE id = " . $id;
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    /**
     * Update a post
     * @param int $id
     * @param array $data Post data
     * @return array Response
     */
    public function update($id, $data) {
        $id = intval($id);
        $titre_post = $this->db->escapeString(trim($data['titre_post']));
        $contenu_post = $this->db->escapeString(trim($data['contenu_post']));

        // Validate required fields
        if (empty($titre_post) || empty($contenu_post)) {
            return [
                'success' => false,
                'error' => 'All fields are required',
                'code' => 'MISSING_FIELDS'
            ];
        }

        // Validate field lengths
        if (strlen($titre_post) > 255) {
            return [
                'success' => false,
                'error' => 'Title must not exceed 255 characters',
                'code' => 'TITLE_TOO_LONG'
            ];
        }

        if (strlen($contenu_post) > 10000) {
            return [
                'success' => false,
                'error' => 'Content must not exceed 10000 characters',
                'code' => 'CONTENT_TOO_LONG'
            ];
        }

        // Update post
        $update_sql = "UPDATE {$this->table} SET titre_post = '" . $titre_post . "', contenu_post = '" . $contenu_post . "' WHERE id = " . $id;

        if ($this->conn->query($update_sql)) {
            return [
                'success' => true,
                'message' => 'Post updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Delete a post
     * @param int $id
     * @return array Response
     */
    public function delete($id) {
        $id = intval($id);

        if (!$id) {
            return [
                'success' => false,
                'error' => 'Post ID is required',
                'code' => 'INVALID_ID'
            ];
        }

        // Delete post
        $delete_sql = "DELETE FROM {$this->table} WHERE id = " . $id;

        if ($this->conn->query($delete_sql)) {
            if ($this->conn->affected_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Post deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Post not found',
                    'code' => 'NOT_FOUND'
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => 'Database error: ' . $this->conn->error,
                'code' => 'DB_ERROR'
            ];
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
