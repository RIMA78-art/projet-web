<?php
/**
 * Post Controller
 * Handles post creation, retrieval, update, and deletion
 */
class PostController {
    private $postModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/Post.php';
        $this->postModel = new Post();
    }

    /**
     * Create a new post
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
                'titre_post' => $_POST['titre_post'] ?? '',
                'contenu_post' => $_POST['contenu_post'] ?? ''
            ];

            $response = $this->postModel->create($data);
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
     * Get all posts
     * @return void
     */
    public function getAll() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $limit = intval($_GET['limit'] ?? 50);
            $posts = $this->postModel->getAll($limit);
            echo json_encode([
                'success' => true,
                'posts' => $posts
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
     * Get a single post by ID
     * @param int $id
     * @return void
     */
    public function getById($id) {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $post = $this->postModel->getById($id);
            if ($post) {
                echo json_encode([
                    'success' => true,
                    'post' => $post
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Post not found'
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
     * Update a post
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
            $data = [
                'titre_post' => $_POST['titre_post'] ?? '',
                'contenu_post' => $_POST['contenu_post'] ?? ''
            ];

            $response = $this->postModel->update($id, $data);
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
     * Delete a post
     * @param int $id
     * @return void
     */
    public function delete($id) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid request method'
            ]);
            exit;
        }

        try {
            $response = $this->postModel->delete($id);
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
