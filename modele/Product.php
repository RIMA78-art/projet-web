<?php
require_once __DIR__ . '/config.php';

class Product {
    // ── Ajouter un produit ────────────────────────────────────────────
    public static function ajouter($data) {
        $nom = trim($data['nom'] ?? '');
        $prix = floatval($data['prix'] ?? 0);
        $description = trim($data['description'] ?? '');
        $rawCat = trim($data['categorie'] ?? 'complement');
        $allowed = ['bio', 'complement', 'sport', 'accessoire'];
        $categorie = in_array($rawCat, $allowed, true) ? $rawCat : 'complement';

        if ($nom === '' || $prix <= 0)
            return ['success' => false, 'error' => 'Nom du produit et prix valide requis'];
        if ($prix > 200)
            return ['success' => false, 'error' => 'Le prix ne peut pas dépasser 200'];
        if (self::nomExiste($nom))
            return ['success' => false, 'error' => 'Un produit avec ce nom existe déjà'];

        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO boutique_products (nom, prix, description, categorie) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prix, $description, $categorie]);
        return ['success' => true, 'id' => $pdo->lastInsertId(), 'message' => 'Produit ajouté avec succès'];
    }

    // ── Tous les produits ─────────────────────────────────────────────
    public static function tousLesProduits() {
        $pdo = config::getConnexion();
        $stmt = $pdo->query("SELECT id, nom, prix, description, categorie, created_at FROM boutique_products ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // ── Mettre à jour un produit ──────────────────────────────────────
    public static function mettreAJour($id, $data) {
        $nom = trim($data['nom'] ?? '');
        $prix = floatval($data['prix'] ?? 0);
        $description = trim($data['description'] ?? '');
        $rawCat = trim($data['categorie'] ?? 'complement');
        $allowed = ['bio', 'complement', 'sport', 'accessoire'];
        $categorie = in_array($rawCat, $allowed, true) ? $rawCat : 'complement';

        if ($nom === '' || $prix <= 0)
            return ['success' => false, 'error' => 'Nom du produit et prix valide requis'];
        if ($prix > 200)
            return ['success' => false, 'error' => 'Le prix ne peut pas dépasser 200'];

        $pdo = config::getConnexion();
        // Vérifier doublon nom (sauf lui-même)
        $check = $pdo->prepare("SELECT id FROM boutique_products WHERE LOWER(nom) = LOWER(?) AND id != ?");
        $check->execute([$nom, intval($id)]);
        if ($check->fetch())
            return ['success' => false, 'error' => 'Un autre produit avec ce nom existe déjà'];

        $stmt = $pdo->prepare("UPDATE boutique_products SET nom = ?, prix = ?, description = ?, categorie = ? WHERE id = ?");
        $stmt->execute([$nom, $prix, $description, $categorie, intval($id)]);
        return ['success' => true, 'message' => 'Produit mis à jour'];
    }

    // ── Supprimer un produit ──────────────────────────────────────────
    public static function supprimer($id) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM boutique_products WHERE id = ?");
        $stmt->execute([intval($id)]);
        return $stmt->rowCount() > 0;
    }

    // ── Nom existe déjà ? ─────────────────────────────────────────────
    private static function nomExiste($nom) {
        $pdo = config::getConnexion();
        $stmt = $pdo->prepare("SELECT id FROM boutique_products WHERE LOWER(nom) = LOWER(?) LIMIT 1");
        $stmt->execute([$nom]);
        return (bool) $stmt->fetch();
    }
}
