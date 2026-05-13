<?php
/**
 * AdminForumControleur — contrôleur backoffice pour la gestion des posts/commentaires
 * API JSON consommée par user1.js (admin panel)
 * Actions : list_posts, get_post, update_post, delete_post,
 *           list_comments, delete_comment, get_stats
 */
require_once __DIR__ . '/../../modele/config.php';
require_once __DIR__ . '/../../modele/Session.php';
require_once __DIR__ . '/../../modele/Post.php';
require_once __DIR__ . '/../../modele/Commentaire.php';
require_once __DIR__ . '/../../modele/User.php';

// Initialiser la session
Session::demarrer();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Protéger : seuls les admins ont accès
$admin = Session::getUtilisateur();
if (!$admin || ($admin['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Accès réservé aux administrateurs'], JSON_UNESCAPED_UNICODE);
    exit;
}

$action       = $_GET['action'] ?? '';
$postModel    = new Post();
$commentModel = new Commentaire();

function jsonOk(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonErr(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {

    // ── LISTE DES POSTS ──────────────────────────────────────────────────────
    case 'list_posts':
        $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));
        $posts = $postModel->getAll($limit);
        foreach ($posts as &$post) {
            $post['comment_count'] = $commentModel->countByPost($post['id']);
        }
        jsonOk(['success' => true, 'posts' => $posts, 'total' => count($posts)]);
        break;

    // ── LISTE DES POSTS AVEC COMMENTAIRES ────────────────────────────────────
    case 'list_posts_with_comments':
        $limit = max(1, min(500, (int)($_GET['limit'] ?? 100)));
        $posts = $postModel->getAllWithComments($limit);
        jsonOk(['success' => true, 'posts' => $posts, 'total' => count($posts)]);
        break;

    // ── DÉTAIL D'UN POST ─────────────────────────────────────────────────────
    case 'get_post':
        $id   = (int)($_GET['id'] ?? 0);
        $post = $postModel->getById($id);
        if (!$post) jsonErr('Post introuvable', 404);
        jsonOk(['success' => true, 'post' => $post]);
        break;

    // ── MODIFIER UN POST ─────────────────────────────────────────────────────
    case 'update_post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Méthode non autorisée', 405);
        $raw = json_decode(file_get_contents('php://input'), true);
        if (!$raw) jsonErr('Données JSON manquantes', 400);

        $postId = (int)($raw['post_id'] ?? 0);
        if (!$postId) jsonErr('ID post requis', 400);

        $result = $postModel->update($postId, [
            'titre_post'   => trim($raw['titre_post']   ?? ''),
            'contenu_post' => trim($raw['contenu_post'] ?? ''),
        ]);
        jsonOk($result);
        break;

    // ── SUPPRIMER UN POST ─────────────────────────────────────────────────────
    case 'delete_post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Méthode non autorisée', 405);
        $raw    = json_decode(file_get_contents('php://input'), true);
        $postId = (int)($raw['post_id'] ?? 0);
        if (!$postId) jsonErr('ID post requis', 400);

        $result = $postModel->delete($postId);
        jsonOk($result);
        break;

    // ── LISTE DES COMMENTAIRES D'UN POST ────────────────────────────────────
    case 'list_comments':
        $postId   = (int)($_GET['id_post'] ?? 0);
        if (!$postId) jsonErr('ID post requis', 400);
        $comments = $commentModel->getByPost($postId, 500);
        jsonOk(['success' => true, 'comments' => $comments]);
        break;

    // ── SUPPRIMER UN COMMENTAIRE ─────────────────────────────────────────────
    case 'delete_comment':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Méthode non autorisée', 405);
        $raw       = json_decode(file_get_contents('php://input'), true);
        $commentId = (int)($raw['comment_id'] ?? 0);
        if (!$commentId) jsonErr('ID commentaire requis', 400);

        $result = $commentModel->delete($commentId);
        jsonOk($result);
        break;

    // ── MODIFIER UN COMMENTAIRE ──────────────────────────────────────────────
    case 'update_comment':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonErr('Méthode non autorisée', 405);
        $raw       = json_decode(file_get_contents('php://input'), true);
        if (!$raw) jsonErr('Données JSON manquantes', 400);

        $commentId = (int)($raw['comment_id'] ?? 0);
        if (!$commentId) jsonErr('ID commentaire requis', 400);

        $result = $commentModel->update($commentId, [
            'contenu' => trim($raw['contenu'] ?? ''),
        ]);
        jsonOk($result);
        break;

    // ── STATISTIQUES FORUM ───────────────────────────────────────────────────
    case 'get_stats':
        $posts = $postModel->getAll(1000);
        $totalPosts    = count($posts);
        $totalComments = 0;
        foreach ($posts as $post) {
            $totalComments += $commentModel->countByPost($post['id']);
        }
        $topContributors = $postModel->getTopContributors(5);

        // Community members count
        $allUsers = User::tousLesUtilisateurs();
        $communityMembers = is_array($allUsers) ? count($allUsers) : 0;

        // Posts this week
        $postsThisWeek = count(array_filter($posts, function($p) {
            return isset($p['created_at']) && strtotime($p['created_at']) > strtotime('-7 days');
        }));

        jsonOk([
            'success'           => true,
            'total_posts'       => $totalPosts,
            'total_comments'    => $totalComments,
            'top_contributors'  => $topContributors,
            'community_members' => $communityMembers,
            'posts_this_week'   => $postsThisWeek,
        ]);
        break;

    default:
        jsonErr('Action invalide', 400);
        break;
}
?>
