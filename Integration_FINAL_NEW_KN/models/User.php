<?php
/**
 * User Model
 * Handles all user-related database operations using PDO
 */
class User {
    private $db;
    private $conn;
    private $table = 'users';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
        $this->createTable();
    }

    /**
     * Create users table if it doesn't exist
     */
    private function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            Nom VARCHAR(255) NOT NULL,
            Prenom VARCHAR(255) NOT NULL,
            Email VARCHAR(255) NOT NULL UNIQUE,
            Mot_de_passe VARCHAR(255) NOT NULL,
            Taille_cm INT DEFAULT NULL,
            Poids_kg FLOAT DEFAULT NULL,
            Objectif VARCHAR(255) DEFAULT NULL,
            Niveau_sportif VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Error creating users table: " . $e->getMessage());
        }
    }

    /**
     * Register a new user using PDO prepared statements
     * @param array $data User data
     * @return array Response
     */
    public function register($data) {
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = trim($data['email'] ?? '');
        $mot_de_passe = $data['mot_de_passe'] ?? '';
        $taille = isset($data['taille']) && $data['taille'] ? intval($data['taille']) : null;
        $poids = isset($data['poids']) && $data['poids'] ? floatval($data['poids']) : null;
        $objectif = trim($data['objectif'] ?? '');
        $niveau_sportif = trim($data['niveau_sportif'] ?? '');

        // Validate required fields
        if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
            return [
                'success' => false,
                'error' => 'Missing required fields',
                'code' => 'MISSING_FIELDS'
            ];
        }

        // Validate password length
        if (strlen($mot_de_passe) < 6) {
            return [
                'success' => false,
                'error' => 'Password must be at least 6 characters',
                'code' => 'WEAK_PASSWORD'
            ];
        }

        try {
            // Check if email already exists
            $check_sql = "SELECT id FROM {$this->table} WHERE Email = :email LIMIT 1";
            $check_stmt = $this->conn->prepare($check_sql);
            $check_stmt->execute([':email' => $email]);
            
            if ($check_stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'error' => 'Email already exists',
                    'code' => 'EMAIL_EXISTS'
                ];
            }

            // Insert new user using prepared statement
            $insert_sql = "INSERT INTO {$this->table} (Nom, Prenom, Email, Mot_de_passe, Taille_cm, Poids_kg, Objectif, Niveau_sportif) 
                           VALUES (:nom, :prenom, :email, :mot_de_passe, :taille, :poids, :objectif, :niveau_sportif)";

            $stmt = $this->conn->prepare($insert_sql);
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mot_de_passe' => $mot_de_passe,
                ':taille' => $taille,
                ':poids' => $poids,
                ':objectif' => $objectif,
                ':niveau_sportif' => $niveau_sportif
            ]);

            return [
                'success' => true,
                'message' => 'User registered successfully',
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
     * Login user with email and password using PDO prepared statements
     * @param string $email
     * @param string $password
     * @return array Response with user data or error
     */
    public function login($email, $password) {
        $email = trim($email);
        $password = trim($password);

        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'error' => 'Email and password are required',
                'code' => 'MISSING_CREDENTIALS'
            ];
        }

        try {
            // Check user credentials using prepared statement
            $sql = "SELECT id, Nom, Prenom, Email FROM {$this->table} WHERE Email = :email AND Mot_de_passe = :password LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':password' => $password
            ]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'nom' => $user['Nom'],
                        'prenom' => $user['Prenom'],
                        'email' => $user['Email']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Invalid email or password',
                    'code' => 'INVALID_CREDENTIALS'
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
     * Get user by ID using PDO
     * @param int $id
     * @return array|null User data or null
     */
    public function getUserById($id) {
        $id = intval($id);
        
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("User::getUserById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by email using PDO
     * @param string $email
     * @return array|null User data or null
     */
    public function getUserByEmail($email) {
        $email = trim($email);
        
        try {
            $sql = "SELECT * FROM {$this->table} WHERE Email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("User::getUserByEmail error: " . $e->getMessage());
            return null;
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
