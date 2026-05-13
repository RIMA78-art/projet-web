<?php
/**
 * MODELE : Session.php
 * Gestion centralisée et sécurisée des sessions PHP
 */
class Session {

    public static function demarrer() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax'   
            ]);
            session_start();
        }

        
    }

    
    public static function regenerer() {
        session_regenerate_id(true);
    }

    /** Créer la session après connexion réussie */
    public static function connecter($user) {
        self::demarrer();
        self::regenerer(); 
        $_SESSION['user_id']  = $user['id_user'];
        $_SESSION['nom']      = $user['nom'];
        $_SESSION['prenom']   = $user['prenom'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['role']     = $user['role'] ?? 'user';
        $_SESSION['login_at'] = time();
    }

    /** Détruire la session (logout) */
    public static function detruire() {
        self::demarrer();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    
    public static function estConnecte() {
        self::demarrer();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    
    public static function estAdmin() {
        return self::estConnecte() && ($_SESSION['role'] ?? '') === 'admin';
    }

    
    public static function getUtilisateur() {
        if (!self::estConnecte()) return null;
        return [
            'id_user'  => $_SESSION['user_id'],
            'nom'      => $_SESSION['nom'],
            'prenom'   => $_SESSION['prenom'],
            'email'    => $_SESSION['email'],
            'role'     => $_SESSION['role'],
            'login_at' => $_SESSION['login_at'] ?? 0,
        ];
    }

    
    public static function proteger($role = 'user') {
        self::demarrer();
        if (!self::estConnecte()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Veuillez vous connecter pour accéder à cette page',
                'code'    => 401
            ]);
            exit;
        }
        if ($role === 'admin' && !self::estAdmin()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Accès refusé : droits insuffisants',
                'code'    => 403
            ]);
            exit;
        }
    }
}
?>
