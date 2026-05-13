<?php
require_once __DIR__ . '/config.php';

class Panier {
    // ── Ajouter au panier ─────────────────────────────────────────────
    public static function ajouter($data) {
        $userEmail = trim($data['user_email'] ?? '');
        $nom = trim($data['nom'] ?? '');
        $prix = floatval($data['prix'] ?? 0);
        $description = trim($data['description'] ?? '');

        if ($nom === '' || $prix < 0)
            return ['success' => false, 'error' => 'Nom du produit et prix valide requis'];

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO panier (user_email, Nom, Prix, Description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userEmail, $nom, $prix, $description]);
        return ['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Produit ajouté au panier'];
    }

    // ── Récupérer le panier d'un utilisateur ──────────────────────────
    public static function parEmail($email) {
        $pdo = config::getConnexion();
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id, user_email, Nom, Prix, Description, created_at FROM panier WHERE user_email = ? ORDER BY created_at DESC");
            $stmt->execute([trim($email)]);
        } else {
            $stmt = $pdo->query("SELECT id, user_email, Nom, Prix, Description, created_at FROM panier ORDER BY created_at DESC");
        }
        return $stmt->fetchAll();
    }

    // ── Supprimer un élément du panier ────────────────────────────────
    public static function supprimer($id, $email = '') {
        $pdo = config::getConnexion();
        $id = intval($id);
        if (!$id) return ['success' => false, 'error' => 'ID requis'];

        if (!empty($email)) {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND user_email = ?");
            $stmt->execute([$id, trim($email)]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->rowCount() > 0
            ? ['success' => true, 'message' => 'Produit retiré du panier']
            : ['success' => false, 'error' => 'Produit non trouvé dans le panier'];
    }

    // ── Supprimer par nom et prix ─────────────────────────────────────
    public static function supprimerParNomPrix($nom, $prix, $email = '') {
        $pdo = config::getConnexion();
        if (!empty($email)) {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE Nom = ? AND Prix = ? AND user_email = ? LIMIT 1");
            $stmt->execute([trim($nom), floatval($prix), trim($email)]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE Nom = ? AND Prix = ? LIMIT 1");
            $stmt->execute([trim($nom), floatval($prix)]);
        }
        return $stmt->rowCount() > 0
            ? ['success' => true, 'message' => 'Produit retiré du panier']
            : ['success' => false, 'error' => 'Produit non trouvé'];
    }

    // ── Vider le panier d'un utilisateur ──────────────────────────────
    public static function vider($email) {
        $pdo = config::getConnexion();
        if (!empty($email)) {
            $stmt = $pdo->prepare("DELETE FROM panier WHERE user_email = ?");
            $stmt->execute([trim($email)]);
        } else {
            $pdo->exec("DELETE FROM panier");
        }
        return ['success' => true, 'message' => 'Panier vidé'];
    }
}
