<?php
/**
 * Database Connection Class (PDO)
 * Handles all database connections and configurations using PDO
 * PDO provides database-agnostic access to multiple database types
 */
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "integration_nutrition_ai";
    private $conn;

    /**
     * Connect to the database using PDO
     * @return PDO
     * @throws Exception
     */
    public function connect() {
        try {
            // PDO connection string (DSN)
            $dsn = "mysql:host=" . $this->servername . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            
            // Create PDO connection
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    // Set error mode to exceptions for better error handling
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Persistent connection (optional, but recommended)
                    PDO::ATTR_PERSISTENT => false,
                    // Emulated prepared statements disabled for security
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $this->conn;
        } catch (PDOException $e) {
            throw new Exception("PDO Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the database connection
     * @return PDO
     */
    public function getConnection() {
        if (!$this->conn) {
            return $this->connect();
        }
        return $this->conn;
    }

    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Escape string for SQL injection prevention
     * Note: Use prepared statements instead of this method when possible
     * @param string $string
     * @return string
     */
    public function escapeString($string) {
        // For PDO, it's recommended to use prepared statements
        // This method is kept for backward compatibility
        return addslashes($string);
    }
}
?>
