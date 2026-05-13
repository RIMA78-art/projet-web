<?php
/**
 * Commentaire Model — adapté pour NutriNova MVC
 * Utilise config::getConnexion() au lieu de Database
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/GroqModerationService.php';

class Commentaire {
    private $conn;
    private $table = 'commentaire';
    private $moderation;

    public function __construct() {
        $this->conn = config::getConnexion();
        $this->moderation = new GroqModerationService();
        $this->initTable();
    }

    private function initTable() {
        try {
            // Ajouter les colonnes de modération si manquantes
            $cols = $this->conn->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('moderation_status', $cols)) {
                $this->conn->exec(
                    "ALTER TABLE {$this->table}
                     ADD COLUMN moderation_status VARCHAR(50) DEFAULT 'pending',
                     ADD COLUMN moderation_checked_at DATETIME DEFAULT NULL"
                );
            }
        } catch (PDOException $e) {
            error_log("Commentaire::initTable: " . $e->getMessage());
        }
    }

    private function validateContent($contenu) {
        // Longueur minimale et maximale
        if (strlen($contenu) < 2)    return ['valid' => false, 'error' => 'Commentaire trop court (min 2 caractères)'];
        if (strlen($contenu) > 5000) return ['valid' => false, 'error' => 'Commentaire trop long (max 5000 caractères)'];
        // Pas 3+ caractères identiques consécutifs
        if (preg_match('/(.)\1{2,}/', $contenu)) {
            return ['valid' => false, 'error' => 'Commentaire invalide (trop de caractères répétés)'];
        }
        return ['valid' => true];
    }

    public function create($data) {
        $contenu    = trim($data['contenu']    ?? '');
        $nom_auteur = trim($data['nom_auteur'] ?? '');
        $id_post    = (int)($data['id_post']   ?? 0);

        if (empty($contenu) || empty($nom_auteur) || !$id_post) {
            return ['success' => false, 'error' => 'Tous les champs sont requis'];
        }

        $validation = $this->validateContent($contenu);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Modération Groq
        $moderationResult = $this->moderation->moderateText($contenu);
        $modStatus = 'approved';
        if ($moderationResult['success'] && $moderationResult['flagged']) {
            $modStatus = 'rejected';
            return ['success' => false, 'error' => 'Commentaire refusé : ' . ($moderationResult['reason'] ?? 'contenu inapproprié')];
        }

        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->table}
                 (contenu, nom_auteur, id_post, moderation_status, moderation_checked_at)
                 VALUES (:contenu, :nom_auteur, :id_post, :mod_status, NOW())"
            );
            $stmt->execute([
                ':contenu'    => $contenu,
                ':nom_auteur' => $nom_auteur,
                ':id_post'    => $id_post,
                ':mod_status' => $modStatus,
            ]);
            return ['success' => true, 'message' => 'Commentaire publié', 'id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Commentaire::create: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }

    public function getByPost($id_post, $limit = 100) {
        $limit = max(1, min(500, (int)$limit));
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_commentaire, contenu, nom_auteur, date_commentaire, moderation_status
                 FROM {$this->table}
                 WHERE id_post = :id_post AND moderation_status != 'rejected'
                 ORDER BY date_commentaire ASC
                 LIMIT :limit"
            );
            $stmt->bindValue(':id_post', (int)$id_post, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Commentaire::getByPost: " . $e->getMessage());
            return [];
        }
    }

    public function countByPost($id_post) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM {$this->table}
                 WHERE id_post = :id_post AND moderation_status != 'rejected'"
            );
            $stmt->execute([':id_post' => (int)$id_post]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM {$this->table} WHERE id_commentaire = :id"
            );
            $stmt->execute([':id' => (int)$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function update($id, $data) {
        $contenu = trim($data['contenu'] ?? '');
        if (empty($contenu)) return ['success' => false, 'error' => 'Contenu requis'];

        $validation = $this->validateContent($contenu);
        if (!$validation['valid']) return ['success' => false, 'error' => $validation['error']];

        // Modération Groq
        $moderationResult = $this->moderation->moderateText($contenu);
        if ($moderationResult['success'] && $moderationResult['flagged']) {
            return ['success' => false, 'error' => 'Modification refusée : ' . ($moderationResult['reason'] ?? 'contenu inapproprié')];
        }

        try {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table} SET contenu = :contenu WHERE id_commentaire = :id"
            );
            $stmt->execute([':contenu' => $contenu, ':id' => (int)$id]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Commentaire mis à jour']
                : ['success' => false, 'error' => 'Commentaire introuvable'];
        } catch (PDOException $e) {
            error_log("Commentaire::update: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_commentaire = :id");
            $stmt->execute([':id' => (int)$id]);
            return $stmt->rowCount() > 0
                ? ['success' => true, 'message' => 'Commentaire supprimé']
                : ['success' => false, 'error' => 'Commentaire introuvable'];
        } catch (PDOException $e) {
            error_log("Commentaire::delete: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }

    public function moderateCommentById($id) {
        $comment = $this->getById($id);
        if (!$comment) return ['success' => false, 'error' => 'Commentaire introuvable'];

        $result = $this->moderation->moderateText($comment['contenu']);
        $status = ($result['success'] && $result['flagged']) ? 'rejected' : 'approved';

        try {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET moderation_status = :status, moderation_checked_at = NOW()
                 WHERE id_commentaire = :id"
            );
            $stmt->execute([':status' => $status, ':id' => (int)$id]);
            return ['success' => true, 'status' => $status, 'moderation' => $result];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Erreur base de données'];
        }
    }
}
?>
