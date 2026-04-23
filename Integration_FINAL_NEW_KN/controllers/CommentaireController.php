<?php
/**
 * Commentaire Controller
 * Handles comment creation, retrieval, update, and deletion
 */
class CommentaireController {
    private $commentaireModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Commentaire.php';
        $this->commentaireModel = new Commentaire();
    }

    /**
     * Create a new comment
     * @return void
     */
    public function create() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $data = [
                'nom_auteur' => $_POST['nom_auteur'] ?? '',
                'contenu' => $_POST['contenu'] ?? '',
                'id_post' => $_POST['id_post'] ?? 0
            ];

            $response = $this->commentaireModel->create($data);
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get all comments for a specific post
     * @return void
     */
    public function getByPost() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_post = intval($_GET['id_post'] ?? 0);
            
            if (!$id_post) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Post ID is required'
                ]);
                exit;
            }

            $limit = intval($_GET['limit'] ?? 100);
            $comments = $this->commentaireModel->getByPost($id_post, $limit);
            echo json_encode([
                'success' => true,
                'comments' => $comments,
                'count' => count($comments)
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get comment count for a specific post
     * @return void
     */
    public function getCountByPost() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_post = intval($_GET['id_post'] ?? 0);
            
            if (!$id_post) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Post ID is required'
                ]);
                exit;
            }

            $count = $this->commentaireModel->countByPost($id_post);
            echo json_encode([
                'success' => true,
                'count' => $count
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get a single comment by ID
     * @param int $id
     * @return void
     */
    public function getById($id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $comment = $this->commentaireModel->getById($id);
            if ($comment) {
                echo json_encode([
                    'success' => true,
                    'comment' => $comment
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Comment not found'
                ]);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Update a comment
     * @param int $id
     * @return void
     */
    public function update($id) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $comments = $this->commentaireModel->getById($id);
            if (!$comments) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Comment not found'
                ]);
                exit;
            }

            $data = [
                'contenu' => $_POST['contenu'] ?? ''
            ];

            $response = $this->commentaireModel->update($id, $data);
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Delete a comment
     * @param int $id
     * @return void
     */
    public function delete($id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = $this->commentaireModel->delete($id);
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}
?>
