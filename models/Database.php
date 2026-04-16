<?php
/**
 * Database Connection Class
 * Handles all database connections and configurations
 */
class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "integration_nutrition_ai";
    private $conn;

    /**
     * Connect to the database
     * @return mysqli|null
     */
    public function connect() {
        $this->conn = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->dbname
        );

        // Check connection
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        // Set charset to utf8mb4
        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }

    /**
     * Get the database connection
     * @return mysqli
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
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Escape string for SQL injection prevention
     * @param string $string
     * @return string
     */
    public function escapeString($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>
