<?php
/**
 * MODELE : User.php
 * Toutes les opérations base de données liées aux utilisateurs
 */
require_once __DIR__ . '/config.php';

class User {

    // ── Propriétés ───────────────────────────────────────────────────────────
    private $id_user;
    private $nom;
    private $prenom;
    private $email;
    private $taille;
    private $poids;
    private $objectif;
    private $niveau_sport;
    private $date_inscription;
    private $role;

    public function __construct($data = []) {
        $this->id_user          = $data['id_user']          ?? null;
        $this->nom              = $data['nom']              ?? '';
        $this->prenom           = $data['prenom']           ?? '';
        $this->email            = $data['email']            ?? '';
        $this->taille           = $data['taille']           ?? 0;
        $this->poids            = $data['poids']            ?? 0;
        $this->objectif         = $data['objectif']         ?? '';
        $this->niveau_sport     = $data['niveau_sport']     ?? '';
        $this->date_inscription = $data['date_inscription'] ?? '';
        $this->role             = $data['role']             ?? 'user';
    }

    // ── Getters ──────────────────────────────────────────────────────────────
    public function getIdUser()          { return $this->id_user; }
    public function getNom()             { return $this->nom; }
    public function getPrenom()          { return $this->prenom; }
    public function getEmail()           { return $this->email; }
    public function getTaille()          { return $this->taille; }
    public function getPoids()           { return $this->poids; }
    public function getObjectif()        { return $this->objectif; }
    public function getNiveauSport()     { return $this->niveau_sport; }
    public function getDateInscription() { return $this->date_inscription; }
    public function getRole()            { return $this->role; }

    // ── IMC ──────────────────────────────────────────────────────────────────
    public function calculerIMC() {
        if ($this->taille > 0 && $this->poids > 0) {
            $tailleM = $this->taille / 100;
            return round($this->poids / ($tailleM * $tailleM), 1);
        }
        return 0;
    }

