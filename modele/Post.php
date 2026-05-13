<?php
/**
 * Post Model — adapté pour NutriNova MVC
 * Utilise config::getConnexion() au lieu de Database
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/GroqModerationService.php';

class Post {
    private $conn;
    private $table = 'post';
    private $moderation;

    public function __construct() {
        $this->conn = config::getConnexion();
        $this->moderation = new GroqModerationService();
        $this->initTable();
    }

    private function initTable() {
        try {
            // Ajouter la colonne fichier si manquante
            $check = $this->conn->query("SHOW COLUMNS FROM {$this->table} LIKE 'fichier'");
            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec("ALTER TABLE {$this->table} ADD COLUMN fichier VARCHAR(255) DEFAULT NULL AFTER contenu_post");
            }
        } catch (PDOException $e) {
            error_log("Post::initTable: " . $e->getMessage());
        }
    }

    public function create($data) {
        $nom_auteur   = trim($data['nom_auteur']   ?? '');
        $titre_post   = trim($data['titre_post']   ?? '');
        $contenu_post = trim($data['contenu_post'] ?? '');
        $fichier      = $data['fichier'] ?? null;

        if (empty($nom_auteur) || empty($titre_post) || empty($contenu_post)) {
            return ['success' => false, 'error' => 'Tous les champs sont requis', 'code' => 'MISSING_FIELDS'];
        }
        if (strlen($titre_post) > 255) {
            return ['success' => false, 'error' => 'Titre trop long (max 255 caractères)', 'code' => 'TITLE_TOO_LONG'];
        }
        if (strlen($contenu_post) > 10000) {
            return ['success' => false, 'error' => 'Contenu trop long (max 10 000 caractères)', 'code' => 'CONTENT_TOO_LONG'];
        }

        // Modération Groq — vérifier titre + contenu
        $textToCheck = $titre_post . "\n" . $contenu_post;
        $modResult = $this->moderation->moderateText($textToCheck);
        if ($modResult['success'] && $modResult['flagged']) {
            return ['success' => false, 'error' => 'Post refusé : ' . ($modResult['reason'] ?? 'contenu inapproprié'), 'code' => 'MODERATION_REJECTED'];
        }

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->table} (nom_auteur, titre_post, contenu_post, fichier)
                 VALUES (:nom_auteur, :titre_post, :contenu_post, :fichier)"
            );
            $stmt->execute([
                ':nom_auteur'   => $nom_auteur,
                ':titre_post'   => $titre_post,
                ':contenu_post' => $contenu_post,
                ':fichier'      => $fichier,
            ]);
            return ['success' => true, 'message' => 'Post créé', 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Post::create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données', 'code' => 'DB_ERROR'];
        }
    }

    public function getAll($limit = 50) {
        $limit = max(1, min(500, (int)$limit));
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, nom_auteur, titre_post, contenu_post, fichier, created_at
                 FROM {$this->table}
                 ORDER BY created_at DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Post::getAll: " . $e->getMessage());
            return [];
        }
    }

    public function getAllWithComments($limit = 1000) {
        $posts = $this->getAll($limit);
        foreach ($posts as &$post) {
            try {
                $stmt = $this->conn->prepare(
                    "SELECT id_commentaire, contenu, nom_auteur, date_commentaire
                     FROM commentaire WHERE id_post = :id ORDER BY date_commentaire ASC"
                );
                $stmt->execute([':id' => $post['id']]);
                $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $post['comments'] = [];
            }
        }
        return $posts;
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id, nom_auteur, titre_post, contenu_post, fichier, created_at
                 FROM {$this->table} WHERE id = :id"
            );
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Post::getById: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $data) {
        $titre_post   = trim($data['titre_post']   ?? '');
        $contenu_post = trim($data['contenu_post'] ?? '');

        if (empty($titre_post) || empty($contenu_post)) {
            return ['success' => false, 'error' => 'Titre et contenu requis'];
        }

        // Modération Groq — vérifier titre + contenu
        $textToCheck = $titre_post . "\n" . $contenu_post;
        $modResult = $this->moderation->moderateText($textToCheck);
        if ($modResult['success'] && $modResult['flagged']) {
            return ['success' => false, 'error' => 'Modification refusée : ' . ($modResult['reason'] ?? 'contenu inapproprié')];
        }

        try {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET titre_post = :titre, contenu_post = :contenu
                 WHERE id = :id"
            );
            $stmt->execute([':titre' => $titre_post, ':contenu' => $contenu_post, ':id' => (int)$id]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Post mis à jour']
                : ['success' => false, 'error' => 'Post introuvable'];
        } catch (PDOException $e) {
            error_log("Post::update: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Post supprimé']
                : ['success' => false, 'error' => 'Post introuvable'];
        } catch (PDOException $e) {
            error_log("Post::delete: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }

    public function getTopContributors($limit = 3) {
        $limit = max(1, min(20, (int)$limit));
        try {
            $stmt = $this->conn->prepare(
                "SELECT nom_auteur, COUNT(*) AS post_count
                 FROM {$this->table}
                 GROUP BY nom_auteur
                 ORDER BY post_count DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Post::getTopContributors: " . $e->getMessage());
            return [];
        }
    }
}
?>
