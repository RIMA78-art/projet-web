<?php
/**
 * CONTROLEUR : backoffice/AdminControleur.php
 * Reçoit les fetch() du JS backoffice et retourne du JSON
 * ACCÈS RÉSERVÉ AUX ADMINS
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../modele/config.php';
require_once __DIR__ . '/../../modele/User.php';
require_once __DIR__ . '/../../modele/Session.php';

// ── Toutes les routes admin nécessitent une session admin ────
Session::proteger('admin');

$action  = $_GET['action'] ?? '';
$reponse = ['success' => false, 'message' => 'Action inconnue'];

try {
    switch ($action) {

        // ════════════════════════════════════
        // Tous les utilisateurs
        // ════════════════════════════════════
        case 'get-all':
            $users   = User::tousLesUtilisateurs();
            // Ajouter score et badge à chaque utilisateur
            $users = array_map(function($user) {
                $score = User::calculerScore($user);
                $badge = User::obtenirBadge($score);
                return array_merge($user, [
                    'score' => $score,
                    'badge' => $badge
                ]);
            }, $users);
            $reponse = ['success' => true, 'users' => $users];
            break;

        // ════════════════════════════════════
        // Recherche
        // ════════════════════════════════════
        case 'search':
            $terme   = $_POST['terme'] ?? ($_GET['terme'] ?? '');
            $users   = User::rechercher($terme);
            // Ajouter score et badge à chaque utilisateur
            $users = array_map(function($user) {
                $score = User::calculerScore($user);
                $badge = User::obtenirBadge($score);
                return array_merge($user, [
                    'score' => $score,
                    'badge' => $badge
                ]);
            }, $users);
            $reponse = ['success' => true, 'users' => $users];
            break;

        // ════════════════════════════════════
        // Créer un utilisateur
        // ════════════════════════════════════
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

            $nom          = trim($_POST['nom']          ?? '');
            $prenom       = trim($_POST['prenom']       ?? '');
            $email        = trim($_POST['email']        ?? '');
            $password     = trim($_POST['password']     ?? '');
            $taille       = (int)($_POST['taille']      ?? 0);
            $poids        = (float)($_POST['poids']     ?? 0);
            $objectif     = trim($_POST['objectif']     ?? '');
            $niveau_sport = trim($_POST['niveau_sport'] ?? '');

            if (!$nom || !$email || !$password) {
                $reponse = ['success' => false, 'message' => 'Champs obligatoires manquants'];
                break;
            }
            if (User::emailExiste($email)) {
                $reponse = ['success' => false, 'message' => 'Email déjà utilisé'];
                break;
            }

            $id = User::creer($nom, $prenom, $email, $password, $taille, $poids, $objectif, $niveau_sport);
            $reponse = ['success' => true, 'message' => 'Utilisateur créé', 'id_user' => $id];
            break;

        // ════════════════════════════════════
        // Modifier un utilisateur
        // ════════════════════════════════════
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

            $id           = (int)($_POST['id_user']      ?? 0);
            $nom          = trim($_POST['nom']            ?? '');
            $prenom       = trim($_POST['prenom']         ?? '');
            $taille       = (int)($_POST['taille']        ?? 0);
            $poids        = (float)($_POST['poids']       ?? 0);
            $objectif     = trim($_POST['objectif']       ?? '');
            $niveau_sport = trim($_POST['niveau_sport']   ?? '');
            $email        = trim($_POST['email']          ?? '');

            if (!$id) { $reponse = ['success' => false, 'message' => 'ID manquant']; break; }

            $ok = User::mettreAJour($id, $nom, $prenom, $taille, $poids, $objectif, $niveau_sport);

            // Changer le mot de passe si fourni
            $password = trim($_POST['password'] ?? '');
            if ($ok && !empty($password)) {
                User::changerMotDePasse($id, $password);
            }

            $reponse = $ok
                ? ['success' => true,  'message' => 'Utilisateur mis à jour']
                : ['success' => false, 'message' => 'Erreur mise à jour'];
            break;

        // ════════════════════════════════════
        // Supprimer un utilisateur
        // ════════════════════════════════════
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') break;

            $id = (int)($_POST['id_user'] ?? 0);
            if (!$id) { $reponse = ['success' => false, 'message' => 'ID manquant']; break; }

            // Empêcher l'admin de se supprimer lui-même
            $sessionUser = Session::getUtilisateur();
            if ($id === (int)$sessionUser['id_user']) {
                $reponse = ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte'];
                break;
            }

            $ok = User::supprimer($id);
            $reponse = $ok
                ? ['success' => true,  'message' => 'Utilisateur supprimé']
                : ['success' => false, 'message' => 'Erreur suppression'];
            break;

        // ════════════════════════════════════
        // Statistiques dashboard
        // ════════════════════════════════════
        case 'stats':
            $stats   = User::getStats();
            $reponse = ['success' => true, 'stats' => $stats];
            break;
    }

} catch (Exception $e) {
    $reponse = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($reponse);
?>
