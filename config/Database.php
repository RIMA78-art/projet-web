<?php
/**
 * Classe de connexion à la base de données avec PDO
 * Utilise le pattern Singleton pour une seule instance
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    // Paramètres de connexion
    private $host = 'localhost';
    private $db_name = 'nutrinova';
    private $user = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données: ' . $e->getMessage());
        }
    }
    
    /**
     * Méthode Singleton pour obtenir l'instance unique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retourne la connexion PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Empêcher le clonage
     */
    public function __clone() {}
    
    /**
     * Empêcher la désérialisation
     */
    public function __wakeup() {}
}
?>