    // ════════════════════════════════════════════════════════════════════════
    // MÉTHODES STATIQUES — Accès base de données
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Trouver un utilisateur par email (avec mot de passe pour vérification)
     */
    public static function trouverParEmail($email) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT id_user, nom, prenom, email, password, taille, poids,
                    objectif, niveau_sport, date_inscription, last_login, last_weight_update,
                    COALESCE(role,'user') AS role
             FROM utilisateur WHERE email = ?"
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Trouver un utilisateur par ID (sans mot de passe)
     */
    public static function trouverParId($id) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT id_user, nom, prenom, email, taille, poids,
                    objectif, niveau_sport, date_inscription, last_login, last_weight_update,
                    COALESCE(role,'user') AS role
             FROM utilisateur WHERE id_user = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Email déjà utilisé ?
     */
    public static function emailExiste($email) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Créer un nouvel utilisateur
     */
    public static function creer($nom, $prenom, $email, $password, $taille, $poids, $objectif, $niveau_sport) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateur
                (nom, prenom, email, password, taille, poids, objectif, niveau_sport, date_inscription, role)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'user')"
        );
        $stmt->execute([
            $nom, $prenom, $email,
            password_hash($password, PASSWORD_DEFAULT),
            $taille, $poids, $objectif, $niveau_sport
        ]);
        return $pdo->lastInsertId();
    }

    /**
     * Mettre à jour le profil (+ enregistrer la mise à jour du poids si changé)
     */
    public static function mettreAJour($id, $nom, $prenom, $taille, $poids, $objectif, $niveau_sport) {
        $pdo  = config::getConnexion();
        
        // Récupérer l'ancien poids pour vérifier s'il a changé
        $oldUser = self::trouverParId($id);
        $poidsChanged = $oldUser && (float)$oldUser['poids'] !== (float)$poids;
        
        // Mettre à jour le profil
        $stmt = $pdo->prepare(
            "UPDATE utilisateur
             SET nom=?, prenom=?, taille=?, poids=?, objectif=?, niveau_sport=?" .
            ($poidsChanged ? ", last_weight_update=NOW()" : "") .
            " WHERE id_user=?"
        );
        return $stmt->execute([$nom, $prenom, $taille, $poids, $objectif, $niveau_sport, $id]);
    }

    /**
     * Supprimer un utilisateur et ses activités
     */
    public static function supprimer($id) {
        $pdo = config::getConnexion();
        $pdo->prepare("DELETE FROM activite WHERE id_user=?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id_user=?");
        return $stmt->execute([$id]);
    }

    /**
     * Tous les utilisateurs (pour admin)
     */
    public static function tousLesUtilisateurs() {
        $pdo  = config::getConnexion();
        $stmt = $pdo->query(
            "SELECT id_user, nom, prenom, email, taille, poids,
                    objectif, niveau_sport, date_inscription, last_login, last_weight_update, role
             FROM utilisateur ORDER BY date_inscription DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Rechercher des utilisateurs (pour admin)
     */
    public static function rechercher($terme) {
        $pdo  = config::getConnexion();
        $like = '%' . $terme . '%';
        $stmt = $pdo->prepare(
            "SELECT id_user, nom, prenom, email, taille, poids,
                    objectif, niveau_sport, date_inscription, last_login, last_weight_update, role
             FROM utilisateur
             WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?
             ORDER BY date_inscription DESC"
        );
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll();
    }

    /**
     * Mettre à jour le mot de passe (changement depuis profil)
     */
    public static function changerMotDePasse($id, $nouveauMotDePasse) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE utilisateur SET password=? WHERE id_user=?");
        return $stmt->execute([password_hash($nouveauMotDePasse, PASSWORD_DEFAULT), $id]);
    }

    // ── Reset mot de passe par email ─────────────────────────────────────────

    public static function definirTokenReset($email) {
        $pdo     = config::getConnexion();
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare("UPDATE utilisateur SET reset_token=?, reset_expires=? WHERE email=?")
            ->execute([$token, $expires, $email]);
        return $token;
    }

    public static function verifierTokenReset($token) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT id_user, email FROM utilisateur
             WHERE reset_token=? AND reset_expires > NOW()"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public static function reinitialiserMotDePasse($token, $nouveauMotDePasse) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "UPDATE utilisateur
             SET password=?, reset_token=NULL, reset_expires=NULL
             WHERE reset_token=?"
        );
        return $stmt->execute([password_hash($nouveauMotDePasse, PASSWORD_DEFAULT), $token]);
    }

    // ── Activités ────────────────────────────────────────────────────────────

    public static function getActivites($id_user) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT id_activite, type_activite, description, date_activite, details
             FROM activite WHERE id_user=? ORDER BY date_activite DESC LIMIT 50"
        );
        $stmt->execute([$id_user]);
        return $stmt->fetchAll();
    }

    public static function ajouterActivite($id_user, $type, $description, $details = null) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO activite (id_user, type_activite, description, date_activite, details)
             VALUES (?, ?, ?, NOW(), ?)"
        );
        return $stmt->execute([$id_user, $type, $description, $details]);
    }

    // ── Stats pour admin ─────────────────────────────────────────────────────

    public static function getStats() {
        $pdo = config::getConnexion();
        return [
            'total'         => $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
            'admins'        => $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='admin'")->fetchColumn(),
            'ce_mois'       => $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE MONTH(date_inscription)=MONTH(NOW()) AND YEAR(date_inscription)=YEAR(NOW())")->fetchColumn(),
            'total_activites' => $pdo->query("SELECT COUNT(*) FROM activite")->fetchColumn(),
        ];
    }


    // ════════════════════════════════════════════════════════════════════════
    // GOOGLE OAUTH — Méthodes pour la connexion via Google
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Chercher un utilisateur par son google_id
     * Utilisé à chaque connexion Google pour retrouver le compte lié
     */
    public static function trouverParGoogleId(string $googleId): ?array {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "SELECT id_user, nom, prenom, email, taille, poids,
                    objectif, niveau_sport, date_inscription,
                    COALESCE(role,'user') AS role, google_id
             FROM utilisateur WHERE google_id = ?"
        );
        $stmt->execute([$googleId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lier un compte existant (trouvé par email) à un google_id
     * Cas : l'utilisateur avait un compte email/password avant de se connecter via Google
     */
    public static function lierGoogleId(int $idUser, string $googleId): void {
        $pdo = config::getConnexion();
        $pdo->prepare("UPDATE utilisateur SET google_id = ? WHERE id_user = ?")
            ->execute([$googleId, $idUser]);
    }

    /**
     * Créer un compte depuis les données Google (sans mot de passe)
     * Le champ password est laissé vide — l'utilisateur ne peut se connecter qu'avec Google
     */
    public static function creerViaGoogle(string $googleId, string $email, string $prenom, string $nom): int {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateur
                (google_id, nom, prenom, email, password, taille, poids, objectif, niveau_sport, date_inscription, role)
             VALUES (?, ?, ?, ?, '', 0, 0, '', '', NOW(), 'user')"
        );
        $stmt->execute([$googleId, $nom, $prenom, $email]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Point d'entrée principal pour la connexion Google
     *
     * Logique :
     *  1. L'utilisateur a déjà ce google_id → connexion directe
     *  2. L'email existe mais pas de google_id → lier le compte + connexion
     *  3. Ni l'un ni l'autre → créer un nouveau compte + connexion
     *
     * @param array $googleUser Données reçues de l'API Google (id, email, given_name, family_name)
     * @return array|null Données utilisateur pour Session::connecter()
     */
    public static function trouverOuCreerViaGoogle(array $googleUser): ?array {
        $googleId = $googleUser['id']          ?? '';
        $email    = $googleUser['email']       ?? '';
        $prenom   = $googleUser['given_name']  ?? '';
        $nom      = $googleUser['family_name'] ?? $googleUser['name'] ?? '';

        if (empty($googleId) || empty($email)) return null;

        // Cas 1 : compte déjà lié à ce google_id
        $user = self::trouverParGoogleId($googleId);
        if ($user) return $user;

        // Cas 2 : email existe mais pas encore lié à Google
        $userParEmail = self::trouverParEmail($email);
        if ($userParEmail) {
            self::lierGoogleId($userParEmail['id_user'], $googleId);
            return self::trouverParId($userParEmail['id_user']);
        }

        // Cas 3 : premier login Google, créer le compte
        $newId = self::creerViaGoogle($googleId, $email, $prenom, $nom);
        return self::trouverParId($newId);
    }

    // ════════════════════════════════════════════════════════════════════════
    // SYSTÈME DE SCORING ET BADGES — Actif / Modéré / Inactif
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Mettre à jour last_login pour l'utilisateur
     * À appeler à chaque connexion/authentification
     */
    public static function mettreAJourDerniereConnexion($id_user) {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("UPDATE utilisateur SET last_login=NOW() WHERE id_user=?");
        return $stmt->execute([$id_user]);
    }

    /**
     * Calculer le score utilisateur (0-100)
     * Basé sur : activité récente, complétude du profil, mise à jour poids, ancienneté
     */
    public static function calculerScore($userData): int {
        $score = 0;

        // ── DERNIÈRE CONNEXION (max 30 points) ──────────────────────────────
        if (!empty($userData['last_login'])) {
            $lastLogin = new DateTime($userData['last_login']);
            $now       = new DateTime();
            $jours     = $now->diff($lastLogin)->days;
            
            if ($jours < 2)       $score += 30;  // Très actif
            elseif ($jours < 7)   $score += 20;  // Actif
            else                  $score += 5;   // Peu actif
        } else {
            $score += 0;  // Jamais connecté
        }

        // ── COMPLÉTUDE DU PROFIL (max 20 points) ────────────────────────────
        $champsMandatoires = [
            'nom' => !empty($userData['nom']),
            'prenom' => !empty($userData['prenom']),
            'email' => !empty($userData['email']),
            'objectif' => !empty($userData['objectif']),
            'niveau_sport' => !empty($userData['niveau_sport']),
            'taille' => (int)($userData['taille'] ?? 0) > 0,
            'poids' => (float)($userData['poids'] ?? 0) > 0
        ];
        
        $champsCo = array_sum($champsMandatoires);
        $totalChamps = count($champsMandatoires);
        
        if ($champsCo === $totalChamps)       $score += 20;  // Profil complet
        elseif ($champsCo >= $totalChamps/2)  $score += 10;  // Profil partiel
        else                                   $score += 0;   // Profil vide

        // ── MISE À JOUR POIDS (max 30 points) ───────────────────────────────
        if (!empty($userData['last_weight_update'])) {
            $lastUpdate = new DateTime($userData['last_weight_update']);
            $now        = new DateTime();
            $jours      = $now->diff($lastUpdate)->days;
            
            if ($jours < 7)       $score += 30;  // À jour récemment
            elseif ($jours < 30)  $score += 15;  // À jour ce mois
            else                  $score += 0;   // Pas à jour
        } else {
            $score += 0;  // Jamais mis à jour
        }

        // ── ANCIENNETÉ (max 20 points) ──────────────────────────────────────
        if (!empty($userData['date_inscription'])) {
            $dateInscription = new DateTime($userData['date_inscription']);
            $now             = new DateTime();
            $jours           = $now->diff($dateInscription)->days;
            
            if ($jours >= 30)     $score += 20;  // Plus d'un mois
            else                  $score += 10;  // Nouveau (< 30j)
        } else {
            $score += 10;  // Par défaut
        }

        return min($score, 100);  // Capper à 100
    }

    /**
     * Obtenir le badge de statut basé sur le score
     * @param int $score Score utilisateur (0-100)
     * @return array ['emoji' => '🔥', 'label' => 'Actif', 'css_class' => 'badge-active']
     */
    public static function obtenirBadge($score): array {
        if ($score >= 70) {
            return [
                'emoji' => '🔥',
                'label' => 'Actif',
                'css_class' => 'badge-active',
                'description' => 'Utilisateur très engagé'
            ];
        } elseif ($score >= 40) {
            return [
                'emoji' => '⚡',
                'label' => 'Modéré',
                'css_class' => 'badge-moderate',
                'description' => 'Utilisateur modérément engagé'
            ];
        } else {
            return [
                'emoji' => '💤',
                'label' => 'Inactif',
                'css_class' => 'badge-inactive',
                'description' => 'Utilisateur peu engagé'
            ];
        }
    }

    /**
     * Obtenir les informations complètes utilisateur avec score et badge
     */
    public static function obtenirAvecScoreBadge($id): ?array {
        $user = self::trouverParId($id);
        if (!$user) return null;
        
        $score = self::calculerScore($user);
        $badge = self::obtenirBadge($score);
        
        return array_merge($user, [
            'score' => $score,
            'badge' => $badge
        ]);
    }
}
?>
